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

        // 调试 addFilter 方法的参数和返回值
        $result = $eventy->addFilter('component.sidebar.plugin.routes', function ($data) {
            \Illuminate\Support\Facades\Log::info('钩子回调执行中');
            return array_merge($data, [[
                'route' => 'partner_links.index',
                'title' => '友情链接',
                'icon' => 'link',
                'sort' => 100
            ]]);
        });

        \Illuminate\Support\Facades\Log::info('addFilter 执行结果', [
            'result' => $result,
            'filters_raw' => get_object_vars($eventy),  // 查看对象的原始属性
            'debug_backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)
        ]);

        // 手动触发过滤器看是否能执行
        $testData = [];
        $filtered = $eventy->filter('component.sidebar.plugin.routes', $testData);

        \Illuminate\Support\Facades\Log::info('手动触发过滤器', [
            'input' => $testData,
            'output' => $filtered
        ]);

        listen_blade_insert('layouts.footer.top', function ($data) {
            $data['links'] = PartnerLink::query()->where('active', 1)->limit(10)->get();

            return view('PartnerLink::front.partner_links', $data);
        });
    }
}
