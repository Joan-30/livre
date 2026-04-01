<?php

namespace App\Http\Controllers;

use App\Models\Avis;
use App\Models\Livre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvisController extends Controller
{
    /**
     * Enregistrer ou mettre à jour un avis sur un livre.
     */
    public function store(Request $request, Livre $livre): JsonResponse
    {
        $validated = $request->validate([
            'note'        => ['required', 'integer', 'min:1', 'max:5'],
            'commentaire' => ['nullable', 'string', 'max:1000'],
        ]);

        $avis = Avis::updateOrCreate(
            [
                'utilisateur_id' => Auth::id(),
                'livre_id'       => $livre->id,
            ],
            [
                'note'        => $validated['note'],
                'commentaire' => $validated['commentaire'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Votre avis a été enregistré avec succès !',
            'avis'    => $avis
        ]);
    }

    /**
     * Récupérer l'avis de l'utilisateur connecté sur un livre spécifique.
     */
    public function show(Livre $livre): JsonResponse
    {
        $avis = Avis::where('utilisateur_id', Auth::id())
                    ->where('livre_id', $livre->id)
                    ->first();

        return response()->json(['avis' => $avis]);
    }
}
