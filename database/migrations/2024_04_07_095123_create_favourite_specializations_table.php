<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFavouriteSpecializationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('favourite_specializations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->references('id')
                ->on('students')
                ->onDelete('cascade');
            $table->foreignId('specialization_id')
                ->references('id')
                ->on('specializations')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favourite_specializations');
    }
};
