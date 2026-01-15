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
        //Climate
        Schema::create("weather_logs", function (Blueprint $table) {
            $table->id();
            $table->foreignId("farm_profile_id")->constrained()->onDelete("cascade");
            $table->decimal("temp_avg", 5, 2)->nullable;
            $table->integer("humidity")->nullable;
            $table->string("weather_condition", )->nullable();
            $table->date("forecast_date");
            $table->timestamps();
        });

        //Moon phases
        Schema::create("moon_phases", function (Blueprint $table) {
            $table->id();
            $table->date("date")->unique();
            $table->string("phase_name");
            $table->text("farming_tip")->nullable();
        });

        //Chat IA - Historial
        Schema::create("ai_chats", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->string("title")->default("Nueva Consulta");
            $table->timestamps();
        });

        //Chat IA - Mensajes
        Schema::create("ai_messages", function (Blueprint $table) {
            $table->id();
            $table->foreignId("ai_chat_id")->constrained('ai_chats')->onDelete("cascade");
            $table->enum("role", ["user", "assistant"]);
            $table->text("content");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_farming');
    }
};
