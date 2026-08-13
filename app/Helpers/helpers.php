<?php

use App\Helpers\ChapterHelper;
use App\Helpers\ExerciseHelper;
use App\Helpers\TemplateHelper;
use App\Models\Chapter;
use App\Models\Exercise;

if (!function_exists('getTitleContent')) {
    function getTitleContent(string $titleName): string
    {
        return TemplateHelper::getTitleContent($titleName);
    }
}

if (!function_exists('getChapterHeaderTag')) {
    function getChapterHeaderTag(Chapter $chapter): string
    {
        return ChapterHelper::getChapterHeaderTag($chapter);
    }
}

if (!function_exists('getChapterOriginLink')) {
    function getChapterOriginLink(Chapter $chapter): ?string
    {
        return ChapterHelper::getChapterOriginLink($chapter);
    }
}

if (!function_exists('getChapterOriginLinkForNumber')) {
    function getChapterOriginLinkForNumber(string $chapter): ?string
    {
        return ChapterHelper::getChapterOriginLinkForNumber($chapter);
    }
}

if (!function_exists('getExerciseDescription')) {
    function getExerciseDescription(Exercise $exercise): string
    {
        return ExerciseHelper::getExerciseDescription($exercise);
    }
}

if (!function_exists('getExerciseOriginLink')) {
    function getExerciseOriginLink(Exercise $exercise): ?string
    {
        return ExerciseHelper::getExerciseOriginLink($exercise);
    }
}
