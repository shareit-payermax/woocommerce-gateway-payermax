<?php
/**
 * Plugin Name: WooCommerce PayerMax Payments
 * Description: A simpler and safer payment suite.
 * Author: PayerMax
 * Author URI: https://www.payermax.com/
 * Version: 2.0.3
 * Requires at least: 4.0
 * Requires PHP: 5.4
 * WC requires at least: 3.0
 * WC tested up to: 7.0
 */

if (!defined('WPINC')) {
    die;
}

define('WC_PAYERMAX_VERSION', '2.0.1');
define('WC_PAYERMAX_MIN_WC_VER', '3.0');
define('WC_PAYERMAX_DIR', __DIR__);

function woocommerce_gateway_payermax() {

    static $plugin;

    if (!isset($plugin)) {

        class WC_PayerMax {

            /**
             * The *Singleton* instance of this class
             *
             * @var WC_PayerMax
             */
            private static $instance;

            /**
             * Returns the *Singleton* instance of this class.
             *
             * @return WC_PayerMax The *Singleton* instance.
             */
            public static function get_instance() {
                if (null === self::$instance) {
                    self::$instance = new self();
                }
                return self::$instance;
            }

            /**
             * Translation
             */
            private static $i18n;

            /**
             * Config
             */
            public static $config;

            public function __construct() {
                $this->init();
            }

            public function init() {
                require_once WC_PAYERMAX_DIR . '/vendor/autoload.php';
                require_once WC_PAYERMAX_DIR . '/i18n/Translation.php';
                require_once WC_PAYERMAX_DIR . '/includes/class-payermax-helper.php';
                require_once WC_PAYERMAX_DIR . '/includes/class-payermax-config.php';
                require_once WC_PAYERMAX_DIR . '/tools/Beyla.php';
                require_once WC_PAYERMAX_DIR . '/tools/Signature.php';
                require_once WC_PAYERMAX_DIR . '/api/Api.php';
                require_once WC_PAYERMAX_DIR . '/includes/gateway/class-wc-gateway.php';
                require_once WC_PAYERMAX_DIR . '/includes/gateway/class-wc-gateway-card.php';

                $lang       = WC_PayerMax_Helper::locale_language(get_user_locale());
                $i18n       = new Translation($lang);
                self::$i18n = $i18n->create();

                // init config
                self::$config = new WC_PayerMax_Config();
                if (is_file(WC_PAYERMAX_DIR . '/dev.php')) {
                    require_once WC_PAYERMAX_DIR . '/dev.php';
                    self::$config = new WC_PayerMax_Config_Dev();
                }

                // Add the Gateway to WooCommerce
                add_filter(
                    'woocommerce_payment_gateways',
                    function ($methods) {
                        $gateways = [
                            'WC_Gateway_PayerMax',
                            'WC_Gateway_PayerMax_CARD',
                        ];
                        return array_merge($methods, $gateways);
                    }
                );

                add_action(
                    'wp_ajax_check_payment_status',
                    ['WC_Gateway_PayerMax', 'check_payment_status']
                );
            }

            /**
             * Translation
             *
             * @return WC_PayerMax trans
             */
            public static function trans($id, array $parameters = [], $domain = null, $locale = null) {
                return self::$i18n->trans($id, $parameters, $domain, $locale);
            }

        }

        $plugin = WC_PayerMax::get_instance();

    }

    return $plugin;
}

function woocommerce_gateway_payermax_init() {

    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . '/wp-admin/includes/plugin.php';
    }

    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        add_action(
            'admin_notices',
            function () {
                echo '<div class="error"><p><strong>' . sprintf(esc_html__('WooCommerce Payermax Payments requires WooCommerce to be installed and active. You can download %s here.', 'woocommerce-paypal-payments'), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>') . '</strong></p></div>';
            }
        );

        return;
    }

    if (version_compare(WC_VERSION, WC_PAYERMAX_MIN_WC_VER, '<')) {
        add_action(
            'admin_notices',
            function () {
                $class   = 'notice notice-error';
                $message = 'PayerMax requires WooCommerce ' . WC_PAYERMAX_MIN_WC_VER . ' or greater to be installed and active.';
                printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
            });

        return;
    }

    woocommerce_gateway_payermax();

}

add_action('plugins_loaded', 'woocommerce_gateway_payermax_init', 0);
