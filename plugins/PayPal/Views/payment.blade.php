<div class="paypal-payment card w-max-700 m-auto h-min-300">
  <div class="card-body">
    <div class="fs-5 mb-3">{{ __('PayPal::common.order_processing') }}</div>
    <div id="paypal-button-container" class="my-4"></div>
    <p class="mt-3 text-muted">{{ __('PayPal::common.redirect_notice') }}</p>
  </div>
</div>

<script src="https://www.paypal.com/sdk/js?client-id={{ plugin_setting('paypal.client_id') }}&currency={{ strtoupper(current_currency_code()) }}"></script>
<script>
  // 初始化 PayPal 按钮
  paypal.Buttons({
    createOrder: function(data, actions) {
      // 使用 srmklive/laravel-paypal 创建订单
      return fetch('{{ route('api.paypal.create-order') }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          order_number: '{{ $order->number }}'
        })
      }).then(function(res) {
        if (!res.ok) {
          console.error('PayPal create order response not OK:', res.status);
          return res.json().then(data => {
            console.error('Error details:', data);
            throw new Error('Failed to create PayPal order');
          });
        }

        return res.json();
      }).then(function(data) {
        if (data.error) {
          console.error('PayPal order error:', data.error);
          throw new Error(data.error);
        }
        return data.id; // srmklive/laravel-paypal 返回的订单 ID
      })
      .catch(function(err) {
        console.error('PayPal create order error:', err);
        alert('Error creating PayPal order: ' + err.message);
        throw err;
      });
    },
    onApprove: function(data, actions) {
      // 使用 srmklive/laravel-paypal 捕获支付
   return fetch('{{ route('api.paypal.capture-order') }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          order_id: data.orderID,
          order_number: '{{ $order->number }}'
        })
      }).then(function(res) {
        return res.json();
      }).then(function(data) {
        if (data.success) {
          window.location.href = '{{ front_route('checkout.success') }}?order_number={{ $order->number }}';
        } else if (data.error) {
          alert(data.error);
        }
      });
    },
    onError: function(err) {
      console.error('PayPal error:', err);
      alert('{{ __('PayPal::common.payment_error') }}');
    }
  }).render('#paypal-button-container');
</script>
