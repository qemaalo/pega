<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maquinaria extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nombre',
        'centro_id',
        'orden',
        'activo',
        'comentario'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
    ];
    
    /**
     * Obtener el centro al que pertenece la maquinaria
     */
    public function centro()
    {
        return $this->belongsTo(Centro::class);
    }
    
    /**
     * Obtener las tareas asignadas a esta maquinaria
     */
    public function compromops()
    {
        return $this->hasMany(Compromops::class);
    }
    
    /**
     * Scope para obtener solo maquinarias activas
     */
    public function scopeActive($query)
    {
        return $query->where('activo', true);
    }
    
    /**
     * Scope para obtener maquinarias con sus centros
     */
    public function scopeWithCentro($query)
    {
        return $query->with('centro');
    }
}
