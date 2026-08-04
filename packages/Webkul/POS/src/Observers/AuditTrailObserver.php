<?php

namespace Webkul\POS\Observers;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Models\PosAuditLog;

class AuditTrailObserver
{
    public function created(Model $model): void
    {
        $this->log('created', $model);
    }

    public function updated(Model $model): void
    {
        $this->log('updated', $model);
    }

    public function deleted(Model $model): void
    {
        $this->log('deleted', $model);
    }

    protected function log(string $eventType, Model $model): void
    {
        if (! config('pos.audit.enabled', true)) {
            return;
        }

        PosAuditLog::create([
            'tenant_id' => $model->tenant_id ?? app('current_tenant')?->id,
            'admin_user_id' => auth('admin')->id(),
            'event_type' => $eventType,
            'entity_type' => get_class($model),
            'entity_id' => $model->getKey(),
            'old_values' => $eventType === 'updated' ? $model->getOriginal() : null,
            'new_values' => $model->getAttributes(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
