<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActivoToCompromopsTable extends Migration
{
    public function up()
{
    Schema::table('compromops', function (Blueprint $table) {
        $table->boolean('activo')->default(true)->after('lugar');
    });
}

public function down()
{
    Schema::table('compromops', function (Blueprint $table) {
        $table->dropColumn('activo');
    });
}

}
