<?php

namespace App\DTO\Admin;

use App\DTO\Navigation\NavItemData;
use Spatie\LaravelData\Data;

class ExportPageData extends Data
{
    /**
     * @param array<int, ExportTypeData> $types
     * @param array<int, NavItemData> $menu
     */
    public function __construct(
        public array $types,
        public string $storeUrl,
        public array $menu,
    ) {
    }
}
