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

        \Illuminate\Support\Facades\Log::info('注册前的 Eventy 状态', [
            'filters' => $eventy->getFilter(),
            'instance' => spl_object_id($eventy)
        ]);

        // 直接调用 addFilter 检查是否能成功注册
        $eventy->addFilter('component.sidebar.plugin.routes', function ($data) {
            \Illuminate\Support\Facades\Log::info('钩子回调开始执行');

            $menuItem = [
                'route' => 'partner_links.index',
                'title' => '友情链接',
                'icon' => 'link',
                'sort' => 100
            ];

            $data[] = $menuItem;

            \Illuminate\Support\Facades\Log::info('钩子回调执行完成', [
                'data' => $data
            ]);

            return $data;
        });

        \Illuminate\Support\Facades\Log::info('注册后的 Eventy 状态', [
            'filters' => $eventy->getFilter(),
            'instance' => spl_object_id($eventy)
        ]);

        listen_blade_insert('layouts.footer.top', function ($data) {
            $data['links'] = PartnerLink::query()->where('active', 1)->limit(10)->get();

            return view('PartnerLink::front.partner_links', $data);
        });
    }
}
