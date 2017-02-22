<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTables extends Migration
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
            $table->unsignedTinyInteger('role_id')->default(0);
            $table->string('avatar_url')->nullable();
            $table->string('username', 50)->nullable()->unique();
            $table->string('password')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('person_id')->references('id')->on('people')->onUpdate('cascade')->onDelete('no action');
            $table->foreign('role_id')->references('id')->on('common.user_roles')->onUpdate('cascade')->onDelete('no action');
        });

        Schema::create('user_social_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedSmallInteger('social_provider_id');
            $table->string('uid');
            $table->timestamps();
            $table->unique(['user_id', 'social_provider_id']);
            $table->unique(['social_provider_id', 'uid']);
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('social_provider_id')->references('id')->on('common.social_providers')->onUpdate('cascade');
        });

        Schema::create('user_invitations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('social_provider_id')->nullable();
            $table->unsignedInteger('person_id')->nullable();
            $table->unsignedTinyInteger('role_id')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->string('invite_key')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->unique(['social_provider_id', 'person_id']);
            $table->unique('invite_key');
            $table->index(['person_id', 'claimed_at', 'expires_at'], 'user_invitations_person_claimed_at_index');
            $table->index(['social_provider_id', 'person_id', 'claimed_at', 'expires_at'], 'user_invitations_social_provider_person_claimed_at_index');
            $table->index(['invite_key', 'claimed_at', 'expires_at']);
            $table->foreign('social_provider_id')->references('id')->on('common.social_providers')->onUpdate('cascade');
            $table->foreign('person_id')->references('id')->on('people')->onUpdate('cascade');
            $table->foreign('role_id')->references('id')->on('common.user_roles')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_invitations');
        Schema::drop('user_social_profiles');
        Schema::drop('users');
    }
}
