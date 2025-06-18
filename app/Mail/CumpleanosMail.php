<?php
// filepath: c:\wamp64\www\example-app2\app\Mail\CumpleanosMail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Cumpleano;

class CumpleanosMail extends Mailable
{
    use Queueable, SerializesModels;

    public $cumpleano;

    /**
     * Create a new message instance.
     */
    public function __construct(Cumpleano $cumpleano)
    {
        $this->cumpleano = $cumpleano;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->view('emails.cumpleanos')
                    ->subject('🎉 ¡Hoy es el cumpleaños de ' . $this->cumpleano->nombre_completo . '!')
                    ->with([
                        'nombre' => $this->cumpleano->nombre_completo,
                        'fechaCumpleanos' => $this->cumpleano->fecha_cumpleanos,
                        'edad' => $this->cumpleano->edad_actual + 1, // Edad que cumple hoy
                        'vinculadoEmpresa' => $this->cumpleano->vinculado_empresa,
                    ]);
    }
}