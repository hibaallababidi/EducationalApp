<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseItemsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('course_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->text('item_description')->nullable();
            $table->foreignId('course_id')
                ->references('id')
                ->on('courses')
                ->onDelete('cascade');
            $table->integer('views')->default(0);
            $table->integer('item_order')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('course_items');
    }
};
