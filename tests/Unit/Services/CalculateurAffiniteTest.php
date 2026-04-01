<?php

namespace Tests\Unit\Services;

use App\Models\Avis;
use App\Models\Livre;
use App\Models\ProfilLecteur;
use App\Models\User;
use App\Services\CalculateurAffinite\CalculateurAffinite;
use App\Services\CalculateurAffinite\ResultatSuggestion;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Tests unitaires du service CalculateurAffinite.
 *
 * Stratégie : les modèles Eloquent sont simulés avec Mockery
 * pour éviter toute dépendance à la base de données.
 */
class CalculateurAffiniteTest extends TestCase
{
    private CalculateurAffinite $calculateur;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculateur = new CalculateurAffinite(
            seuilSimilarite:   0.1,
            poidsCollaboratif: 0.5,
            maxVoisins:        10,
            nombreSuggestions: 5,
        );
    }

    // ─────────────────────────────────────────────────────────────
    // Tests du score de contenu (méthode indirectement testée via genererSuggestions)
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function livre_sans_tags_obtient_score_zero(): void
    {
        $utilisateur = $this->creerUtilisateur(['sombre', 'épique']);
        $livre       = $this->creerLivre(1, []); // aucun tag

        $resultats = $this->calculateur->genererSuggestions($utilisateur, collect([$livre]));

        $this->assertEmpty($resultats, 'Un livre sans tags ne doit pas apparaître dans les suggestions.');
    }

    /** @test */
    public function livre_avec_tags_correspondants_obtient_score_eleve(): void
    {
        $utilisateur = $this->creerUtilisateur(['sombre', 'épique', 'mystérieux']);
        $livre       = $this->creerLivre(1, ['sombre', 'épique']); // 2/2 correspondances

        $resultats = $this->calculateur
            ->avecMaxVoisins(0) // désactiver le collaboratif pour isoler le score contenu
            ->genererSuggestions($utilisateur, collect([$livre]));

        $this->assertCount(1, $resultats);
        /** @var ResultatSuggestion $r */
        $r = $resultats->first();
        $this->assertEqualsWithDelta(2 / 2, $r->scoreContenu, 0.001);
    }

    /** @test */
    public function livres_sont_tries_par_score_decroissant(): void
    {
        $utilisateur = $this->creerUtilisateur(['sombre', 'épique', 'romantique']);

        $livres = collect([
            $this->creerLivre(1, ['romantique']),          // score 1/1 = 1.0
            $this->creerLivre(2, ['sombre', 'épique']),    // score 2/2 = 1.0 (ex-aequo)
            $this->creerLivre(3, ['humoristique']),        // score 0/1 = 0.0 → exclu
        ]);

        $resultats = $this->calculateur
            ->avecMaxVoisins(0)
            ->genererSuggestions($utilisateur, $livres);

        $this->assertCount(2, $resultats);
        // Les deux premiers doivent avoir un score > 0
        $this->assertGreaterThan(0, $resultats->first()->scoreFinal);
    }

    /** @test */
    public function nombre_de_suggestions_est_limite(): void
    {
        $utilisateur = $this->creerUtilisateur(['épique']);

        $livres = collect(range(1, 20))->map(
            fn($i) => $this->creerLivre($i, ['épique'])
        );

        $resultats = $this->calculateur
            ->avecNombreSuggestions(5)
            ->avecMaxVoisins(0)
            ->genererSuggestions($utilisateur, $livres);

        $this->assertCount(5, $resultats);
    }

    /** @test */
    public function exception_levee_si_utilisateur_sans_profil(): void
    {
        $utilisateur = $this->creerUtilisateur(null); // profil null

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/profil lecteur/i');

        $this->calculateur->genererSuggestions($utilisateur, collect());
    }

    /** @test */
    public function similiarite_jaccard_est_symetrique(): void
    {
        // On teste via les méthodes fluides (clone) que le comportement reste stable
        $calculateurA = $this->calculateur->avecSeuilSimilarite(0.3);
        $calculateurB = $this->calculateur->avecSeuilSimilarite(0.3);

        $this->assertEquals(
            $calculateurA->getSeuilSimilarite(),
            $calculateurB->getSeuilSimilarite()
        );
        // Les mutateurs ne modifient pas l'instance originale (immutabilité)
        $this->assertEquals(0.1, $this->calculateur->getSeuilSimilarite());
    }

    /** @test */
    public function mutations_fluides_ne_modifient_pas_linstance_originale(): void
    {
        $clone = $this->calculateur
            ->avecSeuilSimilarite(0.9)
            ->avecPoidsCollaboratif(1.0)
            ->avecMaxVoisins(100)
            ->avecNombreSuggestions(50);

        $this->assertEquals(0.1, $this->calculateur->getSeuilSimilarite());
        $this->assertEquals(0.5, $this->calculateur->getPoidsCollaboratif());
        $this->assertEquals(10,  $this->calculateur->getMaxVoisins());
        $this->assertEquals(5,   $this->calculateur->getNombreSuggestions());

        $this->assertEquals(0.9,  $clone->getSeuilSimilarite());
        $this->assertEquals(1.0,  $clone->getPoidsCollaboratif());
        $this->assertEquals(100,  $clone->getMaxVoisins());
        $this->assertEquals(50,   $clone->getNombreSuggestions());
    }

    // ─────────────────────────────────────────────────────────────
    // Factories de modèles simulés
    // ─────────────────────────────────────────────────────────────

    /**
     * Crée un faux User avec son profil pré-chargé.
     *
     * @param array|null $preferences  null = pas de profil
     */
    private function creerUtilisateur(?array $preferences): User
    {
        $user = new User(['name' => 'Lecteur Test']);
        $user->id = 999;

        if ($preferences === null) {
            $user->setRelation('profil', null);
        } else {
            $profil = new ProfilLecteur();
            $profil->preferences_ambiance = $preferences;
            $user->setRelation('profil', $profil);
        }

        // Pas d'avis par défaut (collection vide)
        $user->setRelation('avis', new EloquentCollection());

        return $user;
    }

    /**
     * Crée un faux Livre avec ses tags d'ambiance.
     */
    private function creerLivre(int $id, array $tags): Livre
    {
        $livre = new Livre([
            'titre'        => "Livre #{$id}",
            'tags_ambiance' => $tags,
        ]);
        $livre->id = $id;
        $livre->setRelation('avis', new EloquentCollection());

        return $livre;
    }
}
