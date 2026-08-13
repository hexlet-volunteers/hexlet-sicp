<?php

namespace App\Models;

use Database\Factories\ExerciseMemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Iben\Statable\Statable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static ExerciseMemberFactory factory(...$parameters)
 */
class ExerciseMember extends Model
{
    use HasFactory;
    use Statable;
    use SoftDeletes;

    public const string STATE_STARTED = 'started';
    public const string STATE_FINISHED = 'finished';

    protected $guarded = [];

    protected $attributes = [
            'state' => self::STATE_STARTED,
        ];

    protected function getGraph(): string
    {
        return 'completed_exercise';
    }

    protected function saveBeforeTransition(): bool
    {
        return true;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function mayFinish(): bool
    {
        return $this->canApply('finish');
    }

    public function finish(): void
    {
        $this->apply('finish');
    }

    public function isStarted(): bool
    {
        return $this->state === self::STATE_STARTED;
    }

    public function isFinished(): bool
    {
        return $this->state === self::STATE_FINISHED;
    }

    public function isNotFinished(): bool
    {
        return !$this->isFinished();
    }

    public function scopeFinished(Builder $builder): Builder
    {
        return $builder->where('state', '=', self::STATE_FINISHED);
    }
}
