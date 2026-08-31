<?php

declare(strict_types=1);

namespace App\Payments\Models;

use App\Payments\Casts\UtcDateTime;
use App\Payments\EventProcessingStatus;
use App\Payments\Models\Concerns\ServiceWrites;
use App\Payments\Models\Concerns\UtcTimestamps;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class PaymentEvent extends Model
{
    use ServiceWrites;
    use UtcTimestamps;

    protected $table = 'payment_events';

    protected $primaryKey = 'payment_event_id';

    protected $guarded = [];

    protected $attributes = [
        'processing_status' => EventProcessingStatus::RECEIVED,
        'attempts' => 0,
    ];

    protected $casts = [
        'payment_transaction_id' => 'integer',
        'signature_valid' => 'boolean',
        'safe_summary' => 'array',
        'attempts' => 'integer',
        'processed_at' => UtcDateTime::class,
    ];

    public $timestamps = true;

    protected static function booted(): void
    {
        static::creating(function (self $event): void {
            // Events are born received with zero attempts; every later
            // state is stamped by the ledger's marks under lock.
            if ($event->processing_status !== EventProcessingStatus::RECEIVED
                || (int) $event->attempts !== 0
                || $event->processed_at !== null
                || trim((string) $event->gateway_code) === ''
                || trim((string) $event->external_event_id) === ''
                || trim((string) $event->event_type) === ''
            ) {
                throw ValidationException::withMessages([
                    'payment_event' => 'A payment event is born received with zero attempts, and carries a gateway code, a provider event id, and an event type.',
                ]);
            }
        });
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }
}
