<?php

namespace App\Presenters;

use App\Helpers\ExerciseHelper;
use App\Models\Exercise;
use Hemp\Presenter\Presenter;

/**
 * @mixin Exercise
 * @property-read string $underscorePath
 * @property-read string $title
 * @property-read string $fullTitle
 */
class ExercisePresenter extends Presenter
{
    public function getUnderscorePathAttribute(): string
    {
        return str_replace('.', '_', $this->path);
    }

    public function getTitleAttribute(): string
    {
        return ExerciseHelper::getExerciseTitle($this->model);
    }

    public function getFullTitleAttribute(): string
    {
        $path          = $this->path;
        $exerciseTitle = $this->title;

        return "$path $exerciseTitle";
    }
}
