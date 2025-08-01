<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Centro extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'cod',
        'descripcion',
        'estado',
        'activo',
        'orden',
        'comentario'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
    ];
    
    /**
     * Obtener las maquinarias del centro
     */
    public function maquinarias()
    {
        return $this->hasMany(Maquinaria::class)->orderBy('orden');
    }
    
    /**
     * Scope para obtener solo centros activos
     */
    public function scopeActive($query)
    {
        return $query->where('activo', 1);
    }
    
    /**
     * Scope para obtener solo centros de producción
     */
    public function scopeProduccion($query)
    {
        return $query->whereIn('descripcion', [
            'PRENSA', 'REVESTIMIENTO', 'POLIURETANO', 'TRAFILA', 'ANILLOS'
        ]);
    }
}
