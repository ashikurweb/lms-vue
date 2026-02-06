<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();

            // Basic info
            $table->string('code', 3)->unique();              // USD, EUR, BDT
            $table->string('name');                           // US Dollar
            $table->string('symbol', 10);                     // $

            // Exchange & status
            $table->decimal('exchange_rate', 15, 6)->default(1.000000);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            // Meta
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['code', 'is_active']);
            $table->index(['is_default', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
