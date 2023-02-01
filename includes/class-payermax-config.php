<?php

class WC_PayerMax_Config {

    const GATEWAY = [
        'UAT'  => 'https://pay-gate-uat.payermax.com',
        'PROD' => 'https://pay-gate.payermax.com',
    ];

    const BEYLA_GATEWAY = [
        'PROD' => 'https://receiver-metis.infeng.site',
    ];

    const ENV = [
        'UAT'  => 'TEST',
        'PROD' => 'PROD',
    ];
}
