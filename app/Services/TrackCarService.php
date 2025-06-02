<?php

namespace App\Services;

use App\Helpers\Helpers;
use App\Models\Device;
use App\Models\Vehicle;
use App\Services\Statistics\UserService;
use Aws\Textract\TextractClient;
use Aws\Exception\AwsException;
use Aws\Credentials\Credentials;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
class TrackCarService
{

    private $textract;
    protected $baseUrl;
    private $currentUser;

    /**
     * Create Amazon Textract Client
     */
    public function __construct()
    {
        $this->baseUrl = config('services.traccar.url').'/'; // Ajouter l'URL dans config/services.php
        $this->currentUser = Helpers::getCurrentUser();
    }

    private function request($method, $endpoint, $data = [])
    {
        return Http::withBasicAuth($this->currentUser->email, $this->currentUser->password_traccar)
            ->$method("{$this->baseUrl}/api{$endpoint}", $data)
            ->json();
    }

    /**
     * Définir le SpeedLimit global
     */
    public function setGlobalSpeedLimit($all)
    {
        $url = "{$this->baseUrl}api/server";
        $speedLimit = $this->kmhToKnots($all['attributes']['speedLimit']); // Convertir 50 km/h en nœuds (~27.02 nd)
        $all['attributes']['speedLimit'] = $speedLimit;

        $response = Http::withBasicAuth($this->currentUser->email, $this->currentUser->password_traccar)
            ->put("{$url}",$all);
        return $response->json();
    }



    function kmhToKnots($kmh) {

        return $kmh / 1.852;
    }

    /**
     * Définir le SpeedLimit pour un véhicule spécifique
     */
    public function setDeviceSpeedLimit($deviceId, $all)
    {
        return $this->request('put', "/devices/{$deviceId}", $all);
    }

    /**
     * Tester la cnx vers le serveur TracCar
     * @return mixed
     */
    public function authenticate()
    {
        return Http::withBasicAuth($this->currentUser->email, $this->currentUser->password_traccar)
            ->get("{$this->baseUrl}/api/session")
            ->json();
    }

    /**
     * Obtenir toute la liste des Apareil
     * @param $email
     * @param $password
     * @return mixed
     */
    public function getDevices($params, $deviceId)
    {
        $url = "{$this->baseUrl}api/devices";
        if ($deviceId) {
            $url .= '/' . $deviceId;
        }
        return Http::withBasicAuth($this->currentUser->email, $this->currentUser->password_traccar)
            ->get("{$url}")
            ->json();
    }

    // Method to get the current position (location) of a device
    public function getDeviceLocation($deviceId)
    {

        $url = "{$this->baseUrl}api/positions?deviceId={$deviceId}";
        $response = Http::withBasicAuth($this->currentUser->email, $this->currentUser->password_traccar)
            ->get("{$url}");

//        if($deviceId == 42){
//             dd($response->json());
//        }

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     *  Les poition de tous les devices
     * @return mixed
     */
    public function getPositions($deviceId,$from = null,$to = null)
    {
        $url = "{$this->baseUrl}api/positions?";

        if ($deviceId) {
            $url .= 'deviceId=' . $deviceId;
        }
        if ($from) {
            $url .= '&from=' . $from;
        }
        if ($to) {
            $url .= '&to=' . $to;
        }

        return Http::withBasicAuth($this->currentUser->email, $this->currentUser->password_traccar)->withHeaders([
            'Accept' => 'application/json', // Ensure the API returns JSON
        ])->get("{$url}")->json();
    }

    /**
     * Inérer une position pour une device
     * Attention TracCar n'autorise pas l'insertion direct d'une position
     * @param $deviceId
     * @param $laltitude
     * @param $longtitude
     * @return mixed
     */
    public function insertPosition($deviceId,$laltitude,$longtitude)
    {

        $url = "{$this->baseUrl}api/positions";

        $data = [
            'deviceId' => $deviceId,
            'latitude' => $laltitude,
            'longitude' => $longtitude,
            'valid' => true,
            'attributes' => [],
        ];

        $response =  Http::withBasicAuth($this->currentUser->email, $this->currentUser->password_traccar)
            ->post("{$url}",$data);

        Log::info('Insertion position '. $response);

        return $response;
    }

    /**
     * ça modifie juste l'attribit long & lat but no effect on the positon inside the MAP
     * @param $deviceId
     * @param $latitude
     * @param $longitude
     * @return mixed
     */
    public function updateDevicePosition($deviceId, $latitude, $longitude)
    {

        $traccarApiUrl = "{$this->baseUrl}api/devices/$deviceId";

        $response = Http::withBasicAuth($this->currentUser->email,$this->currentUser->password_traccar)
            ->put($traccarApiUrl, [
                'id' => $deviceId,
                'name' => 'test',
                'uniqueId' => '1231231312',
                'attributes' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ],
            ]);

        return $response->json();
    }

