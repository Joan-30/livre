<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CalculateurAffinite\CalculateurAffinite;
use App\Services\CalculateurAffinite\ResultatSuggestion;
use Illuminate\Console\Command;

/**
 * Commande Artisan de test du moteur d'affinité hybride.
 *
 * Usage :
 *   php artisan affinite:tester
 *   php artisan affinite:tester --email=alice@lectura.test
 *   php artisan affinite:tester --email=alice@lectura.test --suggestions=5 --poids=0.8 --seuil=0.15
 */
class TesterAffinite extends Command
{
    protected $signature = 'affinite:tester
                            {--email=alice@lectura.test : Email de l\'utilisateur à analyser}
                            {--suggestions=10          : Nombre de suggestions à retourner}
                            {--poids=0.5               : Poids du bonus collaboratif (0.0–1.0)}
                            {--seuil=0.2               : Seuil de similarité Jaccard minimum}
                            {--voisins=50              : Nombre maximum de voisins similaires}';

    protected $description = 'Teste le moteur d\'affinité hybride et affiche les suggestions pour un utilisateur donné.';

    public function handle(): int
    {
        $email = $this->option('email');

        // ── Récupération de l'utilisateur ─────────────────────────
        $utilisateur = User::with(['profil', 'avis', 'favoris'])
            ->where('email', $email)
            ->first();

        if (!$utilisateur) {
            $this->error("❌  Aucun utilisateur trouvé avec l'email : {$email}");
            $this->line("    Lancez d'abord : php artisan db:seed --class=AffiniteSeeder");
            return self::FAILURE;
        }

        $profil = $utilisateur->profil;
        if (!$profil || empty($profil->preferences_ambiance)) {
            $this->error("❌  L'utilisateur n'a pas de profil lecteur ou ses préférences sont vides.");
            return self::FAILURE;
        }

        // ── Affichage du profil ───────────────────────────────────
        $this->newLine();
        $this->line("<fg=cyan;options=bold>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>");
        $this->line("<fg=cyan;options=bold>  🧠  MOTEUR D'AFFINITÉ HYBRIDE – Lectura Innov</>");
        $this->line("<fg=cyan;options=bold>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>");
        $this->newLine();

        $this->line("  <fg=yellow>👤 Utilisateur :</> <options=bold>{$utilisateur->name}</> ({$utilisateur->email})");
        $this->line("  <fg=yellow>🎨 Tags d'ambiance :</> " . implode(', ', $profil->preferences_ambiance));
        $this->line("  <fg=yellow>📚 Niveau :</>  " . ($profil->niveau_complexite ?? 'Non défini'));
        $this->newLine();

        // ── Configuration du calculateur ──────────────────────────
        $calculateur = (new CalculateurAffinite())
            ->avecNombreSuggestions((int)   $this->option('suggestions'))
            ->avecPoidsCollaboratif((float) $this->option('poids'))
            ->avecSeuilSimilarite((float)   $this->option('seuil'))
            ->avecMaxVoisins((int)          $this->option('voisins'));

        $this->line("  <fg=gray>⚙️  Config : seuil={$calculateur->getSeuilSimilarite()} | poids_collab={$calculateur->getPoidsCollaboratif()} | max_voisins={$calculateur->getMaxVoisins()} | suggestions={$calculateur->getNombreSuggestions()}</>");
        $this->newLine();

        // ── Calcul des suggestions ────────────────────────────────
        $debut = microtime(true);

        try {
            $suggestions = $calculateur->genererSuggestions($utilisateur);
        } catch (\InvalidArgumentException $e) {
            $this->error("❌  {$e->getMessage()}");
            return self::FAILURE;
        }

        $duree = round((microtime(true) - $debut) * 1000, 2);

        if ($suggestions->isEmpty()) {
            $this->warn("  ⚠️  Aucun livre suggéré. Vérifiez que des livres ont des tags_ambiance renseignés.");
            return self::SUCCESS;
        }

        // ── Tableau des résultats ─────────────────────────────────
        $this->line("  <fg=green;options=bold>✅  {$suggestions->count()} suggestion(s) générée(s) en {$duree} ms</>");
        $this->newLine();

        $rows = $suggestions->map(function (ResultatSuggestion $r, int $i) {
            $livre = $r->livre;
            $tags  = implode(', ', $livre->tags_ambiance ?? []);

            return [
                '#'             => $i + 1,
                'Titre'         => mb_strimwidth($livre->titre, 0, 32, '…'),
                'Tags'          => mb_strimwidth($tags, 0, 28, '…'),
                'Contenu'       => $this->barre($r->scoreContenu) . ' ' . round($r->scoreContenu * 100) . '%',
                '+Collaboratif' => $this->barre($r->bonusCollaboratif / 0.5) . ' +' . round($r->bonusCollaboratif * 100) . '%',
                'FINAL'         => round($r->scoreFinal / 1.5 * 100) . '%',
                'Voisins'       => $r->nombreUtilisateursSimilaires,
            ];
        })->toArray();

        $this->table(
            ['#', 'Titre', 'Tags d\'ambiance', 'Score Contenu', 'Bonus Collaboratif', 'Affinité Finale', 'Voisins'],
            $rows
        );

        $this->newLine();
        $this->line("<fg=gray>  Astuce : --poids=0 pour du filtrage pur contenu | --poids=1.0 pour du filtrage pur collaboratif</>");
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Génère une mini-barre de progression ASCII (5 caractères).
     * Ex: 0.6 → "███░░"
     */
    private function barre(float $ratio): string
    {
        $plein = (int) round(min($ratio, 1.0) * 5);
        return str_repeat('█', $plein) . str_repeat('░', 5 - $plein);
    }
}
