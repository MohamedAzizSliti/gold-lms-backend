<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\Setting;
use App\Http\Requests\UpdateSettingRequest;
use App\Repositories\Eloquents\SettingRepository;
use App\Services\TrackCarService;

class SettingController extends Controller
{
    public $repository;

    public function __construct(SettingRepository $repository)
    {

        $this->repository = $repository;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

         return $this->repository->latest('created_at')->first();
    }

    public function frontSettings()
    {
         return $this->repository->frontSettings();
    }

    public function appMobileSettings()
    {
        return $this->repository->appMobileSettings();
    }

    public function update(UpdateSettingRequest $request, Setting $setting)
    {
        $old_speed_max = Helpers::getGlobalSpeedLimit();
        $old_api_key_google_map = Helpers::getApiKeyGoogleMap();
        // Mettre à jour la limite globale dans TracCar
        if ($request->input('values.general.speed_max') != $old_speed_max){
            $traccarService = new TrackCarService();
            $traccarService->setGlobalSpeedLimit([
                "id" => 1,
                "attributes" => [
                    "speedLimit" =>$request->input('values.general.speed_max'),
                    "speedUnit" => 'kmh'
                ]
            ]);
        }

        // Mettre à jour la clés de la map
        if ($request->input('values.google_map.api_key') != $old_api_key_google_map){
            $newKey = $request->input('values.google_cloud.api_key');
            if (!$newKey) {
                return response()->json(['error' => 'Invalid API key'], 400);
            }

            // Path to traccar.xml
            $configPath = "/opt/traccar/conf/traccar.xml";

            // Read and modify the config
            $config = file_get_contents($configPath);
            $config = preg_replace("/<entry key='web.maps.key'>(.*?)<\/entry>/", "<entry key='web.maps.key'>{$newKey}</entry>", $config);
            $config = preg_replace("/<entry key='geocoder.key'>(.*?)<\/entry>/", "<entry key='geocoder.key'>{$newKey}</entry>", $config);

            // Save the changes
            file_put_contents($configPath, $config);

            // Restart Traccar to apply changes
            $output = shell_exec("sudo /bin/systemctl restart traccar 2>&1");
            // dd("<pre>$output</pre>") ;
            /**
             * Si l'execution de restart est echoué
             * sudo visudo
             * www-data ALL=(ALL) NOPASSWD: /bin/systemctl restart traccar
             */

            // Map key updated successfully
        }

        return $this->repository->update($request->all(), null);
    }
}
