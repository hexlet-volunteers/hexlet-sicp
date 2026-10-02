<?php

namespace App\DTO\Solution;

use Spatie\LaravelData\Data;

class SolutionFilterData extends Data
{
    public function __construct(
        public ?string $userName,
        public ?string $exerciseId,
    ) {
    }
}
