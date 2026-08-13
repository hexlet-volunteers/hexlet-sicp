<?php

namespace App\Models;

use App\Presenters\ExercisePresenter;
use Hemp\Presenter\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use File;

/**
 * @method ExercisePresenter present()
 */
class Exercise extends Model
{
    use Presentable;
    use HasFactory;

    public string $defaultPresenter = ExercisePresenter::class;

    protected $guarded = [];

    public static function findByPath(string $path): self
    {
        return static::wherePath($path)->firstOrFail();
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'exercise_members');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ExerciseMember::class);
    }

    public function solutions(): HasMany
    {
        return $this->hasMany(Solution::class);
    }

    public function getTitle(): string
    {
        return $this->present()->title;
    }

    public function hasTests(): bool
    {
        return self::isPathExist('.blade.php');
    }

    public function hasTeacherSolution(): bool
    {
        return self::isPathExist('_solution.blade.php');
    }

    public static function underscoreExercisePath(string $path): string
    {
        return str_replace('.', '_', $path);
    }

    private function isPathExist(string $file_name): bool
    {
        $underscoredExercisePath = self::underscoreExercisePath($this->path);
        $path = $this->getExercisePath($underscoredExercisePath, $file_name);

        return File::exists($path);
    }

    public function getFullTitle(): string
    {
        return $this->present()->fullTitle;
    }

    public function getExercisePath(string $underscoredExercisePath, string $file_name): string
    {
        $path = implode(DIRECTORY_SEPARATOR, [
            resource_path(),
            'views',
            'exercise',
            'solution_stub',
            sprintf('%s%s', $underscoredExercisePath, $file_name),
        ]);
        return $path;
    }

    public function getFileContent(string $file_name): string
    {
        $underscoredExercisePath = $this->present()->underscorePath;
        $path = $this->getExercisePath($underscoredExercisePath, $file_name);
        $fileContent = File::get($path);

        return $fileContent;
    }

    public function getExerciseTests(): string
    {
        $fileContent = $this->getFileContent('.blade.php');
        [, $testsLines] = explode(';;; END', $fileContent);

        return trim($testsLines);
    }

    public function getExerciseTeacherSolution(): string
    {
        $fileContent = $this->getFileContent('_solution.blade.php');

        return trim($fileContent);
    }
}
