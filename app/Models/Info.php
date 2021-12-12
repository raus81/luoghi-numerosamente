<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Info extends Model
{
    use HasFactory;
    protected $table = 'info';

    public function comune(){
        return $this->hasOne(Place::class, 'codice', 'codice');
    }
}
