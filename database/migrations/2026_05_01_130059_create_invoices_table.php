<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->string('invoice_no')->unique(); // Örn: INV-2026-00001
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('TRY');
            $table->enum('status', ['unpaid', 'paid', 'pending_approval', 'rejected'])->default('unpaid');
            $table->timestamp('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // Havale/EFT Detayları
            $table->string('payment_receipt_path')->nullable(); // Dekont yolu
            $table->text('admin_notes')->nullable(); // Red gerekçesi vb.
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
