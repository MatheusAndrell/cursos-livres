<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCardsAndTendenciasToBundles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bundles', function (Blueprint $table) {
            $table->string('titulotendencia1')->nullable();
            $table->string('titulotendencia2')->nullable();
            $table->string('titulotendencia3')->nullable();

            $table->text('tendencia1')->nullable();
            $table->text('tendencia2')->nullable();
            $table->text('tendencia3')->nullable();

            $table->string('card1')->nullable();
            $table->string('card2')->nullable();
            $table->string('card3')->nullable();
        
            $table->text('titulocard1')->nullable();
            $table->text('titulocard2')->nullable();
            $table->text('titulocard3')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bundles', function (Blueprint $table) {
            //
        });
    }
}
