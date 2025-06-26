<?php

namespace Plugin\CustomizationOptions\Services;

use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Get filter builder.
     *
     * @param array $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        // 调用父类的 builder 方法获取基础查询
        $builder = parent::builder($filters);

        $customizations = $filters['customizations'] ?? null;
        if ($customizations) {
            $builder->where(function ($query) use ($customizations) {
                foreach ($customizations as $key => $value) { // 直接获取值，而不是 $detail['value']
                    // 匹配定制项的键和值
                    // 根据您提供的结构，直接匹配 reference->customizations->{key}
                    $query->whereJsonContains('reference->customizations->' . $key, $value);
                }
            });
        } else {
            // 如果没有定制项，确保只匹配没有定制项的购物车项目
            $builder->where(function ($query) {
                $query->whereNull('reference')
                    ->orWhereJsonLength('reference->customizations', 0);
            });
        }

        return $builder;
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
            'sku_code'       => $processedData['sku_code'],
            'customer_id'    => $processedData['customer_id'],
            'guest_id'       => $processedData['guest_id'],
            'customizations' => $customizations, // 将定制信息传递给 builder
        ];

        $existingCart = $this->builder($filters)->first();
        if ($existingCart) {
            // 如果找到相同的项目，增加数量并更新定制选项
            $existingCart->increment('quantity', $processedData['quantity']);

            // 如果有定制选项，更新reference字段
            if ($customizations) {
                $currentReference = json_decode($existingCart->reference, true);
                if (!is_array($currentReference)) {
                    $currentReference = [];
                }
                $currentReference['customizations'] = $customizations;
                $existingCart->reference            = json_encode($currentReference);
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
