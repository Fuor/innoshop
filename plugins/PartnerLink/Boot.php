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
        // 1. 先检查条件是否满足
        var_dump([
            'debug_enabled' => config('app.debug'),
            'has_debugbar' => has_debugbar(),
            'hook_name' => 'component.sidebar.plugin.routes'
        ]);

        if (config('app.debug') && has_debugbar()) {
            Debugbar::info('Boot init 开始执行');
        }

        listen_hook_filter('component.sidebar.plugin.routes', function ($data) {
            if (config('app.debug') && has_debugbar()) {
                Debugbar::info('侧边栏钩子被触发', [
                    '当前数据' => $data
                ]);
            }

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

        listen_blade_insert('layouts.footer.top', function ($data) {
            $data['links'] = PartnerLink::query()->where('active', 1)->limit(10)->get();

            return view('PartnerLink::front.partner_links', $data);
        });
    }
}
