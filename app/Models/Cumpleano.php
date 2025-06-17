<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cumpleano extends Model
{
    use HasFactory;

    // Deshabilitar timestamps automáticos
    public $timestamps = false;

    protected $fillable = [
        'rut',
        'nombre',
        'apellido',
        'fecha_cumpleanos',
        'edad',
        'vinculado_empresa',
        'email_enviado',
    ];

    protected $casts = [
        'fecha_cumpleanos' => 'date',
        'vinculado_empresa' => 'boolean',
        'email_enviado' => 'boolean',
    ];

    /**
     * Boot del modelo para calcular edad automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cumpleano) {
            $cumpleano->edad = Carbon::parse($cumpleano->fecha_cumpleanos)->age;
        });

        static::updating(function ($cumpleano) {
            if ($cumpleano->isDirty('fecha_cumpleanos')) {
                $cumpleano->edad = Carbon::parse($cumpleano->fecha_cumpleanos)->age;
            }
        });
    }

    /**
     * Obtener el nombre completo
     */
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre . ' ' . $this->apellido);
    }

    /**
     * Obtener la edad actual de la persona (calculada en tiempo real)
     */
    public function getEdadActualAttribute()
    {
        return Carbon::parse($this->fecha_cumpleanos)->age;
    }

    /**
     * Obtener los días restantes para el próximo cumpleaños
     */
    public function getDiasRestantesAttribute()
    {
        $hoy = Carbon::now();
        $cumpleanos = Carbon::parse($this->fecha_cumpleanos)->year($hoy->year);
        
        // Si ya pasó este año, tomar el próximo año
        if ($cumpleanos->lt($hoy)) {
            $cumpleanos->addYear();
        }
        
        return $hoy->diffInDays($cumpleanos);
    }

    /**
     * Verificar si hoy es el cumpleaños
     */
    public function esCumpleanosHoy()
    {
        $hoy = Carbon::now();
        $cumpleanos = Carbon::parse($this->fecha_cumpleanos);
        
        return $hoy->month === $cumpleanos->month && $hoy->day === $cumpleanos->day;
    }

    /**
     * Obtener los próximos cumpleaños (siguiente fecha de cumpleaños)
     */
    public function getProximoCumpleanosAttribute()
    {
        $hoy = Carbon::now();
        $cumpleanos = Carbon::parse($this->fecha_cumpleanos)->year($hoy->year);
        
        // Si ya pasó este año, tomar el próximo año
        if ($cumpleanos->lt($hoy)) {
            $cumpleanos->addYear();
        }
        
        return $cumpleanos;
    }

    /**
     * Scope para obtener cumpleaños de hoy
     */
    public function scopeCumpleanosHoy($query)
    {
        $hoy = Carbon::now();
        return $query->whereMonth('fecha_cumpleanos', $hoy->month)
                    ->whereDay('fecha_cumpleanos', $hoy->day);
    }

    /**
     * Scope para obtener solo vinculados a la empresa
     */
    public function scopeVinculadosEmpresa($query)
    {
        return $query->where('vinculado_empresa', true);
    }

    /**
     * Scope para obtener emails no enviados
     */
    public function scopeEmailsNoEnviados($query)
    {
        return $query->where('email_enviado', false);
    }
}