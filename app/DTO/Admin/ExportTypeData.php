<?php

namespace App\DTO\Admin;

use Spatie\LaravelData\Data;

class ExportTypeData extends Data
{
    public function __construct(
        public string $value,
        public string $label,
    ) {
    }
}
