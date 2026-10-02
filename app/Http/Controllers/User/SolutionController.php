<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\DTO\Solution\SolutionShowPageData;
use App\Http\Controllers\Controller;
use App\Models\Solution;
use App\Models\User;
use Inertia\Response;

class SolutionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Solution::class, 'solution');
    }

    public function show(User $user, Solution $solution): Response
    {
        $page = SolutionShowPageData::fromExercise($solution->exercise, $user);

        // Имя компонента явное: автовывод дал бы User/Solution/Show — копию Solution/Show.
        return $this->inertia($page->toArray(), 'Solution/Show')
            ->withViewData(['robots' => 'noindex, nofollow', 'description' => $page->description()]);
    }
}
