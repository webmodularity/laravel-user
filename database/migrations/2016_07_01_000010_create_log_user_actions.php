<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use WebModularity\LaravelUser\LogUserAction;

class CreateLogUserActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_user_actions', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('slug', 255)->unique();
        });

        $defaultUserActions = [
            [
                'slug' => 'login'
            ],
            [
                'slug' => 'login-failed'
            ],
            [
                'slug' => 'logout'
            ],
            [
                'slug' => 'register'
            ],
            [
                'slug' => 'link-social'
            ],
            [
                'slug' => 'unlink-social'
            ],
            [
                'slug' => 'change-password-request'
            ],
            [
                'slug' => 'change-password-success'
            ],
            [
                'slug' => 'delete'
            ],
            [
                'slug' => 'lockout'
            ],
        ];

        foreach ($defaultUserActions as $userAction) {
            LogUserAction::create($defaultUserActions);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('log_user_actions');
    }
}
