<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    [
        'name'      => 'type',
        'label'     => '计算方式',
        'label_key' => 'common.type',
        'type'      => 'select',
        'options'   => [
            ['value' => 'fixed', 'label_key' => 'common.fixed', 'label' => '固定运费'],
            ['value' => 'percent', 'label_key' => 'common.percent', 'label' => '百分比'],
            ['value' => 'weight', 'label_key' => 'common.weight', 'label' => '按重量计费'],
            ['value' => 'country', 'label_key' => 'common.country', 'label' => '按国家计费'],
        ],
        'required'  => true,
        'rules'     => 'required',
    ],
    [
        'name'      => 'value',
        'label'     => '运费值',
        'label_key' => 'common.value',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required',
    ],
    [
        'name'      => 'country_rates',
        'label'     => '国家费率',
        'label_key' => 'common.country_rates',
        'type'      => 'textarea',
        'description' => '按国家计费时的各国家费率，格式为：国家代码=费率，每行一个',
        'required'  => false,
        'show_if'   => ['type' => '', '==', 'country'],
    ],
];
