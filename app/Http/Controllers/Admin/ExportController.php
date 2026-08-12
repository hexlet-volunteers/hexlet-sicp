<?php

namespace App\Http\Controllers\Admin;

use App\DTO\Admin\ExportData;
use App\Services\AnalyticsExporter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends AdminController
{
    public function __construct(
        private readonly AnalyticsExporter $exporter
    ) {
        parent::__construct();
    }

    public function index(Request $request): View
    {
        return view('admin.export');
    }

    public function store(ExportData $request): BinaryFileResponse
    {
        $filePath = $this->exporter->export($request->type);

        return response()->download($filePath)->deleteFileAfterSend();
    }
}
