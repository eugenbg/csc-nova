<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GeneratedPostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('generated_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('keyword_id');
            $table->string('slug')->index();
            $table->string('image');
            $table->text('title');
            $table->text('meta_title');
            $table->text('content');
            $table->date('published_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('generated_posts');
    }
}
