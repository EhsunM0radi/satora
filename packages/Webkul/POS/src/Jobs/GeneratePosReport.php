<?php

namespace Webkul\POS\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\POS\Services\PosReportingService;

class GeneratePosReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $reportType,
        public array $params = [],
        public ?int $userId = null,
    ) {}

    public function handle(PosReportingService $reportingService): void
    {
        $result = match ($this->reportType) {
            'daily_sales' => $reportingService->dailySalesReport($this->params['date'] ?? null),
            'cashier' => $reportingService->cashierPerformanceReport($this->params['from'], $this->params['to']),
            'products' => $reportingService->productPerformanceReport($this->params['from'], $this->params['to']),
            'payments' => $reportingService->paymentReport($this->params['from'], $this->params['to']),
            'refunds' => $reportingService->refundReport($this->params['from'], $this->params['to']),
            default => throw new \InvalidArgumentException("Unknown report type: {$this->reportType}"),
        };

        // Store generated report
        \DB::table('pos_generated_reports')->insert([
            'report_type' => $this->reportType,
            'params' => json_encode($this->params),
            'result' => json_encode($result),
            'user_id' => $this->userId,
            'created_at' => now(),
        ]);
    }
}
