<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auteur extends Model
{
    protected $fillable = ['nom', 'biographie'];

    public function livres()
    {
        return $this->hasMany(Livre::class, 'auteur_id');
    }
}
