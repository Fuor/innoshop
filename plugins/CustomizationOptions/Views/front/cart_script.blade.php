@php
  $custom_name_fee = currency_format(plugin_setting('customization_options', 'custom_name_fee', 0));
  $custom_number_fee = currency_format(plugin_setting('customization_options', 'custom_number_fee', 0));
  $custom_labels = [
    'custom_name' => __('CustomizationOptions::common.customize_name'),
    'custom_number' => __('CustomizationOptions::common.customize_number'),
  ];
@endphp
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const cartData = @json($list ?? []); // 获取购物车数据

    cartData.forEach(function(item) {
      if (item.customizations) {
        const row = document.querySelector(`tr[data-id="${item.id}"]`); // 定位到父产品行

        if (row) {
          const productInfoCell = row.querySelector('.td-product-info');
          const priceCell = row.querySelector('.td-price');

          if (productInfoCell && priceCell) {
            let customizationInfoHtml = '<div class="customization-info mt-2">';
            let customizationPriceHtml = '';

            Object.keys(item.customizations).forEach(function(key) {
              const customLabels = @json($custom_labels);
              const customNameFee = @json($custom_name_fee);
              const customNumberFee = @json($custom_number_fee);
              const fee = key === 'custom_name' ? customNameFee : customNumberFee;

              // 在td-product-info中显示定制项名称、值和单价
              customizationInfoHtml += `
                <div class="custom-item-detail text-secondary small d-flex justify-content-between">
                  <span class="text-start">${customLabels[key]} : ${item.customizations[key]['value']}</span>
                  <span class="text-end custom-item-fee d-lg-none">+ ${fee}</span> <!-- 手机版显示单价 -->
                </div>
              `;
              // 桌面版显示单价
              customizationPriceHtml += `<div class="small d-none d-lg-block">+ ${fee}</div>`;
            });

            // 将定制项信息添加到产品信息下方
            customizationInfoHtml += '</div>';
            productInfoCell.querySelector('.product-info').insertAdjacentHTML('beforeend', customizationInfoHtml);

            // 将定制单价添加到价格列下方 (仅在桌面版显示)
            priceCell.insertAdjacentHTML('beforeend', customizationPriceHtml);
          }
        }
      }
    });
  });
</script>
