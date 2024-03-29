<?php

namespace WebModularity\LaravelUser;

use Illuminate\Database\Eloquent\Model;

/**
 * WebModularity\LaravelUser\LogUserAction
 *
 * @property int $id
 * @property string $slug
 */

class LogUserAction extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['slug'];
}