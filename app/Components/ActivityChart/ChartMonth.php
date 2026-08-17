<?php

namespace App\Components\ActivityChart;

use App\Components\ActivityChart\Enums\Month;

readonly class ChartMonth
{
    public function __construct(
        private Month $month,
        private int $weekCount,
    ) {
    }

    public function getMonth(): Month
    {
        return $this->month;
    }

    public function getWeekCount(): int
    {
        return $this->weekCount;
    }
}
