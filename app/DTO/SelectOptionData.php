<?php

namespace App\DTO;

use Spatie\LaravelData\Data;

/** Пункт Mantine Select: значение — строка, как в query string. */
class SelectOptionData extends Data
{
    public function __construct(
        public string $value,
        public string $label,
    ) {
    }
}
