@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.order_returns'))

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      <x-panel-data-criteria :criteria="$criteria ?? []" :action="panel_route('order_returns.index')"/>
      @if ($order_returns->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <td>{{ __('panel/common.id') }}</td>
              <td>{{ __('front/return.number') }}</td>
              <td>{{ __('panel/order_return.customer') }}</td>
              <td>{{ __('front/return.product_name') }}</td>
              <td>{{ __('front/return.opened') }}</td>
              <td>{{ __('front/return.status') }}</td>
              <td>{{ __('front/return.quantity') }}</td>
              @hookinsert('panel.order_returns.index.header.extra')
              <td>{{ __('panel/common.actions') }}</td>
            </tr>
            </thead>
            <tbody>

            @foreach($order_returns as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->number }}</td>
                <td>
                  <a href="{{ panel_route('customers.edit', $item->customer_id) }}" target="_blank">{{ $item->customer->name }}</a> <br/>
                  {{ $item->customer->email }} <br/>
                </td>
                <td>
                  {{ __('panel/order_return.order_number') }}: 
                  <a href="{{ panel_route('orders.edit', $item->order_id) }}" target="_blank">{{ $item->order_number }}</a> <br/>
                  <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="img-fluid wh-30 rounded border border-1">
                  {{ sub_string($item->product_name, 50) }}
                </td>
                <td>{{ $item->opened ? __('front/common.yes') : __('front/common.no') }}</td>
                <td><span class="badge bg-{{ $item->status_color }}">{{ $item->status_format }}</span></td>
                <td>{{ $item->quantity }}</td>

                @hookinsert('panel.order_returns.index.row.extra', $item)

                <td>
                  <a href="{{ panel_route('order_returns.edit', [$item->id]) }}"
                     class="btn btn-sm btn-outline-primary">{{ __('panel/common.view')}}</a>
                  <form action="{{ panel_route('order_returns.destroy', [$item->id]) }}" method="POST" class="d-inline">
                    @csrf
                  </form>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        {{ $order_returns->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data/>
      @endif
    </div>
  </div>
@endsection
@push('footer')
  <script>
  </script>
@endpush
