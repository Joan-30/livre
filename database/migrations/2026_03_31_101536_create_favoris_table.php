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
        Schema::create('favoris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->foreignId('livre_id')->constrained('livres')->onDelete('cascade');
            $table->timestamps();

            // S'assurer qu'un utilisateur ne met en favori un livre qu'une seule fois
            $table->unique(['utilisateur_id', 'livre_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favoris');
    }
};
