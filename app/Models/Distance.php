<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distance extends Model {
    use HasFactory;

    protected $table = 'distanza';

    public function place1(){
        return $this->hasOne(Place::class,'codice', 'codice_1');
    }

    public function place2(){
        return $this->hasOne(Place::class,'codice', 'codice_2');
    }
}
