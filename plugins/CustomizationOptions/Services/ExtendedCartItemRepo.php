<?php

namespace Plugin\CustomizationOptions\Services;

use InnoShop\Common\Repositories\CartItemRepo;
use InnoShop\Common\Models\CartItem;
use InnoShop\Common\Models\Product\Sku;
use Illuminate\Support\Facades\Log;

class ExtendedCartItemRepo extends CartItemRepo
{
    // Override the model property to use CartItem
    protected string $model = CartItem::class;

    public static function getInstance(): static
    {
        return app(static::class);
    }

    public function create($data): mixed
    {
        // 提取定制选项数据
        $customizations = null;
        if (isset($data['customizations'])) {
            $customizations = $data['customizations'];
            unset($data['customizations']);
        }

        // 处理数据
        $processedData = $this->handleDataWithCustomizations($data, $customizations);

        $filters = [
            'sku_code'    => $processedData['sku_code'],
            'customer_id' => $processedData['customer_id'],
            'guest_id'    => $processedData['guest_id'],
        ];

        $existingCart = $this->builder($filters)->first();
        if ($existingCart) {
            // 如果找到相同的项目，增加数量并更新定制选项
            $existingCart->increment('quantity', $processedData['quantity']);

            // 如果有定制选项，更新reference字段
            if ($customizations) {
                $existingCart->reference = json_encode(['customizations' => $customizations]);
                $existingCart->save();
            }

            return $existingCart;
        } else {
            // 创建新的购物车项目
            $cart = new CartItem();

            // 逐个设置属性，避免fillable限制
            $cart->product_id  = $processedData['product_id'];
            $cart->sku_code    = $processedData['sku_code'];
            $cart->customer_id = $processedData['customer_id'];
            $cart->guest_id    = $processedData['guest_id'];
            $cart->selected    = $processedData['selected'];
            $cart->quantity    = $processedData['quantity'];

            // 直接设置reference字段
            if (isset($processedData['reference'])) {
                $cart->reference = $processedData['reference'];
            }

            $cart->saveOrFail();
            return $cart;
        }
    }

    private function handleDataWithCustomizations($requestData, $customizations): array
    {
        $skuId = $requestData['skuId'] ?? ($requestData['sku_id'] ?? 0);
        if ($skuId) {
            $sku = Sku::query()->findOrFail($skuId);
        } else {
            $sku = Sku::query()->where('code', $requestData['sku_code'] ?? '')->firstOrFail();
        }

        // 确保正确获取客户ID和访客ID
        $customerID = $requestData['customer_id'] ?? current_customer_id();
        $guestID    = $requestData['guest_id'] ?? current_guest_id();

        $data = [
            'product_id'  => $sku->product_id,
            'sku_code'    => $sku->code,
            'customer_id' => $customerID,
            'guest_id'    => $customerID ? '' : $guestID,
            'selected'    => true,
            'quantity'    => (int)($requestData['quantity'] ?? 1),
        ];

        // 确保定制选项被正确保存
        if ($customizations) {
            $data['reference'] = json_encode(['customizations' => $customizations]);
        }

        return $data;
    }
}
