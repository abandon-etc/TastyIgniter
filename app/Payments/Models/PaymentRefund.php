<?php

declare(strict_types=1);

namespace App\Payments\Models;

use App\Payments\Casts\UtcDateTime;
use App\Payments\Models\Concerns\ServiceWrites;
use App\Payments\RefundStatus;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class PaymentRefund extends Model
{
    use ServiceWrites;

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
            if (!RefundStatus::isValid($refund->status)
                || $refund->status !== RefundStatus::PENDING
                || !is_int($refund->amount_minor)
                || $refund->amount_minor <= 0
                || strlen((string) $refund->currency) !== 3
                || trim((string) $refund->gateway_code) === ''
            ) {
                throw ValidationException::withMessages([
                    'payment_refund' => 'A refund is created pending, with a positive integer minor amount, an ISO currency, and a gateway code.',
                ]);
            }
        });
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }
}
