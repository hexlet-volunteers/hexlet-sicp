<?php

namespace App\DTO;

use Spatie\LaravelData\Data;

class PaginationLinkData extends Data
{
    public function __construct(
        public ?string $url,
        public string $label,
        public bool $active,
    ) {
    }
}
