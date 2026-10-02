@php
    /**
     * @var ChapterProgressData $progress
     */

    use App\DTO\Progress\ChapterProgressData;
    use App\Helpers\ChapterHelper;
@endphp

<div @class(['chapter-item', 'mb-3', 'ps-4' => !$progress->isRootLevel()])>
    @if($progress->hasChildren)
        <div class="d-flex align-items-center justify-content-between p-2 border rounded"
             data-bs-toggle="collapse"
             data-bs-target="#children-{{ $progress->chapter->id }}"
             role="button"
             aria-expanded="false">
            <div class="d-flex align-items-center">
                <i class="bi bi-chevron-right me-2"></i>
                <a href="{{ route('chapters.show', $progress->chapter) }}"
                   @class([
                       'text-decoration-none',
                       'text-dark fw-bold' => $progress->isRootLevel(),
                       'text-success' => !$progress->isRootLevel() && $progress->isCompleted,
                       'text-muted' => !$progress->isRootLevel() && !$progress->isCompleted,
                   ])
                >
                    {{ ChapterHelper::fullChapterName($progress->chapter->path) }}
                </a>
            </div>
            <span @class([
                'badge',
                'bg-success' => $progress->isCompleted,
                'bg-warning' => $progress->isStarted(),
                'bg-secondary' => !$progress->isCompleted && !$progress->isStarted(),
                'small' => !$progress->isRootLevel(),
            ])>
                {{ $progress->getCompletedChildrenCount() }}/{{ $progress->getTotalChildrenCount() }}
            </span>
        </div>
        <div class="collapse" id="children-{{ $progress->chapter->id }}">
            <div class="chapter-children mt-2">
                @foreach($progress->childrenProgress as $childProgress)
                    @include('partials.user_chapter_partial', ['progress' => $childProgress])
                @endforeach
            </div>
        </div>
    @else
        <div @class(['p-2 border rounded' => $progress->isRootLevel()])>
            <div class="d-flex align-items-center py-1">
                <span class="me-2">
                    @if($progress->isCompleted)
                        <i class="bi bi-check-circle-fill text-success"></i>
                    @else
                        <i class="bi bi-circle text-muted"></i>
                    @endif
                </span>
                <a href="{{ route('chapters.show', $progress->chapter) }}"
                   @class([
                       'text-decoration-none',
                       'text-dark fw-bold' => $progress->isRootLevel(),
                       'text-success' => !$progress->isRootLevel() && $progress->isCompleted,
                       'text-muted' => !$progress->isRootLevel() && !$progress->isCompleted,
                   ])>
                    {{ ChapterHelper::fullChapterName($progress->chapter->path) }}
                </a>
            </div>

            @if($progress->exercisesProgress)
                <div class="ms-4 mt-1">
                    @foreach($progress->exercisesProgress as $exerciseProgress)
                        <div class="d-flex align-items-center py-1 small">
                            <span class="me-2">
                                <i @class([
                                    'bi',
                                    'bi-check-circle-fill text-success' => $exerciseProgress->isCompleted(),
                                    'bi-clock-history text-warning' => $exerciseProgress->isInProgress(),
                                    'bi-circle text-muted' => $exerciseProgress->isNotStarted(),
                                ])></i>
                            </span>
                            <a href="{{ route('exercises.show', $exerciseProgress->exercise) }}"
                               class="text-decoration-none link-secondary"
                            >
                                {{ $exerciseProgress->exercise->getFullTitle() }}
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
