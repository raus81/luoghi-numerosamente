<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Place extends Model {
    use HasFactory;

    protected $table = 'places';

    function infos()
    {
        return $this->hasMany(Info::class, 'codice', 'codice');
    }

    function upLevel()
    {
        return $this->hasOne(Place::class, 'codice', 'parent');
    }

    function downLevel()
    {
        return $this->hasMany(Place::class, 'parent', 'codice');
    }
}
