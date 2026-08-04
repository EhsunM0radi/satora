<?php

namespace Webkul\POS\Exceptions;

use Exception;

class PosSessionException extends Exception
{
    public static function alreadyOpen(int $terminalId): self
    {
        return new self("Terminal {$terminalId} already has an open session.");
    }

    public static function notOpen(int $terminalId): self
    {
        return new self("Terminal {$terminalId} has no open session.");
    }

    public static function sessionClosed(int $sessionId): self
    {
        return new self("Session {$sessionId} is already closed.");
    }

    public static function closingBalanceRequired(): self
    {
        return new self('Closing balance is required to close a session.');
    }
}
