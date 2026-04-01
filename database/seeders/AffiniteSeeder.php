<?php

namespace Database\Seeders;

use App\Models\Auteur;
use App\Models\Avis;
use App\Models\Categorie;
use App\Models\Livre;
use App\Models\ProfilLecteur;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder de démonstration pour le moteur d'affinité hybride.
 *
 * Scénario :
 *  - 1 utilisateur principal (alice@lectura.test) avec un profil « sombre, épique »
 *  - 5 utilisateurs voisins ayant des profils variés et des avis sur les livres
 *  - 12 livres couvrant 4 catégories, chacun avec ses tags d'ambiance
 *
 * Résultat attendu :
 *  Les livres "sombre" + "épique" remontent en tête avec un bonus collaboratif
 *  lorsque des voisins similaires ont donné de bonnes notes sur ces mêmes livres.
 */
class AffiniteSeeder extends Seeder
{
    // ── Tags d'ambiance disponibles dans le système ──────────────
    private const TAGS = [
        'sombre', 'épique', 'romantique', 'mystérieux', 'joyeux',
        'philosophique', 'humoristique', 'poétique', 'haletant', 'contemplatif',
    ];

    public function run(): void
    {
        $this->command->info('🌱  Seeding AffiniteSeeder…');

        // ── 1. Catégories ─────────────────────────────────────────
        $categories = $this->creerCategories();

        // ── 2. Auteurs ───────────────────────────────────────────
        $auteurs = $this->creerAuteurs();

        // ── 3. Livres ────────────────────────────────────────────
        $livres = $this->creerLivres($auteurs, $categories);

        // ── 4. Utilisateurs + profils ────────────────────────────
        [$alice, $voisins] = $this->creerUtilisateurs();

        // ── 5. Avis des voisins ──────────────────────────────────
        $this->creerAvis($voisins, $livres);

        $this->command->info('✅  AffiniteSeeder terminé.');
        $this->command->info("    👤 Connexion : alice@lectura.test / password");
    }

    // ─────────────────────────────────────────────────────────────

    private function creerCategories(): array
    {
        $data = [
            ['nom' => 'Fantasy',       'description' => 'Mondes imaginaires, magie et aventures épiques.'],
            ['nom' => 'Thriller',      'description' => 'Suspense, tension psychologique et rebondissements.'],
            ['nom' => 'Philosophie',   'description' => 'Réflexions sur l\'existence, l\'éthique et le sens.'],
            ['nom' => 'Romance',       'description' => 'Histoires d\'amour et de sentiments profonds.'],
        ];

        $cats = [];
        foreach ($data as $d) {
            $cats[$d['nom']] = Categorie::firstOrCreate(['nom' => $d['nom']], $d);
        }
        return $cats;
    }

    private function creerAuteurs(): array
    {
        $data = [
            ['nom' => 'Brandon Sanderson', 'biographie' => 'Maître de la fantasy épique contemporaine.'],
            ['nom' => 'Gillian Flynn',      'biographie' => 'Reine du thriller psychologique sombre.'],
            ['nom' => 'Albert Camus',       'biographie' => 'Philosophe et écrivain de l\'absurde.'],
            ['nom' => 'Toni Morrison',      'biographie' => 'Prix Nobel de littérature, prose poétique et sombre.'],
            ['nom' => 'Patrick Rothfuss',   'biographie' => 'Auteur de fantasy lyrique et épique.'],
            ['nom' => 'Agatha Christie',    'biographie' => 'La reine du mystère et du roman policier.'],
        ];

        $auteurs = [];
        foreach ($data as $d) {
            $auteurs[$d['nom']] = Auteur::firstOrCreate(['nom' => $d['nom']], $d);
        }
        return $auteurs;
    }

