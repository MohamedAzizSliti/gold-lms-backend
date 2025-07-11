<?php

namespace App\Enums;

enum FrontSettingsEnum:string {
  case GENERAL = 'general';
  case ANALYTICS = 'analytics';
  case ACTIVATION = 'activation';
  case MAINTENANCE = 'maintenance';
  case DELIVERY = 'delivery';
  case WALLET_POINTS = 'wallet_points';
  case GOOGLE_RECAPTCHA = 'google_reCaptcha';
  // Todo here if you want add nex menu for setting
  case GOOGLE_CLOUD = 'google_cloud';
}