    /**
     * Traccar n'autorise pas l'insertion d'une postion
     * @param $deviceId
     * @param $latitude
     * @param $longitude
     * @return string
     */
    function addPositionToDevice($deviceId, $latitude, $longitude)
    {
        // Étape 1 : Ajouter une position
        $positionResponse = Http::withBasicAuth($this->currentUser->email,$this->currentUser->password_traccar)
            ->post( "{$this->baseUrl}api/positions", [
                'protocol' => 'manual',
                'deviceId' => $deviceId,
                'serverTime' => now(),
                'deviceTime' => now(),
                'fixTime' => now(),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'altitude' => 0,
                'speed' => 0,
                'course' => 0,
                'attributes' => new \stdClass(),
            ]);

        if ($positionResponse->failed()) {
            return 'Error creating position: ' . $positionResponse->body();
        }

        $positionId = $positionResponse->json()['id'];

        // Étape 2 : Associer cette position au device
        $deviceResponse = Http::withBasicAuth(env('TRACCAR_USER'), env('TRACCAR_PASSWORD'))
            ->put("{$this->baseUrl}api/devices/$deviceId", [
                'id' => $deviceId,
                'positionId' => $positionId,
            ]);

        if ($deviceResponse->failed()) {
            return 'Error updating device: ' . $deviceResponse->body();
        }

        return 'Position added and device updated successfully.';
    }

