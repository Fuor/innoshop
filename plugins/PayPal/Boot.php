<?php
/**
 * @Desc:
 * @Author: Fuor
 * @Time: 2025/4/30 11:34
 */

namespace Plugin\PayPal;

use Exception;
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Services\StateMachineService;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class Boot
{
    public function init()
    {
        // 加载 composer 依赖
        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            require_once __DIR__ . '/vendor/autoload.php';
        }

        // 监听支付方法路由展示
        listen_hook_filter('service.payment.pay.paypal.view', function ($viewPath) {
            return 'PayPal::payment';
        });
    }
}
