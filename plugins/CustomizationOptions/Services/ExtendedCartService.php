<?php

namespace Plugin\CustomizationOptions\Services;

use InnoShop\Common\Services\CartService;
use Illuminate\Support\Facades\Log;

class ExtendedCartService extends CartService
{
    public static function getInstance(int $customerID = 0, string $guestID = ''): static
    {
        // 强制使用服务容器解析
        return app(static::class, compact('customerID', 'guestID'));
    }

    public function addCart($data): array
    {
        $data = $this->mergeAuthId($data);

        // 直接使用扩展的Repository
        $extendedRepo = new \Plugin\CustomizationOptions\Services\ExtendedCartItemRepo();
        $extendedRepo->create($data);

        return $this->handleResponse();
    }
}
