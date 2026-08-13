@extends('layouts.app')
@php
  /** @var \Illuminate\Support\Collection|\App\Models\Activity[] $logItems */
  /** @var \Illuminate\Support\Collection|\App\Models\Comment[] $comments */
  use App\Helpers\MarkdownHelper;
@endphp
@section('content')
  <div class="row">
    <div class="col-md-8">
      <h1 class="my-4">{{ __('welcome.title') }}</h1>
      <p>{{ __('welcome.description') }}</p>
      <a href="https://mitp-content-server.mit.edu/books/content/sectbyfn/books_pres_0/6515/sicp.zip/full-text/book/book-Z-H-4.html">
        {{ __('welcome.link_to_book') }}
      </a>
      <hr class="dropdown-divider">
      <h2> {{ __('welcome.statistic.title') }}</h2>
      <div class="col-md-auto ">
        <ul class="nav nav-tabs">
          <li class="nav-item">
            <a @class([
                'nav-link',
                'active' => $statisticTable['filterForQuery'] === 'all',
            ]) aria-current="page" href="{{ route('home', ['filter' => 'all']) }}">
              {{ __('welcome.statistic.table.per_all_time') }}
            </a>
          </li>
          <li class="nav-item">
            <a @class([
                'nav-link',
                'active' => $statisticTable['filterForQuery'] === 'month',
            ]) href="{{ route('home', ['filter' => 'month']) }}">
              {{ __('welcome.statistic.table.per_month') }}
            </a>
          </li>
          <li class="nav-item">
            <a @class([
                'nav-link',
                'active' => $statisticTable['filterForQuery'] === 'week',
            ]) href="{{ route('home', ['filter' => 'week']) }}">
              {{ __('welcome.statistic.table.per_week') }}
            </a>
          </li>
        </ul>
        <div class="container m-0 p-0  mt-2">
          <div class="row m-0 p-0  text-center">
            <div class="col mt-4">
              <p>{{ __('welcome.statistic.table.count_read_chapter') }}:</p>
              <h3>{{ $statisticTable['countChapterMember'] }}</h3>
            </div>
            <div class="col mt-4">
              <p>{{ __('welcome.statistic.table.count_completed_exercise') }}:</p>
              <h3>{{ $statisticTable['countCompletedExercise'] }}</h3>
            </div>
          </div>
        </div>
      </div>

    </div>
    <div class="col-md-4">
      <h2 class="my-3">{{ __('welcome.what_is_here') }}</h2>
      <p>{{ __('welcome.about_sicp') }}
        @if (App::isLocale('ru'))
          <a href="https://guides.hexlet.io/how-to-learn-sicp/">{{ __('layout.nav.sicp_read') }}</a>
        @else
          <a href="{{ route('pages.show', ['page' => 'how-to-learn-sicp']) }}">{{ __('layout.nav.sicp_read') }}</a>
        @endif
      </p>
      <h2 class="my-3">{{ __('welcome.features') }}</h2>
      <ul>
        @foreach (__('welcome.features_list') as $key => $item)
          <li>{{ __(sprintf('welcome.features_list.%s', $key)) }}</li>
        @endforeach
      </ul>
      <a class="btn btn-primary btn-lg" href="{{ route('my.show') }}">{{ __('layout.welcome.start_learning') }}</a>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      @include('components.activity_chart')
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <h3 class="my-3"><a href="{{ route('log.index') }}">{{ __('activitylog.title') }}</a></h3>
      @foreach ($logItems as $logItem)
        <div class="media text-muted pt-1">
          <div class="text-truncate media-body pb-1 mb-0 small lh-125 border-bottom border-gray">
            <div class="d-flex justify-content-between align-items-center w-100">
              <strong class="text-gray-dark">
                @if ($logItem->causer)
                  <a href="{{ route('users.show', $logItem->causer->id) }}">{{ $logItem->causer->name }}</a>
                @endif
              </strong>
              <a href="{{ $logItem->getProperty('url') ?? '#' }}">{{ $logItem->created_at }}</a>
            </div>
            @switch($logItem->description)
              @case('completed_exercise')
                {{ $logItem->getDescription() }}
                <a href="{{ route('exercises.show', $logItem->getProperty('exercise_id')) }}">
                  {{ $logItem->subject->getFullTitle() }}
                </a>
              @break

              @case('destroy_exercise')
                {{ $logItem->getDescription() }}
                <a href="{{ route('exercises.show', $logItem->getProperty('exercise_id')) }}">
                  {{ $logItem->subject->getFullTitle() }}
                </a>
              @break

              @case('commented')
                {{ $logItem->getDescription() }}.
                <a href="{{ $logItem->getProperty('url') }}">
                  <!-- FIXME: add check for null // use soft delete -->
                  {{ $logItem?->subject?->getCommentableName() }}
                </a>
              @break

              @case('add_solution')
                {{ $logItem->getDescription() }}
                <a href="{{ route('exercises.show', $logItem->getProperty('exercise_id')) }}">
                  {{ $logItem->getProperty('exercise_path') }}
                  {{ App\Models\Exercise::findByPath($logItem->getProperty('exercise_path'))->getTitle() }}
                </a>
              @break

              @case('removed')
              @case('multiple_chapters_added')
                <div class="d-block">
                  <a data-bs-toggle="collapse" href="#collapseExp{{ $logItem->id }}" aria-expanded="false"
                    aria-controls="collapseExp{{ $logItem->id }}">
                    {{ $logItem->getDescription() }}
                  </a>
                  <div class="collapse" id="collapseExp{{ $logItem->id }}">
                    @if ($logItem->getProperty('chapters'))
                      <ul>
                        @foreach ($logItem->getProperty('chapters') as $chapterPath)
                          <li>
                            <a href="{{ getChapterOriginLinkForNumber($chapterPath) }}">
                              {{ App\Helpers\ChapterHelper::fullChapterName($chapterPath) }}
                            </a>
                          </li>
                        @endforeach
                      </ul>
                    @endif
                  </div>
                </div>
              @break

              @default
                <span>{{ __('activitylog.action_unknown') }}</span>
            @endswitch
          </div>
        </div>
      @endforeach
    </div>
    <div class="col-md-6">
      <h3 class="my-3"><a href="{{ route('comments.index') }}">{{ __('comment.latest_comments') }}</a></h3>
      @foreach ($comments as $comment)
        <div class="media text-muted pt-1">
          <div class="text-truncate media-body pb-1 mb-0 small lh-125 border-bottom border-gray">
            <div class="d-flex justify-content-between align-items-center w-100">
              <strong class="text-gray-dark">
                @if ($comment->user)
                  <a href="{{ route('users.show', $comment->user->id) }}">{{ $comment->user->name }}</a>
                @endif
              </strong>
              <a href="{{ $comment->present()->getLink() }}">{{ $comment->created_at }}</a>
            </div>
            <span>{!! MarkdownHelper::text(str_limit($comment->content, 80)) !!}</span>
          </div>
        </div>
      @endforeach
    </div>
  </div>

  <!-- /.row -->
@endsection
