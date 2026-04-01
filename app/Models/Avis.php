<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avis extends Model
{
    protected $fillable = ['utilisateur_id', 'livre_id', 'note', 'commentaire'];

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    public function livre()
    {
        return $this->belongsTo(Livre::class, 'livre_id');
    }
}