    private function creerLivres(array $auteurs, array $categories): array
    {
        $data = [
            [
                'titre'        => 'Le Chemin des Rois',
                'auteur'       => 'Brandon Sanderson',
                'categorie'    => 'Fantasy',
                'tags_ambiance'=> ['épique', 'sombre', 'philosophique'],
                'description'  => 'Premier tome de l\'Archive des Tempêtes : un monde brisé, des guerres sans fin, et une magie née de la douleur.',
            ],
            [
                'titre'        => 'Les Mots de Lumière',
                'auteur'       => 'Brandon Sanderson',
                'categorie'    => 'Fantasy',
                'tags_ambiance'=> ['épique', 'sombre', 'haletant'],
                'description'  => 'Deuxième tome. Les révélations s\'enchaînent dans un conflit qui dépasse les mortels.',
            ],
            [
                'titre'        => 'Gone Girl',
                'auteur'       => 'Gillian Flynn',
                'categorie'    => 'Thriller',
                'tags_ambiance'=> ['sombre', 'mystérieux', 'haletant'],
                'description'  => 'Une disparition, un mari suspect, et des journaux intimes qui mentent. Thriller psychologique implacable.',
            ],
            [
                'titre'        => 'Les Lieux Sombres',
                'auteur'       => 'Gillian Flynn',
                'categorie'    => 'Thriller',
                'tags_ambiance'=> ['sombre', 'mystérieux', 'contemplatif'],
                'description'  => 'Une survivante d\'un massacre familial replonge dans son passé pour trouver la vérité.',
            ],
            [
                'titre'        => 'L\'Étranger',
                'auteur'       => 'Albert Camus',
                'categorie'    => 'Philosophie',
                'tags_ambiance'=> ['contemplatif', 'philosophique', 'sombre'],
                'description'  => 'Un homme indifférent au monde commet un meurtre et affronte l\'absurdité de la justice humaine.',
            ],
            [
                'titre'        => 'La Peste',
                'auteur'       => 'Albert Camus',
                'categorie'    => 'Philosophie',
                'tags_ambiance'=> ['contemplatif', 'philosophique', 'sombre'],
                'description'  => 'Oran assiégée par la maladie. Une réflexion sur la solidarité humaine face à l\'absurde.',
            ],
            [
                'titre'        => 'Beloved',
                'auteur'       => 'Toni Morrison',
                'categorie'    => 'Philosophie',
                'tags_ambiance'=> ['sombre', 'poétique', 'contemplatif'],
                'description'  => 'Une ancienne esclave hantée par le fantôme de sa fille. Chef-d\'œuvre de la littérature américaine.',
            ],
            [
                'titre'        => 'Le Nom du Vent',
                'auteur'       => 'Patrick Rothfuss',
                'categorie'    => 'Fantasy',
                'tags_ambiance'=> ['épique', 'poétique', 'romantique'],
                'description'  => 'La légende de Kvothe racontée par lui-même. Fantasy lyrique et épique inoubliable.',
            ],
            [
                'titre'        => 'La Peur du Sage',
                'auteur'       => 'Patrick Rothfuss',
                'categorie'    => 'Fantasy',
                'tags_ambiance'=> ['épique', 'romantique', 'mystérieux'],
                'description'  => 'Kvothe parcourt le monde, maîtrise la magie et découvre les secrets de la Chandrian.',
            ],
            [
                'titre'        => 'Le Meurtre de Roger Ackroyd',
                'auteur'       => 'Agatha Christie',
                'categorie'    => 'Thriller',
                'tags_ambiance'=> ['mystérieux', 'haletant', 'humoristique'],
                'description'  => 'Hercule Poirot enquête sur un meurtre dans un village anglais. La fin vous laissera sans voix.',
            ],
            [
                'titre'        => 'Dix Petits Nègres',
                'auteur'       => 'Agatha Christie',
                'categorie'    => 'Thriller',
                'tags_ambiance'=> ['mystérieux', 'haletant', 'sombre'],
                'description'  => 'Dix inconnus isolés sur une île. L\'un d\'eux est un assassin. Le compte à rebours commence.',
            ],
            [
                'titre'        => 'Orgueil et Préjugés',
                'auteur'       => 'Agatha Christie', // volontairement faux pour tests
                'categorie'    => 'Romance',
                'tags_ambiance'=> ['romantique', 'joyeux', 'humoristique'],
                'description'  => 'Elizabeth Bennet et Mr Darcy : un roman d\'amour pétillant qui traverse les siècles.',
            ],
        ];

        $livres = [];
        foreach ($data as $d) {
            $livres[$d['titre']] = Livre::firstOrCreate(
                ['titre' => $d['titre']],
                [
                    'description'   => $d['description'],
                    'auteur_id'     => $auteurs[$d['auteur']]->id,
                    'categorie_id'  => $categories[$d['categorie']]->id,
                    'tags_ambiance' => $d['tags_ambiance'],
                    'date_publication' => now()->subYears(rand(1, 30)),
                ]
            );
        }
        return $livres;
    }

