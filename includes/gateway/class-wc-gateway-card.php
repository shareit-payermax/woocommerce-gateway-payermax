<?php
/**
 * Payment Gateway
 *
 * @class          WC_Gateway_PayerMax_CARD
 * @extends        WC_Payment_Gateway
 * @author         PayerMax
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_Gateway_PayerMax_CARD extends WC_Gateway_PayerMax {
    public $id   = 'WC_Gateway_PayerMax_CARD';
    public $name = 'card';

    public $paymentMethod = 'CARD';

    public function __construct() {
        parent::__construct();

        $this->method_title       = __(WC_PayerMax::trans('settings.standard.method_title'), $this->id);
        $this->method_description = __(WC_PayerMax::trans('settings.card.method_description'), $this->id);

    }
}
