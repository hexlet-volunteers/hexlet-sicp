<?php

namespace App\Helpers;

use App\Models\Exercise;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

class ExerciseHelper
{
    private const FALLBACK_LOCALE = 'en';

    /**
     * Названия упражнений из data.yml в пределах запроса: путь к файлу => название.
     *
     * @var array<string, string>
     */
    private static array $titleCache = [];

    public static function getExerciseLocalePath(Exercise $exercise, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $exerciseKey = $exercise->present()->underscorePath;

        return resource_path("exercises/{$exerciseKey}/{$locale}");
    }

    public static function getExerciseDescription(Exercise $exercise): string
    {
        $path = self::resolveLocalizedFile($exercise, 'README.md');

        if ($path === null) {
            return '';
        }

        return File::get($path);
    }

    public static function getExerciseTitle(Exercise $exercise): string
    {
        $path = self::resolveLocalizedFile($exercise, 'data.yml');

        if ($path === null) {
            return $exercise->path;
        }

        $name = self::$titleCache[$path] ??= (string) (Yaml::parseFile($path)['name'] ?? '');

        return $name === '' ? $exercise->path : $name;
    }

    public static function getExerciseOriginLink(Exercise $exercise): ?string
    {
        $links = require resource_path('exercise-links.php');

        return $links[$exercise->path] ?? null;
    }

    /**
     * Ищет файл упражнения в текущей локали, откатываясь на английскую.
     */
    private static function resolveLocalizedFile(Exercise $exercise, string $filename): ?string
    {
        $locales = array_unique([app()->getLocale(), self::FALLBACK_LOCALE]);

        foreach ($locales as $locale) {
            $path = self::getExerciseLocalePath($exercise, $locale) . "/{$filename}";

            if (File::exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
