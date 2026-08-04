<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Contracts\PosReceipt as PosReceiptContract;
use Webkul\POS\Contracts\PosReceiptModel;
use Webkul\POS\Support\BelongsToTenant;

class PosReceipt extends Model implements PosReceiptContract, PosReceiptModel
{
    use BelongsToTenant;

    protected $table = 'pos_receipts';

    protected $fillable = [
        'tenant_id',
        'pos_order_id',
        'pos_refund_id',
        'receipt_number',
        'type',
        'template',
        'delivery_method',
        'recipient_email',
        'recipient_phone',
        'content_html',
        'qr_code_data',
        'printed',
        'printed_at',
    ];

    protected function casts(): array
    {
        return [
            'printed' => 'boolean',
            'printed_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }

    public function refund()
    {
        return $this->belongsTo(PosRefund::class, 'pos_refund_id');
    }
}
