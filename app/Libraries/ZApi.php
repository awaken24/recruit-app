<?php

namespace App\Libraries;

use GuzzleHttp\Client;

class ZApi
{
    protected $client;

    protected $secury_token;

    public function __construct(string $instancia, string $token, string $securyToken)
    {
        $baseUrl = "https://api.z-api.io/instances/{$instancia}/token/{$token}/";
        $this->secury_token = $securyToken;

        $this->client = new Client([
            'base_uri' => $baseUrl,
            'timeout'  => 10.0,
        ]);
    }

    public function sendMessage(string $numero, string $mensagem): array
    {
        $headers = [
            'client-token' => $this->secury_token,
            'Content-Type' => 'application/json',
        ];

        $response = $this->client->post('send-text', [
            'headers' => $headers,
            'json' => [
                'phone'   => $numero,
                'message' => $mensagem,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }





}