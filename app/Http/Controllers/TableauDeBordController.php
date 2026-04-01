<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CalculateurAffinite\CalculateurAffinite;
use App\Services\CalculateurAffinite\ResultatSuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TableauDeBordController extends Controller
{
    public function __construct(
        private readonly CalculateurAffinite $calculateur
    ) {}

    /**
     * Affiche le tableau de bord avec les suggestions personnalisées.
     * Le service calcule un score hybride (contenu + collaboratif) pour chaque livre.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        /** @var User $utilisateur */
        $utilisateur = Auth::user();

        // Reconfiguration à la volée si des paramètres sont passés en query string
        // ex: /tableau-de-bord?suggestions=6&poids=0.8
        $calculateur = $this->calculateur
            ->avecNombreSuggestions((int) $request->query('suggestions', 10))
            ->avecPoidsCollaboratif((float) $request->query('poids', config('affinite.poids_collaboratif', 0.5)));

        // Tentative de génération des suggestions réelles
        // Si l'utilisateur n'a pas de profil, on retourne une liste vide
        try {
            $suggestions = $calculateur->genererSuggestions($utilisateur);
        } catch (\InvalidArgumentException $e) {
            $suggestions = collect();
        }

        // Transformation des ResultatSuggestion en tableaux compatibles avec la vue
        $livres = $suggestions->map(fn(ResultatSuggestion $r) => $this->formaterResultat($r));

        return view('tableau_de_bord', [
            'livres'           => $livres,
            'utilisateur'      => $utilisateur,
            'aProfilComplet'   => $utilisateur->profil !== null
                                  && !empty($utilisateur->profil->preferences_ambiance),
        ]);
    }

    /**
     * Transforme un ResultatSuggestion en tableau exploitable par la vue Blade.
     * Le score final (0–2) est converti en pourcentage d'affinité (0–100 %).
     */
    private function formaterResultat(ResultatSuggestion $r): array
    {
        $livre = $r->livre;

        // scoreContenu ∈ [0,1], bonusCollaboratif ∈ [0,0.5] → scoreFinal ∈ [0,1.5]
        // On normalise par le maximum théorique (1 + poidsCollaboratif)
        $maxTheorique = 1 + config('affinite.poids_collaboratif', 0.5);
        $affinite     = (int) round(min($r->scoreFinal / $maxTheorique, 1.0) * 100);

        return [
            'id'                      => $livre->id,
            'titre'                   => $livre->titre,
            'auteur'                  => $livre->auteur?->nom ?? 'Auteur inconnu',
            'description'             => $livre->description ?? '',
            'categorie'               => $livre->categorie?->nom ?? 'Non classé',
            'tags_ambiance'           => $livre->tags_ambiance ?? [],
            'image'                   => null, // à enrichir si une colonne image est ajoutée
            'affinite'                => $affinite,
            'score_contenu'           => round($r->scoreContenu * 100),           // 0-100%
            'bonus_collaboratif'      => round($r->bonusCollaboratif * 100),      // 0-50%
            'utilisateurs_similaires' => $r->nombreUtilisateursSimilaires,
        ];
    }
}
