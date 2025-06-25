<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories\Order;

use Exception;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Models\Product\Sku;
use InnoShop\Common\Repositories\BaseRepo;

class ItemRepo extends BaseRepo
{
    /**
     * @param  $order
     * @return array
     */
    public function getOptions($order): array
    {
        $options = [];
        foreach ($order->items as $item) {
            $options[] = [
                'key'   => $item->id,
                'label' => $item->name,
            ];
        }

        return $options;
    }

    /**
     * @param  Order  $order
     * @param  $items
     * @return void
     * @throws Exception
     */
    public function createItems(Order $order, $items): void
    {
        if (empty($items)) {
            throw new Exception('Empty cart list when create order items.');
        }

        $orderItems = [];
        foreach ($items as $item) {
            // 直接从 $item 中获取 customizations，因为它们现在已经包含在 $data['data'] 中
            $customizations = $item['customizations'] ?? null;

            $orderItems[] = $this->handleItem($order, $item, $customizations);
        }
        $order->items()->createMany($orderItems);
    }

    /**
     * @param  Order  $order
     * @param  $requestData
     * @return array
     */
    private function handleItem(Order $order, $requestData,  ?array $customizations = null): array
    {
        $sku = Sku::query()->where('code', $requestData['sku_code'])->firstOrFail();

        return [
            'order_id'       => $requestData['order_id'] ?? 0,
            'product_id'     => $sku->product_id,
            'order_number'   => $order->number,
            'product_sku'    => $sku->code,
            'variant_label'  => $requestData['variant_label'] ?? $sku->variant_label,
            'name'           => $requestData['product_name'],
            'image'          => $requestData['image'],
            'quantity'       => $requestData['quantity'],
            'price'          => $requestData['price'],
            'customizations' => $customizations, // 将定制信息保存到新的字段
        ];
    }
}
