<?php
/**
 * @Desc:
 * @Author: 黄辉全
 * @Time: 2025/4/30 14:08
 */

return [
    'mode'           => plugin_setting('paypal', 'mode', 'sandbox'), // 可以是 'sandbox' 或 'live'
    'sandbox'        => [
        'client_id'     => plugin_setting('paypal', 'client_id', ''),
        'client_secret' => plugin_setting('paypal', 'client_secret', ''),
    ],
    'live'           => [
        'client_id'     => plugin_setting('paypal', 'client_id', ''),
        'client_secret' => plugin_setting('paypal', 'client_secret', ''),
    ],
    'payment_action' => 'Sale',
    'currency'       => strtoupper(current_currency_code()),
    'notify_url'     => '',
    'locale'         => 'en_US',
    'validate_ssl'   => true,
];
