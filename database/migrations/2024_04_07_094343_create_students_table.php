<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->boolean('status')->nullable();;
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone_number')->nullable();
            $table->foreignId('location_id')
                ->nullable()
                ->references('id')
                ->on('locations')
                ->onDelete('cascade');
            $table->dateTime('email_verified_at')->nullable();
            $table->text('device_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
};
