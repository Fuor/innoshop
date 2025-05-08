<?php
/**
 * @Desc:
 * @Author: 黄辉全
 * @Time: 2025/4/30 16:33
 */

namespace Plugin\PayPal\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Services\StateMachineService;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalController
{
    /**
     * Create a PayPal order
     */
    public function createOrder(Request $request)
    {
        try {
            $orderNumber = $request->input('order_number');
            $order       = Order::where('number', $orderNumber)->firstOrFail();

            // Initialize PayPal client
            $provider = $this->getPayPalProvider();

            // Create PayPal order
            $orderData = [
                'intent'              => 'CAPTURE',
                'purchase_units'      => [
                    [
                        'reference_id' => $order->number,
                        'amount'       => [
                            'currency_code' => strtoupper(current_currency_code()),
                            'value'         => number_format($order->total, 2, '.', '')
                        ],
                        'description'  => 'Order #' . $order->number
                    ]
                ],
                'application_context' => [
                    'return_url' => front_route('checkout.success') . '?order_number=' . $order->number,
                    'cancel_url' => front_route('checkout.index')
                ]
            ];

            $response = $provider->createOrder($orderData);

            return response()->json($response);
        } catch (Exception $e) {
            Log::error('PayPal create order error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Capture a PayPal payment
     */
    public function captureOrder(Request $request)
    {
        try {
            $paypalOrderId = $request->input('order_id');
            $orderNumber   = $request->input('order_number');
            $order         = Order::where('number', $orderNumber)->firstOrFail();

            // Initialize PayPal client
            $provider = $this->getPayPalProvider();

            // Capture payment
            $response = $provider->capturePaymentOrder($paypalOrderId);

            if ($response['status'] === 'COMPLETED') {
                // Update order status after successful payment
                StateMachineService::getInstance($order)->changeStatus(
                    StateMachineService::PAID,
                    'Paid via PayPal. Transaction ID: ' . $response['id']
                );

                return response()->json(['success' => true]);
            }

            return response()->json(['error' => 'Payment not completed'], 400);
        } catch (Exception $e) {
            Log::error('PayPal capture order error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get PayPal client instance
     */
    private function getPayPalProvider()
    {
        try {
            // Ensure config directory exists
            $configPath = plugin_path('PayPal/config');
            if (!file_exists($configPath)) {
                mkdir($configPath, 0755, true);
            }

            // Load config
            $config = include plugin_path('PayPal/config/paypal.php');

            // Ensure config is up to date
            $config['mode']                     = plugin_setting('paypal', 'mode', 'sandbox');
            $config['sandbox']['client_id']     = plugin_setting('paypal', 'client_id', '');
            $config['sandbox']['client_secret'] = plugin_setting('paypal', 'client_secret', '');
            $config['live']['client_id']        = plugin_setting('paypal', 'client_id', '');
            $config['live']['client_secret']    = plugin_setting('paypal', 'client_secret', '');
            $config['currency']                 = strtoupper(current_currency_code());

            $provider = new PayPalClient($config);
//            $provider->setApiCredentials($config);

            // Try to get access token and log the result
            try {
                $token = $provider->getAccessToken();
                \Log::info('PayPal access token obtained successfully');
            } catch (\Exception $e) {
                \Log::error('PayPal access token error: ' . $e->getMessage());
                throw $e;
            }

            return $provider;
        } catch (\Exception $e) {
            \Log::error('PayPal provider error: ' . $e->getMessage());
//            \Log::error('PayPal provider error trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
}
