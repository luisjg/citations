<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('citations',function(Blueprint $table) {
            $table->string('publication_id')->primary();
            $table->string('type')->nullable();
            $table->string('doi')->nullable();
            $table->string('handle')->nullable();
            $table->string('document')->nullable();
            $table->string('title')->nullable();
            $table->text('abstract')->nullable();
            $table->string('booktitle')->nullable();
            $table->string('journal')->nullable();
            $table->integer('edition')->nullable();
            $table->integer('series')->nullable();
            $table->integer('number')->nullable();
            $table->integer('volume')->nullable();
            $table->string('chapter')->nullable();
            $table->integer('pages')->nullable();
            $table->string('institution')->nullable();
            $table->string('organization')->nullable();
            $table->string('publisher')->nullable();
            $table->string('school')->nullable();
            $table->string('howpublished')->nullable();
            $table->string('address')->nullable();
            $table->string('date')->nullable();
            $table->text('note')->nullable();


         });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('table');
    }
}
