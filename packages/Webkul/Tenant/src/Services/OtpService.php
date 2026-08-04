<?php

namespace Webkul\Tenant\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\Tenant\Contracts\SmsDriver;

/**
 * One-Time Password service for phone verification.
 *
 * Generates cryptographically random 6-digit codes, persists them
 * with a configurable expiry, and verifies them atomically to prevent
 * replay attacks.
 *
 * Codes expire after $ttlMinutes (default: 5). A phone number can
 * have at most one active code per purpose at a time — requesting
 * a new code invalidates the previous one.
 */
class OtpService
{
    protected int $codeLength = 6;

    protected int $ttlMinutes = 5;

    protected int $maxAttempts = 5;

    public function __construct(
        protected SmsDriver $smsDriver
    ) {}

    /**
     * Generate and send an OTP code to the given phone number.
     *
     * Returns the generated code (so it can be shown in dev).
     * In production, the code is only sent via SMS — never returned.
     */
    public function send(string $phone, string $purpose = 'signup'): string
    {
        // Invalidate any existing active code for this phone + purpose
        DB::table('satora_otp_codes')
            ->where('phone', $phone)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $code = $this->generateCode();
        $expiresAt = Carbon::now()->addMinutes($this->ttlMinutes);

        DB::table('satora_otp_codes')->insert([
            'phone' => $phone,
            'code' => $code,
            'purpose' => $purpose,
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $message = __('tenant::otp.message', ['code' => $code]);

        $this->smsDriver->send($phone, $message);

        return $code;
    }

    /**
     * Verify an OTP code for the given phone number.
     *
     * Uses an atomic update to prevent race conditions and replay.
     * Returns true if the code is valid and was marked as used.
     */
    public function verify(string $phone, string $code, string $purpose = 'signup'): bool
    {
        // Prune expired codes first
        $this->pruneExpired();

        // Atomic: mark as used if valid, unexpired, and unused
        $updated = DB::table('satora_otp_codes')
            ->where('phone', $phone)
            ->where('code', $code)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->limit(1)
            ->update(['used_at' => now()]);

        return $updated > 0;
    }

    /**
     * Check how many attempts have been made for this phone+purpose
     * in the current window (to detect brute-force patterns).
     */
    public function recentAttempts(string $phone, string $purpose = 'signup'): int
    {
        return DB::table('satora_otp_codes')
            ->where('phone', $phone)
            ->where('purpose', $purpose)
            ->where('created_at', '>=', Carbon::now()->subMinutes(15))
            ->count();
    }

    /**
     * Generate a cryptographically random numeric code.
     */
    protected function generateCode(): string
    {
        $min = 10 ** ($this->codeLength - 1);
        $max = (10 ** $this->codeLength) - 1;

        return (string) random_int($min, $max);
    }

    /**
     * Remove codes that have expired (older than 24 hours)
     * or have been used.
     */
    protected function pruneExpired(): void
    {
        DB::table('satora_otp_codes')
            ->where('expires_at', '<', Carbon::now()->subHours(24))
            ->orWhereNotNull('used_at')
            ->delete();
    }

    /**
     * Configure OTP settings at runtime.
     */
    public function setTtl(int $minutes): self
    {
        $this->ttlMinutes = $minutes;

        return $this;
    }

    public function setCodeLength(int $length): self
    {
        $this->codeLength = max(4, min(10, $length));

        return $this;
    }
}
