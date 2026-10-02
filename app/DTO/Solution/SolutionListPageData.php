<?php

namespace App\DTO\Solution;

use App\DTO\Navigation\NavItemData;
use App\DTO\PaginationData;
use App\DTO\SelectOptionData;
use Spatie\LaravelData\Data;

class SolutionListPageData extends Data
{
    /**
     * @param array<int, SolutionListItemData> $items
     * @param array<int, SelectOptionData> $exercises
     * @param array<int, NavItemData> $tabs
     */
    public function __construct(
        public array $items,
        public PaginationData $pagination,
        public SolutionFilterData $filter,
        public string $filterUrl,
        public array $exercises,
        public array $tabs,
    ) {
    }
}
