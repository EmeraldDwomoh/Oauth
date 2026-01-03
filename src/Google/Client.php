<?php

namespace Emerald\Oauth\Google;
use InvalidArgumentException;

class Client{

    protected $requestedScopes = [];
    private $config;
    public $token;
    public function setClientId($client_id) {
        $this->config["ClientId"] = $client_id;
    }

    public function setClientSecret($client_secret) {
        $this->config["ClientSecret"] = $client_secret;
    }

    public function setRedirectUri($redirect_uri) {
        $this->config["RedirectUri"] = $redirect_uri;
    }

    public function addScope($scope_or_scopes)
    {
        if (is_string($scope_or_scopes) && !in_array($scope_or_scopes, $this->requestedScopes)) {
            $this->requestedScopes[] = $scope_or_scopes;
        } elseif (is_array($scope_or_scopes)) {
            foreach ($scope_or_scopes as $scope) {
                $this->addScope($scope);
            }
        }
    }

    public function createAuthUrl() {
        $placeholder = "https://accounts.google.com/o/oauth2/v2/auth?";
        $clientId = $this->config["ClientId"];
        $redirectURI = $this->config["RedirectUri"];
        $scope = $this->requestedScopes;
        $final_scope = "";
        $i = 0;
        foreach ($scope as $value) {
            $i = $i + 1;
            if ($i == count($scope)) {
                $final_scope = $final_scope.$value;
            } else {
                $final_scope = $final_scope.$value."%20";
            }
        }
        
        return $placeholder."response_type=code&access_type=online&client_id=".$clientId."&redirect_uri=".urlencode($redirectURI)."&state&scope=".$final_scope."&prompt=auto";
    }

    public function fetchAccessTokenWithAuthCode($code) {
        $url = 'https://oauth2.googleapis.com/token';
        $data = [
            'grant_type' => "authorization_code",
            'code' => $code,
            'redirect_uri'=> $this->config["RedirectUri"],
            'client_id' => $this->config["ClientId"],
            'client_secret' => $this->config["ClientSecret"],
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Use http_build_query for form data
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    
    public function setAccessToken($token)
    {
        if (is_string($token)) {
            if ($json = json_decode($token, true)) {
                $token = $json;
            } else {
                // assume $token is just the token string
                $token = [
                    'access_token' => $token,
                ];
            }
        }
        if ($token == null) {
            throw new InvalidArgumentException('invalid json token');
        }
        if (!isset($token['access_token'])) {
            throw new InvalidArgumentException("Invalid token format");
        }
        $this->token = $token;
    }
}
