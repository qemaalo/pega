<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Centro;
use App\Models\Maquinaria;

class MaquinariaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Definir las maquinarias por centro basándose en los datos proporcionados
        $maquinariasPorCentro = [
            'PRENSA' => [
                'PRENSA 19', 'PRENSA 18', 'PRENSA 17', 'PRENSA 16', 'PRENSA 15',
                'PRENSA 14', 'PRENSA 13', 'PRENSA 12', 'PRENSA 11', 'PRENSA 4',
                'PRENSA 3', 'PRENSA 2'
            ],
            'REVESTIMIENTO' => [
                'INGOMAR REV 1', 'INGOMAR REV 2', 'INGOMAR REV 3', 'INGOMAR REV 4', 'INGOMAR REV 5',
                'CONTRATISTA 1-1', 'CONTRATISTA 1-2', 'CONTRATISTA 1-3', 'CONTRATISTA 1-4', 'CONTRATISTA 1-5',
                'CONTRATISTA 2-1', 'CONTRATISTA 2-2', 'CONTRATISTA 2-3', 'CONTRATISTA 2-4', 'CONTRATISTA 2-5',
                'CONTRATISTA 3-1', 'CONTRATISTA 3-2', 'CONTRATISTA 3-3', 'CONTRATISTA 3-4', 'CONTRATISTA 3-5'
            ],
            'POLIURETANO' => [
                'INGOMAR PU 1', 'INGOMAR PU 2', 'INGOMAR PU 3'
            ],
            'TRAFILA' => [
                'INGOMAR TRAF 1', 'INGOMAR TRAF 2', 'INGOMAR TRAF 3', 'INGOMAR TRAF 4', 'INGOMAR TRAF 5'
            ],
            'ANILLOS' => [
                'PRENSA 8-1', 'PRENSA 8-2', 'PRENSA 8-3', 'PRENSA 8-4',
                'PRENSA 9-1', 'PRENSA 9-2', 'PRENSA 9-3', 'PRENSA 9-4',
                'PRENSA 10-1', 'PRENSA 10-2', 'PRENSA 10-3', 'PRENSA 10-4', 'PRENSA 10-5', 'PRENSA 10-6',
                'PRENSA 7-1', 'PRENSA 7-2'
            ]
        ];

        foreach ($maquinariasPorCentro as $centroNombre => $maquinarias) {
            // Buscar el centro por descripción
            $centro = Centro::where('descripcion', $centroNombre)->first();
            
            if ($centro) {
                foreach ($maquinarias as $orden => $nombreMaquinaria) {
                    Maquinaria::create([
                        'nombre' => $nombreMaquinaria,
                        'centro_id' => $centro->id,
                        'orden' => $orden + 1,
                        'activo' => true,
                        'comentario' => 'Creado por seeder'
                    ]);
                }
            }
        }
    }
}
