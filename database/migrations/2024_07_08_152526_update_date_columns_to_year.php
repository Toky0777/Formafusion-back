<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->year('Date_debut')->change();
            $table->year('Date_fin')->change();
        });

        Schema::table('diplomes', function (Blueprint $table) {
            $table->year('Date_debut')->change();
            $table->year('Date_fin')->change();
        });
    }


    public function down()
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->date('Date_debut')->change();
            $table->date('Date_fin')->change();
        });

        Schema::table('diplomes', function (Blueprint $table) {
            $table->date('Date_debut')->change();
            $table->date('Date_fin')->change();
        });
    }
};
