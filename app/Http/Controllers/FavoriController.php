<?php

namespace App\Http\Controllers;

use App\Models\Livre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriController extends Controller
{
    /**
     * Affiche la liste complète des livres mis en favoris (souhaits).
     */
    public function index()
    {
        $utilisateur = Auth::user();
        $favoris = $utilisateur->favoris()->with(['auteur', 'categorie'])->get();

        return view('favoris.index', compact('favoris', 'utilisateur'));
    }

    /**
     * Bascule l'état favori d'un livre pour l'utilisateur connecté (toggle).
     * Retourne une réponse JSON consommée par le composant btn-favori en JavaScript.
     *
     * @return JsonResponse  { "estFavori": bool, "message": string }
     */
    public function basculer(Request $request, Livre $livre): JsonResponse
    {
        $utilisateur = Auth::user();

        // toggleFavori : attache si absent, détache si présent
        $resultat = $utilisateur->favoris()->toggle($livre->id);

        $estFavori = !empty($resultat['attached']);

        return response()->json([
            'estFavori' => $estFavori,
            'message'   => $estFavori
                ? "« {$livre->titre} » ajouté à vos favoris."
                : "« {$livre->titre} » retiré de vos favoris.",
            'livreId'   => $livre->id,
        ]);
    }

    /**
     * Retourne les IDs des livres mis en favori par l'utilisateur connecté.
     * Utilisé au chargement de la page pour initialiser l'état des boutons.
     *
     * @return JsonResponse  { "favoris": int[] }
     */
    public function liste(): JsonResponse
    {
        $ids = Auth::user()
            ->favoris()
            ->pluck('livres.id')
            ->toArray();

        return response()->json(['favoris' => $ids]);
    }
}
