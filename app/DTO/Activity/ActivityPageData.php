<?php

namespace App\DTO\Activity;

use App\DTO\PaginationData;
use Spatie\LaravelData\Data;

class ActivityPageData extends Data
{
    /**
     * @param array<int, ActivityItemData> $items
     */
    public function __construct(
        public array $items,
        public PaginationData $pagination,
    ) {
    }
}
