<?php

namespace WebModularity\LaravelUser;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use WebModularity\LaravelContact\Person;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Laravel\Socialite\Contracts\User as SocialUser;
use WebModularity\LaravelUser\UserSocialProvider as SocialProvider;
use Illuminate\Auth\Events\Registered;
use WebModularity\LaravelUser\Events\UserSocialProviderLinked;

/**
 * WebModularity\LaravelUser\User
 *
 * @property int $id
 * @property int $person_id
 * @property bool $role_id
 * @property string $avatar_url
 * @property string $username
 * @property string $password
 * @property string $remember_token
 * @property \Carbon\Carbon $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read Person $person
 * @property-read UserRole $role
 * @property-read \Illuminate\Database\Eloquent\Collection|UserSocialProfile[] $userSocialProfiles
 */

class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, SoftDeletes, Notifiable;

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'person_id', 'role_id', 'username', 'password', 'avatar_url'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('withPerson', function (Builder $builder) {
            $builder->with(['person']);
        });
    }

    /**
     * Get the person that owns the user.
     */
    public function person()
    {
        return $this->belongsTo(Person::class)->withTrashed();
    }

    /**
     * Get the role associated with this user.
     */
    public function role()
    {
        return $this->belongsTo(UserRole::class);
    }

    public function socialProviders()
    {
        return $this->belongsToMany(UserSocialProvider::class)->withPivot(['uid', 'email', 'avatar_url']);
    }

    public function scopeVisibleByRole($query, $roleId = 0)
    {
        return $query->where('role_id', '<=', $roleId);
    }

    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->person->email;
    }

    public function routeNotificationForMail()
    {
        return $this->person->email;
    }

    public function isPending()
    {
        return $this->role_id < 1;
    }

    public static function findFromSocialUser($socialUser, $socialProvider)
    {
        return static::whereHas('socialProviders', function ($query) use ($socialUser, $socialProvider) {
            $query->where([
                ['user_social_provider_id', $socialProvider->id],
                ['uid', $socialUser->getId()]
            ]);
        })->first();
    }

    /**
     * Create new User from data passed by SocialUser and SocialProvider
     * @param SocialUser $socialUser
     * @param UserSocialProvider $socialProvider
     * @return User
     */
    public static function createFromSocialUser(SocialUser $socialUser, SocialProvider $socialProvider)
    {
        $person = Person::firstOrCreate(['email' => $socialUser->getEmail()]);
        $nameFromSocial = $socialProvider->getPersonNameFromSocialUser($socialUser);
        if ($person->nameIsEmpty()) {
            $person->first_name = $nameFromSocial['firstName'];
            $person->last_name = $nameFromSocial['lastName'];
            $person->save();
        }
        $person->user()->create([
            'role_id' => static::getNewUserRoleId(),
            'avatar_url' => $socialProvider->getAvatarFromSocial($socialUser)
        ]);
        event(new Registered($person->user));
        // Link social profile
        $person->user->socialProviders()->attach($socialProvider, [
            'uid' => $socialUser->getId(),
            'email' => $socialUser->getEmail(),
            'avatar_url' => $socialProvider->getAvatarFromSocial($socialUser)
        ]);
        event(new UserSocialProviderLinked($person->user, $socialProvider));
        return $person->user;
    }

    public static function linkFromSocialUser(SocialUser $socialUser, SocialProvider $socialProvider)
    {
        // Search for user with matching email address
        $user = static::whereHas('person', function ($query) use ($socialUser) {
            $query->where('email', $socialUser->getEmail());
        })->first();
        if (is_null($user)) {
            return null;
        }

        // Link social profile
        $userSocialProfile = UserSocialProfile::create([
            'user_id' => $user->id,
            'social_provider_id' => $socialProvider->id,
            'uid' => $socialUser->getId()
        ]);
        event(new UserSocialProfileLinked($userSocialProfile));
        $userSocialProfile->load('user');
        // Update Avatar
        if (empty($userSocialProfile->user->avatar_url)) {
            $userSocialProfile->user->avatar_url = $socialProvider->getAvatarFromSocial($socialUser);
            $userSocialProfile->user->save();
        }
        // Update Name
        $nameFromSocial = $socialProvider->getPersonNameFromSocialUser($socialUser);
        if ($userSocialProfile->user->person->nameIsEmpty()) {
            $userSocialProfile->user->person->first_name = $nameFromSocial['firstName'];
            $userSocialProfile->user->person->last_name = $nameFromSocial['lastName'];
            $userSocialProfile->user->person->save();
        }
        return $userSocialProfile->user;
    }

    public static function getNewUserRoleId()
    {
        return config('wm.user.register') === true || config('wm.user.register', 0) < 1
            ? 0
            : config('wm.user.register', 0);
    }
}
