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
            $table->string('action', 255)->unique();
        });

        foreach (['Login', 'Logout', 'Register', 'Link Social', 'Change Password', 'Delete', 'Lockout', 'Failed Login'] as $action) {
            LogUserAction::create(['action' => $action]);
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
