<div class="product-price">
  <span class="price" id="current-display-price">{{ $sku['price_format'] }}</span>
  @if($sku['origin_price'])
    <span class="old-price ms-2">{{ $sku['origin_price_format'] }}</span>
  @endif
</div>

@if(is_array($sku['ladder_prices']) && !empty($sku['ladder_prices']))
  <div class="ladder-price-table-wrap mt-3">
    <h6 class="mb-2 text-primary">{{ __('LadderPrice::front.ladder_price_rules') }}</h6>
    <p class="ladder-price-description">
      @php
        $descriptions = [];
        foreach ($sku['ladder_prices'] as $rule) {
            $descriptions[] = __('LadderPrice::front.full_quantity_price', [
                'min_quantity' => $rule['min_quantity'],
                'price' => currency_format($rule['price'])
            ]);
        }
        echo implode('<br>', $descriptions);
      @endphp
    </p>
  </div>

  @push('footer')
    <script>
      $(document).ready(function() {
        const ladderPrices = @json($sku['ladder_prices']  ?? []);
        const productQuantityInput = $('.product-quantity'); // 产品数量输入框
        const priceDisplay = $('#current-display-price'); // 显示当前价格的元素
        const originalPrice = parseFloat('{{ $sku['price'] }}'); // 获取 SKU 的原始价格（未格式化）

        function updatePriceDisplay() {
          const currentQuantity = parseInt(productQuantityInput.val());
          let newPrice = originalPrice; // 默认价格为 SKU 的原始价格

          if (ladderPrices.length > 0) {
            for (let i = 0; i < ladderPrices.length; i++) {
              const rule = ladderPrices[i];
              // 确保规则的 min_quantity 和 max_quantity 是数字
              const minQty = parseInt(rule.min_quantity);
              const maxQty = parseInt(rule.max_quantity);

              if (currentQuantity >= minQty && currentQuantity <= maxQty) {
                newPrice = parseFloat(rule.price);
                break; // 找到匹配规则后跳出
              }
            }
          }
          // 更新价格显示，使用后端提供的 currency_format 函数（如果前端有类似实现）
          // 或者直接使用 toFixed(2) 进行格式化，并添加货币符号
          priceDisplay.text(formatCurrency(newPrice));
        }

        // 监听数量输入框的变化
        productQuantityInput.on('input', function() {
          updatePriceDisplay();
        });

        // 监听数量增减按钮的点击
        $('.quantity-wrap .plus, .quantity-wrap .minus').on('click', function() {
          // 延迟执行，确保 input 的值已经更新
          setTimeout(updatePriceDisplay, 50);
        });

        // 辅助函数：格式化货币（如果后端没有提供API）
        // 这是一个简化的例子，实际应根据您的货币格式化规则实现
        function formatCurrency(value) {
          return '{{ current_currency()->symbol_left }}' + parseFloat(value).toFixed(2);
        }

        // 初始加载时更新一次价格
        updatePriceDisplay();
      });
    </script>
  @endpush
@endif
