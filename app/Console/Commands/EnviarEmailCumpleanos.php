<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\Cumpleano;
use App\Mail\CumpleanosMail;
use Carbon\Carbon;

class EnviarEmailCumpleanos extends Command
{
    protected $signature = 'cumpleanos:enviar-emails {--test : Modo de prueba sin enviar emails reales}';

    protected $description = 'Envía emails automáticos cuando es el cumpleaños de alguien vinculado a la empresa';

    public function handle()
    {
        $modoTest = $this->option('test');
        $emailDestino = 'ingomar@ingomar.cl'; // CAMBIA ESTE EMAIL SI NECESITAS

        if ($modoTest) {
            $this->info('🧪 MODO DE PRUEBA ACTIVADO - No se enviarán emails reales');
        }

        $this->info('🎂 Verificando cumpleaños de hoy...');
        
        // Obtener todos los cumpleaños de hoy (vinculados y no vinculados)
        $todosCumpleanosHoy = $this->getTodosCumpleanosHoy();
        $cumpleanosVinculados = $this->getCumpleanosVinculadosHoy();
        $cumpleanosDesvinculados = $todosCumpleanosHoy->where('vinculado_empresa', false);

        // Mostrar estadísticas
        $this->mostrarEstadisticas($todosCumpleanosHoy, $cumpleanosVinculados, $cumpleanosDesvinculados);

        if ($cumpleanosVinculados->isEmpty()) {
            $this->info('✅ No hay cumpleaños de empleados vinculados para notificar hoy.');
            return Command::SUCCESS;
        }

        $this->info("🎉 ¡Enviando emails para {$cumpleanosVinculados->count()} empleados vinculados!");

        $emailsEnviados = 0;

        foreach ($cumpleanosVinculados as $cumpleano) {
            $nombreCompleto = $cumpleano->nombre_completo;
            $edadActual = $cumpleano->edad_actual;
            $cargo = $cumpleano->cargo ?? 'Sin cargo';
            
            $this->line("   🎂 {$nombreCompleto} ({$cargo}) cumple " . ($edadActual + 1) . " años");

            if (!$modoTest) {
                $resultado = $this->enviarEmail($emailDestino, $cumpleano, $nombreCompleto);

                // ✅ MARCAR COMO ENVIADO SIEMPRE QUE EL EMAIL SE ENVÍE EXITOSAMENTE
                if ($resultado) {
                    $cumpleano->email_enviado = true;
                    $cumpleano->save();
                    $this->line("     📧 Marcado como enviado en la base de datos");
                }
            } else {
                // En modo test, también simular el marcado
                $this->line("     📧 (En modo test: se marcaría como enviado)");
            }

            $emailsEnviados++;
        }

        // Resetear emails de cumpleaños pasados
        $this->resetearEmailsPasados();

        $mensaje = $modoTest 
            ? "🧪 Se habrían enviado {$emailsEnviados} emails de cumpleaños."
            : "✅ Se enviaron {$emailsEnviados} emails de cumpleaños exitosamente.";

        $this->info($mensaje);

        return Command::SUCCESS;
    }

    /**
     * Obtener TODOS los cumpleaños de hoy (para estadísticas)
     */
    private function getTodosCumpleanosHoy()
    {
        $hoy = Carbon::now();

        return Cumpleano::whereMonth('fecha_cumpleanos', $hoy->month)
                        ->whereDay('fecha_cumpleanos', $hoy->day)
                        ->get();
    }

    /**
     * Obtener solo los cumpleaños de empleados VINCULADOS de hoy
     */
    private function getCumpleanosVinculadosHoy()
    {
        $hoy = Carbon::now();

        return Cumpleano::whereMonth('fecha_cumpleanos', $hoy->month)
                        ->whereDay('fecha_cumpleanos', $hoy->day)
                        ->where('vinculado_empresa', true) // ✅ SOLO VINCULADOS
                        ->where('email_enviado', false) // Solo los que NO han sido notificados
                        ->get();
    }

    /**
     * Mostrar estadísticas de cumpleaños del día
     */
    private function mostrarEstadisticas($todos, $vinculados, $desvinculados)
    {
        $totalHoy = $todos->count();
        $totalVinculados = $vinculados->count();
        $totalDesvinculados = $desvinculados->count();

        $this->info("📊 Estadísticas del día:");
        $this->line("   👥 Total cumpleaños hoy: {$totalHoy}");
        $this->line("   🏢 Empleados vinculados: {$totalVinculados}");
        $this->line("   🚫 Empleados desvinculados: {$totalDesvinculados}");

        // Mostrar empleados desvinculados si los hay
        if ($totalDesvinculados > 0) {
            $this->warn("⚠️  Empleados desvinculados (NO se enviarán emails):");
            foreach ($desvinculados as $desvinculado) {
                $cargo = $desvinculado->cargo ?? 'Sin cargo';
                $this->line("   🚫 {$desvinculado->nombre_completo} ({$cargo})");
            }
        }

        $this->line(''); // Línea en blanco para separar
    }

    private function enviarEmail($emailDestino, $cumpleano, $nombreCompleto)
    {
        try {
            Mail::to($emailDestino)->send(new CumpleanosMail($cumpleano));
            $this->line("     ✓ Email enviado a {$emailDestino} para {$nombreCompleto}");
            return true;
        } catch (\Exception $e) {
            $this->error("     ✗ Error enviando email para {$nombreCompleto}: " . $e->getMessage());
            return false;
        }
    }

    private function resetearEmailsPasados()
    {
        $hoy = Carbon::now();
        
        // Resetear solo cumpleaños que ya pasaron este año
        $reseteados = Cumpleano::where('email_enviado', true)
            ->where(function ($query) use ($hoy) {
                // Cumpleaños de meses anteriores
                $query->whereMonth('fecha_cumpleanos', '<', $hoy->month)
                      // O del mismo mes pero días anteriores
                      ->orWhere(function ($q) use ($hoy) {
                          $q->whereMonth('fecha_cumpleanos', $hoy->month)
                            ->whereDay('fecha_cumpleanos', '<', $hoy->day);
                      });
            })
            ->update(['email_enviado' => false]);

        if ($reseteados > 0) {
            $this->info("🔄 Reseteados {$reseteados} registros de cumpleaños pasados.");
        }
    }
}
