<?php

namespace BillDesk;

use GuzzleHttp\Client;
use BillDesk\BillDesk;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use GuzzleHttp\Exception\RequestException;


class BaseClass
{
    protected $config;
    protected $client;
    protected $jwt;

    public function __construct()
    {
        $this->client = new Client();
        $this->config = BillDesk::init()->getConfig();
        $this->jwt = new JWT();
    }

    public function encodePayload($payload)
    {
        $headers = [
            "alg" => "HS256",
            "clientid" => $this->config["CLIENT_ID"]
        ];

        $secretKey = $this->config["SECRET_KEY"];

        $encoded_payload = $this->jwt->encode($payload, $secretKey, "HS256", null, $headers);

        return $encoded_payload;
    }

    public function decodePayload($encoded_payload)
    {
        $key = new Key($this->config["SECRET_KEY"], "HS256");

        $decoded_payload = $this->jwt->decode($encoded_payload, $key);

        # Converting object to array
        $decoded_payload = json_decode(json_encode($decoded_payload), true);

        return $decoded_payload;
    }

    public function executeRequest($payload)
    {
        $encoded_payload = $this->encodePayload($payload);

        try {
            $response = $this->client->post($this->config["BILLDESK_URL"], [
                'headers' => [
                    "Content-Type" => "application/jose",
                    "accept" => "application/jose",
                    "BD-Traceid" => uniqid(),
                    "BD-Timestamp" => date('YmdHis')
                ],
                'body' => $encoded_payload
            ]);
            
            $result = $response->getBody()->getContents();

            return $this->decodePayload($result);
        } catch (RequestException $e) {
            $error = $e->getMessage();
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseBodyAsString = $response->getBody()->getContents();
                // Decode and output the response body if needed
                try {
                    $decodedResponseBody = $this->decodePayload($responseBodyAsString);
                    $error .= "Decoded Error Response: ".json_encode($decodedResponseBody);
                } catch (\Exception $decodeException) {
                    $error .= "Error Response: ".$responseBodyAsString;
                }
            }
            
            throw new \Exception($error);
        }
    }

    // Function to get the client IP address
    protected function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
}
