<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Livre extends Model
{
    protected $fillable = ['titre', 'description', 'auteur_id', 'categorie_id', 'date_publication', 'tags_ambiance'];

    protected $casts = [
        'date_publication' => 'date',
        'tags_ambiance'    => 'array',
    ];

    /**
     * Calcule le nombre de tags d'ambiance communs avec une liste de tags donnée.
     *
     * @param  array $tagsUtilisateur  Ex: ["sombre", "épique"]
     * @return int   Nombre de tags en commun
     */
    public function nombreTagsCommuns(array $tagsUtilisateur): int
    {
        $tagsLivre = $this->tags_ambiance ?? [];
        return count(array_intersect(
            array_map('mb_strtolower', $tagsLivre),
            array_map('mb_strtolower', $tagsUtilisateur)
        ));
    }

    public function auteur()
    {
        return $this->belongsTo(Auteur::class, 'auteur_id');
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }

    public function avis()
    {
        return $this->hasMany(Avis::class, 'livre_id');
    }

    public function favorisUtilisateurs()
    {
        return $this->belongsToMany(User::class, 'favoris', 'livre_id', 'utilisateur_id')->withTimestamps();
    }
}
