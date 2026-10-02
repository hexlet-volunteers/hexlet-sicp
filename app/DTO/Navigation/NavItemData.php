<?php

namespace App\DTO\Navigation;

use Spatie\LaravelData\Data;

class NavItemData extends Data
{
    /**
     * @param string|null $method 'post' — ссылку нужно отправить через router.post(), а не открыть
     * @param bool $inertia маршрут уже на Inertia: рендерить <Link>, иначе обычный <a>
     * @param array<int, NavItemData> $children
     * @param bool $highlight пункт выделен цветом (в Blade-шапке — link-info)
     */
    public function __construct(
        public string $label,
        public string $href,
        public bool $active = false,
        public bool $inertia = false,
        public ?string $method = null,
        public ?string $icon = null,
        public array $children = [],
        public bool $highlight = false,
    ) {
    }
}
