<?php

namespace App\DTO\Admin;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

/**
 * Фильтр админских списков по пользователю (filter[name], filter[email]):
 * в users — по самому пользователю, в comments и solutions — по автору.
 */
class UserFilterData extends Data
{
    public function __construct(
        public ?string $name,
        public ?string $email,
    ) {
    }

    // ?filter[name][]=… QueryBuilder принимает, а строковое поле DTO — нет.
    public static function fromQuery(Request $request): self
    {
        $name = $request->input('filter.name');
        $email = $request->input('filter.email');

        return new self(
            name: is_string($name) ? $name : null,
            email: is_string($email) ? $email : null,
        );
    }
}
