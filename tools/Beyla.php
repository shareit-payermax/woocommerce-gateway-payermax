<?php

require_once WC_PAYERMAX_DIR . "/tools/Request.php";

class Beyla {
    private $app_id;
    private $merchant_number;
    private $gateway;
    private $env;

    public function __construct($options) {
        $this->app_id          = $options['app_id'];
        $this->merchant_number = $options['merchant_number'];
        $this->gateway         = $options['gateway'];
        $this->env             = $options['env'];
    }

    public function report($params) {
        $message = [
            'project'   => 'pay',
            'logStore'  => 'cashier',
            'groupName' => 'web',
            'message'   => json_encode([
                'appId'        => 'woocommerce',
                'beylaId'      => time() . '-' . round((float) rand() / (float) getrandmax(), 8),
                'publicParams' => [
                    'merchant_app_id' => $this->app_id,
                    'merchant_id'     => $this->merchant_number,
                    'version'         => WC_PAYERMAX_VERSION,
                    'wc_version'      => WC_VERSION,
                    'php_version'     => phpversion(),
                    'gateway'         => $this->gateway,
                    'env'             => $this->env,
                ],
                'params'       => $params,
                'reportTime'   => (int) (time() . '000'),
                'reportType'   => 'custom',
            ]),
        ];

        $data = [
            'message' => base64_encode(json_encode($message)),
        ];

        $url     = WC_PayerMax_Helper::get_beyla_url($this->env);
        $request = new Request();
        $request->post(
            $url,
            $data,
            [
                'timeout' => 1,
            ]
        );

    }
}