<?php

namespace App\Console\Commands;

use App\Services\TrackCarService;
use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SimulateGPS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulate:movement
                            {deviceId : ID du device cible}
                            {--sourceDevice= : ID du device source (positions existantes)}
                            {--startDate= : Date de début au format YYYY-MM-DD HH:MM:SS (optionnel)}
                            {--endDate= : Date de fin au format YYYY-MM-DD HH:MM:SS (optionnel)}
                            {--interval=5 : Intervalle entre les envois (en secondes)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simule un déplacement en utilisant les positions existantes d\'un autre device';

    /**
     * Instance du service Traccar.
     *
     * @var TraccarService
     */
    protected $traccarService;

    /**
     * Constructeur
     */
    public function __construct(TrackCarService $traccarService)
    {
        parent::__construct();
        $this->traccarService = $traccarService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deviceId = (int)$this->argument('deviceId');
        $sourceDevice = $this->option('sourceDevice');
        $startDate = $this->option('startDate');
        $endDate = $this->option('endDate');
        $interval = (int)$this->option('interval');

        if (!$sourceDevice) {
            $this->error('Vous devez fournir un device source avec l\'option --sourceDevice.');
            return;
        }

        // Récupérer les positions existantes
        $positions = $this->getPositions($sourceDevice, $startDate, $endDate);

        if (empty($positions)) {
            $this->error("Aucune position trouvée pour le device source $sourceDevice dans la période spécifiée.");
            return;
        }

        $this->info("Simulation pour le device ID $deviceId en utilisant les positions du device source $sourceDevice...");

        foreach ($positions as $position) {
            // Appeler la méthode du service pour insérer la position
            $this->traccarService->addPositionDirectlyInTheBase(
                $deviceId,
                $position->latitude,
                $position->longitude,
                $position->altitude,
                $position->speed,
                Carbon::now(), // Utiliser l'heure actuelle
                $position->course
            );

            $this->info("Position envoyée : Latitude {$position->latitude}, Longitude {$position->longitude}, Vitesse {$position->speed}");

            // Attendre avant d'envoyer la prochaine position
            sleep($interval);
        }

        $this->info("Simulation terminée.");
    }

    /**
     * Récupérer les positions existantes pour un device et une période.
     *
     * @param int $sourceDevice
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    private function getPositions(int $sourceDevice, ?string $startDate, ?string $endDate): array
    {
        $query = DB::connection('traccar')->table('positions')
            ->where('deviceid', $sourceDevice)
            ->orderBy('servertime');

        if ($startDate) {
            $query->where('servertime', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('servertime', '<=', $endDate);
        }
        $query->where('speed', '!=', 0);
        return $query->get()->toArray();
    }
}
