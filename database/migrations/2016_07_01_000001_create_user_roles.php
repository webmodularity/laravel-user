<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use WebModularity\LaravelUser\LogUserAction;
use WebModularity\LaravelUser\UserRole;

class CreateUserRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->unsignedInteger('id');
            $table->string('slug', 255)->unique();

            $table->primary('id');
        });

        $defaultRoles = [
            [
                'id' => 0,
                'slug' => 'guest'
            ],
            [
                'id' => 1,
                'slug' => 'user'
            ],
            [
                'id' => 100,
                'slug' => 'admin'
            ],
            [
                'id' => 255,
                'slug' => 'super-admin'
            ]
        ];

        foreach ($defaultRoles as $role) {
            UserRole::create($role);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_roles');
    }
}
