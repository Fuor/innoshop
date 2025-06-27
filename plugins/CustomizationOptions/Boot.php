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
        listen_hook_filter('resource.cart_list_item', function ($data) {
            $cartItem = $data['cart'];
            if (isset($cartItem->reference)) {
                $reference = is_string($cartItem->reference) ? json_decode(
                    $cartItem->reference,
                    true
                ) : $cartItem->reference;
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
                    $data['data']['customizations'] = $processedCustomizations; // 更新 customizations 字段

                    $data['data']['customization_fee'] = $customizationFee = $this->calculateItemCustomizationFee(
                        $reference['customizations']
                    );

                    // 将定制费用加入到subtotal中
                    $originalSubtotal                = $data['data']['price'] * $cartItem->quantity;
                    $newSubtotal                     = $originalSubtotal + ($customizationFee * $cartItem->quantity);
                    $data['data']['subtotal']        = $newSubtotal;
                    $data['data']['subtotal_format'] = currency_format($newSubtotal);

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
                            $data['data']['children'][] = [
                                'id'              => 'custom_' . $cartItem->id . '_' . $key, // 唯一ID
                                'product_name'    => '',// 定制项没有产品名称
                                'sku_code'        => $customItemTitle . ' : ' . $value,
                                'quantity'        => $data['data']['quantity'], // 定制项数量与产品数量一致
                                'price'           => $customItemPrice,
                                'price_format'    => currency_format($customItemPrice),
                                'subtotal'        => $customItemPrice * $data['data']['quantity'],
                                'subtotal_format' => currency_format($customItemPrice * $data['data']['quantity']),
                                'is_custom_item'  => true, // 标记为定制项
                            ];
                        }
                    }
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
