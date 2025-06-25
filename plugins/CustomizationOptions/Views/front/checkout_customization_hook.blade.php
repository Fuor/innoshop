{{-- 显示定制项作为子项目 --}}
@if (isset($product['children']) && is_array($product['children']))
  @foreach ($product['children'] as $child)
    <div class="products-table-list custom-item-row"> {{-- 添加一个类以便样式区分 --}}
      <div>
        <div class="product-item">
          {{-- 定制项通常没有图片 --}}
          <div class="product-info ms-5"> {{-- 增加左边距以缩进 --}}
            <div class="name">{{ $child['product_name'] }}</div>
            <div class="sku text-secondary small ms-3">
              @if (isset($child['sku_code']) && $child['sku_code'])
                {{ $child['sku_code'] }}
              @endif
               x {{ $child['quantity'] }}
            </div>
          </div>
        </div>
      </div>
      <div class="text-end">{{ $child['price_format'] }}</div>
    </div>
  @endforeach
@endif
