<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrivateLessonsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('private_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')
                ->references('id')
                ->on('teachers')
                ->onDelete('cascade');
            $table->foreignId('student_id')
                ->references('id')
                ->on('students')
                ->onDelete('cascade');
            $table->dateTime('lesson_date');
            $table->double('price');
            $table->boolean('is_confirmed')->default(0);
            $table->enum('rate', [1, 2, 3, 4, 5])->nullable();
            $table->text('meet_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('private_lessons');
    }
};