    private function creerUtilisateurs(): array
    {
        // ── 1. Administrateur de la plateforme ──
        User::firstOrCreate(
            ['email' => 'admin@lectura.test'],
            ['name' => 'Admin Suprême', 'password' => Hash::make('password'), 'role' => User::ROLE_ADMIN]
        );

        // ── 2. Utilisateur principal (Lecteur) : profil sombre + épique ──
        $alice = User::firstOrCreate(
            ['email' => 'alice@lectura.test'],
            ['name' => 'Alice Dupont', 'password' => Hash::make('password'), 'role' => User::ROLE_LECTEUR]
        );
        ProfilLecteur::firstOrCreate(
            ['utilisateur_id' => $alice->id],
            ['preferences_ambiance' => ['sombre', 'épique'], 'niveau_complexite' => 'difficile']
        );

        // Voisins avec profils variés
        $voisinsData = [
            [
                'user'   => ['name' => 'Bob Martin',   'email' => 'bob@lectura.test'],
                'profil' => ['preferences_ambiance' => ['sombre', 'épique', 'haletant'], 'niveau_complexite' => 'difficile'],
            ],
            [
                'user'   => ['name' => 'Chloé Legrand', 'email' => 'chloe@lectura.test'],
                'profil' => ['preferences_ambiance' => ['épique', 'romantique', 'poétique'], 'niveau_complexite' => 'moyen'],
            ],
            [
                'user'   => ['name' => 'David Morel',   'email' => 'david@lectura.test'],
                'profil' => ['preferences_ambiance' => ['sombre', 'mystérieux', 'contemplatif'], 'niveau_complexite' => 'difficile'],
            ],
            [
                'user'   => ['name' => 'Emma Bernard',  'email' => 'emma@lectura.test'],
                'profil' => ['preferences_ambiance' => ['joyeux', 'romantique', 'humoristique'], 'niveau_complexite' => 'facile'],
            ],
            [
                'user'   => ['name' => 'Félix Petit',   'email' => 'felix@lectura.test'],
                'profil' => ['preferences_ambiance' => ['philosophique', 'contemplatif', 'sombre'], 'niveau_complexite' => 'difficile'],
            ],
        ];

        $voisins = [];
        foreach ($voisinsData as $d) {
            $user = User::firstOrCreate(['email' => $d['user']['email']], array_merge($d['user'], ['password' => Hash::make('password')]));
            ProfilLecteur::firstOrCreate(['utilisateur_id' => $user->id], $d['profil']);
            $voisins[] = $user->load('profil');
        }

        return [$alice, $voisins];
    }

    private function creerAvis(array $voisins, array $livres): void
    {
        // Bob (sombre+épique) → aime les livres épiques et thrillers sombres
        $this->noterSiAbsent($voisins[0], $livres['Le Chemin des Rois'],        5, 'Chef-d\'œuvre absolu.');
        $this->noterSiAbsent($voisins[0], $livres['Les Mots de Lumière'],       5, 'Encore mieux que le premier.');
        $this->noterSiAbsent($voisins[0], $livres['Gone Girl'],                 4, 'Ambiance sombre parfaite.');
        $this->noterSiAbsent($voisins[0], $livres['Dix Petits Nègres'],         4, 'Haletant du début à la fin.');

        // Chloé (épique+romantique) → fantasy épique et romance
        $this->noterSiAbsent($voisins[1], $livres['Le Nom du Vent'],            5, 'Poétique et épique à la fois.');
        $this->noterSiAbsent($voisins[1], $livres['La Peur du Sage'],           5, 'Le meilleur tome.');
        $this->noterSiAbsent($voisins[1], $livres['Le Chemin des Rois'],        4, 'Très bon, un peu long.');
        $this->noterSiAbsent($voisins[1], $livres['Orgueil et Préjugés'],       5, 'Classique indémodable.');

        // David (sombre+mystérieux) → thrillers et fantasy dark
        $this->noterSiAbsent($voisins[2], $livres['Gone Girl'],                 5, 'Tordu à souhait.');
        $this->noterSiAbsent($voisins[2], $livres['Les Lieux Sombres'],         4, 'Ambiance oppressante réussie.');
        $this->noterSiAbsent($voisins[2], $livres['Le Meurtre de Roger Ackroyd'], 5, 'Fin époustouflante.');
        $this->noterSiAbsent($voisins[2], $livres['Beloved'],                   4, 'Lourd et beau.');
        $this->noterSiAbsent($voisins[2], $livres['Le Chemin des Rois'],        3, 'Bien mais trop long.');

        // Emma (joyeux+romantique) → romance et policier léger
        $this->noterSiAbsent($voisins[3], $livres['Orgueil et Préjugés'],       5, 'Mon livre préféré.');
        $this->noterSiAbsent($voisins[3], $livres['Le Meurtre de Roger Ackroyd'], 4, 'Amusant et malin.');
        $this->noterSiAbsent($voisins[3], $livres['Le Nom du Vent'],            3, 'Bien mais trop sombre.');

        // Félix (philosophique+sombre) → littérature exigeante
        $this->noterSiAbsent($voisins[4], $livres['L\'Étranger'],               5, 'Inégalable.');
        $this->noterSiAbsent($voisins[4], $livres['La Peste'],                  5, 'Toujours d\'actualité.');
        $this->noterSiAbsent($voisins[4], $livres['Beloved'],                   5, 'Bouleversant.');
        $this->noterSiAbsent($voisins[4], $livres['Le Chemin des Rois'],        4, 'Profond pour de la fantasy.');
    }

    /** Crée un avis uniquement s'il n'existe pas déjà (idempotent). */
    private function noterSiAbsent(User $user, Livre $livre, int $note, string $commentaire): void
    {
        Avis::firstOrCreate(
            ['utilisateur_id' => $user->id, 'livre_id' => $livre->id],
            ['note' => $note, 'commentaire' => $commentaire]
        );
    }
}
