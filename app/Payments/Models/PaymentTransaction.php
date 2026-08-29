<?php

declare(strict_types=1);

namespace App\Payments\Models;

use App\Payments\Casts\UtcDateTime;
use App\Payments\Models\Concerns\ServiceWrites;
use App\Payments\Models\Concerns\UtcTimestamps;
use App\Payments\PaymentStatus;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentTransaction extends Model
{
    use ServiceWrites;
    use UtcTimestamps;

    protected $table = 'payment_transactions';

    protected $primaryKey = 'payment_transaction_id';

    protected $guarded = [];

    protected $attributes = [
        'status' => PaymentStatus::PENDING,
        'refunded_amount_minor' => 0,
    ];

    protected $casts = [
        'payable_id' => 'integer',
        'amount_minor' => 'integer',
        'refunded_amount_minor' => 'integer',
        'safe_metadata' => 'array',
        'authorized_at' => UtcDateTime::class,
        'succeeded_at' => UtcDateTime::class,
        'failed_at' => UtcDateTime::class,
        'cancelled_at' => UtcDateTime::class,
        'refunded_at' => UtcDateTime::class,
    ];

    public $timestamps = true;

    protected static function booted(): void
    {
        static::creating(function (self $transaction): void {
            $transaction->public_id ??= (string) Str::uuid();

            // Validate the RAW attributes: the integer get-cast would let
            // a float pass is_int while the raw float reaches the driver.
            $raw = $transaction->getAttributes();

            if ($transaction->status !== PaymentStatus::PENDING
                || !is_int($raw['amount_minor'] ?? null)
                || $raw['amount_minor'] <= 0
                || !is_int($raw['payable_id'] ?? null)
                || $raw['payable_id'] <= 0
                || !preg_match('/\A[A-Z]{3}\z/', (string) ($raw['currency'] ?? ''))
                || trim((string) $transaction->idempotency_key) === ''
                || trim((string) $transaction->gateway_code) === ''
                || trim((string) $transaction->payable_type) === ''
            ) {
                throw ValidationException::withMessages([
                    'payment_transaction' => 'A payment transaction is created pending, with a positive integer minor amount, a positive payable id, an ISO 4217 currency, a gateway code, a payable type, and an idempotency key.',
                ]);
            }
        });
    }

    public function events(): HasMany
    {
        return $this->hasMany(PaymentEvent::class, 'payment_transaction_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(PaymentRefund::class, 'payment_transaction_id');
    }
}
