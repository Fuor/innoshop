{{-- 显示定制项的单价和总计 --}}
@if (isset($product['customizations']) && is_array($product['customizations']))
  @foreach ($product['customizations'] as $key => $detail)
    @php
      $label = ($key === 'custom_name') ? __('CustomizationOptions::common.customize_name') : __('CustomizationOptions::common.customize_number');
      $value = $detail['value'] ?? '';
      $fee = $detail['price'] ?? 0;
      $customizationItemTotal = $fee * $product['quantity'];
    @endphp
    <tr class="customization-item">
      <td  class="text-start"><span class="small text-secondary">{{ $label }} : {{$value}}</span></td> {{-- 商品列显示定制项名称 --}}
      <td>
                        <span class="small text-secondary">
                          {{ currency_format($fee, $order->currency_code, $order->currency_value) }}
                        </span>
      </td> {{-- 单价列显示定制项单价 --}}
      <td><span class="small text-secondary">{{ $product['quantity'] }}</span></td>
      <td>
                        <span class="small text-secondary">
                          {{ currency_format($customizationItemTotal, $order->currency_code, $order->currency_value) }}
                        </span>
      </td> {{-- 小计列显示定制项总计 --}}
      <td></td>
    </tr>
  @endforeach
@endif
