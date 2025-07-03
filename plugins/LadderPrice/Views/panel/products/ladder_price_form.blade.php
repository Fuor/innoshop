@php
  // 确保 $product 存在且有 masterSku
  $sku = $product->masterSku ?? null;
  $ladderPrices = $sku->ladder_prices ?? []; // 从 SKU 获取阶梯价格
@endphp

<div class="card mb-3">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('LadderPrice::panel.ladder_prices') }}</h5>
  </div>
  <div class="card-body">
    <div class="form-group mb-3">
      <label class="form-label">{{ __('LadderPrice::panel.enable_ladder_price') }}</label>
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="enable_ladder_price_switch" name="ladder_price_enabled" {{ !empty($ladderPrices) ? 'checked' : '' }}>
        <label class="form-check-label" for="enable_ladder_price_switch"></label>
      </div>
    </div>

    <div id="ladder-price-rules-container" style="{{ !empty($ladderPrices) ? '' : 'display: none;' }}">
      <h6 class="mb-3">{{ __('LadderPrice::panel.price_rules') }}</h6>
      <table class="table table-bordered" id="ladder-price-table">
        <thead>
        <tr>
          <th>{{ __('LadderPrice::panel.min_quantity') }}</th>
          <th>{{ __('LadderPrice::panel.max_quantity') }}</th>
          <th>{{ __('LadderPrice::panel.unit_price') }}</th>
          <th>{{ __('panel/common.actions') }}</th>
        </tr>
        </thead>
        <tbody>
        @if (!empty($ladderPrices))
          @foreach ($ladderPrices as $index => $rule)
            <tr>
              <td><input type="number" class="form-control min-quantity-input" value="{{ $rule['min_quantity'] ?? 1 }}" min="1"></td>
              <td><input type="number" class="form-control max-quantity-input" value="{{ $rule['max_quantity'] ?? 99999 }}" min="{{ $rule['min_quantity'] ?? 1 }}"></td>
              <td><input type="number" class="form-control price-input" value="{{ $rule['price'] ?? 0 }}" min="0" step="0.01"></td>
              <td><button type="button" class="btn btn-danger btn-sm remove-rule">{{ __('panel/common.delete') }}</button></td>
            </tr>
          @endforeach
        @endif
        </tbody>
      </table>
      <button type="button" class="btn btn-primary mt-2" id="add-rule-btn">{{ __('panel/common.add') }}</button>
    </div>
  </div>
</div>

@push('footer')
  <script>
    $(document).ready(function() {
      const enableSwitch = $('#enable_ladder_price_switch');
      const rulesContainer = $('#ladder-price-rules-container');
      const ladderPriceTableBody = $('#ladder-price-table tbody');
      const addRuleBtn = $('#add-rule-btn');
      const hiddenInput = $('#ladder_prices_input');

      // 初始化隐藏字段的值
      updateHiddenInput();

      // 切换阶梯价格启用状态
      enableSwitch.on('change', function() {
        if ($(this).is(':checked')) {
          rulesContainer.show();
          if (ladderPriceTableBody.children().length === 0) {
            addRule(); // 如果启用且没有规则，添加一条默认规则
          }
        } else {
          rulesContainer.hide();
        }
        updateHiddenInput();
      });

      // 添加规则
      addRuleBtn.on('click', function() {
        addRule();
        updateHiddenInput();
      });

      // 移除规则
      ladderPriceTableBody.on('click', '.remove-rule', function() {
        $(this).closest('tr').remove();
        updateHiddenInput();
      });

      // 监听输入框变化，更新隐藏字段
      ladderPriceTableBody.on('input', 'input', function() {
        updateHiddenInput();
      });

      function addRule() {
        const newRow = `
                <tr>
                    <td><input type="number" class="form-control min-quantity-input" value="1" min="1"></td>
                    <td><input type="number" class="form-control max-quantity-input" value="99999" min="1"></td>
                    <td><input type="number" class="form-control price-input" value="0" min="0" step="0.01"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-rule">{{ __('panel/common.delete') }}</button></td>
                </tr>
            `;
        ladderPriceTableBody.append(newRow);
      }

      function updateHiddenInput() {
        const rules = [];
        if (enableSwitch.is(':checked')) {
          ladderPriceTableBody.children('tr').each(function() {
            const minQuantity = $(this).find('.min-quantity-input').val();
            const maxQuantity = $(this).find('.max-quantity-input').val();
            const price = $(this).find('.price-input').val();
            rules.push({
              min_quantity: parseInt(minQuantity),
              max_quantity: parseInt(maxQuantity),
              price: parseFloat(price)
            });
          });
        }
        hiddenInput.val(JSON.stringify(rules));
      }
    });
  </script>
@endpush

<!-- 隐藏字段用于提交数据 -->
<input type="hidden" name="skus[0][ladder_prices]" id="ladder_prices_input" value="{{ json_encode($product->masterSku->ladder_prices ?? []) }}">
