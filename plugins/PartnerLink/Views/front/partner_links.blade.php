@if($links->count())
<section class="module-blogroll">
  <div class="container">
    @php
      $linksLogo = $links->filter(function($link) {
        return $link->logo;
      });

      $linksNoLogo = $links->filter(function($link) {
        return !$link->logo;
      });
    @endphp

    @if ($linksLogo->count())
    <ul class="inform-wrap mb-4 list-unstyled d-flex flex-wrap align-items-center">
      <li class="me-2"><span><i class="bi bi-link-45deg"></i>{{ __('PartnerLink::route.title')}} : </span></li>
      @foreach($linksLogo as $link)
      <li class="me-2">
        <a href="{{ $link->url }}" target="_blank">
          <img src="{{ $link->logo }}" alt="{{ $link->name }}" title="{{ $link->name }}" class="img-fluid w-max-200 h-max-100">
        </a>
      </li>
      @endforeach
    </ul>
    @endif

    @if ($linksNoLogo->count())
    <ul class="inform-wrap mb-4 list-unstyled d-flex flex-wrap align-items-center">
      <li class="me-2"><span><i class="bi bi-link-45deg"></i>{{ __('PartnerLink::route.title')}} : </span></li>
      @foreach($linksNoLogo as $link)
      <li class="me-2">
        <a href="{{ $link->url }}" target="_blank">{{ $link->name }}</a>
      </li>
      @endforeach
    </ul>
    @endif
  </div>
</section>
@endif
