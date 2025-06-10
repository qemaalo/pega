<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compromops extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'op',
        'linea',
        'usuario',
        'np',
        'fecha',
        'finicio',
        'ftermino',
        'comentario',
        'fterminoreal',
        'finicioreal',
        'lugar'
    ];
    
    protected $casts = [
        'finicio' => 'datetime',
        'ftermino' => 'datetime',
        'finicioreal' => 'datetime',
        'fterminoreal' => 'datetime',
        'fecha' => 'datetime',
    ];

    public function historial()
    {
        return $this->hasMany(CompromopsHistory::class, 'compromops_id');
    }

    /**
     * Obtener los comentarios asociados a este compromiso
     */
    public function comments()
    {
        return $this->hasMany(CompromopsComment::class);
    }
}