<?php

namespace App\DTO;

use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\LaravelData\Data;

/**
 * Свой конверт вместо PaginatedDataCollection: там `links` — это {first,last,prev,next},
 * а не `links[]` пагинатора Laravel. Подробнее — docs/frontend-migration.md.
 */
class PaginationData extends Data
{
    /**
     * @param array<int, PaginationLinkData> $links
     */
    public function __construct(
        public int $currentPage,
        public int $lastPage,
        public int $total,
        public array $links,
    ) {
    }

    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        return new self(
            currentPage: $paginator->currentPage(),
            lastPage: $paginator->lastPage(),
            total: $paginator->total(),
            links: array_map(
                // В словаре pagination подписи с HTML-сущностями (&laquo;) — их ещё рендерит Blade.
                fn(array $link) => new PaginationLinkData(
                    url: $link['url'],
                    label: html_entity_decode((string) $link['label']),
                    active: $link['active'],
                ),
                $paginator->linkCollection()->all(),
            ),
        );
    }
}
