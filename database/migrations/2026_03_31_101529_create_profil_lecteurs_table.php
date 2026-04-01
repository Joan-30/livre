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
        Schema::create('profil_lecteurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->json('preferences_ambiance')->nullable(); // ex: ["sombre", "joyeux", "épique"]
            $table->string('niveau_complexite')->nullable(); // ex: facile, moyen, difficile
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profil_lecteurs');
    }
};
