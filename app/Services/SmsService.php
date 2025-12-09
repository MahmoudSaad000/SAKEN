<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected Client $client;

    protected string $apiKey;

    protected string $apiUrl;

    public function __construct()
    {
        $this->client = new Client;
        $this->apiKey = env('SMS_TRACER_API_KEY', 'efkL7P83SJe93NZN6HOnV3:APA91bHfFtKbFmbUz6U2t7UjfOsD1Gm8eWcw_Ep8ellZF_XZmve81UG-o1SCwThxxhOrRGKCqY9XhBvfrpeXmgQ_ItajOJ2bPuDI50xOgZ-btI8u8reaH-o');
        $this->apiUrl = env('SMS_TRACER_API_URL', 'https://www.traccar.org/sms/');
    }

    public function sendSMS(string $phone, string $message): bool
    {
        Log::info('Attempting to send SMS', [
            'phone' => $phone,
            'message' => $message,
        ]);

        try {
            $response = $this->client->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'to' => $phone,
                    'message' => $message,
                ],
            ]);

            Log::info('SMS API response', [
                'status' => $response->getStatusCode(),
                'body' => (string) $response->getBody(),
            ]);

            return true;
        } catch (RequestException $e) {
            Log::error('Failed to send SMS', [
                'phone' => $phone,
                'message' => $message,
                'error' => $e->getMessage(),
                'response' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null,
            ]);

            return false;
        }
    }
}
