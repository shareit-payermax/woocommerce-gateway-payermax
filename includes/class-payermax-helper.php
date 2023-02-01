<?php

class WC_PayerMax_Helper {
    /**
     * Converts a WooCommerce locale to the closest supported by PayerMax
     * @see https://gist.github.com/danielbachhuber/14af08c5faac07d5c0c182eb66b19b3e#file-wordpress-language-codes-csv
     */
    public static function locale_language($locale = 'en') {
        // $supported = [
        //     'en',
        //     'zh',
        // ];

        // $langs = explode('_', $locale);

        // if (in_array($langs[0], $supported, true)) {
        //     return $locale;
        // }
        return 'en';
    }

    /**
     * Converts a WooCommerce locale to the closest supported by Cashier
     */
    public static function cashier_language($locale = 'en') {
        $langs = explode('_', $locale);
        return $langs[0];
    }

    /**
     * Mark WC order status
     */
    public static function mark_order_status($order, $response) {
        if ($order->get_status() != 'on-hold') {
            return;
        }

        if (isset($response['code']) && ($response['code'] == 'APPLY_SUCCESS')) {
            $status = isset($response['data']) ? $response['data']['status'] : '';

            if (empty($status)) {
                return;
            }

            if ($status == 'SUCCESS') {
                $order->payment_complete();
            }

            if ($status == 'FAILED' || $status == 'CLOSED') {
                $order->update_status('failed');
            }

            $order->add_order_note(
                WC_PayerMax::trans(
                    'note.payment_status',
                    ['%status%' => $status]
                )
            );
        }
    }

    /**
     * Cashier URL
     */
    public static function get_pay_url($env = 'PROD', $suffix = '') {
        $gateway = WC_PayerMax::$config::GATEWAY;
        return $gateway[$env] . '/aggregate-pay/api/gateway' . $suffix;
    }

    /**
     * Beyla URL
     */
    public static function get_beyla_url($env = 'PROD') {
        $gateway  = WC_PayerMax::$config::BEYLA_GATEWAY;
        $test_url = isset($gateway['TEST']) ? $gateway['TEST'] : '';
        $prod_url = isset($gateway['PROD']) ? $gateway['PROD'] : '';
        $url      = $env === 'PROD' ? $prod_url : $test_url;
        return $url . '/encode/web';
    }
}