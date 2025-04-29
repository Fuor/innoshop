<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FixedShipping;

use InnoShop\Common\Entities\ShippingEntity;
use InnoShop\Plugin\Core\BaseBoot;

class Boot extends BaseBoot
{
    public function init()
    {
    }

    /**
     * Get quotes.
     *
     * @param ShippingEntity $entity
     * @return array
     */
    public function getQuotes(ShippingEntity $entity): array
    {
        $code          = $this->plugin->getCode();
        $resource      = $this->pluginResource->jsonSerialize();
        $cost          = $this->getShippingFee($entity);
        $shippingType  = plugin_setting('fixed_shipping', 'type', 'fixed');
        $shippingValue = plugin_setting('fixed_shipping', 'value', 0);
        $destAddress   = $entity->getDestAddress();
        $weight        = $entity->getWeight();
        $count         = array_sum(array_column($entity->getProducts(), 'quantity'));
        $countryId     = $destAddress['country_id'] ?? 0;
        $countryCode   = '';

        // 如果有国家ID，通过ID查询国家代码
        if ($countryId) {
            $country = \InnoShop\Common\Models\Country::find($countryId);
            if ($country) {
                $countryCode = $country->code;
            }
        }

        // 根据不同计费方式提供不同的描述
        $description = $resource['description'];
        if ($shippingType == 'weight') {
            $weight      = $entity->getWeight();
            $description .= ' (' . weight_format($weight, setting('system.weight_unit')) . ')';
        } elseif ($shippingType == 'country') {
            $description .= ' (' . $countryCode . ')';
        }

        $quotes[] = [
            'type'          => 'shipping',
            'code'          => "{$code}.0",
            'name'          => $resource['name'],
            'description'   => $description,
            'icon'          => $resource['icon'],
            'cost'          => $cost,
            'weight'        => $weight,
            'count'         => $count,
            'cost_format'   => currency_format($cost),
            'shippingType'  => $shippingType,
            'shippingValue' => $shippingValue,
        ];

        return $quotes;
    }

    /**
     * Calculate shipping fee.
     *
     * @param ShippingEntity $entity
     * @return float|int
     */
    public function getShippingFee(ShippingEntity $entity): float|int
    {
        $subtotal      = $entity->getSubtotal();
        $shippingType  = plugin_setting('fixed_shipping', 'type', 'fixed');
        $shippingValue = plugin_setting('fixed_shipping', 'value', 0);
        $weight        = $entity->getWeight();
        $count         = array_sum(array_column($entity->getProducts(), 'quantity'));

        // 获取目的地国家信息
        $destAddress = $entity->getDestAddress();
        $countryId   = $destAddress['country_id'] ?? 0;
        $countryCode = '';

        // 如果有国家ID，通过ID查询国家代码
        if ($countryId) {
            $country = \InnoShop\Common\Models\Country::find($countryId);
            if ($country) {
                $countryCode = $country->code;
            }
        }

        // 根据不同计费方式计算运费
        switch ($shippingType) {
            case 'fixed':
                return $shippingValue;
            case 'percent':
                return $subtotal * $shippingValue / 100;
            case 'weight':
                // 按重量计费
                return $weight * $shippingValue;
            case 'country':
                // 按国家计费
                $countryRatesStr = plugin_setting('fixed_shipping', 'country_rates', '');
                $countryRates    = [];

                // 解析国家费率设置
                if (!empty($countryRatesStr)) {
                    $lines = explode("\n", $countryRatesStr);
                    foreach ($lines as $line) {
                        $parts = explode('=', $line, 2);
                        if (count($parts) == 2) {
                            $code                = trim($parts[0]);
                            $rate                = (float)trim($parts[1]);
                            $countryRates[$code] = $rate;
                        }
                    }
                }

                // 如果找到对应国家的费率，则使用该费率，否则使用默认值
                $cost = $countryRates[$countryCode] ?? (float)$shippingValue;

                //首件按设置运费算，后续每件只收取原运费的60%
                if ($count > 1) {
                    $cost += ($count - 1) * ($cost * 0.6);
                }

                return $cost;
            default:
                return 0;
        }
    }
}
