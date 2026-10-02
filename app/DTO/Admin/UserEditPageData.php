<?php

namespace App\DTO\Admin;

use App\DTO\Navigation\NavItemData;
use Spatie\LaravelData\Data;

class UserEditPageData extends Data
{
    /**
     * @param array<int, NavItemData> $menu
     */
    public function __construct(
        public string $name,
        public ?string $githubName,
        public bool $isAdmin,
        public string $updateUrl,
        public string $cancelUrl,
        public array $menu,
    ) {
    }
}
