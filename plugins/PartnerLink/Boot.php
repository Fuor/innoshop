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

        // 直接在 filter 属性中添加回调
        $tag = 'component.sidebar.plugin.routes';
        $priority = 10;
        $callback = function ($data) {
            \Illuminate\Support\Facades\Log::info('钩子执行前', [
                'data' => $data
            ]);

            $menuItem = [
                'route' => 'partner_links.index',
                'title' => '友情链接',
                'icon' => 'link',
                'sort' => 100
            ];

            $result = is_array($data) ? array_merge($data, [$menuItem]) : [$menuItem];

            \Illuminate\Support\Facades\Log::info('钩子执行后', [
                'result' => $result
            ]);

            return $result;
        };

        // 手动添加过滤器
        $reflection = new \ReflectionClass($eventy);
        $filterProp = $reflection->getProperty('filter');
        $filterProp->setAccessible(true);

        $filters = $filterProp->getValue($eventy);
        if (!isset($filters['TorMorten\Eventy\Filter'][$tag][$priority])) {
            $filters['TorMorten\Eventy\Filter'][$tag][$priority] = [];
        }
        $filters['TorMorten\Eventy\Filter'][$tag][$priority][] = $callback;

        $filterProp->setValue($eventy, $filters);

        \Illuminate\Support\Facades\Log::info('手动注册后的钩子状态', [
            'filters' => $filterProp->getValue($eventy)
        ]);

        listen_blade_insert('layouts.footer.top', function ($data) {
            $data['links'] = PartnerLink::query()->where('active', 1)->limit(10)->get();

            return view('PartnerLink::front.partner_links', $data);
        });
    }
}
