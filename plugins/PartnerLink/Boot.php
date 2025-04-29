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

        \Illuminate\Support\Facades\Log::info('开始注册钩子');

        listen_hook_filter('component.sidebar.plugin.routes', function ($data) {
            \Illuminate\Support\Facades\Log::info('钩子执行', [
                'data' => $data
            ]);

            $menuItem = [
                'route' => 'partner_links.index',
                'title' => '友情链接',
                'icon' => 'link',
                'sort' => 100
            ];

            if (is_array($data)) {
                $data[] = $menuItem;
            } else {
                $data = [$menuItem];
            }

            return $data;
        });

        // 检查注册后的状态
        $eventy = app('eventy');
        $reflection = new \ReflectionClass($eventy);
        $filterProp = $reflection->getProperty('filter');
        $filterProp->setAccessible(true);

        \Illuminate\Support\Facades\Log::info('注册后的钩子状态', [
            'filters' => $filterProp->getValue($eventy)
        ]);

        listen_blade_insert('layouts.footer.top', function ($data) {
            $data['links'] = PartnerLink::query()->where('active', 1)->limit(10)->get();

            return view('PartnerLink::front.partner_links', $data);
        });
    }
}
