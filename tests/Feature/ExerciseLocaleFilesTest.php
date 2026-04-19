<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

class ExerciseLocaleFilesTest extends TestCase
{
    private const LOCALES = ['en', 'ru'];

    private static function listingPath(): string
    {
        return realpath(__DIR__ . '/../..') . '/resources/views/exercise/listing';
    }

    public static function dataYmlProvider(): array
    {
        $dataset = [];

        foreach (glob(self::listingPath() . '/*/') as $exerciseDir) {
            $key = basename($exerciseDir);

            foreach (self::LOCALES as $locale) {
                $ymlPath = "{$exerciseDir}{$locale}/data.yml";
                $label   = "{$key} [{$locale}]";

                $dataset[$label] = [$ymlPath, $key, $locale];
            }
        }

        return $dataset;
    }

    #[DataProvider('dataYmlProvider')]
    public function testDataYmlHasRequiredFields(
        string $ymlPath,
        string $key,
        string $locale
    ): void {
        $this->assertFileExists(
            $ymlPath,
            "Missing data.yml for exercise {$key} [{$locale}]"
        );

        $data = Yaml::parseFile($ymlPath);

        $this->assertArrayHasKey(
            'name',
            $data,
            "Missing 'name' field in {$ymlPath}"
        );

        $this->assertNotEmpty(
            $data['name'],
            "Empty 'name' field in {$ymlPath}"
        );
    }
}
