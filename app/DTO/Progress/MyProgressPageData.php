<?php

namespace App\DTO\Progress;

use Spatie\LaravelData\Data;

class MyProgressPageData extends Data
{
    /**
     * @param array<int, ChapterNodeData> $chapters
     */
    public function __construct(
        public string $userName,
        public string $userUrl,
        public string $solutionsUrl,
        public array $chapters,
    ) {
    }
}
