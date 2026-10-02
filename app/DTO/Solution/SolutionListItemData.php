<?php

namespace App\DTO\Solution;

use App\Models\Solution;
use Spatie\LaravelData\Data;

class SolutionListItemData extends Data
{
    public function __construct(
        public int $id,
        public string $userName,
        public string $userUrl,
        public string $userAvatarUrl,
        public string $exerciseTitle,
        public string $exerciseUrl,
        public string $createdAt,
        public string $showUrl,
    ) {
    }

    public static function fromModel(Solution $solution): self
    {
        return new self(
            id: $solution->id,
            userName: $solution->user->name,
            userUrl: route('users.show', $solution->user),
            userAvatarUrl: $solution->user->present()->getProfileImageLink(),
            exerciseTitle: $solution->exercise->getFullTitle(),
            exerciseUrl: route('exercises.show', $solution->exercise),
            createdAt: $solution->created_at->format('d.m.Y H:i'),
            showUrl: route('solutions.show', $solution),
        );
    }
}
