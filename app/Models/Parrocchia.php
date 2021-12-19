<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parrocchia extends Model {
    use HasFactory;

    protected $table = 'parrocchie';

    public function getNomeParsedAttribute()
    {
        $nome = $this->nome;
        $nome = strtolower($nome);
        $nome = ucwords($nome);
        return $nome;
    }
}
