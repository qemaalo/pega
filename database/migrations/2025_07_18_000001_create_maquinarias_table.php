<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maquinarias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // ej: "PRENSA 19", "INGOMAR REV 1"
            $table->unsignedBigInteger('centro_id'); // relación con centros
            $table->integer('orden')->default(1); // orden dentro del centro
            $table->boolean('activo')->default(true);
            $table->text('comentario')->nullable();
            $table->timestamps();
            
            $table->foreign('centro_id')->references('id')->on('centros');
            $table->index(['centro_id', 'orden']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('maquinarias');
    }
};
