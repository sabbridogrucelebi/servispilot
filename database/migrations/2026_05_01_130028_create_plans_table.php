<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                // Başlangıç, Pro, Enterprise
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);       // Aylık fiyat (TL)
            $table->decimal('yearly_price', 10, 2)->nullable(); // Yıllık fiyat
            $table->string('currency', 3)->default('TRY');
            $table->integer('max_vehicles')->default(5);
            $table->integer('max_users')->default(3);
            $table->json('features')->nullable();   // Ek özellikler listesi
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_popular')->default(false); // Öne çıkan paket
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
