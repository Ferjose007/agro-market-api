<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_profile_id')->constrained()->onDelete('cascade');

            // El puente con Categorías (¡Esto arregla tu error!)
            $table->foreignId('category_id')->constrained();

            $table->string('name');
            $table->string('description')->nullable();

            // 👇 CAMBIO SUGERIDO: Usar los mismos nombres que en Vue
            $table->decimal('price_per_unit', 10, 2); // Antes: price
            $table->string('unit');                   // Antes: unit_type

            $table->integer('stock_quantity');
            $table->date('harvest_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
