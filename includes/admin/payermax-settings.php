<?php

if (!defined('ABSPATH')) {
    exit;
}

$envOptions    = WC_PayerMax::$config::ENV;
$default_title = WC_PayerMax::trans('settings.' . $this->name . '.title');

return apply_filters(
    'wc_payermax_settings',
    [
        'title'                => [
            'title'   => __(WC_PayerMax::trans('settings.title.title'), $this->id),
            'type'    => 'text',
            'default' => __($default_title, $this->id),
        ],
        'description'          => [
            'title' => __(WC_PayerMax::trans('settings.description.title'), $this->id),
            'type'  => 'textarea',
            'css'   => 'max-width: 650px;',
        ],
        'app_id'               => [
            'title' => __(WC_PayerMax::trans('settings.app_id.title'), $this->id),
            'type'  => 'text',
        ],
        'merchant_number'      => [
            'title' => __(WC_PayerMax::trans('settings.merchant_number.title'), $this->id),
            'type'  => 'text',
        ],
        'merchant_public_key'  => [
            'title' => __(WC_PayerMax::trans('settings.merchant_public_key.title'), $this->id),
            'type'  => 'textarea',
            'css'   => 'max-width: 650px; min-height:150px;',
        ],
        'merchant_private_key' => [
            'title' => __(WC_PayerMax::trans('settings.merchant_private_key.title'), $this->id),
            'type'  => 'textarea',
            'css'   => 'max-width: 650px; min-height:150px;',
        ],
        'env'                  => [
            'title'   => __(WC_PayerMax::trans('settings.env.title'), $this->id),
            'type'    => 'select',
            'default' => 'PROD',
            'options' => $envOptions,
        ],
        'enabled'              => [
            'title'   => __(WC_PayerMax::trans('settings.enabled.title'), $this->id),
            'type'    => 'checkbox',
            'label'   => __(WC_PayerMax::trans('settings.enabled.label'), $this->id),
            'default' => 'no',
        ],
    ]
);
