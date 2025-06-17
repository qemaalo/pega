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

    protected $description = 'Envía emails automáticos cuando es el cumpleaños de alguien';

    public function handle()
    {
        $modoTest = $this->option('test');
        $emailDestino = 'admin@empresa.com'; // CAMBIA ESTE EMAIL SI NECESITAS

        if ($modoTest) {
            $this->info('🧪 MODO DE PRUEBA ACTIVADO - No se enviarán emails reales');
        }

        $this->info('🎂 Verificando cumpleaños de hoy...');
        $cumpleanosHoy = $this->getCumpleanosHoy();

        if ($cumpleanosHoy->isEmpty()) {
            $this->info('✅ No hay cumpleaños para notificar hoy.');
            return Command::SUCCESS;
        }

        $this->info("🎉 ¡Encontrados {$cumpleanosHoy->count()} cumpleaños para HOY!");

        $emailsEnviados = 0;

        foreach ($cumpleanosHoy as $cumpleano) {
            $nombreCompleto = "{$cumpleano->nombre} {$cumpleano->apellido}";
            $this->line("   🎂 {$nombreCompleto} ({$cumpleano->edad} años)");

            if (!$modoTest) {
                $resultado = $this->enviarEmail($emailDestino, $cumpleano, $nombreCompleto);

                if ($resultado) {
                    $cumpleano->email_enviado = true;
                    $cumpleano->save();
                }
            }

            $emailsEnviados++;
        }

        $this->resetearEmailsPasados();

        $mensaje = $modoTest 
            ? "🧪 Se habrían enviado {$emailsEnviados} emails de cumpleaños."
            : "✅ Se enviaron {$emailsEnviados} emails de cumpleaños exitosamente.";

        $this->info($mensaje);

        return Command::SUCCESS;
    }

    private function getCumpleanosHoy()
    {
        $hoy = Carbon::now();

        return Cumpleano::whereMonth('fecha_cumpleanos', $hoy->month)
                        ->whereDay('fecha_cumpleanos', $hoy->day)
                        ->where('email_enviado', false)
                        ->get();
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
        $reseteados = Cumpleano::where('email_enviado', true)
            ->where(function ($query) use ($hoy) {
                $query->whereMonth('fecha_cumpleanos', '<', $hoy->month)
                      ->orWhere(function ($q) use ($hoy) {
                          $q->whereMonth('fecha_cumpleanos', $hoy->month)
                            ->whereDay('fecha_cumpleanos', '<', $hoy->day);
                      });
            })
            ->update(['email_enviado' => false]);

        if ($reseteados > 0) {
            $this->info("🔄 Reseteados {$reseteados} registros para el próximo año.");
        }
    }
}
