<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompromopsHistory extends Model
{
    use HasFactory;
    
    protected $table = 'compromops_history';
    
    protected $fillable = [
        'compromops_id',
        'finicio_old',
        'ftermino_old',
        'finicio_new',
        'ftermino_new',
        'tipo_cambio',
        'confirmado',
        'usuario'
    ];
    
    protected $casts = [
        'finicio_old' => 'datetime',
        'ftermino_old' => 'datetime',
        'finicio_new' => 'datetime',
        'ftermino_new' => 'datetime',
        'confirmado' => 'boolean'
    ];
    
    public function compromiso()
    {
        return $this->belongsTo(Compromops::class, 'compromops_id');
    }
    
    public function comentarios()
    {
        return $this->hasMany(CompromopsComment::class, 'history_id');
    }
}
