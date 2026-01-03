<?php

namespace Emeraldd\Oauth\Google;

class UserInfo {

    private $currentClient;
    public function __construct($client)
    {
        $this->currentClient = $client;
    }

    public function get() {
        $ch = curl_init();
        
        $url = 'https://www.googleapis.com/oauth2/v2/userinfo';
        curl_setopt($ch, CURLOPT_URL, $url);

        // Define your custom headers as an indexed array
        $custom_headers = array(
            'authorization: Bearer '.$this->currentClient->token['access_token']
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response_body = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        }

        curl_close($ch);

        return json_decode($response_body);
    }
}