<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompromopsComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'compromops_id',
        'comentario', 
        'usuario',
        'finicio',
        'ftermino'
    ];

    public function compromops()
    {
        return $this->belongsTo(Compromops::class);
    }
}