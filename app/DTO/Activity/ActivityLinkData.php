<?php

namespace App\DTO\Activity;

use Spatie\LaravelData\Data;

class ActivityLinkData extends Data
{
    public function __construct(
        public string $label,
        public ?string $href,
    ) {
    }
}
