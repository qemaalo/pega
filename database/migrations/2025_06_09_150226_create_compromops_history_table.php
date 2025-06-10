<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompromopsHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compromops_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('compromops_id');
            $table->dateTime('finicio_old')->nullable();
            $table->dateTime('ftermino_old')->nullable();
            $table->dateTime('finicio_new')->nullable();
            $table->dateTime('ftermino_new')->nullable();
            $table->string('tipo_cambio'); // 'arrastre', 'redimension_inicio', 'redimension_fin'
            $table->boolean('confirmado')->default(false);
            $table->string('usuario')->nullable();
            $table->timestamps();
            
            $table->foreign('compromops_id')->references('id')->on('compromops')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('compromops_history');
    }
}
