<?php

namespace App\DTO\Solution;

use App\Models\Exercise;
use App\Models\User;
use Spatie\LaravelData\Data;

class SolutionShowPageData extends Data
{
    /**
     * @param array<int, SolutionVersionData> $versions
     */
    public function __construct(
        public string $title,
        public string $exerciseTitle,
        public string $exerciseUrl,
        public string $userName,
        public string $userUrl,
        public array $versions,
    ) {
    }

    /**
     * Все версии решений пользователя по упражнению — страница сравнивает их между собой.
     */
    public static function fromExercise(Exercise $exercise, User $user): self
    {
        $versions = $exercise->solutions()
            ->where('user_id', $user->id)
            ->orderBy('id')
            ->get();

        return new self(
            title: __('solution.solution_for_title', ['exercise' => $exercise->getFullTitle()]),
            exerciseTitle: $exercise->getFullTitle(),
            exerciseUrl: route('exercises.show', $exercise),
            userName: $user->name,
            userUrl: route('users.show', $user),
            versions: $versions->map(SolutionVersionData::fromModel(...))->all(),
        );
    }

    public function description(): string
    {
        return implode(' - ', [
            $this->userName,
            __('solution.code_review'),
            __('solution.exercise') . " {$this->exerciseTitle}",
        ]);
    }
}
