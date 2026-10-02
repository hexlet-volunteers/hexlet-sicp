<?php

namespace App\DTO\Admin;

use App\Helpers\CommentHelper;
use App\Helpers\MarkdownHelper;
use App\Models\Comment;
use Spatie\LaravelData\Data;

class CommentListItemData extends Data
{
    public function __construct(
        public int $id,
        public string $userName,
        public string $userUrl,
        public ?string $commentableName,
        public string $commentableUrl,
        // HTML из Parsedown в safe mode — можно отдавать в dangerouslySetInnerHTML.
        public string $contentHtml,
        public string $url,
        public string $createdAt,
    ) {
    }

    public static function fromModel(Comment $comment): self
    {
        return new self(
            id: $comment->id,
            userName: $comment->user->name,
            userUrl: route('users.show', $comment->user),
            commentableName: $comment->getCommentableName(),
            commentableUrl: CommentHelper::getCommentableUrl($comment),
            // Режем markdown до рендера: обрезанный HTML оставлял бы незакрытые теги.
            contentHtml: MarkdownHelper::text(mb_substr($comment->content, 0, 100)),
            url: route('comments.show', $comment),
            createdAt: $comment->created_at->format('d.m.Y H:i'),
        );
    }
}
