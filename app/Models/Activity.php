<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Activity as BaseActivity;

/**
 * @property int $id
 * @property string|null $log_name
 * @property string $description
 * @property int|null $subject_id
 * @property string|null $subject_type
 * @property int|null $causer_id
 * @property string|null $causer_type
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $causer
 * @property-read \Illuminate\Support\Collection $changes
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $subject
 */
class Activity extends BaseActivity
{
    use SoftDeletes;

    public function getDescription(): string
    {
        $itemsCount = $this->getProperty('count');
        $description = $this->description;
        return trans_choice("activitylog.action_{$description}", $itemsCount, ['count' => $itemsCount]);
    }
}