    /**
     * Ajouter direcement sur la base TracCar  (Meilleur Methode puisque Traccar ne fournit pas la possibilité dajouter une postion)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addPositionDirectlyInTheBase($deviceId,$latitude,$longitude,$altiude = 0,$speed = 0 ,$timestamp = null,$corse = 0)
    {

        // Astuce : Simuler une position depuis un appareil GPS
        // curl -X GET "http://your-traccar-server:5055?id=123456&lat=36.8065&lon=10.1815&speed=0"

        // Insérer la position directement dans la base de données Traccar
        $positionId = DB::connection('traccar')->table('positions')->insertGetId([
            'protocol' => 'osmand',
            'deviceid' => $deviceId,
            'servertime' => $timestamp ? $timestamp :  now(),
            'devicetime' => $timestamp ? $timestamp :  now(),
            'fixtime' => $timestamp ? $timestamp :  now(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'altitude' => $altiude,
            'speed' => $speed,
            'valid' => 1,
            'course' => $corse,
            'accuracy' => 5,
            'attributes' => '{"sat":12,"distance":0.09896745887096436,"totalDistance":1.0250650190759915E8,"motion":false}',
        ]);

        // Associer la position au device
        DB::connection('traccar')->table('devices')->where('id', $deviceId)->update([
            'positionid' => $positionId,
        ]);

        return response()->json(['message' => 'Position added successfully.']);
    }


    public function simulateGetCurrentPosition($deviceId = null,$ajax = true){
        $traccarUrl = 'https://gps-server.bensassiridha.com/api/reports/route?';
        $traccarUser = 'bensassiridha1@gmail.com';
        $traccarPassword = 'admin';

        // Exemple de trajet :
        $trajets = [
            '42'=>['name'=>'','adresse_depart' => '','adresse_fin' => '','from' => '2025-03-24T11:53:02.000Z','to' => '2025-03-24T13:42:42.000Z','durée' => '','note' => ''], // BMX AKREMA
            '34'=>['name'=>'Tunis-Ariana','adresse_depart' => 'Rue meftah saadalh','adresse_fin' => 'Rouad Ariana','from' => '2025-03-25T10:21:59.000Z','to' => '2025-03-25T10:59:00.000Z','durée' => '37min','note' => ''], // VW T ROC
            '33'=>['name'=>'Tunis-tastous Beja','adresse_depart' => '3 Rue 3001 Tunis','adresse_fin' => 'Tasteur Béja','from' => '2025-03-25T10:21:59.000Z','to' => '2025-03-25T11:52:00.000Z','durée' => '1h44','note' => ''], // VW T ROC
            '3'=>['name'=>'testous Beja - Tunis','adresse_depart' => 'Testour Béja','adresse_fin' => 'Tunis','from' => '2025-03-24T12:54:15.000Z','to' => '2025-03-24T15:04:55.000Z','durée' => '2h00','note' => ''], // VW T ROC
        ];

        $tab = [];
        if ($deviceId){
            $tab = [$deviceId];
        }else{
            $tab = array_keys($trajets);
        }

        foreach ($tab as $deviceID){
            // Récupérer la position suivante
            $positions = Cache::get("historique_$deviceId");
            $index = Cache::get("index_$deviceId", 0);

            // voir si la simulation en temp réel se termine on réinitialise l'index
            if ($positions && $index >= count($positions) && count($positions) > 0) {
                Cache::forget("index_$deviceId");
                // return response()->json(['end' => true]); // Fin de la simulation
                // router('/reset-index/'.$deviceId)
            }


            // Charger l’historique une seule fois et le stocker en cache pour éviter des appels inutiles
            if (!Cache::has("historique_$deviceId") && isset($trajets[$deviceId])) {
                $startDate = now()->subDay()->toISOString(); // Par défaut, les dernières 24h
                $endDate = now()->toISOString();

                if ($deviceId) {
                    $traccarUrl .= 'deviceId='.'42';
                }
                if ($startDate) {
                    $traccarUrl .= '&from=' . $trajets[$deviceId]['from'];
                }
                if ($endDate) {
                    $traccarUrl .= '&to=' . $trajets[$deviceId]['to'];
                }

                $response = Http::withBasicAuth($traccarUser, $traccarPassword)->withHeaders([
                    'Accept' => 'application/json', // Ensure the API returns JSON
                ])->get($traccarUrl);

                if ($response->successful()) {
                    $positions = $response->json();
                    Cache::put("historique_$deviceId", $positions, 3600); // Cache pour 1h
                } else {
                    if ($ajax){
                        return response()->json(['error' => 'Impossible de récupérer l’historique'], 500);
                    }else{
                        return 'Impossible de récupérer l’historique';
                    }
                }
            }

            if ($positions && count($positions) > 0 && isset( $positions[$index])){
                $position = $positions[$index];
                Cache::put("index_$deviceId", $index + 1, 3600); // Incrémenter l’index

                if ($ajax){
                    return response()->json($position);

                }else{
                    return $position;
                }
            }else{
                // si on'a pas un trajet de simulation pour l'appareil on récupére sa position depuis TracCar
                $traccarService = new \App\Services\TrackCarService();
                $positon = $traccarService->getPositions($deviceId);
                if (isset($positon[0])){
                    if ($ajax){
                        return response()->json($positon[0]);
                    }else{
                        return $positon[0];
                    }
                }
                else
                    return response()->json('Pas de position pour le device N° '.$deviceId);
            }
        }
    }

    /**
     *  Récupérer les trajet pour une période et pour une liste de devices
     * @param $params
     * @return mixed
     */
    public function getTrips($deviceId, $from, $to,$export = false)
    {

        $url = "{$this->baseUrl}api/reports/trips?";
        if ($deviceId) {
            $url .= 'deviceId=' . $deviceId;
        }
        if ($from) {
            $url .= '&from=' . Helpers::convertToISO8601($from);
        }
        if ($to) {
            $url .= '&to=' . Helpers::convertToISO8601($to);
        }

        $response =  Http::withBasicAuth($this->currentUser->email, $this->currentUser->password_traccar);

        if (!$export){
            $response = $response->withHeaders([
                'Accept' => 'application/json', // Ensure the API returns JSON
            ])->get("{$url}")->json();

        }else{
            $response = $response->get("{$url}");
        }

        return $response;
    }


    /**
     *  Récupérer les trajet pour une période et pour une liste de devices
     * @param $params
     * @return mixed
     */
    public function getStops($deviceID,$from,$to)
    {

        $url = "{$this->baseUrl}api/reports/stops?";

        if ($deviceID) {
            $url .= 'deviceId=' . $deviceID;
        }
        if ($from) {
            $url .= '&from=' . Helpers::convertToISO8601($from);
        }
        if ($to) {
            $url .= '&to=' . Helpers::convertToISO8601($to);
        }

        return Http::withBasicAuth($this->currentUser->email, $this->currentUser->password_traccar)->withHeaders([
            'Accept' => 'application/json', // Ensure the API returns JSON
        ])
            ->get("{$url}")
            ->json();
    }

