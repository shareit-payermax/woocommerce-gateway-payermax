<?php

require_once WC_PAYERMAX_DIR . "/tools/Request.php";

class Api {

    protected $gateway;

    public function __construct($gateway) {
        $this->gateway = $gateway;
    }

    /**
     * Pay Order
     * @param  [array] $order
     */
    public function pay($order) {
        $params = [
            'version'       => '1.0',
            'keyVersion'    => '1',
            'requestTime'   => (new \DateTime())->format('Y-m-d\TH:i:s.vP'),
            'merchantAppId' => $this->gateway->app_id,
            'merchantNo'    => $this->gateway->merchant_number,
        ];

        $defaultCountry = get_option('woocommerce_default_country');
        $country        = explode(':', $defaultCountry);

        $goodsDetails = [];
        $goodsName    = [];

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if (!$product) {
                continue;
            }

            $gd_name        = (string) $item->get_name();
            $goodsDetails[] = [
                'goodsId'   => (string) $item->get_id(),
                'goodsName' => $gd_name,
                'quantity'  => (string) $item->get_quantity(),
                'price'     => (string) ($product->get_price()),
            ];

            $goodsName[] = $gd_name;
        }
        $subject = implode(';', $goodsName);

        $params['data'] = [
            'outTradeNo'       => $this->get_trade_no($order),
            'subject'          => $subject,
            'totalAmount'      => $order->get_total(),
            'currency'         => $order->get_currency(),
            'country'          => $country[0],
            'userId'           => is_user_logged_in() ? (string) $order->get_customer_id() : 'NA',
            'paymentDetail'    => [
                'paymentMethod' => $this->gateway->paymentMethod,
            ],
            'goodsDetails'     => $goodsDetails,
            'shippingInfo'     => [
                'firstName' => $order->get_shipping_first_name(),
                'lastName'  => $order->get_shipping_last_name(),
                'address1'  => $order->get_shipping_address_1(),
                'city'      => $order->get_shipping_city(),
                'state'     => $order->get_shipping_state(),
                'country'   => $order->get_shipping_country(),
                'zipCode'   => $order->get_shipping_postcode(),
            ],
            'billingInfo'      => [
                'firstName' => $order->get_billing_first_name(),
                'lastName'  => $order->get_billing_last_name(),
                'phoneNo'   => $order->get_billing_phone(),
                'email'     => $order->get_billing_email(),
                'address1'  => $order->get_billing_address_1(),
                'city'      => $order->get_billing_city(),
                'state'     => $order->get_billing_state(),
                'country'   => $order->get_billing_country(),
                'zipCode'   => $order->get_billing_postcode(),
            ],
            'language'         => WC_PayerMax_Helper::cashier_language(get_user_locale()),
            'reference'        => (string) $order->get_id(),
            'frontCallbackUrl' => $this->gateway->get_return_url($order),
            'notifyUrl'        => WC()->api_request_url($this->gateway->id),
        ];

        return $this->wc_post('/orderAndPay', $params);
    }

    /**
     * Post Api
     */
    protected function wc_post($url, $data) {
        $base_url = WC_PayerMax_Helper::get_pay_url($this->gateway->env, $url);

        $request  = new Request();
        $response = $request->post(
            $base_url,
            $data,
            [
                'headers' => [
                    'sign: ' . $this->gateway->signature->sign($data),
                ],
            ]
        );

        $json_data = json_decode($response, true);

        if (empty($json_data)) {
            $json_data = [
                'code' => 'SYSTEM_ERROR',
            ];
        }

        $code = isset($json_data['code']) ? $json_data['code'] : '';
        $this->gateway->beyla->report([
            'eventName' => 'result_api',
            'pveCur'    => '/api' . $url,
            'extras'    => [
                'request_url'    => $base_url,
                'request_params' => json_encode($data),
                'response'       => $response,
                'code'           => (string) $code,
            ],
        ]);

        return $json_data;
    }

    /**
     * Generate Transaction ID
     */
    protected function get_trade_no($order) {
        $transaction_id = $order->get_transaction_id();
        if ($transaction_id) {
            return $transaction_id;
        }

        $transaction_id = substr('WCP_' . (new Datetime())->format('YmdHisv') . $order->get_order_number(), -64);
        $order->set_transaction_id($transaction_id);
        $order->save();

        $order->add_order_note(
            WC_PayerMax::trans(
                'note.transaction_id',
                ['%id%' => $transaction_id]
            )
        );

        return $transaction_id;
    }

    /**
     * Search Order
     * @param  [array] $data
     */
    public function queryOrder($order) {
        $params = [
            'version'       => '1.0',
            'keyVersion'    => '1',
            'requestTime'   => (new \DateTime())->format('Y-m-d\TH:i:s.vP'),
            'merchantAppId' => $this->gateway->app_id,
            'merchantNo'    => $this->gateway->merchant_number,
        ];

        $params['data'] = [
            'outTradeNo' => $order->get_transaction_id(),
        ];

        return $this->wc_post('/orderQuery', $params);
    }
}
