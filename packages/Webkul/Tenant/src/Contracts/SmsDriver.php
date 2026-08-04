<?php

namespace Webkul\Tenant\Contracts;

interface SmsDriver
{
    /**
     * Send an SMS message.
     *
     * @param  string  $phone  Recipient phone number
     * @param  string  $message  Message body
     * @return bool Whether the send was accepted by the provider
     */
    public function send(string $phone, string $message): bool;
}
