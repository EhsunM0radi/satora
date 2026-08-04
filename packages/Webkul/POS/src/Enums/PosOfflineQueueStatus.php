<?php

namespace Webkul\POS\Enums;

enum PosOfflineQueueStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CONFLICT = 'conflict';

    public function label(): string
    {
        return __('pos::app.enums.'.strtolower($this->name));
    }
}
