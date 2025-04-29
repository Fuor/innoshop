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

        // 在注册钩子前检查已有的过滤器
        \Illuminate\Support\Facades\Log::info('注册前的过滤器', [
            'filters' => $eventy->getFilter()  // 如果有这个方法的话
        ]);

        listen_hook_filter('component.sidebar.plugin.routes', function ($data) {
            // 在钩子中记录日志
            \Illuminate\Support\Facades\Log::info('Sidebar hook triggered', [
                'data' => $data
            ]);

            $menuItem = [
                'route' => 'partner_links.index',
                'title' => '友情链接',
                'icon' => 'link',
                'sort' => 100
            ];

            $data[] = $menuItem;

            if (config('app.debug') && has_debugbar()) {
                Debugbar::info('添加菜单后的数据', [
                    '新增项' => $menuItem,
                    '最终数据' => $data
                ]);
            }

            return $data;
        });

        // 检查钩子是否已注册
        \Illuminate\Support\Facades\Log::info('注册后的过滤器', [
            'filters' => $eventy->getFilter()  // 如果有这个方法的话
        ]);

        listen_blade_insert('layouts.footer.top', function ($data) {
            $data['links'] = PartnerLink::query()->where('active', 1)->limit(10)->get();

            return view('PartnerLink::front.partner_links', $data);
        });
    }
}
