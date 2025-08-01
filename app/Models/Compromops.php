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
        'lugar',
        'maquinaria_id',
        'activo'
    ];
    
    protected $casts = [
        'finicio' => 'datetime',
        'ftermino' => 'datetime',
        'finicioreal' => 'datetime',
        'fterminoreal' => 'datetime',
        'fecha' => 'datetime',
        'activo' => 'boolean', // Asegurar que activo sea booleano
    ];
    
    /**
     * Verificar si la tarea está activa
     */
    public function isActive()
    {
        return $this->activo == 1 || $this->activo === true;
    }
    
    /**
     * Scope para obtener solo tareas activas
     */
    public function scopeActive($query)
    {
        return $query->where('activo', 1);
    }
    
    /**
     * Scope para obtener solo tareas inactivas
     */
    public function scopeInactive($query)
    {
        return $query->where('activo', 0);
    }
    
    /**
     * Obtener la maquinaria asignada a esta tarea
     */
    public function maquinaria()
    {
        return $this->belongsTo(Maquinaria::class);
    }
}