<?php

namespace App\DTO\Progress;

use App\Helpers\ChapterHelper;
use Spatie\LaravelData\Data;

/**
 * Сериализуемый узел дерева прогресса. Считает ChapterProgressService, здесь только перекладка.
 */
class ChapterNodeData extends Data
{
    /**
     * @param array<int, ChapterNodeData> $children
     * @param array<int, ExerciseNodeData> $exercises
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public bool $isCompleted,
        public int $completedChildrenCount,
        public int $totalChildrenCount,
        public array $children,
        public array $exercises,
    ) {
    }

    public static function fromProgress(ChapterProgressData $progress): self
    {
        return new self(
            id: $progress->chapter->id,
            title: ChapterHelper::fullChapterName($progress->chapter->path),
            url: route('chapters.show', $progress->chapter),
            isCompleted: $progress->isCompleted,
            completedChildrenCount: $progress->getCompletedChildrenCount(),
            totalChildrenCount: $progress->getTotalChildrenCount(),
            children: $progress->childrenProgress?->map(self::fromProgress(...))->values()->all() ?? [],
            exercises: $progress->exercisesProgress?->map(ExerciseNodeData::fromProgress(...))->values()->all() ?? [],
        );
    }
}
