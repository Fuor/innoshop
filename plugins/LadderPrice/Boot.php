<?php

namespace Plugin\LadderPrice;

use Illuminate\Support\Facades\Log;
use InnoShop\Common\Models\Product\Sku as SkuModel;

// 引入 Sku 模型

class Boot
{
    public function init(): void
    {
        // 在商品编辑页面的价格区域下方插入阶梯价格表单
        listen_blade_insert('panel.product.edit.form_single.after', function ($data) {
            // 确保 $product 变量可用
            $product = $data['product'] ?? null;
            if ($product) {
                return view('LadderPrice::panel.products.ladder_price_form', compact('product'));
            }
            return '';
        });

        // 3. 注册 resource.cart_item 钩子，用于在购物车列表生成时应用阶梯价
        // 优先级设置为 10，确保在 CustomizationOptions 插件的 resource.cart_item 钩子之前执行
        listen_hook_filter('resource.cart.item', function ($data) {
            // 直接从 $data 数组中获取 sku_id 和 quantity
            $skuId            = $data['sku_id'] ?? null;
            $cartItemQuantity = $data['quantity'] ?? 0;

            $sku = null;
            if ($skuId) {
                // 从数据库中加载完整的 Sku 模型，包含 ladder_prices
                $sku = SkuModel::find($skuId);
            }

            // 计算阶梯价格并更新主商品价格
            if ($sku && is_array($sku->ladder_prices) && !empty($sku->ladder_prices)) {
                foreach ($sku->ladder_prices as $rule) {
                    if (isset($rule['min_quantity']) && isset($rule['max_quantity']) && isset($rule['price'])) {
                        if ($cartItemQuantity >= $rule['min_quantity'] && $cartItemQuantity <= $rule['max_quantity']) {
                            $data['price']        = (float)$rule['price']; // 直接更新 $data['price']
                            $data['price_format'] = currency_format(
                                (float)$rule['price']
                            ); // 直接更新 $data['price_format']
                            break;
                        }
                    }
                }
            }

            // 重新计算 subtotal，包含阶梯价格
            $calculatedSubtotal      = $data['price'] * $cartItemQuantity;
            $data['subtotal']        = $calculatedSubtotal; // 直接更新 $data['subtotal']
            $data['subtotal_format'] = currency_format($calculatedSubtotal); // 直接更新 $data['subtotal_format']

            return $data;
        }, 10);

        // 新增 service.cart.response 钩子，重新计算总金额
        listen_hook_filter('service.cart.response', function ($data) {
            $recalculatedAmount   = 0;
            $recalculatedQuantity = 0;
            foreach ($data['list'] as $item) {
                // 这里的 $item['subtotal'] 已经是经过 resource.cart_list_item 钩子处理后的值
                // 包含了阶梯价格和定制项费用
                // 并且 $item['selected'] 属性表示该商品是否被选中
                if ($item['selected']) { // 只计算选中的商品
                    $recalculatedAmount   += $item['subtotal'];
                    $recalculatedQuantity += $item['quantity'];
                }
            }

            $data['amount']        = $recalculatedAmount;
            $data['amount_format'] = currency_format($recalculatedAmount);

            $data['total']        = $recalculatedQuantity;
            $data['total_format'] = $recalculatedQuantity <= 99 ? $recalculatedQuantity : '99+';


            return $data;
        }, 5); // 设置一个非常高的优先级（数字越小优先级越高），确保在 CustomizationOptions 插件的 service.cart.response 钩子之前执行

        // 使用 @hookupdate('front.product.show.price') 钩子来替换价格显示
        listen_blade_update('front.product.show.price', function ($output, $data) {
            $skuData = $data['sku'] ?? null; // 这是 SkuListItem 转换后的数组
            $product = $data['product'] ?? null; // 获取 Product 模型

            if ($skuData && $product) {
                // 从数据库中重新加载完整的 Sku 模型，包含 ladder_prices
                $fullSku = SkuModel::find($skuData['id']);
                if ($fullSku && is_array(
                        $fullSku->ladder_prices
                    ) && !empty($fullSku->ladder_prices)) {
                    // 将 ladder_prices 添加到 $skuData 数组中
                    $skuData['ladder_prices'] = $fullSku->ladder_prices;

                    // 渲染包含阶梯价格表和动态JS的视图
                    // 将修改后的 $skuData 传递给视图
                    return view('LadderPrice::front.product_price_display', ['sku' => $skuData, 'product' => $product]
                    )->render();
                }
            }
            return $output; // 如果没有 SKU 或阶梯价，返回原始内容
        });
    }
}
