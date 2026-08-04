<?php

namespace Webkul\POS\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CollectAITrainingData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $data,
    ) {}

    public function handle(): void
    {
        // Store AI training data for later model training
        \DB::table('pos_ai_training_data')->insert([
            'type' => $this->data['type'],
            'tenant_id' => $this->data['tenant_id'] ?? null,
            'payload' => json_encode($this->data['data']),
            'created_at' => now(),
        ]);
    }
}
