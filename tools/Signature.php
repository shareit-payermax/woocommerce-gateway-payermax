<?php

class Signature {
    private $private_key;
    private $public_key;

    public function __construct($keys) {
        $this->public_key  = $keys['public_key'];
        $this->private_key = $keys['private_key'];
    }

    public function sign($content) {
        $key = str_replace(PHP_EOL, '', trim($this->private_key));
        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
        wordwrap($key, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";

        openssl_sign(json_encode($content), $sign, $res, OPENSSL_ALGO_SHA256);
        $sign = base64_encode($sign);

        return $sign;
    }

    public function verify($content, $sign) {
        $key = str_replace(PHP_EOL, '', trim($this->public_key));
        $res = "-----BEGIN PUBLIC KEY-----\n" .
        wordwrap($key, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";

        return openssl_verify(json_encode($content), base64_decode($sign), $res, OPENSSL_ALGO_SHA256) === 1;
    }
}
