<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompromopsCommentsTable extends Migration
{
    public function up()
    {
        Schema::create('compromops_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compromops_id')->constrained('compromops')->onDelete('cascade');
            $table->text('comentario');
            $table->string('usuario');
            $table->date('finicio')->nullable();
            $table->date('ftermino')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('compromops_comments');
    }
}