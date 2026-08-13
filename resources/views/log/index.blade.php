@extends('layouts.app')

@section('content')
  <div class="row my-4">
    <div class="col-12 my-4">
      <h1 class="h3">{{ __('activitylog.title') }}</h1>

      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{{ __('activitylog.user') }}</th>
              <th>{{ __('activitylog.description') }}</th>
              <th>{{ __('activitylog.time') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($logItems as $logItem)
              <tr>
                <td>
                  @if ($logItem->causer)
                    <a href="{{ route('users.show', $logItem->causer->id) }}">
                      {{ $logItem->causer->name }}
                    </a>
                  @endif
                </td>
                <td>
                  @switch($logItem->description)
                    @case('added')
                    @case('removed')
                      {{ $logItem->getDescription() }}
                      <ul>
                        @foreach ($logItem->getProperty('chapters') as $chapterPath)
                          <li>
                            <a href="{{ getChapterOriginLinkForNumber($chapterPath) }}">
                              {{ App\Helpers\ChapterHelper::fullChapterName($chapterPath) }}
                            </a>
                          </li>
                        @endforeach
                      </ul>
                    @break

                    @case('commented')
                      {{ $logItem->getDescription() }}.
                      <a href="{{ $logItem->getProperty('url') }}">
                        {{ $logItem->subject->getCommentableName() }}
                      </a>
                    @break

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

                    @case('add_solution')
                      {{ $logItem->getDescription() }}
                      <a href="{{ route('exercises.show', $logItem->getProperty('exercise_id')) }}">
                        {{ $logItem->getProperty('exercise_path') }}
                        {{ App\Models\Exercise::findByPath($logItem->getProperty('exercise_path'))->getTitle() }}
                      </a>
                    @break

                    @default
                      <span>{{ __('activitylog.action_unknown') }}</span>
                  @endswitch
                </td>
                <td>{{ $logItem->created_at }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
        {{ $logItems->links() }}
      </div>
    </div>
  </div>
@endsection
