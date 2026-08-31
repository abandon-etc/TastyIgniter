<?php

declare(strict_types=1);

namespace App\Payments\Models;

use App\Payments\Casts\UtcDateTime;
use App\Payments\Models\Concerns\ServiceWrites;
use App\Payments\Models\Concerns\UtcTimestamps;
use App\Payments\RefundStatus;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class PaymentRefund extends Model
{
    use ServiceWrites;
    use UtcTimestamps;

    protected $table = 'payment_refunds';

    protected $primaryKey = 'payment_refund_id';

    protected $guarded = [];

    protected $attributes = [
        'status' => RefundStatus::PENDING,
    ];

    protected $casts = [
        'payment_transaction_id' => 'integer',
        'amount_minor' => 'integer',
        'succeeded_at' => UtcDateTime::class,
        'failed_at' => UtcDateTime::class,
    ];

    public $timestamps = true;

    protected static function booted(): void
    {
        static::creating(function (self $refund): void {
            // Raw-attribute validation, for the same reason as the
            // transaction model: the integer get-cast would hide a float.
            $raw = $refund->getAttributes();

            if ($refund->status !== RefundStatus::PENDING
                || !is_int($raw['amount_minor'] ?? null)
                || $raw['amount_minor'] <= 0
                || !preg_match('/\A[A-Z]{3}\z/', (string) ($raw['currency'] ?? ''))
                || trim((string) $refund->gateway_code) === ''
                || ($refund->external_refund_id !== null && trim((string) $refund->external_refund_id) === '')
            ) {
                throw ValidationException::withMessages([
                    'payment_refund' => 'A refund is created pending, with a positive integer minor amount, an ISO 4217 currency, a gateway code, and a non-empty provider refund id when one is given.',
                ]);
            }
        });
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }
}
