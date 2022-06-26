<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDistanceFromRefToSerp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('serps', function (Blueprint $table) {
            $table->integer('words')->after('da')->nullable();
            $table->decimal('distance_to_ref', 6, 4)->after('words')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('serps', function (Blueprint $table) {
            $table->dropColumn('distance_to_ref');
            $table->dropColumn('words');
        });
    }
}
