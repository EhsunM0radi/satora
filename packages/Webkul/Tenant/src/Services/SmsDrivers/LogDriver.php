<?php

namespace Webkul\Tenant\Services\SmsDrivers;

use Illuminate\Support\Facades\Log;
use Webkul\Tenant\Contracts\SmsDriver;

/**
 * Log-based SMS driver for development and testing.
 *
 * Writes SMS messages to the Laravel log instead of sending real SMS.
 * The OTP code is logged at 'info' level for developer visibility.
 *
 * For production, swap this with a real provider driver
 * (e.g., Kavenegar, Twilio, Ghasedak) by binding SmsDriver
 * to a different implementation in TenantServiceProvider.
 */
class LogDriver implements SmsDriver
{
    public function send(string $phone, string $message): bool
    {
        Log::info('[SMS] OTP sent', [
            'phone' => $this->maskPhone($phone),
            'message' => $message,
        ]);

        return true;
    }

    /**
     * Mask the middle digits of a phone number for log privacy.
     */
    protected function maskPhone(string $phone): string
    {
        $len = strlen($phone);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        return substr($phone, 0, 3).str_repeat('*', $len - 6).substr($phone, -3);
    }
}