    /**
     *  Récupérer les trajet pour une période et pour une liste de devices
     * @param $params
     * @return mixed
     */
    public function getRoute($params)
    {

        $url = "{$this->baseUrl}api/reports/route?";

        if ($params->has('deviceId')) {
            $url .= 'deviceId=' . $params->deviceId;
        }
        if ($params->has('from')) {
            $url .= '&from=' . $params->from;
        }
        if ($params->has('to')) {
            $url .= '&to=' . $params->to;
        }

        return Http::withBasicAuth($this->currentUser->email, $this->currentUser->password_traccar)->withHeaders([
            'Accept' => 'application/json', // Ensure the API returns JSON
        ])
            ->get("{$url}")
            ->json();
    }

    /**
     *  Récupérer les trajet pour une période et pour une liste de devices
     * @param $params
     * @return mixed
     */
    public function getEvents($deviceId,$type,$from,$to)
    {
        $url = "{$this->baseUrl}api/reports/events?";
        if ($deviceId) {
            $url .= 'deviceId=' . $deviceId;
        }
        if ($type) {
            $url .= '&type=' . $type;
        }
        if ($from) {
            $url .= '&from=' . Helpers::convertToISO8601($from);
        }
        if ($to) {
            $url .= '&to=' . Helpers::convertToISO8601($to);
        }

        return Http::withBasicAuth($this->currentUser->email, $this->currentUser->password_traccar)
            ->get("{$url}")
            ->json();
    }

    /**
     *  Récupérer les trajet pour une période et pour une liste de devices
     * @param $params
     * @return mixed
     */
    public function getSummary($deviceId, $from, $to, $daily = 'false')
    {

        $url = "{$this->baseUrl}api/reports/summary?";
        if ($from) {
            $url .= 'from=' . urlencode(Helpers::convertToISO8601($from));
        }
        if ($to) {
            $url .= '&to=' . urlencode(Helpers::convertToISO8601($to));
        }

         $url .= '&daily=' . ($daily == "true" ? 'true' : 'false');

        if ($deviceId) {
            $url .= '&deviceId=' . $deviceId;
        }

        return Http::withBasicAuth($this->currentUser->email, $this->currentUser->password_traccar)->withHeaders([
            'Accept' => 'application/json', // Ensure the API returns JSON
        ])->get("{$url}")->json();
    }


    /**
     * Obtenir toute la liste des Apareil
     * @param $email
     * @param $password
     * @return mixed
     */
    public function getGeofences($prams, $id)
    {
        $url = "{$this->baseUrl}api/geofences";
        if ($id) {
            $url .= '/' . $id;
        }
        $response = Http::withBasicAuth($this->currentUser->email, $this->currentUser->password_traccar)
            ->get("{$url}")
            ->json();
        // Pour chaque geofence voir s'il est associé à une voiture
        foreach ($response as $key => $geofence){
            // Si la géofence est privé est associé à un seul vehiclule
            if (isset($geofence['attributes']['device_id'])){
                $device = Device::with('vehicle')->where('traccar_device_id',$geofence['attributes']['device_id'])->first();
                $response[$key]['car'] = $device->vehicle;
                if ($prams['device_id'] &&  $prams['device_id'] != "null"  && $geofence['attributes']['device_id'] != $prams['device_id']){
                    unset($response[$key]);
                }
            }
        }
        return $response;
    }


    /**
     * Obtenir toute la liste des Apareil
     * @param $email
     * @param $password
     * @return mixed
     */
    public function deleteGeofence($id)
    {
        $url = "{$this->baseUrl}api/geofences";

        if ($id) {
            $url .= '/' . $id;
        }

        return Http::withBasicAuth($this->currentUser->email, $this->currentUser->password_traccar)
            ->delete("{$url}")
            ->json();
    }


