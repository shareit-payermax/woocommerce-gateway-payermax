<?php

class Request {

    /**
     * curl
     * @param  [string] $url
     * @param  [array] $data
     * @param  [array] $options
     */
    public function curl($url, $data, array $options = []) {
        $data = json_encode($data);
        $ch   = curl_init();

        $timeout = isset($options['timeout']) ? $options['timeout'] : 15;
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $headers = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data),
        ];

        if (isset($options['headers'])) {
            $headers = array_merge($headers, $options['headers']);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, $options['method'] === 'post');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $res = curl_exec($ch);

        curl_close($ch);
        return $res;
    }

    public function post($url, $data, array $options = []) {
        $options['method'] = 'post';
        return $this->curl($url, $data, $options);
    }
}
