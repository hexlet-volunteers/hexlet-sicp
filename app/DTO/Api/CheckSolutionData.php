<?php

namespace App\DTO\Api;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class CheckSolutionData extends Data
{
    public function __construct(
        #[Required]
        public string $solution_code,
    ) {
    }
}