    /**
     * Obtenir toute la liste des Apareil
     * @param $email
     * @param $password
     * @return mixed
     */
    public function updateGeofence($geofence,$id)
    {
        $url = "{$this->baseUrl}api/geofences";

        if ($id) {
            $url .= '/' . $id;
        }

        $response = Http::withBasicAuth($this->currentUser->email, $this->currentUser->password_traccar)
            ->put("{$url}",[
                'id' => $geofence['id'],
                'name'=> $geofence['name'],
                'area'=> $geofence['area'],
                'description'=> 'tesssst',
                'attributes' => ['show'=>$geofence['isVisible'],
                    'global'=>$geofence['isGlobal'],
                    'date'=>  Carbon::now()->format('d-m-Y') ,
                    'device_id'=>$geofence['device_id'],
                     'intensity' => 1 , // on zoom ou non par exemple si quite la tunisie
                    'actions' => isset($geofence['actions']) ?  implode(',',$geofence['actions']) : '1',  // Par défaut Juste alerter
                    'zoom_in' => $geofence['zoom_in'] ?? null
                ],
            ])
            ->json();

        return $response ;
    }


    /**
     * Enregistrer une Geofence
     * @param $prams
     * @return mixed
     */
    public function saveGeofence($prams){
        $url = "{$this->baseUrl}api/geofences";

        // From Admin
        if (isset($prams['vehicle_id'])){
            $vehicle = Vehicle::with('device')->find($prams['vehicle_id']);
            $prams['device_id'] = $vehicle->device->traccar_device_id;
        }

     $response =  Http::withBasicAuth($this->currentUser->email, $this->currentUser->password_traccar)
         ->post("{$url}",['name'=> $prams['name'],
             'area'=> $prams['area'],
             'description'=> 'tesssst',
             'attributes' => ['show'=>$prams['isVisible'],
                 'global'=>$prams['isGlobal'],
                 'date'=>  Carbon::now()->format('d-m-Y') ,
                 'device_id'=>$prams['device_id'] ?? null,
                 'intensity' => 1 , // on zoom ou non par exemple si quite la tunisie
                 'actions' => isset($prams['actions']) ?  implode(',',$prams['actions']) : '1',  // Par défaut Juste alerter
                 'zoom_in' => $prams['zoom_in'] ?? null
             ],
         ])
         ->json();

        return $response;
    }

    public function getClient()
    {
        return $this->textract;
    }


    /**
     * Get Current Long Altituse from specified device with or not adresse
     * @param $deviceId
     * $withAdresse : Attetion si tu met à true tu vas cosommer plus de sole sur geocoding
     * @return array|null
     * @throws \Exception
     */
    function getCurrentLocation($deviceId,$withAdresse = false)
    {

        $response = Http::withBasicAuth($this->currentUser->email, $this->currentUser->password_traccar)
            ->get("{$this->baseUrl}api/positions");

        if ($response->successful()) {
            $positions = $response->json();

            // Find position for the given device ID
            foreach ($positions as $position) {
                if ($position['deviceId'] == $deviceId) {
                    if ($withAdresse){
                        $position['adresse'] = $this->getAddressFromCoordinatesUsingGoogle($position['latitude'], $position['longitude']);
                    }
                    return $position;
                }
            }

            return null; // No position found for the device
        }

        throw new \Exception('Failed to fetch positions from Traccar.');
    }


    /**
     * Geocoding with Google
     * @param $latitude
     * @param $longitude
     * @return string
     * @throws \Exception
     */
    function getAddressFromCoordinatesUsingGoogle($latitude, $longitude,$JsonFormat = false)
    {
        $googleApiKey = env('GOOGLE_MAPS_API_KEY'); // Google Maps API Key
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&key=$googleApiKey";

        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            if (!empty($data['results' ])) {
                if ($JsonFormat ){

                    return  $data['results'][0]['formatted_address'] ;
                }else{
                    return $data['results'][0]['formatted_address'];
                }

            }

            return 'No address found';
        }

