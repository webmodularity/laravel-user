<?php

namespace WebModularity\LaravelUser;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;

/**
 * WebModularity\LaravelUser\User
 *
 * @property int $id
 * @property int $person_id
 * @property bool $role_id
 * @property string $avatar_url
 * @property string $username
 * @property string $password
 * @property bool $status
 * @property string $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \WebModularity\LaravelContact\Person $person
 * @property-read \WebModularity\LaravelUser\Role $role
 * @property-read \Illuminate\Database\Eloquent\Collection|\WebModularity\LaravelUser\UserSocialProfile[] $userSocialProfiles
 */
class User extends Authenticatable
{
    use Notifiable;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('withPerson', function (Builder $builder) {
            $builder->with(['person']);
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'person_id', 'role_id', 'username', 'password', 'status', 'avatar_url'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get the person that owns the user.
     */
    public function person()
    {
        return $this->belongsTo('WebModularity\LaravelContact\Person');
    }

    /**
     * Get the role associated with this user.
     */
    public function role()
    {
        return $this->belongsTo('WebModularity\LaravelUser\Role');
    }

    /**
     * Get the social profiles for this user.
     */
    public function userSocialProfiles()
    {
        return $this->hasMany('WebModularity\LaravelUser\UserSocialProfile');
    }
}
