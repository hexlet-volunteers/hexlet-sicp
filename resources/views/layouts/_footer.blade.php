@php
  /** @var \App\DTO\Navigation\NavigationData $nav */
@endphp

<footer>
  <div class="container">
    <div class="row gap-4 gap-lg-0 row-cols-1 row-cols-lg-4 py-5">
      @foreach ($nav->footer as $section)
        <div class="col-5">
          @if ($section->title)
            <div class="fw-bold">{{ $section->title }}</div>
          @endif
          <ul class="nav flex-column align-items-start">
            @foreach ($section->items as $item)
              <li><a href="{{ $item->href }}" class="nav-link px-0">{{ $item->label }}</a></li>
            @endforeach
          </ul>
        </div>
      @endforeach
    </div>
  </div>
</footer>