        throw new \Exception('Failed to fetch address from Google Maps.');
    }


    /**
     * Use TracCarServer to get Adresse From Coordinates
     * @param $latitude
     * @param $longitude
     * @return mixed|null
     * @throws \Exception
     */
    function getAddressFromCoordinatesUsingTracCarServer($latitude, $longitude)
    {

        $response = Http::withBasicAuth($this->currentUser->email, $this->currentUser->password_traccar)
            ->get("{$this->baseUrl}geocode?lat={$latitude}&lon={$longitude}");


        if ($response->successful()) {
            return $response->json();
        }



        throw new \Exception('Failed to fetch adresse from Traccar.');
    }

    /**
     * Get Adresse Using OPENCAGE
     * @param $latitude
     * @param $longitude
     * @return mixed|string
     * @throws \Exception
     */
    function getAddressFromCoordinates($latitude, $longitude)
    {
        $openCageKey = env('OPENCAGE_API_KEY'); // Your OpenCage API Key
        $url = "https://api.opencagedata.com/geocode/v1/json?q=$latitude+$longitude&key=$openCageKey";

        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();

            if (!empty($data['results'])) {
                return $data['results'][0]['formatted']; // Return the formatted address
            }

            return 'No address found';
        }

        throw new \Exception('Failed to fetch address from OpenCage.');
    }

    /**
     * Geocoding With  openstreetmap
     * @param $latitude
     * @param $longitude
     * @return string
     * @throws \Exception
     */
    function getAddressFromCoordinatesOpenStreetMap($latitude, $longitude)
    {
        $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=$latitude&lon=$longitude";

        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            return $data['display_name'] ?? 'No address found';
        }

        return null;
    }


    /**
     *  Récupérer l'adresse en cours
     * @param $deviceId
     * @return array|string
     * @throws \Exception
     */
    function getCurrentAddress($deviceId)
    {
        // Get current location from Traccar
        $location = $this->getCurrentLocation($deviceId);

        if ($location) {
            // Retrieve address using reverse geocoding
            $address = $this->getAddressFromCoordinatesUsingGoogle($location['latitude'], $location['longitude']);
            return [
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'address' => $address,
            ];
        }

        return null;
    }


    /**
     * Calculer le temps total en mouvement pour une période donnée.
     * @param int $deviceId
     * @param string $from
     * @param string $to
     * @return int Temps total en mouvement (en secondes)
     */
    public function getMotionTimeFromTrips($deviceId, $from, $to,$trips = null)
    {

        if (!$trips){
            $trips = $this->getTrips($deviceId, $from, $to);
        }

        $output = ['nbrTrajet' => 0, 'durration' => 0];

        if ($trips) {

            $output['nbrTrajet'] = count($trips);
            if (!empty($trips)) {
                // Calculer le temps total en mouvement
                $totalMotionTime = 0;

                foreach ($trips as $trip) {
                    if (isset($trip['duration'])) {
                        $totalMotionTime += $trip['duration'];
                    }
                }

                $output['durration'] = $this->formatDuration(intval($totalMotionTime / 1000));
                // Convertir de millisecondes en secondes
                return $output;
            }
        }

        // Retourner 0 si aucune donnée n'est trouvée
        return $output;
    }

    /**
     * Convertir le temps en secondes en une durée lisible par l'homme
     * @param int $seconds
     * @return string
     */
    public function formatDuration(int $seconds): string
    {
        $minutes = $seconds / 60;
        $hours = floor($minutes / 60);
        $days = floor($hours / 24);
        $months = floor($days / 30);

        $remainingDays = $days % 30;
        $remainingHours = $hours % 24;
        $remainingMinutes = $minutes % 60;

        $formattedDuration = '';

        if ($months > 0) {
            $formattedDuration .= $months . ' mois ';
        }

        if ($remainingDays > 0) {
            $formattedDuration .= $remainingDays . ' jrs ';
        }

        if ($remainingHours > 0) {
            $formattedDuration .= $remainingHours . ' hrs ';
        }

        if ($remainingMinutes > 0) {
            $formattedDuration .= $remainingMinutes . ' mnt';
        }

        return trim($formattedDuration);
    }


    /**
     * Calculer le coût total de l'essence consommée sur une période donnée
     *
     * @param int $deviceId
     * @param string $from
     * @param string $to
     * @param float $fuelConsumption Consommation moyenne (litres par 100 km)
     * @param float $fuelPrice Prix du litre de carburant
     * @return float
     */
    public function calculateFuelCost(int $deviceId, string $from, string $to, float $fuelConsumption, float $fuelPrice,$sumary = null): float
    {

        $fuelConsumption = 0;
        $fuelPrice = 0;
        if (!$sumary){
            $sumary = $this->getSummary($deviceId, $from, $to);
        }

        $device = null;
        if($deviceId)
            $device = Device::with('vehicle')->where('traccar_device_id',$deviceId)->first();

        // Selon le vehicle
        if($device->vehicle){
            $fuelConsumption = $device->vehicle->coso_moy_carburant;

            if($device->vehicle->carburant == 'essence'){
                $fuelPrice = Helpers::getPriceCarburant()['price_essence'];
            }
            if($device->vehicle->carburant == 'diesel'){
                $fuelPrice = Helpers::getPriceCarburant()['price_diesel'];
            }
        }

        if ($sumary) {
            $data = $sumary;

            if (isset($data[0]))
                $data = $data[0];

            if (!empty($data) && isset($data['distance'])) {
                // Distance totale parcourue en mètres, convertir en km
                $distanceInKm = $data['distance'] / 1000;

                // Calculer le coût total de l'essence
                $fuelCost = ($distanceInKm * $fuelConsumption / 100) * $fuelPrice;

                return round($fuelCost, 2); // Arrondir à 2 décimales
            }
        }

        // Retourner 0 si aucune donnée n'est disponible
        return 0.0;
    }

    /** Récupérer la vitesse Moy pour chaque Tranche KLM
     * @param $deviceId
     * @param $from
     * @param $to
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSpeedHistogramData($deviceId, $from, $to,$trips = null)
    {

        // Tranches de distance (en km)
        $ranges = [0, 20, 40, 60, 80, 100, 120];
        $labels = ['0km', '20km', '40km', '60km', '80km', '100km', '120km'];
        $speedData = array_fill(0, count($ranges) - 1, 0); // Tableau des vitesses moyennes
        $countData = array_fill(0, count($ranges) - 1, 0); // Compteur par tranche

        // Traitement des trajets
        foreach ($trips as $trip) {
            $distance = $trip['distance'] / 1000; // Convertir la distance en kilomètres
            $averageSpeed = $trip['averageSpeed'] * 3.6; // Convertir la vitesse moyenne en km/h

            // Identifier la tranche correspondante
            foreach ($ranges as $index => $range) {
                if ($index < count($ranges) - 1 && $distance >= $range && $distance < $ranges[$index + 1]) {
                    $speedData[$index] += $averageSpeed; // Ajouter la vitesse à la tranche
                    $countData[$index]++; // Incrémenter le compteur
                    break;
                }
            }
        }

        // Calcul des moyennes pour chaque tranche
        foreach ($speedData as $index => $totalSpeed) {
            if ($countData[$index] > 0) {
                $speedData[$index] = $totalSpeed / $countData[$index]; // Calcul de la moyenne
            }
        }

        // S'assurer que toutes les tranches sont présentes
        $speedData = array_map(function ($value) {
            return round($value, 2); // Arrondir à 2 décimales
        }, $speedData);
        $speedData = array_reverse($speedData);
        return [
            'labels' => $labels,
            'data' => $speedData,
        ];
    }


    /**
     * Les Activité par heures
     * @param $deviceId
     * @param $from
     * @param $to
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHourlyActivityData($deviceId,$from,$to)
    {

        $positions = $this->getPositions($deviceId,$from,$to);


        // Initialisation des données horaires (24 heures)
        $hourlyActivity = array_fill(0, 24, 0);

        // Parcourir les positions pour compter les activités par heure
        foreach ($positions as $position) {
            $timestamp = $position['serverTime']; // Récupérer le timestamp
            $hour =  Carbon::parse($timestamp)->hour; // Extraire l'heure
            $hourlyActivity[$hour]++; // Incrémenter le compteur pour cette heure
        }

        // Normalisation des données pour les afficher entre 0 et 1
        $maxActivity = max($hourlyActivity);
        if ($maxActivity > 0) {
            $hourlyActivity = array_map(function ($value) use ($maxActivity) {
                return round($value / $maxActivity, 2); // Normaliser à 2 décimales
            }, $hourlyActivity);
        }

        // Préparer les données pour le frontend
        return response()->json([
            'labels' => array_map(fn($hour) => "{$hour}h", range(0, 23)), // Labels "0h", "1h", ..., "23h"
            'data' => $hourlyActivity,
        ]);
    }


    /**
     * Execute Commande
     * @param $deviceId
     * @param $type : ex : engineStop
     * @param $command  : ex : engineStop
     * @return mixed
     */
    public function sendCommand($deviceId, $type, $command)
    {
        $response = Http::withBasicAuth($this->currentUser->email, $this->currentUser->password_traccar)
            ->post("{$this->baseUrl}api/commands/send", [
                'deviceId' => $deviceId,
                'type' => $type,
                'attributes' => [
                    'data' => $command,
                ],
            ]);

        return $response->json();
    }

}
