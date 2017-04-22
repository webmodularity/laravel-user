<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserInvitations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_invitations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('social_provider_id')->nullable();
            $table->unsignedInteger('person_id')->nullable();
            $table->unsignedTinyInteger('role_id')->default(0);
            $table->string('invite_key')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->unique(['social_provider_id', 'person_id']);
            $table->unique('invite_key');
            $table->index(['person_id', 'claimed_at', 'expires_at'], 'user_invitations_person_claimed_at_index');
            $table->index(['social_provider_id', 'person_id', 'claimed_at', 'expires_at'], 'user_invitations_social_provider_person_claimed_at_index');
            $table->index(['invite_key', 'claimed_at', 'expires_at']);
            $table->foreign('social_provider_id')->references('id')->on('user_social_providers')->onUpdate('cascade');
            $table->foreign('person_id')->references('id')->on('people')->onUpdate('cascade');
            $table->foreign('role_id')->references('id')->on('user_roles')->onUpdate('cascade');
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
    }
}
