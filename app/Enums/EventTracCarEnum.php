<?php

namespace App\Enums;

enum EventTracCarEnum: string
{
case OVERSPEED = 'deviceOverspeed'; // Dépassement de vitesse
case GEOFENCE_ENTER = 'geofenceEnter'; // Entrée dans une zone géographique
case GEOFENCE_EXIT = 'geofenceExit'; // Sortie d'une zone géographique
case IGNITION_ON = 'ignitionOn'; // Démarrage du moteur
case IGNITION_OFF = 'ignitionOff'; // Arrêt du moteur
case DEVICE_ONLINE = 'deviceOnline'; // Appareil en ligne
case DEVICE_OFFLINE = 'deviceOffline'; // Appareil hors ligne
case SOS_ALERT = 'sos'; // Bouton SOS activé
case TAMPER_ALERT = 'tamper'; // Alarme de sabotage
case BATTERY_LOW = 'lowBattery'; // Batterie faible
case POWER_CUT = 'powerCut'; // Coupure d'alimentation
}
