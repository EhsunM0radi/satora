<?php

namespace Webkul\POS\Services;

use Webkul\POS\Events\PosSessionClosed;
use Webkul\POS\Events\PosSessionOpened;
use Webkul\POS\Events\PosSessionSuspended;
use Webkul\POS\Exceptions\PosSessionException;
use Webkul\POS\Models\PosCashRegister;
use Webkul\POS\Models\PosSession;
use Webkul\POS\Models\PosTerminal;
use Webkul\POS\Repositories\PosCashMovementRepository;
use Webkul\POS\Repositories\PosSessionRepository;

class PosSessionService
{
    public function __construct(
        protected PosSessionRepository $sessionRepository,
        protected PosCashMovementRepository $cashMovementRepository,
    ) {}

    public function openSession(PosTerminal $terminal, float $openingBalance, ?string $notes = null): PosSession
    {
        if ($terminal->currentSession()->exists()) {
            throw PosSessionException::alreadyOpen($terminal->id);
        }

        $sessionNumber = $this->generateSessionNumber($terminal);

        $session = $this->sessionRepository->create([
            'pos_terminal_id' => $terminal->id,
            'admin_user_id' => auth('admin')->id(),
            'session_number' => $sessionNumber,
            'status' => 'open',
            'opening_balance' => $openingBalance,
            'notes' => $notes,
            'opened_at' => now(),
        ]);

        // Find or create cash register for the terminal session
        $register = PosCashRegister::firstOrCreate(
            [
                'pos_terminal_id' => $terminal->id,
                'pos_session_id' => $session->id,
            ],
            [
                'name' => 'Main Register',
                'type' => 'cash',
                'current_balance' => 0,
                'currency' => config('pos.default_currency', 'IRR'),
            ]
        );

        // Record opening cash movement
        if ($openingBalance > 0) {
            $this->cashMovementRepository->create([
                'pos_session_id' => $session->id,
                'pos_cash_register_id' => $register->id,
                'admin_user_id' => auth('admin')->id(),
                'type' => 'opening',
                'amount' => $openingBalance,
                'balance_after' => $openingBalance,
                'reason' => 'Session opening',
            ]);

            $register->update(['current_balance' => $openingBalance]);
        }

        event(new PosSessionOpened($session, auth('admin')->user(), $openingBalance));

        return $session;
    }

    public function closeSession(PosSession $session, float $closingBalance, ?string $notes = null): PosSession
    {
        if ($session->status !== 'open') {
            throw PosSessionException::sessionClosed($session->id);
        }

        $register = $session->cashRegisters()->first();
        $expectedBalance = $register ? $register->current_balance : $session->opening_balance;
        $difference = $closingBalance - $expectedBalance;

        $session->update([
            'status' => 'closing',
            'closing_balance' => $closingBalance,
            'expected_balance' => $expectedBalance,
            'difference' => $difference,
            'notes' => $notes,
        ]);

        // Record closing cash movement
        if ($register) {
            $this->cashMovementRepository->create([
                'pos_session_id' => $session->id,
                'pos_cash_register_id' => $register->id,
                'admin_user_id' => auth('admin')->id(),
                'type' => 'closing',
                'amount' => $closingBalance,
                'balance_after' => $closingBalance,
                'reason' => 'Session closing',
            ]);

            $register->update([
                'current_balance' => $closingBalance,
                'is_active' => false,
            ]);
        }

        $session->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        event(new PosSessionClosed($session, auth('admin')->user(), $closingBalance, $difference));

        return $session;
    }

    public function suspendSession(PosSession $session): PosSession
    {
        $session->update(['status' => 'suspended']);
        event(new PosSessionSuspended($session, auth('admin')->user()));

        return $session;
    }

    public function getCurrentSession(PosTerminal $terminal): ?PosSession
    {
        return $terminal->currentSession;
    }

    protected function generateSessionNumber(PosTerminal $terminal): string
    {
        $prefix = 'POS';
        $date = now()->format('Ymd');
        $seq = PosSession::where('pos_terminal_id', $terminal->id)
            ->whereDate('created_at', today())
            ->count() + 1;

        return sprintf('%s-%s-%s-%03d', $prefix, $terminal->code, $date, $seq);
    }
}
