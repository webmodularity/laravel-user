<?php

namespace WebModularity\LaravelUser;

use Illuminate\Database\Eloquent\Model;

/**
 * WebModularity\LaravelUser\UserRole
 *
 * @property int $id
 * @property string $slug
 */
class UserRole extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'slug'];
}
