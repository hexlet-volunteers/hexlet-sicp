<?php

namespace App\Presenters;

use Hemp\Presenter\Presenter;
use Illuminate\Support\Str;

/**
 * @mixin \App\Models\Comment
 */
class CommentPresenter extends Presenter
{
    public function getLink(): string
    {
        $commentableResourceName = Str::plural(strtolower(class_basename($this->commentable_type)));
        $commentableUrl = route("{$commentableResourceName}.show", $this->commentable);
        $commentUrl = "{$commentableUrl}#comment-{$this->id}";
        return $commentUrl;
    }
}
