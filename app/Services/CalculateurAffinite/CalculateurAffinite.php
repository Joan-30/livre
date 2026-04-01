<?php

namespace App\Services\CalculateurAffinite;

use App\Models\Livre;
use App\Models\ProfilLecteur;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service de recommandation hybride basé sur :
 *  1. Filtrage par contenu   → similarité des tags d'ambiance livre ↔ profil lecteur
 *  2. Filtrage collaboratif  → bonus accordé aux livres plébiscités par des utilisateurs
 *                              ayant un profil d'ambiance similaire à l'utilisateur courant
 *
 * ──────────────────────────────────────────────────────────────────
 * ALGORITHME GLOBAL
 * ──────────────────────────────────────────────────────────────────
 *
 *  scoreContenu(livre, user)       = |tags_livre ∩ préférences_user| / max(|tags_livre|, 1)
 *
 *  similarité(userA, userB)        = |préfs_A ∩ préfs_B| / |préfs_A ∪ préfs_B|  (Jaccard)
 *
 *  popularitéCollaborative(livre)  = Σ (similarité(user, voisin) × noteNormalisée(voisin, livre))
 *                                    ─────────────────────────────────────────────────────────
 *                                    Σ similarité(user, voisin)              (si > 0)
 *
 *  bonusCollaboratif               = popularitéCollaborative × $poidsCollaboratif
 *
 *  scoreFinal                      = scoreContenu + bonusCollaboratif
 * ──────────────────────────────────────────────────────────────────
 */
class CalculateurAffinite
{
    // ─── Paramètres configurables ──────────────────────────────────

    /**
     * Seuil de similarité Jaccard minimum pour qu'un voisin
     * soit pris en compte dans le filtrage collaboratif (0 - 1).
     */
    private float $seuilSimilarite;

    /**
     * Poids appliqué au bonus collaboratif dans le score final.
     * Augmenter cette valeur rend le système plus collaboratif,
     * la diminuer le rend plus basé sur le contenu seul.
     */
    private float $poidsCollaboratif;

    /**
     * Nombre maximum de voisins similaires à considérer.
     */
    private int $maxVoisins;

    /**
     * Nombre de suggestions à retourner.
     */
    private int $nombreSuggestions;

    // ───────────────────────────────────────────────────────────────

    public function __construct(
        float $seuilSimilarite    = 0.2,
        float $poidsCollaboratif  = 0.5,
        int   $maxVoisins         = 50,
        int   $nombreSuggestions  = 10
    ) {
        $this->seuilSimilarite   = $seuilSimilarite;
        $this->poidsCollaboratif = $poidsCollaboratif;
        $this->maxVoisins        = $maxVoisins;
        $this->nombreSuggestions = $nombreSuggestions;
    }

    // ═══════════════════════════════════════════════════════════════
    // POINT D'ENTRÉE PUBLIC
    // ═══════════════════════════════════════════════════════════════

    /**
     * Génère une liste de livres suggérés triés par score d'affinité décroissant.
     *
     * @param  User                       $utilisateur  L'utilisateur pour qui on génère les suggestions
     * @param  Collection<int, Livre>|null $livresPool  Livres candidats (null = tous les livres)
     * @return Collection<int, ResultatSuggestion>      Suggestions triées, score décroissant
     *
     * @throws \InvalidArgumentException Si l'utilisateur n'a pas de profil lecteur
     */
    public function genererSuggestions(User $utilisateur, ?Collection $livresPool = null): Collection
    {
        // 1. Récupération & validation du profil lecteur
        $profil = $utilisateur->profil;
        if ($profil === null) {
            throw new \InvalidArgumentException(
                "L'utilisateur #{$utilisateur->id} ({$utilisateur->name}) n'a pas de profil lecteur. "
                . "Veuillez créer un ProfilLecteur avant d'utiliser ce service."
            );
        }

        $preferencesUtilisateur = array_map(
            'mb_strtolower',
            $profil->preferences_ambiance ?? []
        );

        // Si l'utilisateur n'a aucune préférence, on ne peut pas calculer l'affinité
        if (empty($preferencesUtilisateur)) {
            return collect();
        }

        // 2. Chargement du pool de livres (exclusion de ceux déjà lus / en favoris)
        $livres = $livresPool ?? Livre::with(['auteur', 'categorie', 'avis'])->get();

        // 3. Étape collaborative : calcul des bonus par livre
        $bonusCollaboratifs = $this->calculerBonusCollaboratifs(
            $utilisateur,
            $preferencesUtilisateur,
            $livres
        );

        // 4. Calcul du score final pour chaque livre
        $resultats = $livres->map(function (Livre $livre) use ($preferencesUtilisateur, $bonusCollaboratifs) {
            $scoreContenu      = $this->calculerScoreContenu($livre, $preferencesUtilisateur);
            $bonusData         = $bonusCollaboratifs[$livre->id] ?? ['bonus' => 0.0, 'voisins' => 0];
            $bonusCollaboratif = $bonusData['bonus'];
            $scoreFinal        = $scoreContenu + $bonusCollaboratif;

            return new ResultatSuggestion(
                livre:                    $livre,
                scoreContenu:             $scoreContenu,
                bonusCollaboratif:        $bonusCollaboratif,
                scoreFinal:               $scoreFinal,
                nombreUtilisateursSimilaires: $bonusData['voisins'],
            );
        });

        // 5. Tri décroissant + limitation au nombre de suggestions demandé
        return $resultats
            ->filter(fn(ResultatSuggestion $r) => $r->scoreFinal > 0)
            ->sortByDesc(fn(ResultatSuggestion $r) => $r->scoreFinal)
            ->values()
            ->take($this->nombreSuggestions);
    }

