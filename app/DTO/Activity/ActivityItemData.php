<?php

namespace App\DTO\Activity;

use App\Helpers\ChapterHelper;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Exercise;
use App\Models\Solution;
use App\Services\ActivityService;
use Spatie\LaravelData\Data;

class ActivityItemData extends Data
{
    private const array KNOWN_ACTIONS = [
        ActivityService::ACTIVITY_CHAPTER_ADDED,
        ActivityService::ACTIVITY_CHAPTER_REMOVED,
        ActivityService::COMMENTED,
        ActivityService::ACTIVITY_EXERCISE_COMPLETED,
        ActivityService::ACTIVITY_EXERCISE_REMOVED,
        ActivityService::ACTIVITY_SOLUTION_ADDED,
    ];

    /**
     * @param array<int, ActivityLinkData> $links
     */
    public function __construct(
        public int $id,
        public ?string $causerName,
        public ?string $causerUrl,
        public string $description,
        public array $links,
        public string $createdAt,
    ) {
    }

    /**
     * Ждёт causer и subject, загруженные заранее (см. ActivityController): иначе запрос на каждую запись.
     * Subject бывает удалён — тогда запись остаётся, но без ссылки.
     */
    public static function fromModel(Activity $activity): self
    {
        $causer = $activity->causer;

        if (!in_array($activity->description, self::KNOWN_ACTIONS, true)) {
            $description = __('activitylog.action_unknown');
        } elseif ($activity->description === ActivityService::COMMENTED) {
            $description = "{$activity->getDescription()}.";
        } else {
            $description = $activity->getDescription();
        }

        return new self(
            id: $activity->id,
            causerName: $causer?->name,
            causerUrl: $causer ? route('users.show', $causer) : null,
            description: $description,
            links: self::links($activity),
            createdAt: $activity->created_at->format('d.m.Y H:i'),
        );
    }

    /**
     * @return array<int, ActivityLinkData>
     */
    private static function links(Activity $activity): array
    {
        $subject = $activity->subject;
        $exerciseUrl = fn() => route('exercises.show', $activity->getProperty('exercise_id'));

        return match ($activity->description) {
            ActivityService::ACTIVITY_CHAPTER_ADDED, ActivityService::ACTIVITY_CHAPTER_REMOVED => array_map(
                fn(string $path) => new ActivityLinkData(
                    label: ChapterHelper::fullChapterName($path),
                    href: ChapterHelper::getChapterOriginLinkForNumber($path),
                ),
                $activity->getProperty('chapters') ?? [],
            ),
            ActivityService::COMMENTED => $subject instanceof Comment && $subject->getCommentableName() !== null
                ? [new ActivityLinkData($subject->getCommentableName(), $activity->getProperty('url'))]
                : [],
            ActivityService::ACTIVITY_EXERCISE_COMPLETED, ActivityService::ACTIVITY_EXERCISE_REMOVED
                => $subject instanceof Exercise ? [new ActivityLinkData($subject->getFullTitle(), $exerciseUrl())] : [],
            ActivityService::ACTIVITY_SOLUTION_ADDED => [new ActivityLinkData(
                $subject instanceof Solution && $subject->exercise
                    ? $subject->exercise->getFullTitle()
                    : (string) $activity->getProperty('exercise_path'),
                $exerciseUrl(),
            )],
            default => [],
        };
    }
}
