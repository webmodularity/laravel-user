<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('person_id')->unique();
            $table->unsignedTinyInteger('user_role_id')->default(0);
            $table->string('avatar_url')->nullable();
            $table->string('username', 50)->nullable()->unique();
            $table->string('password')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('person_id')->references('id')->on('people')->onUpdate('cascade')->onDelete('no action');
            $table->foreign('user_role_id')->references('id')->on('user_roles')->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
