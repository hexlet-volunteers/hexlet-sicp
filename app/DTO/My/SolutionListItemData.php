<?php

namespace App\DTO\My;

use App\Models\Solution;
use Spatie\LaravelData\Data;

class SolutionListItemData extends Data
{
    public function __construct(
        public int $id,
        public string $exerciseTitle,
        public string $chapterPath,
        public string $showUrl,
    ) {
    }

    public static function fromModel(Solution $solution): self
    {
        return new self(
            id: $solution->id,
            exerciseTitle: $solution->exercise->getFullTitle(),
            chapterPath: $solution->exercise->chapter->path,
            showUrl: route('users.solutions.show', [$solution->user_id, $solution]),
        );
    }
}
