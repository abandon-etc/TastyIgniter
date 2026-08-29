<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The three shared payment ledgers proposed in
// BIRTHDAY_PAYMENT_ARCHITECTURE_DESIGN.md §8 and planned in
// PAYMENT_WORKSTREAM_PLAN.md §1. Additive only, and deliberately
// self-contained: the only foreign keys point at payment_transactions
// itself, so this migration is safe in the root pass, which runs before
// every extension (the constraint PR #124 established). The payable is a
// polymorphic (payable_type, payable_id) pair by design — no cross-table
// constraint.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->bigIncrements('payment_transaction_id');
            $table->uuid('public_id')->unique('payment_transactions_public_id_unique');
            $table->string('payable_type', 32);
            $table->unsignedBigInteger('payable_id');
            $table->string('gateway_code', 64);
            $table->string('external_payment_id', 191)->nullable();
            $table->string('idempotency_key', 191);
            $table->unsignedBigInteger('amount_minor');
            $table->char('currency', 3);
            $table->string('status', 32);
            $table->dateTime('authorized_at')->nullable();
            $table->dateTime('succeeded_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->dateTime('refunded_at')->nullable();
            $table->unsignedBigInteger('refunded_amount_minor')->default(0);
            $table->json('safe_metadata')->nullable();
            $table->timestamps();

            $table->unique('idempotency_key', 'payment_transactions_idempotency_unique');
            $table->unique(['gateway_code', 'external_payment_id'], 'payment_transactions_external_unique');
            $table->index(['payable_type', 'payable_id'], 'payment_transactions_payable_index');
            $table->index('status', 'payment_transactions_status_index');
        });

        Schema::create('payment_events', function (Blueprint $table): void {
            $table->bigIncrements('payment_event_id');
            $table->string('gateway_code', 64);
            $table->string('external_event_id', 191);
            $table->unsignedBigInteger('payment_transaction_id')->nullable();
            $table->string('event_type', 64);
            $table->boolean('signature_valid');
            $table->json('safe_summary')->nullable();
            $table->string('processing_status', 32);
            $table->unsignedInteger('attempts')->default(0);
            $table->dateTime('processed_at')->nullable();
            $table->string('safe_error', 191)->nullable();
            $table->timestamps();

            $table->foreign('payment_transaction_id', 'payment_events_transaction_foreign')
                ->references('payment_transaction_id')->on('payment_transactions')->restrictOnDelete();
            $table->unique(['gateway_code', 'external_event_id'], 'payment_events_provider_event_unique');
            $table->index('processing_status', 'payment_events_processing_index');
        });

        Schema::create('payment_refunds', function (Blueprint $table): void {
            $table->bigIncrements('payment_refund_id');
            $table->unsignedBigInteger('payment_transaction_id');
            $table->string('gateway_code', 64);
            $table->string('external_refund_id', 191)->nullable();
            $table->unsignedBigInteger('amount_minor');
            $table->char('currency', 3);
            $table->string('status', 32);
            $table->string('safe_reason', 191)->nullable();
            $table->dateTime('succeeded_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->timestamps();

            $table->foreign('payment_transaction_id', 'payment_refunds_transaction_foreign')
                ->references('payment_transaction_id')->on('payment_transactions')->restrictOnDelete();
            $table->unique(['gateway_code', 'external_refund_id'], 'payment_refunds_provider_refund_unique');
            $table->index(['payment_transaction_id', 'status'], 'payment_refunds_transaction_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_refunds');
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('payment_transactions');
    }
};
