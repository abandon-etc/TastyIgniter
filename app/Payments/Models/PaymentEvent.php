<?php

declare(strict_types=1);

namespace App\Payments\Models;

use App\Payments\Casts\UtcDateTime;
use App\Payments\EventProcessingStatus;
use App\Payments\Models\Concerns\ServiceWrites;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class PaymentEvent extends Model
{
    use ServiceWrites;

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
            if (!EventProcessingStatus::isValid($event->processing_status)
                || trim((string) $event->gateway_code) === ''
                || trim((string) $event->external_event_id) === ''
                || trim((string) $event->event_type) === ''
            ) {
                throw ValidationException::withMessages([
                    'payment_event' => 'A payment event carries a gateway code, a provider event id, an event type, and a valid processing status.',
                ]);
            }
        });
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }
}
