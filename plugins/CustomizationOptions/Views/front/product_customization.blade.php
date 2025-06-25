<div class="form-check mb-3 mt-3">
  <input type="checkbox" class="form-check-input" id="toggle-customization">
  <label class="form-check-label" for="toggle-customization">{{ __('CustomizationOptions::common.enable_customization') }}</label>
</div>

<div id="customization-content" class="product-customization mt-4" style="display: none;">
  <h6>{{ __('CustomizationOptions::common.title') }}</h6>
  <div class="row">
    <div class="col-md-4 mb-3">
      <label class="form-label">{{ __('CustomizationOptions::common.customize_name') }} (+ ${{ currency_format(plugin_setting('customization_options', 'custom_name_fee', 0),2) }})</label>
      <input type="text" class="form-control customization-input"
             name="custom_name"
             data-fee="{{ plugin_setting('customization_options', 'custom_name_fee', 0) }}"
      >
    </div>
    <div class="col-md-4 mb-3">
      <label class="form-label">{{ __('CustomizationOptions::common.customize_number') }} (+ ${{ currency_format(plugin_setting('customization_options', 'custom_number_fee', 0),2) }})</label>
      <input type="text" class="form-control customization-input"
             name="custom_number"
             data-fee="{{ plugin_setting('customization_options', 'custom_number_fee', 0) }}"
      >
    </div>
    <div class="col-md-4 customization-total mb-3">
      <strong>{{ __('CustomizationOptions::common.customize_fee') }}: $<span id="customization-fee">0</span></strong>
    </div>
  </div>
</div>

<script>
  document.getElementById('toggle-customization').addEventListener('change', function() {
    const content = document.getElementById('customization-content');
    const inputs = content.querySelectorAll('.customization-input');

    if (this.checked) {
      content.style.display = 'block';
    } else {
      content.style.display = 'none';
      inputs.forEach(input => {
        input.value = ''; // 清空输入框内容
      });
    }
  });
</script>
