@php
  /** @var \App\DTO\Navigation\NavigationData $nav */
  // NavItemData::$icon → класс bootstrap-icons (в Inertia-шелле — ItemIcon.tsx)
  $icons = [
      'shield-lock' => 'bi-shield-lock',
      'users' => 'bi-people',
      'messages' => 'bi-chat-dots',
      'code' => 'bi-code-square',
      'download' => 'bi-download',
  ];
@endphp

<header class="navbar navbar-expand-xl">
  <nav class="container py-2 border-bottom">
    <a href="{{ $nav->homeUrl }}" class="navbar-brand">
      <img src="{{ $nav->logoUrl }}" alt="{{ $nav->logoAlt }}" height="25px" class="align-baseline"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-responsive"
      aria-controls="navbar-responsive" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="navbar-collapse collapse" id="navbar-responsive">
      <ul class="navbar-nav">
        @foreach ($nav->main as $item)
          @if ($item->children)
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle p-2" href="#" id="adminDropdown" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi {{ $icons[$item->icon] ?? '' }}"></i> {{ $item->label }}
              </a>
              <ul class="dropdown-menu x-z-index-dropdown dropdown-menu-right" aria-labelledby="adminDropdown">
                @foreach ($item->children as $child)
                  <li>
                    <a class="dropdown-item" href="{{ $child->href }}">
                      <i class="bi {{ $icons[$child->icon] ?? '' }}"></i> {{ $child->label }}
                    </a>
                  </li>
                @endforeach
              </ul>
            </li>
          @else
            <li class="nav-item"><a href="{{ $item->href }}"
                @class(['nav-link', 'link-info' => $item->highlight, 'p-2'])>{{ $item->label }}</a></li>
          @endif
        @endforeach
      </ul>
      <ul class="navbar-nav ms-md-auto">
        @guest
          @foreach ($nav->user as $item)
            @if ($item->method === 'post')
              <li>
                <a href="{{ $item->href }}" class="nav-link px-2" data-method="post"
                  data-confirm="Are you sure you want to submit?"> {{ $item->label }}</a>
              </li>
            @else
              <li class="nav-item"><a href="{{ $item->href }}" class="nav-link p-2">{{ $item->label }}</a></li>
            @endif
          @endforeach
        @else
          <li class="nav-item dropdown d-md-block">
            <a class="nav-link dropdown-toggle py-1 px-2 link-secondary" id="dropdownMenuButton" data-bs-toggle="dropdown"
              aria-haspopup="true" aria-expanded="false" href="#">
              <i class="bi bi-person align-middle fs-4"></i>
            </a>
            <ul class="dropdown-menu x-z-index-dropdown dropdown-menu-right" aria-labelledby="dropdownMenuButton">
              @foreach ($nav->user as $item)
                @if ($loop->first)
                  <li>
                    <a href="{{ $item->href }}" class="dropdown-item link-secondary">{{ $item->label }}</a>
                  </li>
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                @elseif ($item->method === 'post')
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  <li>
                    <a class="dropdown-item" href="{{ $item->href }}" data-method="post" rel="nofollow">
                      {{ $item->label }}
                    </a>
                  </li>
                @else
                  <li>
                    <a class="dropdown-item" href="{{ $item->href }}">{{ $item->label }}</a>
                  </li>
                @endif
              @endforeach
            </ul>
          </li>
        @endguest
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle px-2 link-secondary" id="dropdownFlagButton" data-bs-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false" href="#">
            <img src="{{ $nav->currentLocale->flagUrl }}" alt="{{ $nav->currentLocale->label }}" class="me-1"
              width="24">
            <span class="d-md-none">{{ $nav->currentLocale->label }}</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-right x-min-w-0" aria-labelledby="dropdownFlagButton">
            @foreach ($nav->otherLocales as $locale)
              <li>
                <a href="{{ $locale->href }}" rel="alternate" class="dropdown-item" hreflang="{{ $locale->code }}">
                  <img src="{{ $locale->flagUrl }}" alt="{{ $locale->label }}" class="mr-1" width="24">
                  <span class="d-md-none">{{ $locale->label }}</span>
                </a>
              </li>
            @endforeach
          </ul>
        </li>
      </ul>
    </div>
  </nav>
</header>
