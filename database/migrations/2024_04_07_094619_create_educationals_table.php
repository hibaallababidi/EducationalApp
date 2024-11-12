<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEducationalsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('educationals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('status')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone_number')->nullable();
            $table->text('details')->nullable();
            $table->boolean('is_accepted')->default(0);
            $table->enum('type',['public_school','center','institute','private_school']);
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
        Schema::dropIfExists('educationals');
    }
};
