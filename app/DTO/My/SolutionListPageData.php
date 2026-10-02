<?php

namespace App\DTO\My;

use App\DTO\PaginationData;
use Spatie\LaravelData\Data;

class SolutionListPageData extends Data
{
    /**
     * @param array<int, SolutionListItemData> $items
     */
    public function __construct(
        public string $userName,
        public string $userUrl,
        public string $progressUrl,
        public array $items,
        public PaginationData $pagination,
    ) {
    }
}
