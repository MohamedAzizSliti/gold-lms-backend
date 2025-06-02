<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class XSenderService
{

    /**
     * Send SMS
     * @param $to (+21692702009)
     * @param $message
     * @return mixed
     */
    public static function sendSms($to,$message)
    {

        $url = env('XSENDER_URL').'api/sms/send'; // Replace with your API URL
        $apiKey = env('XSENDER_API_KEY'); // Replace with your actual API token

        // Define the request payload
        $postData = [
            "contact" => [
                [
                    "number" => $to,  // Replace with recipient's phone number
                    "body" => $message,
                    "sms_type" => "plain",
                    // "gateway_identifier" => "*****************" // Replace with your identifier // Without gateway_identifier will ude the default gateway
                ]
            ]
        ];

        // Make the API request
        $response = Http::withHeaders([
          //  'Authorization' => 'Bearer ' . $apiKey, // If authentication is required
            'Content-Type' => 'application/json',
            'Api-key' => $apiKey
        ])->post($url, $postData);
          // dd($response);
        // Handle the response
        if ($response->successful()) {
            return response()->json([
                'status' => 'success',
                'message' => 'SMS sent successfully',
                'data' => $response->json()
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send SMS',
                'error' => $response->json()
            ], $response->status());
        }
    }

    /**
     * Send SMS
     * @param $to (+21692702009)
     * @param $message
     * @return mixed
     */
    public static function sendWhatsApp($to,$message)
    {

        $url = env('XSENDER_URL').'api/sms/send'; // Replace with your API URL
        $apiKey = env('XSENDER_API_KEY'); // Replace with your actual API token

        // Define the request payload
        $postData = [
            "contact" => [
                [
                    "number" => $to,  // Replace with recipient's phone number
                    "message" => $message,
                   // "schedule_at" => "plain",
                    // "gateway_identifier" => "*****************" // Replace with your identifier // Without gateway_identifier will ude the default gateway
                ]
            ]
        ];

        // Make the API request
        $response = Http::withHeaders([
            //  'Authorization' => 'Bearer ' . $apiKey, // If authentication is required
            'Content-Type' => 'application/json',
            'Api-key' => $apiKey
        ])->post($url, $postData);
        // dd($response);
        // Handle the response
        if ($response->successful()) {
            return response()->json([
                'status' => 'success',
                'message' => 'WhatsApp sent successfully',
                'data' => $response->json()
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send WhatsApp',
                'error' => $response->json()
            ], $response->status());
        }
    }
}
