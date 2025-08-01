<?php
// filepath: c:\wamp64\www\example-app2\database\migrations\2025_06_16_152507_create_cumpleanos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCumpleanosTableStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cumpleanos', function (Blueprint $table) {
            $table->id();
            $table->string('rut', 20)->unique();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->date('fecha_cumpleanos');
            $table->unsignedInteger('edad');
            $table->boolean('vinculado_empresa')->default(false);
            $table->boolean('email_enviado')->default(false);
            
            // Índices para mejorar consultas
            $table->index(['fecha_cumpleanos']);
            $table->index(['vinculado_empresa']);
            $table->index(['email_enviado']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cumpleanos');
    }
}