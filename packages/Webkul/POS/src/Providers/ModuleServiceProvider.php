<?php

namespace Webkul\POS\Providers;

use Konekt\Concord\BaseModuleServiceProvider;
use Webkul\POS\Models\PosAuditLog;
use Webkul\POS\Models\PosCashMovement;
use Webkul\POS\Models\PosCashRegister;
use Webkul\POS\Models\PosDiscount;
use Webkul\POS\Models\PosEmployeeAssignment;
use Webkul\POS\Models\PosEmployeeRole;
use Webkul\POS\Models\PosExchange;
use Webkul\POS\Models\PosHardwareEvent;
use Webkul\POS\Models\PosInventoryReservation;
use Webkul\POS\Models\PosLocation;
use Webkul\POS\Models\PosOfflineQueue;
use Webkul\POS\Models\PosOrder;
use Webkul\POS\Models\PosOrderItem;
use Webkul\POS\Models\PosPayment;
use Webkul\POS\Models\PosProductCache;
use Webkul\POS\Models\PosReceipt;
use Webkul\POS\Models\PosRefund;
use Webkul\POS\Models\PosRefundItem;
use Webkul\POS\Models\PosSession;
use Webkul\POS\Models\PosTerminal;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        PosLocation::class,
        PosTerminal::class,
        PosSession::class,
        PosCashRegister::class,
        PosCashMovement::class,
        PosOrder::class,
        PosOrderItem::class,
        PosPayment::class,
        PosRefund::class,
        PosRefundItem::class,
        PosExchange::class,
        PosDiscount::class,
        PosReceipt::class,
        PosEmployeeRole::class,
        PosEmployeeAssignment::class,
        PosInventoryReservation::class,
        PosOfflineQueue::class,
        PosProductCache::class,
        PosHardwareEvent::class,
        PosAuditLog::class,
    ];
}
