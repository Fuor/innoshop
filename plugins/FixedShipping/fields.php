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
];
