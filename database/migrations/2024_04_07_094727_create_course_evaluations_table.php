<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseEvaluationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('course_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')
                ->references('id')
                ->on('courses')
                ->onDelete('cascade');
            $table->enum('rate',[1,2,3,4,5]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('course_evaluations');
    }
};
