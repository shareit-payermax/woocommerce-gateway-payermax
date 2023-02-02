<?php
/**
 * Payment Gateway
 *
 * @class          WC_Gateway_PayerMax
 * @extends        WC_Payment_Gateway
 * @author         PayerMax
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_Gateway_PayerMax extends WC_Payment_Gateway {
    public $id   = 'WC_Gateway_PayerMax';
    public $name = 'standard';

    public $paymentMethod = '';
    public $beyla;
    public $signature;

    public function __construct() {

        $this->id                 = strtolower($this->id);
        $this->icon               = wp_get_attachment_image_url(get_option($this->id), 'full');
        $this->method_title       = __(WC_PayerMax::trans('settings.standard.method_title'), $this->id);
        $this->method_description = __(WC_PayerMax::trans('settings.standard.method_description'), $this->id);

        $this->init_form_fields();
        $this->init_settings();

        $this->title           = $this->get_option('title');
        $this->description     = $this->get_option('description');
        $this->enabled         = $this->get_option('enabled');
        $this->app_id          = $this->get_option('app_id');
        $this->merchant_number = $this->get_option('merchant_number');
        $this->public_key      = $this->get_option('merchant_public_key');
        $this->private_key     = $this->get_option('merchant_private_key');
        $this->env             = $this->get_option('env');

        $this->init_hooks();

        $this->signature = new Signature([
            'public_key'  => $this->public_key,
            'private_key' => $this->private_key,
        ]);

        $this->beyla = new Beyla([
            'app_id'          => $this->app_id,
            'merchant_number' => $this->merchant_number,
            'gateway'         => self::class,
            'env'             => $this->env,
        ]);

    }

    function admin_options() {
        require_once WC_PAYERMAX_DIR . '/includes/admin/admin-options.php';

    }

    function process_admin_options() {
        $post_data = $this->get_post_data();
        $name      = $post_data[$this->id];

        if (isset($name)) {
            update_option($this->id, $name);
        }

        parent::process_admin_options();
    }

    function add_media_script($hook_suffix) {
        if ($hook_suffix === "woocommerce_page_wc-settings") {
            wp_enqueue_media();
        }
    }

    public function check_payermax_settings() {
        if ($this->enabled === 'no') {
            return;
        }

        if (empty($this->app_id) || empty($this->merchant_number) || empty($this->public_key) || empty($this->private_key)) {
            $class   = 'notice notice-error';
            $message = WC_PayerMax::trans('notice.settings');
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
        }
    }

    public function init_hooks() {
        add_action('admin_enqueue_scripts', [$this, 'add_media_script']);
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_thankyou_' . $this->id, [$this, 'thankyou_page']);
        // add_action('woocommerce_admin_order_data_after_order_details', [$this, 'check_order_details']);
        add_action('woocommerce_api_' . $this->id, [$this, 'check_order_notify']);
        add_action('admin_notices', [$this, 'check_payermax_settings']);
    }

    public function init_form_fields() {
        $this->form_fields = require WC_PAYERMAX_DIR . '/includes/admin/payermax-settings.php';
    }

    /**
     * Process the payment and return the result.
     *
     * @see https://woocommerce.com/document/payment-gateway-api/
     * @param int $order_id Order ID.
     * @return array
     */
    public function process_payment($orderId) {
        if (empty($this->app_id) || empty($this->merchant_number) || empty($this->public_key) || empty($this->private_key)) {
            return wc_add_notice('Payment misconfiguration', 'error');
        }

        $order = new WC_Order($orderId);

        // After the order transaction fails,
        // re initiate the payment to generate a new transaction id
        if ($order->get_status() == 'failed') {
            $order->set_transaction_id('');
            $order->save();
        }

        $api      = new Api($this);
        $response = $api->pay($order);

        $redirectUrl = isset($response['data']) ? isset($response['data']['redirectUrl']) ? $response['data']['redirectUrl'] : '' : '';

        if (empty($redirectUrl)) {
            return wc_add_notice(isset($response['msg']) ? $response['msg'] : 'Payment exception, please try again later.', 'error');
        }

        $order->update_status('on-hold', WC_PayerMax::trans('process.on_hold'));
        WC()->cart->empty_cart();

        return [
            'result'   => 'success',
            'redirect' => $redirectUrl,
        ];
    }

    public function thankyou_page($orderId) {
        $order       = new WC_Order($orderId);
        $orderStatus = $order->get_status();

        if ($orderStatus == 'on-hold') {
            $api      = new Api($this);
            $response = $api->queryOrder($order);
            WC_PayerMax_Helper::mark_order_status($order, $response);
        }
    }

    /**
     * Order notification callback
     */
    public function check_order_notify() {
        if (!isset($_SERVER['HTTP_SIGN'])) {
            exit();
        }
        $data   = file_get_contents("php://input");
        $verify = $this->signature->verify($data, $_SERVER['HTTP_SIGN']);

        $this->beyla->report([
            'eventName' => 'show_ve',
            'pveCur'    => '/notify',
            'extras'    => [
                'notify_data' => $data,
                'sign'        => $_SERVER['HTTP_SIGN'],
                'verify'      => $verify ? 'yes' : 'no',
            ],
        ]);

        if (!$verify) {
            exit();
        }

        $post = json_decode($data, true);
        if ($post['code'] == 'APPLY_SUCCESS') {
            $orderId = $post['data']['reference'];
            $order   = new WC_Order($orderId);
            WC_PayerMax_Helper::mark_order_status($order, $post);

        }

        header('Content-Type:application/json');
        status_header(200);

        exit(json_encode([
            'code' => 'SUCCESS',
            'msg'  => 'Success',
        ]));
    }
}
