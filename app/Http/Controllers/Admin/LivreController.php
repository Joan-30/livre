<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auteur;
use App\Models\Categorie;
use App\Models\Livre;
use Illuminate\Http\Request;

class LivreController extends Controller
{
    /**
     * Affiche la liste des livres pour l'administrateur.
     */
    public function index()
    {
        $livres = Livre::with(['auteur', 'categorie'])->orderBy('created_at', 'desc')->paginate(12);
        return view('admin.livres.index', compact('livres'));
    }

    /**
     * Affiche le formulaire de création d'un livre.
     */
    public function create()
    {
        $auteurs = Auteur::orderBy('nom')->get();
        $categories = Categorie::orderBy('nom')->get();
        return view('admin.livres.create', compact('auteurs', 'categories'));
    }

    /**
     * Enregistre le nouveau livre avec ses tags JSON.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'auteur_id' => 'required|exists:auteurs,id',
            'categorie_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'tags_ambiance' => 'required|string', // Envoyé en texte par un textarea séparé par des virgules
        ]);

        // Traitement des tags (séparation par virgule, trim)
        $tagsStr = $validated['tags_ambiance'];
        $tagsArray = array_map('trim', explode(',', $tagsStr));

        Livre::create([
            'titre' => $validated['titre'],
            'auteur_id' => $validated['auteur_id'],
            'categorie_id' => $validated['categorie_id'],
            'description' => $validated['description'],
            'tags_ambiance' => $tagsArray,
            'date_publication' => now()
        ]);

        return redirect()->route('admin.livres.index')->with('success', 'Livre ajouté au catalogue avec succès !');
    }

    /**
     * Affiche le formulaire d'édition.
     */
    public function edit(Livre $livre)
    {
        $auteurs = Auteur::orderBy('nom')->get();
        $categories = Categorie::orderBy('nom')->get();
        return view('admin.livres.edit', compact('livre', 'auteurs', 'categories'));
    }

    /**
     * Met à jour le livre.
     */
    public function update(Request $request, Livre $livre)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'auteur_id' => 'required|exists:auteurs,id',
            'categorie_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'tags_ambiance' => 'required|string',
        ]);

        $tagsArray = array_map('trim', explode(',', $validated['tags_ambiance']));

        $livre->update([
            'titre' => $validated['titre'],
            'auteur_id' => $validated['auteur_id'],
            'categorie_id' => $validated['categorie_id'],
            'description' => $validated['description'],
            'tags_ambiance' => $tagsArray,
        ]);

        return redirect()->route('admin.livres.index')->with('success', 'Livre mis à jour !');
    }

    /**
     * Supprime le livre du catalogue.
     */
    public function destroy(Livre $livre)
    {
        $livre->delete();
        return redirect()->route('admin.livres.index')->with('success', 'Le livre a été retiré.');
    }
}
