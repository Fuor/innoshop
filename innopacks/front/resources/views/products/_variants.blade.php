@if (is_array($variants) && count($variants))
  <div class="product-variant-box">
    @hookupdate('front.products.show.variants.value')
    @foreach($variants as $key => $variant)
      <div class="product-variant">
        <div class="variant-title">
            {{ $variant['name'][front_locale_code()] ?? ($variant['name'][setting_locale_code()] ?? '-') }}</div>
          <div class="variant-values">
            @foreach ($variant['values'] as $vk => $value)
              <div class="variant-value-name" data-variant="{{ $key }}" data-value="{{ $vk }}">
                {{ $value['name'][front_locale_code()] ?? ($value['name'][setting_locale_code()] ?? '-') }}</div>
            @endforeach
        </div>
      </div>
    @endforeach
    @endhookupdate
  </div>
@endif
@push('footer')
  <script>
    let skus = @json($skus ?? []);

    if ($('.product-variant-box').length) {
      let masterSku = @json($sku);

      masterSku.variants.forEach((variant, i) => {
        $('.product-variant-box .product-variant').eq(i).find('.variant-values .variant-value-name').eq(variant).addClass('active');
      });

      updateVariantStatus()

      function updateVariantStatus() {
        $('.product-variant-box .product-variant').each((variant_index, el) => {
          $(el).find('.variant-values .variant-value-name').each((value_index, value) => {
            let masterSkuVariants = masterSku.variants.slice(0);
            masterSkuVariants[variant_index] = value_index;
            let sku = skus.find(sku => sku.variants.join('') === masterSkuVariants.join(''));
            if (sku && sku.quantity > 0) {
              $(value).removeClass('disabled');
            } else {
              $(value).addClass('disabled');
            }
          });
        });

        if (masterSku.quantity * 1 <= 0) {
          $('.product-info-bottom .add-cart, .product-info-bottom .buy-now, .product-info-bottom.quantity-wrap').addClass('disabled');
          $('.stock-wrap .in-stock').addClass('d-none').siblings('.out-stock').removeClass('d-none');
        } else {
          $('.product-info-bottom .add-cart, .product-info-bottom .buy-now, .product-info-bottom .quantity-wrap').removeClass('disabled');
          $('.stock-wrap .in-stock').removeClass('d-none').siblings('.out-stock').addClass('d-none');
        }
      }

      $('.product-variant-box .variant-value-name').click(function () {
        const variant = $(this).data('variant');
        const value = $(this).data('value');
        let variants = masterSku.variants.slice(0);
        variants[variant] = value;
        masterSku = skus.find(sku => sku.variants.toString() === variants.toString());

        $('.product-param .sku .value').text(masterSku.code);
        $('.product-param .model .value').text(masterSku.model);
        $('.product-price .price').text(masterSku.price_format);
        $('.product-price .old-price').text(masterSku.origin_price_format);
        $('.product-quantity').data('sku-id', masterSku.id);

        // 触发一个自定义事件，通知阶梯价格模块更新显示
        $(document).trigger('skuChanged', masterSku); // 添加这一行

        if (masterSku.origin_image_url) {
          $('.main-product-img img').attr('src', masterSku.origin_image_url);
        }
        history.pushState({}, '', inno.updateQueryStringParameter(window.location.href, 'sku_id', masterSku.id));

        if (masterSku.quantity * 1 <= 0) {
          $('.product-info-bottom .add-cart, .product-info-bottom .buy-now,.product-info-bottom.quantity-wrap').addClass('disabled');
          $('.stock-wrap .in-stock').addClass('d-none').siblings('.out-stock').removeClass('d-none');
        } else {
          $('.product-info-bottom .add-cart, .product-info-bottom .buy-now, .product-info-bottom.quantity-wrap').removeClass('disabled');
          $('.stock-wrap .in-stock').removeClass('d-none').siblings('.out-stock').addClass('d-none');
        }

        $(this).addClass('active').siblings().removeClass('active');
        updateVariantStatus()
      });
    }
  </script>
@endpush
