<?php

namespace Plugin\CustomizationOptions;

use Plugin\CustomizationOptions\Services\CustomizationFeeService;
use Illuminate\Support\Facades\Log;

class Boot
{
    public function init(): void
    {
        // 完全替换CartService类的静态调用
        app()->singleton(\InnoShop\Common\Services\CartService::class, function ($app, $parameters) {
            return new \Plugin\CustomizationOptions\Services\ExtendedCartService(
                $parameters['customerID'] ?? 0,
                $parameters['guestID'] ?? ''
            );
        });

        // 拦截购物车存储请求
        listen_hook_action('front.cart.store.before', function ($request) {
            $customizations = $request->get('customizations');
            if ($customizations) {
                // 直接处理定制选项数据
                $this->handleCustomizationCart($request->all());

                // 构建响应数据
                $cartService  = \InnoShop\Common\Services\CartService::getInstance();
                $responseData = $cartService->handleResponse();

                // 直接返回JSON响应，阻止原始处理
                echo json_encode([
                    'success' => true,
                    'message' => 'Saved successfully',
                    'data'    => $responseData
                ]);
                exit; // 强制停止后续处理
            }
        });

        // 在产品详情页添加定制选项
        listen_blade_insert('product.detail.brand.after', function ($data) {
            return view('CustomizationOptions::front.product_customization', $data);
        });

        // 添加JavaScript处理逻辑到产品详情页
        listen_blade_insert('product.show.bottom', function ($data) {
            return view('CustomizationOptions::front.product_script', $data);
        });

        // 扩展购物车列表资源数据
        listen_hook_filter('resource.cart.item', function ($data) {
            // $data['cart'] 不再存在，需要从 $data 中获取原始 CartItem 模型的 ID
            // 假设 $data['id'] 是 CartItem 的 ID
            $cartItem = \InnoShop\Common\Models\CartItem::find($data['id']); // 根据 ID 重新加载 CartItem 模型

            if ($cartItem && isset($cartItem->reference)) { // 检查重新加载的 $cartItem
                $existingReference = is_string($cartItem->reference) ? json_decode(
                    $cartItem->reference,
                    true
                ) : $cartItem->reference;

                // 确保 $existingReference 是一个数组
                if (!is_array($existingReference)) {
                    $existingReference = [];
                }

                $reference = $existingReference; // 从现有 reference 中获取所有数据
                if (isset($reference['customizations'])) {
                    // 为每个定制项添加价格信息
                    $processedCustomizations = [];
                    foreach ($reference['customizations'] as $key => $value) {
                        $customItemPrice = 0;
                        if ($key === 'custom_name') {
                            $customItemPrice = (float)plugin_setting('customization_options', 'custom_name_fee', 0);
                        } elseif ($key === 'custom_number') {
                            $customItemPrice = (float)plugin_setting('customization_options', 'custom_number_fee', 0);
                        }
                        $processedCustomizations[$key] = [
                            'value' => $value,
                            'price' => $customItemPrice, // 保存下单时的单价
                        ];
                    }
                    $data['customizations'] = $processedCustomizations; // 直接更新 $data['customizations']

                    $data['customization_fee'] = $customizationFee = $this->calculateItemCustomizationFee(
                        $reference['customizations']
                    );

                    // 将定制费用加入到subtotal中
                    $originalSubtotal        = $data['subtotal']; // 直接从 $data 中获取 subtotal
                    $newSubtotal             = $originalSubtotal + ($customizationFee * $data['quantity']); // 直接从 $data 中获取 quantity
                    $data['subtotal']        = $newSubtotal; // 直接更新 $data['subtotal']
                    $data['subtotal_format'] = currency_format($newSubtotal); // 直接更新 $data['subtotal_format']

                    // 为每个定制项创建单独的“虚拟”行项目
                    foreach ($reference['customizations'] as $key => $value) {
                        $customItemPrice = 0;
                        $customItemTitle = '';

                        if ($key === 'custom_name') {
                            $customItemPrice = (float)plugin_setting('customization_options', 'custom_name_fee', 0);
                            $customItemTitle = __('CustomizationOptions::common.customize_name');
                        } elseif ($key === 'custom_number') {
                            $customItemPrice = (float)plugin_setting('customization_options', 'custom_number_fee', 0);
                            $customItemTitle = __('CustomizationOptions::common.customize_number');
                        }

                        if ($customItemPrice > 0) {
                            $data['children'][] = [ // 直接更新 $data['children']
                                'id'              => 'custom_' . $cartItem->id . '_' . $key, // 使用重新加载的 $cartItem->id
                                'product_name'    => '',
                                'sku_code'        => $customItemTitle . ' : ' . $value,
                                'quantity'        => $data['quantity'],
                                'price'           => $customItemPrice,
                                'price_format'    => currency_format($customItemPrice),
                                'subtotal'        => $customItemPrice * $data['quantity'],
                                'subtotal_format' => currency_format($customItemPrice * $data['quantity']),
                                'is_custom_item'  => true,
                            ];
                        }
                    }

                    // 关键修改：合并 customizations 到现有 reference 中
                    $reference['customizations'] = $processedCustomizations;
                    $data['reference']           = json_encode($reference); // 将合并后的 reference 编码回 JSON
                }
            }
            return $data;
        }, 20);

        // 扩展购物车响应数据，包含定制费用
        listen_hook_filter('service.cart.response', function ($data) {
            $recalculatedAmount   = 0;
            $recalculatedQuantity = 0;
            foreach ($data['list'] as $item) {
                if ($item['selected']) {
                    $recalculatedAmount   += $item['subtotal'];
                    $recalculatedQuantity += $item['quantity'];
                }
            }

            $data['amount']        = $recalculatedAmount;
            $data['amount_format'] = currency_format($recalculatedAmount);

            $data['total']        = $recalculatedQuantity;
            $data['total_format'] = $recalculatedQuantity <= 99 ? $recalculatedQuantity : '99+';

            return $data;
        }, 20);

        // 在购物车页面底部添加JavaScript来显示定制信息
        listen_blade_insert('cart.bottom', function ($data) {
            return view('CustomizationOptions::front.cart_script', $data);
        });

        // 注册费用服务到结账系统
        listen_hook_filter('service.checkout.fee.methods', function ($classes) {
            $classes[] = \Plugin\CustomizationOptions\Services\CustomizationFeeService::class;
            return $classes;
        });

        // 在结账页面产品信息后显示定制选项
        listen_blade_insert('checkout.product_item.after_sku', function ($data) {
            return view('CustomizationOptions::front.checkout_customization_hook', $data);
        });

        // 在订单详情页面产品信息后显示定制选项
        listen_blade_insert('panel.orders.info.order_items.list.after', function ($data) {
            return view('CustomizationOptions::panel.customizations_lists', $data);
        });
    }

    private function calculateItemCustomizationFee($customizations): float|int
    {
        $totalFee = 0;

        if (isset($customizations['custom_name'])) {
            $totalFee += (float)plugin_setting('customization_options', 'custom_name_fee', 0);
        }

        if (isset($customizations['custom_number'])) {
            $totalFee += (float)plugin_setting('customization_options', 'custom_number_fee', 0);
        }

        return $totalFee;
    }

    private function handleCustomizationCart($data)
    {
        // 使用您的扩展Repository直接处理
        $extendedRepo = new \Plugin\CustomizationOptions\Services\ExtendedCartItemRepo();
        return $extendedRepo->create($data);
    }
}
