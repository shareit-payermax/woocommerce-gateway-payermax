<?php

return [
    'notice'       => [
        'settings' => 'PayerMax Payment Settings: * is required.',
    ],

    'process'      => [
        'on_hold' => 'Waiting for payment',
    ],

    'note'         => [
        'transaction_id' => 'Set transaction ID: %id%',
        'payment_status' => 'Payment Status: %status%',
    ],

    'icon'         => 'ICON',
    'upload_icon'  => 'Upload ICON',
    'remove'       => 'Remove',
    'check_status' => 'Check Payment Status',

    'settings'     => [
        'enabled'              => [
            'title' => 'Enable/Disable',
            'label' => 'Enable this module?',
        ],
        'app_id'               => [
            'title'       => 'App ID *',
            'description' => 'App ID',
        ],
        'merchant_number'      => [
            'title'       => 'Merchant No. *',
            'description' => 'Merchant Number',
        ],
        'merchant_public_key'  => [
            'title'       => 'Merchant Public Key *',
            'description' => 'Merchant Public Key',
        ],
        'merchant_private_key' => [
            'title'       => 'Merchant Private Key *',
            'description' => 'Merchant Private Key',
        ],
        'env'                  => [
            'title'       => 'Environment',
            'description' => 'Set the environment for payment requests',
        ],
        'title'                => [
            'title'       => 'Title',
            'description' => 'Enter the title for the payment method displayed in the checkout and order confirmation emails',
        ],
        'description'          => [
            'title'       => 'Description',
            'description' => 'Enter the description of the payment method displayed in the checkout page.',
        ],

        'standard'             => [
            'title'              => 'Standard Cashier',
            'description'        => '',
            'method_title'       => 'PayerMax Payment',
            'method_description' => 'Achieve all-faceted payment capabilities globally hosted on Payermax, and quickly collect payment on various devices.',
        ],

        'card'                 => [
            'title'              => 'Credit/Debit Card payment',
            'description'        => '',
            'method_description' => 'Only Credit/Debit card payment is supported.',
        ],
    ],
];
