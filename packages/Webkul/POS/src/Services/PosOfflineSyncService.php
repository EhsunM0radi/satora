<?php

namespace Webkul\POS\Services;

use Illuminate\Support\Facades\DB;
use Webkul\POS\Events\PosOfflineConflictDetected;
use Webkul\POS\Events\PosOfflineTransactionSynced;
use Webkul\POS\Exceptions\PosInventoryException;
use Webkul\POS\Models\PosOfflineQueue;
use Webkul\POS\Models\PosOrder;
use Webkul\POS\Models\PosProductCache;
use Webkul\POS\Models\PosSession;
use Webkul\POS\Models\PosTerminal;
use Webkul\POS\Repositories\PosOfflineQueueRepository;

class PosOfflineSyncService
{
    public function __construct(
        protected PosOfflineQueueRepository $queueRepository,
    ) {}

    public function pushTransaction(PosTerminal $terminal, string $action, array $payload, string $localId): PosOfflineQueue
    {
        $maxQueue = config('pos.offline.max_queue_size', 1000);

        // Prevent unbounded growth
        $count = PosOfflineQueue::where('pos_terminal_id', $terminal->id)
            ->where('status', 'pending')
            ->count();

        if ($count >= $maxQueue) {
            throw new \RuntimeException("Offline queue full ({$count} pending). Sync required.");
        }

        return $this->queueRepository->create([
            'pos_terminal_id' => $terminal->id,
            'action' => $action,
            'payload' => $payload,
            'status' => 'pending',
            'local_id' => $localId,
            'attempts' => 0,
        ]);
    }

    public function syncBatch(PosTerminal $terminal): array
    {
        $results = ['synced' => 0, 'failed' => 0, 'conflicts' => 0];

        $pending = $this->queueRepository->findWhere([
            'pos_terminal_id' => $terminal->id,
            'status' => 'pending',
        ])->sortBy('created_at');

        foreach ($pending as $item) {
            try {
                $this->processQueueItem($item);
                $results['synced']++;
            } catch (PosInventoryException $e) {
                // Conflict detected — mark and continue
                $item->update([
                    'status' => 'conflict',
                    'last_error' => $e->getMessage(),
                    'synced_at' => now(),
                ]);
                $results['conflicts']++;

                event(new PosOfflineConflictDetected($item, [
                    'error' => $e->getMessage(),
                    'payload' => $item->payload,
                ]));
            } catch (\Exception $e) {
                $item->update([
                    'status' => 'failed',
                    'attempts' => $item->attempts + 1,
                    'last_error' => $e->getMessage(),
                ]);
                $results['failed']++;
            }
        }

        return $results;
    }

    public function pullData(PosTerminal $terminal): array
    {
        return [
            'products' => $this->syncProductCache($terminal),
            'customers' => $this->syncCustomerCache($terminal),
            'settings' => $this->getTerminalSettings($terminal),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    protected function processQueueItem(PosOfflineQueue $item): void
    {
        DB::transaction(function () use ($item) {
            $item->update(['status' => 'processing']);

            $payload = $item->payload;
            $serverId = null;

            switch ($item->action) {
                case 'create_order':
                    $session = PosSession::findOrFail($payload['session_id']);
                    $checkout = app(PosCheckoutService::class);
                    $order = $checkout->createOrder($session, $payload['items'], $payload['options'] ?? []);
                    $serverId = $order->id;

                    // Process payment if included
                    if (! empty($payload['payments'])) {
                        $paymentService = app(PosPaymentService::class);
                        $paymentService->processSplitPayment($order, $payload['payments']);
                    }
                    break;

                case 'create_payment':
                    $order = PosOrder::findOrFail($payload['order_id']);
                    $paymentService = app(PosPaymentService::class);
                    $payment = $paymentService->processPayment(
                        $order,
                        $payload['method'],
                        $payload['amount'],
                        $payload['extra'] ?? []
                    );
                    $serverId = $payment->id;
                    break;

                case 'create_refund':
                    $order = PosOrder::findOrFail($payload['order_id']);
                    $refundService = app(PosRefundService::class);
                    $refund = $refundService->initiateRefund(
                        $order,
                        $payload['items'],
                        $payload['refund_method'] ?? 'original_payment',
                        $payload['reason'] ?? null
                    );
                    $refundService->completeRefund($refund);
                    $serverId = $refund->id;
                    break;

                default:
                    throw new \RuntimeException("Unknown offline action: {$item->action}");
            }

            $item->update([
                'status' => 'completed',
                'server_id' => $serverId,
                'synced_at' => now(),
            ]);

            event(new PosOfflineTransactionSynced($item, $serverId));
        });
    }

    protected function syncProductCache(PosTerminal $terminal): array
    {
        $since = $terminal->last_sync_at ?? now()->subDay();

        $products = \DB::table('product_flat')
            ->where('updated_at', '>', $since)
            ->limit(500)
            ->get()
            ->toArray();

        // Update cache
        foreach ($products as $product) {
            PosProductCache::updateOrCreate(
                [
                    'pos_terminal_id' => $terminal->id,
                    'product_id' => $product->id,
                ],
                [
                    'cached_data' => (array) $product,
                    'last_synced_at' => now(),
                ]
            );
        }

        $terminal->update(['last_sync_at' => now()]);

        return $products;
    }

    protected function syncCustomerCache(PosTerminal $terminal): array
    {
        return \DB::table('customers')
            ->limit(200)
            ->get(['id', 'first_name', 'last_name', 'email', 'phone'])
            ->toArray();
    }

    protected function getTerminalSettings(PosTerminal $terminal): array
    {
        return $terminal->settings ?? [];
    }
}
