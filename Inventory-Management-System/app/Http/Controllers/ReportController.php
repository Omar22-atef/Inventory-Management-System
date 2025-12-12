<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    protected $service;

    // Single constructor
    public function __construct(ReportService $service)
    {
        $this->service = $service;
    }

    /**
     * Blade view report
     */
    public function salesInventoryReport()
    {
        $report = $reportService->salesInventory();
        return view('reports.sales_inventory', compact('report'));

    }

    /**
     * API endpoints (JSON responses)
     */

    
    public function salesInventory(): JsonResponse
    {
        return response()->json($this->service->salesInventory());
    }

    public function lowStock(): JsonResponse
    {
        return response()->json($this->service->lowStock());
    }

    public function purchaseHistory(): JsonResponse
    {
        return response()->json($this->service->purchaseHistory());
    }

    public function salesPerformance(): JsonResponse
    {
        return response()->json($this->service->salesPerformance());
    }

    public function supplierPerformance(): JsonResponse
    {
        return response()->json($this->service->supplierPerformance());
    }
}
