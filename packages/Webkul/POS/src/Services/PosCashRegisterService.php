<?php

namespace Webkul\POS\Services;

use Webkul\POS\Events\PosCashDrawerOpened;
use Webkul\POS\Events\PosCashMovementCreated;
use Webkul\POS\Models\PosCashMovement;
use Webkul\POS\Models\PosCashRegister;
use Webkul\POS\Models\PosPayment;
use Webkul\POS\Repositories\PosCashMovementRepository;
use Webkul\POS\Repositories\PosCashRegisterRepository;

class PosCashRegisterService
{
    public function __construct(
        protected PosCashRegisterRepository $registerRepository,
        protected PosCashMovementRepository $movementRepository,
    ) {}

    public function recordSale(int $registerId, float $amount, PosPayment $payment): PosCashMovement
    {
        $register = $this->registerRepository->find($registerId);
        $newBalance = $register->current_balance + $amount;

        $movement = $this->movementRepository->create([
            'pos_session_id' => $register->pos_session_id,
            'pos_cash_register_id' => $register->id,
            'admin_user_id' => auth('admin')->id(),
            'type' => 'sale',
            'amount' => $amount,
            'balance_after' => $newBalance,
            'reference_type' => 'pos_payment',
            'reference_id' => $payment->id,
            'reason' => 'POS sale payment',
        ]);

        $register->update(['current_balance' => $newBalance]);

        event(new PosCashMovementCreated($movement, $register));

        return $movement;
    }

    public function recordRefund(int $registerId, float $amount): PosCashMovement
    {
        $register = $this->registerRepository->find($registerId);
        $newBalance = $register->current_balance - $amount;

        $movement = $this->movementRepository->create([
            'pos_session_id' => $register->pos_session_id,
            'pos_cash_register_id' => $register->id,
            'admin_user_id' => auth('admin')->id(),
            'type' => 'refund',
            'amount' => $amount,
            'balance_after' => $newBalance,
            'reason' => 'POS refund',
        ]);

        $register->update(['current_balance' => $newBalance]);

        event(new PosCashMovementCreated($movement, $register));

        return $movement;
    }

    public function cashIn(int $registerId, float $amount, string $reason): PosCashMovement
    {
        $register = $this->registerRepository->find($registerId);
        $newBalance = $register->current_balance + $amount;

        $movement = $this->movementRepository->create([
            'pos_session_id' => $register->pos_session_id,
            'pos_cash_register_id' => $register->id,
            'admin_user_id' => auth('admin')->id(),
            'type' => 'cash_in',
            'amount' => $amount,
            'balance_after' => $newBalance,
            'reason' => $reason,
        ]);

        $register->update(['current_balance' => $newBalance]);

        event(new PosCashMovementCreated($movement, $register));

        return $movement;
    }

    public function cashOut(int $registerId, float $amount, string $reason): PosCashMovement
    {
        $register = $this->registerRepository->find($registerId);
        $newBalance = $register->current_balance - $amount;

        $movement = $this->movementRepository->create([
            'pos_session_id' => $register->pos_session_id,
            'pos_cash_register_id' => $register->id,
            'admin_user_id' => auth('admin')->id(),
            'type' => 'cash_out',
            'amount' => $amount,
            'balance_after' => $newBalance,
            'reason' => $reason,
        ]);

        $register->update(['current_balance' => $newBalance]);

        event(new PosCashMovementCreated($movement, $register));

        return $movement;
    }

    public function openDrawer(PosCashRegister $register, ?string $reason = null): void
    {
        Gate::authorize('pos.open_drawer');

        event(new PosCashDrawerOpened($register, auth('admin')->user(), $reason));
    }
}
