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
        Schema::table('farm_profiles', function (Blueprint $table) {
            // Usamos 'nullable' porque son opcionales
            $table->string('address')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            // Si no tenías description:
            // $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farm_profiles', function (Blueprint $table) {
            //
        });
    }
};
