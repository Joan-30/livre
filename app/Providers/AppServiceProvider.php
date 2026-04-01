<?php

namespace App\Providers;

use App\Services\CalculateurAffinite\CalculateurAffinite;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Enregistrement de CalculateurAffinite comme singleton.
        // Les paramètres par défaut peuvent être surchargés via config/affinite.php
        // ou directement à l'injection en utilisant les méthodes fluides du service.
        $this->app->singleton(CalculateurAffinite::class, function () {
            return new CalculateurAffinite(
                seuilSimilarite:   config('affinite.seuil_similarite', 0.2),
                poidsCollaboratif: config('affinite.poids_collaboratif', 0.5),
                maxVoisins:        config('affinite.max_voisins', 50),
                nombreSuggestions: config('affinite.nombre_suggestions', 10),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}
