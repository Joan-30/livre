<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute le champ tags_ambiance JSON à la table livres.
     * Exemples de tags : ["sombre", "épique", "romantique", "humoristique", "mystérieux", "joyeux", "philosophique"]
     */
    public function up(): void
    {
        Schema::table('livres', function (Blueprint $table) {
            $table->json('tags_ambiance')->nullable()->after('categorie_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('livres', function (Blueprint $table) {
            $table->dropColumn('tags_ambiance');
        });
    }
};
