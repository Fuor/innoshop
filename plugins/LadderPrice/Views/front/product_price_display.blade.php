<div class="product-price">
  <span class="price" id="current-display-price">{{ $sku['price_format'] }}</span>
  @if($sku['origin_price'])
    <span class="old-price ms-2">{{ $sku['origin_price_format'] }}</span>
  @endif
</div>

@if(is_array($sku['ladder_prices']) && !empty($sku['ladder_prices']))
  <div class="ladder-price-table-wrap mt-3">
    <h6 class="mb-2 text-primary">{{ __('LadderPrice::front.ladder_price_rules') }}</h6>
    <p class="ladder-price-description" id="ladder-price-description-text">
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
        let currentLadderPrices = @json($sku['ladder_prices']  ?? []);
        const productQuantityInput = $('.product-quantity');
        const priceDisplay = $('#current-display-price');
        let currentOriginalPrice = parseFloat('{{ $sku['price'] }}');
        const ladderPriceDescriptionText = $('#ladder-price-description-text');

        function updatePriceDisplay() {
          const currentQuantity = parseInt(productQuantityInput.val());
          let newPrice = currentOriginalPrice;

          if (currentLadderPrices.length > 0) {
            for (let i = 0; i < currentLadderPrices.length; i++) {
              const rule = currentLadderPrices[i];
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

        // 监听 SKU 切换事件
        $(document).on('skuChanged', function(event, newSku) {
          console.log('SKU changed:', newSku);

          currentLadderPrices = newSku.ladder_prices ?? [];
          currentOriginalPrice = parseFloat(newSku.price); // 使用新的 SKU 价格
          priceDisplay.text(newSku.price_format); // 更新显示价格为新 SKU 的默认价格

          // 更新阶梯价格描述
          if (currentLadderPrices.length > 0) {
            let descriptions = [];
            currentLadderPrices.forEach(rule => {
              descriptions.push(`{{ __('LadderPrice::front.full_quantity_price', ['min_quantity' => 'RULE_MIN_QTY', 'price' => 'RULE_PRICE']) }}`
                .replace('RULE_MIN_QTY', rule.min_quantity)
                .replace('RULE_PRICE', formatCurrency(rule.price))
              );
            });
            ladderPriceDescriptionText.html(descriptions.join('<br>'));
          } else {
            ladderPriceDescriptionText.html('');
          }

          updatePriceDisplay(); // 重新计算并显示价格
        });

        updatePriceDisplay();
      });
    </script>
  @endpush
@endif
