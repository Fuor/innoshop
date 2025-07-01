<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Carousel;

use Jenssegers\Agent\Agent;
use Plugin\Carousel\Models\Carousel;
use Plugin\Carousel\Models\Page;
use Illuminate\Support\Facades\Log;

class Boot
{
    private $topCarousel;

    private $bottomCarousel;

    private $agent;

    public function init(): void
    {
        // 在插件的 ServiceProvider 的 boot 方法中注册钩子
        listen_hook_filter('home.index.data', function ($data) {
            // 将 slideshow 数据置空
            $data['slideshow'] = [];

            return $data;
        });

        listen_hook_filter('panel.component.sidebar.setting.routes', function ($data) {
            $data[] = [
                'route' => 'carousels.index',
                'title' => '轮播图',
            ];

            return $data;
        });

        $pageSlug = str_replace('/', '', request()->getPathInfo());

        // 获取当前语言代码
        $currentLocale = app()->getLocale();

        // 如果路径是空的（默认首页）或者与当前语言代码匹配（带语言的首页）
        if ($pageSlug == '' || $pageSlug == $currentLocale) {
            $page     = new Page();
            $page->id = 0; // 首页的 page_id 为 0
        } else {
            $page = Page::where('slug', $pageSlug)->first();
        }

        if ($page) {
            $this->topCarousel    = Carousel::where('page_id', $page->id)->where('position', 'top')->where('active', true)->orderBy('position', 'asc')->get();
            $this->bottomCarousel = Carousel::where('page_id', $page->id)->where('position', 'bottom')->where('active', true)->orderBy('position', 'asc')->get();
            $this->agent          = new Agent();

            if ($this->topCarousel->count()) {
                listen_blade_insert('home.content.top', function ($data) {
                    $data['carousels'] = $this->topCarousel;
                    $data['agent']     = $this->agent;

                    return view('Carousel::front.carousels', $data);
                });
            }
            if ($this->bottomCarousel->count()) {
                listen_blade_insert('home.content.bottom', function ($data) {
                    $data['carousels'] = $this->bottomCarousel;
                    $data['agent']     = $this->agent;

                    return view('Carousel::front.carousels', $data);
                });
            }
        }
    }
}
