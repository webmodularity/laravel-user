<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('user_action_id');
            $table->unsignedInteger('log_request_id');
            $table->unsignedInteger('user_id');
            $table->unsignedSmallInteger('social_provider_id')->nullable();
            $table->timestamp('created_at');
            $table->index('created_at');
            $table->index(['user_id', 'user_action_id', 'created_at']);
            $table->unique(['user_id', 'log_request_id', 'user_action_id']);
            $table->foreign('log_request_id')->references('id')->on('log_requests')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('social_provider_id')->references('id')->on('user_social_providers')->onUpdate('cascade');
            $table->foreign('user_action_id')->references('id')->on('log_user_actions')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('log_users');
    }
}
