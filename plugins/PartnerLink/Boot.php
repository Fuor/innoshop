<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PartnerLink;

use Plugin\PartnerLink\Models\PartnerLink;
use Barryvdh\Debugbar\Facades\Debugbar;

class Boot
{
    public function init(): void
    {

        $eventy = app('eventy');

        // 检查内部属性
        $reflection = new \ReflectionClass($eventy);
        $properties = array_map(function($prop) use ($eventy) {
            $prop->setAccessible(true);
            return [
                'name' => $prop->getName(),
                'value' => $prop->getValue($eventy)
            ];
        }, $reflection->getProperties());

        \Illuminate\Support\Facades\Log::info('Eventy 内部属性', [
            'properties' => $properties
        ]);

        $eventy->addFilter('component.sidebar.plugin.routes', function ($data) {
            \Illuminate\Support\Facades\Log::info('钩子执行', [
                'data' => $data
            ]);
            return array_merge($data, [[
                'route' => 'partner_links.index',
                'title' => '友情链接',
                'icon' => 'link',
                'sort' => 100
            ]]);
        });

        listen_blade_insert('layouts.footer.top', function ($data) {
            $data['links'] = PartnerLink::query()->where('active', 1)->limit(10)->get();

            return view('PartnerLink::front.partner_links', $data);
        });
    }
}
