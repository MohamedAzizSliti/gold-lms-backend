<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class localization
{
    public function handle($request, Closure $next)
    {
        try {
            // Extraire la locale depuis l'en-tête Accept-Language
            $locale = $this->parseLocaleFromHeader($request->header('Accept-Language'));

            // Get supported locales from config, fallback to default set
            $supportedLocales = config('app.locales', ['en', 'fr', 'ar']);

            // Vérifier si la locale est supportée par l'application
            if ($locale && in_array($locale, $supportedLocales)) {
                App::setLocale($locale);
            } else {
                // Définir la locale par défaut si la locale extraite est invalide
                App::setLocale(config('app.fallback_locale', 'en'));
            }
        } catch (\Exception $e) {
            // If any error occurs, just use the fallback locale
            App::setLocale(config('app.fallback_locale', 'en'));
        }

        return $next($request);
    }

    private function parseLocaleFromHeader($header)
    {
        if (!$header) {
            return null;
        }

        try {
            // Extraire la première langue de l'en-tête
            $languages = explode(',', $header);

            foreach ($languages as $lang) {
                // Récupérer uniquement le code langue sans les paramètres `q=`
                $locale = trim(explode(';', $lang)[0]);

                // Convert locale formats: en_GB -> en, fr_FR -> fr, etc.
                if (strpos($locale, '_') !== false) {
                    $locale = explode('_', $locale)[0];
                } elseif (strpos($locale, '-') !== false) {
                    $locale = explode('-', $locale)[0];
                }

                // Ensure it's lowercase and only 2 characters
                $locale = strtolower(substr($locale, 0, 2));

                // Retourner la première locale valide trouvée
                if (preg_match('/^[a-z]{2}$/', $locale)) {
                    return $locale;
                }
            }
        } catch (\Exception $e) {
            // If parsing fails, return null
            return null;
        }

        return null;
    }
}
