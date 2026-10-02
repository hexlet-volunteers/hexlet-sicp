<?php

namespace App\Support\Inertia;

use Laracasts\Flash\Message;

/**
 * Единая точка чтения flash для Blade-лейаута и Inertia-шелла. Каналов два:
 * flash() из laracasts/flash (ключ flash_notification) и ->with('success'|...).
 * Вызовы flash() не переписываем: оба канала читаются здесь до конца фазы 2.
 */
class FlashBag
{
    private const array LEVELS = ['success', 'error', 'warning', 'info'];

    /**
     * Сообщения обоих каналов; отданное удаляется из сессии, чтобы не показать дважды.
     * Уровень приведён к success|error|warning|info (danger из laracasts → error).
     *
     * @return array<int, array{message: string, level: string}>
     */
    public function pull(): array
    {
        $messages = collect(session()->pull('flash_notification', []))
            ->map(fn(Message $message) => [
                'message' => $message->message,
                'level' => $message->level === 'danger' ? 'error' : $message->level,
            ])
            ->values()
            ->all();

        foreach (self::LEVELS as $level) {
            if (session()->has($level)) {
                $messages[] = ['message' => session()->pull($level), 'level' => $level];
            }
        }

        return $messages;
    }
}
