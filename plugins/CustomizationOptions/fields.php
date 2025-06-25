<?php
return [
    [
        'name' => 'custom_name_fee',
        'label' => '定制名字费用',
        'label_key' => 'common.name_fee',
        'type' => 'string',
        'required' => true,
        'rules' => 'required|numeric|min:0'
    ],
    [
        'name' => 'custom_number_fee',
        'label' => '定制号码费用',
        'label_key' => 'common.number_fee',
        'type' => 'string',
        'required' => true,
        'rules' => 'required|numeric|min:0'
    ]
];
