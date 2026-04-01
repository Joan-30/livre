<?php

namespace App\Http\Controllers;

use App\Models\ProfilLecteur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfilLecteurController extends Controller
{
    private const TAGS_DISPONIBLES = [
        'sombre', 'épique', 'romantique', 'mystérieux', 'joyeux',
        'philosophique', 'humoristique', 'poétique', 'haletant', 'contemplatif',
    ];

    private const NIVEAUX = ['facile', 'moyen', 'difficile'];

    /**
     * Affiche le formulaire d'édition du profil lecteur.
     */
    public function edit(): View
    {
        $utilisateur = Auth::user()->load('profil');

        return view('profil-lecteur.edit', [
            'utilisateur'    => $utilisateur,
            'profil'         => $utilisateur->profil,
            'tagsDisponibles'=> self::TAGS_DISPONIBLES,
            'niveaux'        => self::NIVEAUX,
        ]);
    }

    /**
     * Sauvegarde (ou crée) le profil lecteur de l'utilisateur connecté.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preferences_ambiance'   => ['nullable', 'array', 'max:5'],
            'preferences_ambiance.*' => ['string', 'in:' . implode(',', self::TAGS_DISPONIBLES)],
            'niveau_complexite'      => ['nullable', 'string', 'in:' . implode(',', self::NIVEAUX)],
        ], [
            'preferences_ambiance.max'    => 'Vous pouvez choisir au maximum 5 tags d\'ambiance.',
            'preferences_ambiance.*.in'   => 'Un ou plusieurs tags sélectionnés ne sont pas valides.',
            'niveau_complexite.in'        => 'Le niveau de complexité sélectionné est invalide.',
        ]);

        ProfilLecteur::updateOrCreate(
            ['utilisateur_id' => Auth::id()],
            [
                'preferences_ambiance' => $validated['preferences_ambiance'] ?? [],
                'niveau_complexite'    => $validated['niveau_complexite'] ?? null,
            ]
        );

        return redirect()
            ->route('tableau-de-bord')
            ->with('succes', 'Votre profil lecteur a été mis à jour. Les recommandations ont été recalculées !');
    }
}
