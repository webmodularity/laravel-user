<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use WebModularity\LaravelUser\UserSocialProvider;

class CreateUserSocialProviders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_social_providers', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('slug', 255)->unique();
            $table->boolean('status')->default(0);
        });


        $defaultSocialProviders = [
            [
                'slug' => 'facebook',
                'status' => false
            ],
            [
                'slug' => 'twitter',
                'status' => false
            ],
            [
                'slug' => 'linkedin',
                'status' => false
            ],
            [
                'slug' => 'google',
                'status' => false
            ],
            [
                'slug' => 'github',
                'status' => false
            ],
            [
                'slug' => 'bitbucket',
                'status' => false
            ],
        ];

        foreach ($defaultSocialProviders as $socialProvider) {
            UserSocialProvider::create($socialProvider);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_social_providers');
    }
}
