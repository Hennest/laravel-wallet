<?php

declare(strict_types=1);

use Hennest\Wallet\Enums\TransactionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table): void {
            $table->ulid('id');

            $table->morphs('payable');
            $table->foreignUlid('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50)->index();
            $table->string('amount', 64);
            $table->string('status', 50)->default(TransactionStatus::Pending)->index();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['payable_type', 'payable_id'], 'payable_type_payable_id_ind');
            $table->index(['payable_type', 'payable_id', 'type'], 'payable_type_ind');
            $table->index(['payable_type', 'payable_id', 'status'], 'payable_status_ind');
            $table->index(['payable_type', 'payable_id', 'type', 'status'], 'payable_type_status_ind');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
