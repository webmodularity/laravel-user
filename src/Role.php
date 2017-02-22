<?php

namespace WebModularity\LaravelUser;

use Illuminate\Database\Eloquent\Model;

/**
 * WebModularity\LaravelUser\Role
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 */

class Role extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'common.user_roles';
}
