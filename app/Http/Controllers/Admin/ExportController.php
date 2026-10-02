<?php

namespace App\Http\Controllers\Admin;

use App\DTO\Admin\ExportData;
use App\DTO\Admin\ExportPageData;
use App\DTO\Admin\ExportTypeData;
use App\Services\AnalyticsExporter;
use App\Support\Navigation\NavigationBuilder;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends AdminController
{
    public function __construct(
        private readonly AnalyticsExporter $exporter
    ) {
        parent::__construct();
    }

    /** Значения, которые принимает AnalyticsExporter::export(). */
    private const array TYPES = ['users', 'chapters', 'exercises', 'solutions', 'comments', 'activity'];

    public function index(NavigationBuilder $navigation): Response
    {
        $page = new ExportPageData(
            types: array_map(
                fn(string $type) => new ExportTypeData($type, __("admin.export.types.{$type}")),
                self::TYPES,
            ),
            storeUrl: route('admin.export.store'),
            menu: $navigation->admin(),
        );

        return $this->inertia($page->toArray())
            ->withViewData(['robots' => 'noindex, nofollow']);
    }

    public function store(ExportData $request): BinaryFileResponse
    {
        $filePath = $this->exporter->export($request->type);

        return response()->download($filePath)->deleteFileAfterSend();
    }
}
