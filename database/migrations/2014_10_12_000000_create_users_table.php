<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable(); // could be nullable due to information sharing from social platform
            $table->string('password');

            // For future extension. Despite the requirement is only required social login but in future might need
            // self registration function. Hence, nullable is declared.
            $table->string('social_id')->nullable();
            $table->enum('auth_type', ['facebook', 'google', 'github'])->nullable();

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
