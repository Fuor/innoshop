<?php
/**
 * @Desc:
 * @Author: Fuor
 * @Time: 2025/4/30 11:34
 */

return [
    [
        'name'      => 'client_id',
        'label'     => 'PayPal客户端ID',
        'label_key' => 'common.client_id',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required',
    ],
    [
        'name'      => 'client_secret',
        'label'     => 'PayPal客户端密钥',
        'label_key' => 'common.client_secret',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required',
    ],
    [
        'name'      => 'mode',
        'label'     => '运行模式',
        'label_key' => 'common.mode',
        'type'      => 'select',
        'options'   => [
            [
                'value' => 'sandbox',
                'label' => 'Sandbox (测试模式)'
            ],
            [
                'value' => 'live',
                'label' => 'Live (正式模式)'
            ]
        ],
        'required'  => true,
        'rules'     => 'required',
    ],
    [
        'name'      => 'available',
        'label'     => '开启端',
        'label_key' => 'common.available_platforms',
        'type'      => 'checkbox',
        'options'   => [
            [
                'value' => 'pc_web',
                'label' => 'PC Web'
            ],
            [
                'value' => 'mobile_web',
                'label' => 'Mobile Web'
            ]
        ],
        'required'  => true,
        'rules'     => 'required',
    ],
];
