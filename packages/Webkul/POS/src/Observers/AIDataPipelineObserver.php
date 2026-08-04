<?php

namespace Webkul\POS\Observers;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Events\PosAIDataCollected;

class AIDataPipelineObserver
{
    protected static array $trackedEvents = [
        'created', 'updated',
    ];

    public function created(Model $model): void
    {
        $this->collect($model, 'created');
    }

    public function updated(Model $model): void
    {
        $this->collect($model, 'updated');
    }

    protected function collect(Model $model, string $eventType): void
    {
        if (! config('pos.ai.data_collection_enabled', true)) {
            return;
        }

        event(new PosAIDataCollected(
            entityType: get_class($model),
            entityId: $model->getKey(),
            eventType: $eventType,
            tenantId: $model->tenant_id,
            payload: $model->getAttributes(),
            timestamp: now(),
        ));
    }
}
