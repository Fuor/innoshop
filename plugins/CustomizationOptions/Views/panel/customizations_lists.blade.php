{{-- 显示定制项 --}}
@php
  $itemReference = is_string($item->reference) ? json_decode($item->reference, true) : $item->reference;
  $customizations = $itemReference['customizations'] ?? $item->customizations ??null;
@endphp
@if (isset($customizations) && is_array($customizations))
  @foreach ($customizations as $key => $detail) {{-- 遍历包含价格的定制项 --}}
  @php
    $label = ($key === 'custom_name') ? __('CustomizationOptions::common.customize_name') : __('CustomizationOptions::common.customize_number');
    $fee = $detail['price'] ?? 0; // 从保存的定制信息中获取单价
    $customizationTotalFee = $fee * $item->quantity;
  @endphp

  <tr>
    <td></td>
    <td><span class="small text-secondary">{{ $label }}</span></td>
    <td><span class="small text-secondary">{{ $detail['value'] }}</span></td>
    <td></td>
    <td>
                  <span class="small text-secondary">
                  {{ currency_format($fee, $order->currency_code, $order->currency_value) }}
                  </span>
    </td>
    <td><span class="small text-secondary">{{currency_format($customizationTotalFee, $order->currency_code, $order->currency_value)}}</span>
    </td>
  </tr>
  @endforeach
@endif
