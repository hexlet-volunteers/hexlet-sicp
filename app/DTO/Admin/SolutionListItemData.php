<?php

namespace App\DTO\Admin;

use App\Models\Solution;
use Spatie\LaravelData\Data;

class SolutionListItemData extends Data
{
    public function __construct(
        public int $id,
        public string $userName,
        public string $userUrl,
        public string $exercisePath,
        public string $exerciseTitle,
        public string $exerciseUrl,
        public string $content,
        public string $url,
        public string $createdAt,
    ) {
    }

    public static function fromModel(Solution $solution): self
    {
        return new self(
            id: $solution->id,
            userName: $solution->user->name,
            userUrl: route('users.show', $solution->user),
            exercisePath: $solution->exercise->path,
            exerciseTitle: $solution->exercise->getTitle(),
            exerciseUrl: route('exercises.show', $solution->exercise),
            content: mb_substr($solution->content, 0, 100),
            url: route('users.solutions.show', [$solution->user, $solution]),
            createdAt: $solution->created_at->format('d.m.Y H:i'),
        );
    }
}
