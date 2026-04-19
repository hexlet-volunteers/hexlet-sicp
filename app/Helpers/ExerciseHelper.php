<?php

namespace App\Helpers;

use App\Models\Exercise;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

class ExerciseHelper
{
    public static function getExerciseLocalePath(Exercise $exercise, ?string $locale = null): string
    {
        $locale    = $locale ?? app()->getLocale();

        $viewName  = $exercise->present()->underscorePath;

        return resource_path("views/exercise/listing/{$viewName}/{$locale}");
    }

    public static function getExerciseDescription(Exercise $exercise): string
    {
        $locale = app()->getLocale();
        $path   = self::getExerciseLocalePath($exercise, $locale) . '/README.md';

        if (!File::exists($path)) {
            $path = self::getExerciseLocalePath($exercise, 'en') . '/README.md';
        }

        if (!File::exists($path)) {
            return '';
        }

        return File::get($path);
    }

    public static function getExerciseTitle(Exercise $exercise): string
    {
        $locale   = app()->getLocale();
        $ymlPath  = self::getExerciseLocalePath($exercise, $locale) . '/data.yml';

        if (!File::exists($ymlPath)) {
            $ymlPath = self::getExerciseLocalePath($exercise, 'en') . '/data.yml';
        }

        if (!File::exists($ymlPath)) {
            return $exercise->path;
        }

        $data = Yaml::parseFile($ymlPath);

        return $data['name'] ?? $exercise->path;
    }

    public static function getExerciseOriginLink(Exercise $exercise): string
    {
        $links = require resource_path('exercise-links.php');
        $link  = $links[$exercise->path];
        $link  = $links[$exercise->path];
        return $link;
    }
}
