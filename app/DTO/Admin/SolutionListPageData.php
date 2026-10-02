<?php

namespace App\DTO\Admin;

use App\DTO\Navigation\NavItemData;
use App\DTO\PaginationData;
use Spatie\LaravelData\Data;

class SolutionListPageData extends Data
{
    /**
     * @param array<int, SolutionListItemData> $items
     * @param array<int, NavItemData> $menu
     */
    public function __construct(
        public array $items,
        public PaginationData $pagination,
        public UserFilterData $filter,
        public string $filterUrl,
        public array $menu,
    ) {
    }
}
