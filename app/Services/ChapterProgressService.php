<?php

namespace App\Services;

use App\DTO\Progress\ChapterProgressData;
use App\DTO\Progress\ExerciseProgressData;
use App\Models\Chapter;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class ChapterProgressService
{
    /**
     * Всё дерево двумя запросами: главы с упражнениями грузятся разом, children собираются в памяти,
     * поэтому loadMissing в buildChapterProgress уже ничего не догружает.
     *
     * @return Collection<int, ChapterProgressData>
     */
    public function buildTreeProgress(Collection $chapterMembers, Collection $exerciseMembers): Collection
    {
        $chapters = Chapter::with('exercises')->get();
        $childrenByParent = $chapters->groupBy('parent_id');

        foreach ($chapters as $chapter) {
            $chapter->setRelation('children', new EloquentCollection($childrenByParent->get($chapter->id, [])));
        }

        return $chapters->whereNull('parent_id')->sortBy('path')->values()
            ->map(fn(Chapter $root) => $this->buildChapterProgress($root, $chapterMembers, $exerciseMembers));
    }

    public function buildChapterProgress(
        Chapter $chapter,
        Collection $chapterMembers,
        Collection $exerciseMembers
    ): ChapterProgressData {
        $hasChildren = $chapter->children->isNotEmpty();

        $childrenProgress = null;

        if ($hasChildren) {
            $chapter->children->loadMissing(['children', 'exercises']);

            $childrenProgress = $chapter->children->sortBy('path')->map(
                fn($child) => $this->buildChapterProgress(
                    $child,
                    $chapterMembers,
                    $exerciseMembers
                )
            );
        }

        $exercisesProgress = $this->buildExercisesProgress(
            $chapter,
            $exerciseMembers
        );

        $isCompleted = $hasChildren
            ? $childrenProgress->every(fn($child) => $child->isCompleted)
            : ($chapterMembers->has($chapter->id) && $chapterMembers[$chapter->id]->isFinished());

        return new ChapterProgressData(
            chapter: $chapter,
            hasChildren: $hasChildren,
            isCompleted: $isCompleted,
            childrenProgress: $childrenProgress,
            exercisesProgress: $exercisesProgress,
        );
    }

    private function buildExercisesProgress(Chapter $chapter, Collection $exerciseMembers): ?Collection
    {
        if ($chapter->exercises->isEmpty()) {
            return null;
        }

        return $chapter->exercises->map(function ($exercise) use ($exerciseMembers) {
            $exerciseMember = $exerciseMembers->get($exercise->id);

            return new ExerciseProgressData(
                exercise: $exercise,
                exerciseMember: $exerciseMember,
            );
        });
    }
}
