<?php

namespace Webkul\POS\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\POS\Models\PosReceipt;
use Webkul\POS\Notifications\PosReceiptEmail;

class SendPosReceipt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public PosReceipt $receipt,
    ) {}

    public function handle(): void
    {
        if ($this->receipt->recipient_email) {
            \Mail::to($this->receipt->recipient_email)
                ->send(new PosReceiptEmail($this->receipt));
        }
    }
}
