<?php

namespace App\DTO\Solution;

use App\Models\Solution;
use Spatie\LaravelData\Data;

class SolutionVersionData extends Data
{
    public function __construct(
        public int $id,
        public string $content,
    ) {
    }

    public static function fromModel(Solution $solution): self
    {
        return new self(id: $solution->id, content: $solution->content);
    }
}
