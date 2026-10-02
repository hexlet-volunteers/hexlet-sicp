<?php

namespace App\DTO\Admin;

use App\Models\User;
use Spatie\LaravelData\Data;

class UserListItemData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public bool $isAdmin,
        public string $createdAt,
        public string $showUrl,
        public string $editUrl,
    ) {
    }

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            isAdmin: (bool) $user->is_admin,
            createdAt: $user->created_at->format('d.m.Y H:i'),
            showUrl: route('users.show', $user),
            editUrl: route('admin.users.edit', $user),
        );
    }
}
