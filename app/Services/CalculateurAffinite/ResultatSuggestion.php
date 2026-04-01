<?php

namespace App\Services\CalculateurAffinite;

use App\Models\Livre;

/**
 * Objet de transfert de données (DTO) représentant
 * un livre suggéré enrichi de ses métriques de score.
 */
final class ResultatSuggestion
{
    public function __construct(
        /** Le livre suggéré */
        public readonly Livre $livre,

        /**
         * Score issu de la comparaison directe entre les tags d'ambiance
         * du livre et les préférences de l'utilisateur courant.
         * Plage : 0.0 – 1.0
         */
        public readonly float $scoreContenu,

        /**
         * Bonus accordé grâce au filtrage collaboratif :
         * popularité du livre chez des utilisateurs au profil similaire.
         * Plage : 0.0 – 1.0
         */
        public readonly float $bonusCollaboratif,

        /**
         * Score final = scoreContenu + bonusCollaboratif (normalisé 0.0 – 2.0).
         * Un score plus élevé signifie une meilleure adéquation.
         */
        public readonly float $scoreFinal,

        /**
         * Nombre d'utilisateurs similaires ayant interagi avec ce livre
         * (utilisé pour la transparence et le débogage).
         */
        public readonly int $nombreUtilisateursSimilaires,
    ) {}

    /**
     * Représentation lisible pour le débogage.
     */
    public function __toString(): string
    {
        return sprintf(
            '[%s] contenu=%.2f | collaboratif=%.2f | final=%.2f | similarités=%d',
            $this->livre->titre,
            $this->scoreContenu,
            $this->bonusCollaboratif,
            $this->scoreFinal,
            $this->nombreUtilisateursSimilaires
        );
    }
}