    // ═══════════════════════════════════════════════════════════════
    // CALCUL DU SCORE DE CONTENU
    // ═══════════════════════════════════════════════════════════════

    /**
     * Mesure la correspondance entre les tags d'ambiance d'un livre
     * et les préférences de l'utilisateur courant.
     *
     * Formule :  |tags_livre ∩ préférences_user| / max(|tags_livre|, 1)
     *
     * Exemples :
     *   livre.tags = ["sombre", "épique", "mystérieux"]  user.prefs = ["sombre", "épique"]
     *   → scoreContenu = 2/3 ≈ 0.67
     *
     * @param  Livre $livre
     * @param  array $preferencesUtilisateur  Tags en minuscules
     * @return float Score entre 0.0 et 1.0
     */
    private function calculerScoreContenu(Livre $livre, array $preferencesUtilisateur): float
    {
        $tagsLivre = array_map('mb_strtolower', $livre->tags_ambiance ?? []);

        if (empty($tagsLivre)) {
            return 0.0;
        }

        $intersection = count(array_intersect($tagsLivre, $preferencesUtilisateur));

        return $intersection / max(count($tagsLivre), 1);
    }

    // ═══════════════════════════════════════════════════════════════
    // FILTRAGE COLLABORATIF
    // ═══════════════════════════════════════════════════════════════

    /**
     * Calcule, pour chaque livre du pool, un bonus collaboratif basé sur
     * les notes pondérées des utilisateurs voisins (profil similaire).
     *
     * Étapes :
     *  a) Charger tous les profils lecteurs (sauf celui de l'utilisateur courant)
     *  b) Calculer la similarité Jaccard entre chaque profil et l'utilisateur courant
     *  c) Garder les $maxVoisins voisins les plus similaires (au-dessus du seuil)
     *  d) Pour chaque livre, calculer la popularité collaborative pondérée
     *
     * @param  User       $utilisateur
     * @param  array      $preferencesUtilisateur  Tags en minuscules
     * @param  Collection $livres
     * @return array<int, array{bonus: float, voisins: int}>
     *         Clé = livre_id, valeur = ['bonus' => float, 'voisins' => int]
     */
    private function calculerBonusCollaboratifs(
        User       $utilisateur,
        array      $preferencesUtilisateur,
        Collection $livres
    ): array {
        // a) Chargement des voisins potentiels avec leur profil + avis
        $voisinsPotentiels = User::with(['profil', 'avis'])
            ->where('id', '!=', $utilisateur->id)
            ->whereHas('profil', fn($q) => $q->whereNotNull('preferences_ambiance'))
            ->get();

        if ($voisinsPotentiels->isEmpty()) {
            return [];
        }

        // b) Calcul de similarité Jaccard pour chaque voisin
        $voisinsScores = $voisinsPotentiels
            ->map(function (User $voisin) use ($preferencesUtilisateur) {
                $prefsVoisin = array_map(
                    'mb_strtolower',
                    $voisin->profil->preferences_ambiance ?? []
                );
                $similarite = $this->calculerSimilariteJaccard($preferencesUtilisateur, $prefsVoisin);

                return [
                    'utilisateur' => $voisin,
                    'similarite'  => $similarite,
                ];
            })
            ->filter(fn($v) => $v['similarite'] >= $this->seuilSimilarite)
            ->sortByDesc('similarite')
            ->take($this->maxVoisins)
            ->values();

        if ($voisinsScores->isEmpty()) {
            return [];
        }

        // c) Construction du bonus collaboratif par livre
        // Index des livres par id pour accès O(1)
        $livresParId = $livres->keyBy('id');
        $bonusParLivre = [];

        $sommeSimilarites = $voisinsScores->sum('similarite');

        foreach ($livresParId as $livreId => $livre) {
            $sommeNotesPonderees = 0.0;
            $nbVoisinsAvecAvis   = 0;

            foreach ($voisinsScores as $voisinData) {
                /** @var User $voisin */
                $voisin     = $voisinData['utilisateur'];
                $similarite = $voisinData['similarite'];

                // Cherche si ce voisin a laissé un avis sur ce livre
                $avis = $voisin->avis->firstWhere('livre_id', $livreId);

                if ($avis !== null) {
                    // Normalisation de la note : de l'échelle [1-5] vers [0-1]
                    $noteNormalisee       = ($avis->note - 1) / 4.0;
                    $sommeNotesPonderees += $similarite * $noteNormalisee;
                    $nbVoisinsAvecAvis++;
                } elseif ($this->estEnFavori($voisin, $livreId)) {
                    // Bonus si le livre est en favori chez ce voisin (équivalent note 4/5)
                    $sommeNotesPonderees += $similarite * 0.75;
                    $nbVoisinsAvecAvis++;
                }
            }

            if ($nbVoisinsAvecAvis > 0 && $sommeSimilarites > 0) {
                // Popularité collaborative normalisée par la somme des similarités
                $popularite = $sommeNotesPonderees / $sommeSimilarites;
                $bonus      = $popularite * $this->poidsCollaboratif;
            } else {
                $bonus = 0.0;
            }

            $bonusParLivre[$livreId] = [
                'bonus'   => round($bonus, 4),
                'voisins' => $nbVoisinsAvecAvis,
            ];
        }

        return $bonusParLivre;
    }

