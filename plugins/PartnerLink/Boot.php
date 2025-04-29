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

class Boot
{
    public function init(): void
    {
        \Log::info('PartnerLink Boot init called');

        add_filter('panel.sidebar.routes', function ($data) {
            \Log::info('Sidebar hook triggered', ['current_data' => $data]);

            $data[] = [
                'route' => 'partner_links.index',
                'title' => '友情链接',
                'icon' => 'link', // 添加图标
                'sort' => 100    // 添加排序
            ];

            return $data;
        });

//        listen_hook_filter('component.sidebar.plugin.routes', function ($data) {
//            $data[] = [
//                'route' => 'partner_links.index',
//                'title' => '友情链接',
//            ];
//
//            return $data;
//        });

        listen_hook_filter('component.sidebar.plugin.routes', function ($data) {
            \Log::info('Sidebar hook called', [
                'data' => $data,
                'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2),
                'time' => now()->toDateTimeString(),
                'memory' => memory_get_usage(true)
            ]);

            try {
                $newItem = [
                    'route' => 'partner_links.index',
                    'title' => '友情链接',
                ];
                $data[] = $newItem;

                \Log::info('After adding menu item', [
                    'newItem' => $newItem,
                    'finalData' => $data
                ]);

                return $data;
            } catch (\Exception $e) {
                \Log::error('Error in sidebar hook', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        });

        listen_blade_insert('layouts.footer.top', function ($data) {
            $data['links'] = PartnerLink::query()->where('active', 1)->limit(10)->get();

            return view('PartnerLink::front.partner_links', $data);
        });
    }
}
