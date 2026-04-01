<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilLecteur extends Model
{
    protected $table = 'profil_lecteurs';

    protected $fillable = ['utilisateur_id', 'preferences_ambiance', 'niveau_complexite'];

    protected $casts = [
        'preferences_ambiance' => 'array',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}
