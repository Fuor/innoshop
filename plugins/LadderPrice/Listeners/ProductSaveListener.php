<?php

namespace Plugin\LadderPrice\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

// 引入 Request Facade

class ProductSaveListener
{
    // handle 方法只接收 $product 参数
    public static function handle($product)
    {
        // 直接从请求中获取所有数据
        $requestData = Request::all();

        if (!$product || !$product->masterSku) {
            return $product; // 过滤器需要返回 $product
        }

        // 确认 $requestData 中是否包含 'ladder_prices' 键
        if (!isset($requestData['ladder_prices'])) {
            return $product; // 过滤器需要返回 $product
        }

        $ladderPrices = json_decode($requestData['ladder_prices'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $ladderPrices = [];
        }

        $sku                = $product->masterSku;
        $sku->ladder_prices = $ladderPrices;

        $sku->save();

        return $product; // 过滤器必须返回 $product
    }
}