    // ═══════════════════════════════════════════════════════════════
    // MESURES DE SIMILARITÉ
    // ═══════════════════════════════════════════════════════════════

    /**
     * Calcule la similarité de Jaccard entre deux ensembles de tags.
     *
     * Jaccard(A, B) = |A ∩ B| / |A ∪ B|
     *
     * Propriétés :
     *  - Retourne 0.0 si les deux ensembles sont vides
     *  - Retourne 1.0 si les deux ensembles sont identiques
     *  - Symétrique : Jaccard(A,B) = Jaccard(B,A)
     *
     * @param  array $tagsA  Tags en minuscules
     * @param  array $tagsB  Tags en minuscules
     * @return float Similarité entre 0.0 et 1.0
     */
    private function calculerSimilariteJaccard(array $tagsA, array $tagsB): float
    {
        if (empty($tagsA) && empty($tagsB)) {
            return 0.0;
        }

        $intersection = count(array_intersect($tagsA, $tagsB));
        $union        = count(array_unique(array_merge($tagsA, $tagsB)));

        return $union > 0 ? $intersection / $union : 0.0;
    }

    // ═══════════════════════════════════════════════════════════════
    // UTILITAIRES
    // ═══════════════════════════════════════════════════════════════

    /**
     * Vérifie si un utilisateur a mis un livre en favori via sa relation chargée.
     * Si la relation 'favoris' n'est pas encore chargée, effectue une vérification DB.
     *
     * @param  User $utilisateur
     * @param  int  $livreId
     * @return bool
     */
    private function estEnFavori(User $utilisateur, int $livreId): bool
    {
        if ($utilisateur->relationLoaded('favoris')) {
            return $utilisateur->favoris->contains('id', $livreId);
        }

        return DB::table('favoris')
            ->where('utilisateur_id', $utilisateur->id)
            ->where('livre_id', $livreId)
            ->exists();
    }

    // ─── Accesseurs pour les paramètres ────────────────────────────

    public function getSeuilSimilarite(): float   { return $this->seuilSimilarite; }
    public function getPoidsCollaboratif(): float  { return $this->poidsCollaboratif; }
    public function getMaxVoisins(): int           { return $this->maxVoisins; }
    public function getNombreSuggestions(): int    { return $this->nombreSuggestions; }

    // ─── Mutateurs fluides pour reconfigurer à la volée ────────────

    public function avecSeuilSimilarite(float $seuil): static
    {
        $clone = clone $this;
        $clone->seuilSimilarite = max(0.0, min(1.0, $seuil));
        return $clone;
    }

    public function avecPoidsCollaboratif(float $poids): static
    {
        $clone = clone $this;
        $clone->poidsCollaboratif = max(0.0, $poids);
        return $clone;
    }

    public function avecMaxVoisins(int $max): static
    {
        $clone = clone $this;
        $clone->maxVoisins = max(1, $max);
        return $clone;
    }

    public function avecNombreSuggestions(int $nombre): static
    {
        $clone = clone $this;
        $clone->nombreSuggestions = max(1, $nombre);
        return $clone;
    }
}
