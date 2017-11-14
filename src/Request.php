<?php

namespace ChrisEsser\GDAXExchange;

class Request
{
    public function call($url, $method, $headers, $body = '')
    {
        $curl = curl_init();

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ];

        $method = strtolower($method);

        if ($method == 'get') {
            $options[CURLOPT_HTTPGET] = true;
        } elseif ($method == 'post') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $body;
        } elseif ($method == 'delete') {
            $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        } elseif ($method == 'put') {
            $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);

        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);


        return ['statusCode' => $statusCode, 'body' => $response];
    }
}