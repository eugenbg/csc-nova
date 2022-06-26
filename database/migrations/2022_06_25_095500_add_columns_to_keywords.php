<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToKeywords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('keywords', function (Blueprint $table) {
            $table->json('additional_data')->nullable();
            $table->string('keyword_frase')->after('keyword')->nullable();
            $table->string('object_name')->after('keyword_frase')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('keywords', function (Blueprint $table) {
            $table->dropColumn('additional_data');
            $table->dropColumn('keyword_frase');
            $table->dropColumn('object_name');
        });
    }
}
