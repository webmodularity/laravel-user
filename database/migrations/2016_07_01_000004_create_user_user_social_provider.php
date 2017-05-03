<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserUserSocialProvider extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_user_social_provider', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->unsignedSmallInteger('user_social_provider_id');
            $table->string('uid');
            $table->primary(['user_id', 'user_social_provider_id']);
            $table->unique(['user_social_provider_id', 'uid']);
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_social_provider_id')->references('id')->on('user_social_providers')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_user_social_provider');
    }
}
