<?php

namespace App\DTO\Progress;

use Spatie\LaravelData\Data;

class ExerciseNodeData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public bool $isCompleted,
        public bool $isInProgress,
    ) {
    }

    public static function fromProgress(ExerciseProgressData $progress): self
    {
        return new self(
            id: $progress->exercise->id,
            title: $progress->exercise->getFullTitle(),
            url: route('exercises.show', $progress->exercise),
            isCompleted: $progress->isCompleted(),
            isInProgress: $progress->isInProgress(),
        );
    }
}
