<script>
  document.addEventListener('DOMContentLoaded', function() {
    // 完全重写addCart方法
    window.inno.addCart = function({skuId, quantity = 1, isBuyNow = false}, event, callback) {
      const base = document.querySelector('base').href;
      const $btn = $(event);
      $btn.addClass('disabled').prepend('<span class="spinner-border spinner-border-sm me-1"></span>');
      $(document).find('.tooltip').remove();

      // 构建请求数据
      const requestData = {
        sku_id: skuId,
        quantity,
        buy_now: isBuyNow
      };

      // 收集定制选项
      const customizations = {};
      document.querySelectorAll('.customization-input').forEach(input => {
        if (input.value.trim() !== '') {
          customizations[input.name] = input.value.trim();
        }
      });

      // 如果有定制选项，添加到请求数据中
      if (Object.keys(customizations).length > 0) {
        requestData.customizations = customizations;
        console.log('Sending data with customizations:', requestData);
      }

      // 发送请求
      axios.post(urls.cart_add, requestData).then((res) => {
        console.log('Post data : ', requestData);

        if (!isBuyNow) {
          layer.msg(res.message)
        }

        $('.header-cart-icon .icon-quantity').text(res.data.total_format)

        if (callback) {
          callback(res)
        }
      }).finally(() => {
        $btn.removeClass('disabled').find('.spinner-border').remove();
      });
    };

    // 费用计算逻辑保持不变
    function calculateCustomizationFee() {
      let totalFee = 0;
      document.querySelectorAll('.customization-input').forEach(input => {
        if (input.value.trim() !== '') {
          totalFee += parseFloat(input.dataset.fee || 0);
        }
      });

      const feeElement = document.getElementById('customization-fee');
      if (feeElement) {
        feeElement.textContent = totalFee.toFixed(2);
      }
      return totalFee;
    }

    // 监听输入变化
    document.querySelectorAll('.customization-input').forEach(input => {
      ['input', 'change', 'keyup'].forEach(eventType => {
        input.addEventListener(eventType, calculateCustomizationFee);
      });
    });

    calculateCustomizationFee();
  });
</script>
