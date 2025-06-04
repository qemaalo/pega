<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plano extends Model
{
    protected $table = 'planos';
    protected $fillable = [
        'codigo', 'descripcion', 'tipo_plano', 'nombre_plano', 'ruta', 'temporal', 'ext',
        'nombre_dwg', 'ruta_dwg', 'temporal_dwg', 'ext_dwg', 'nombre_otro', 'temporal_otro',
        'ext_otro', 'vercion', 'orden', 'rev', 'comentario', 'ani_form', 'materiales',
        'subido_user', 'activo', 'valido', 'ref_np', 'comentario_desabi'
    ];
    public $timestamps = true;
}