<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\GraphQL\Exceptions\ExceptionHandler as GraphQLExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (Exception $exception, $request) {

            if ($exception instanceof NotFoundHttpException) {

                if ($request->hasHeader("Accept-Language")) {
                    try {
                        // Parse the Accept-Language header safely
                        $acceptLanguage = $request->header("Accept-Language");
                        $locale = $this->parseLocaleFromHeader($acceptLanguage);

                        // Set locale only if it's valid and supported
                        $supportedLocales = config('app.locales', ['en', 'fr', 'ar']);
                        if ($locale && in_array($locale, $supportedLocales)) {
                            app()->setLocale($locale);
                        } else {
                            app()->setLocale(config('app.fallback_locale', 'en'));
                        }
                    } catch (\Exception $e) {
                        // If locale parsing fails, use fallback
                        app()->setLocale(config('app.fallback_locale', 'en'));
                    }
                }

                throw new GraphQLExceptionHandler($exception->getMessage(), 400);
            }
        });
    }

    /**
     * Parse locale from Accept-Language header
     */
    private function parseLocaleFromHeader($header)
    {
        if (!$header) {
            return null;
        }

        try {
            // Extract the first language from the header
            $languages = explode(',', $header);

            foreach ($languages as $lang) {
                // Get only the language code without `q=` parameters
                $locale = trim(explode(';', $lang)[0]);

                // Convert locale formats: en_GB -> en, fr_FR -> fr, etc.
                if (strpos($locale, '_') !== false) {
                    $locale = explode('_', $locale)[0];
                } elseif (strpos($locale, '-') !== false) {
                    $locale = explode('-', $locale)[0];
                }

                // Ensure it's lowercase and only 2 characters
                $locale = strtolower(substr($locale, 0, 2));

                // Return the first valid locale found
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
