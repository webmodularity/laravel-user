<?php

namespace WebModularity\LaravelUser;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use WebModularity\LaravelProviders\SocialProvider;

/**
 * WebModularity\LaravelUser\UserInvitation
 *
 * @property int $id
 * @property int $social_provider_id
 * @property int $person_id
 * @property bool $role_id
 * @property bool $status
 * @property string $invite_key
 * @property string $expires_at
 * @property string $claimed_at
 * @property-read \WebModularity\LaravelContact\Person $person
 * @property-read \WebModularity\LaravelUser\Role $role
 * @property-read \WebModularity\LaravelProviders\SocialProvider $socialProvider
 * @method static \Illuminate\Database\Query\Builder|\WebModularity\LaravelUser\UserInvitation notClaimed()
 * @method static \Illuminate\Database\Query\Builder|\WebModularity\LaravelUser\UserInvitation notExpired()
 */

class UserInvitation extends Model
{
    public $timestamps = false;

    protected $dates = [
        'expires_at',
        'claimed_at'
    ];

    protected $fillable = [
        'social_provider_id',
        'person_id',
        'role_id',
        'status',
        'invite_key',
        'expires_at',
        'claimed_at'
    ];

    /**
     * Get the person that owns this UserInvitation.
     */
    public function person()
    {
        return $this->belongsTo('WebModularity\LaravelContact\Person');
    }

    /**
     * Get the role associated with this UserInvitation.
     */
    public function role()
    {
        return $this->belongsTo('WebModularity\LaravelUser\Role');
    }

    /**
     * Get the SocialProvider associated with this UserInvitation.
     */
    public function socialProvider()
    {
        return $this->belongsTo('WebModularity\LaravelProviders\SocialProvider');
    }

    public function scopeNotClaimed($query)
    {
        return $query->whereNull('claimed_at');
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('expires_at')
                ->orWhere('expires_at', '>=', Carbon::now());
        });
    }

    /**
     *
     * @param SocialProvider $socialProvider
     * @param string $email
     * @return Model|null
     */

    public static function firstFromSocial(SocialProvider $socialProvider, $email = null)
    {
        if (!is_null($socialProvider) && $socialProvider->authIsActive()) {
            if (!empty($email)) {
                $emailInvitation = static::where('social_provider_id', $socialProvider->id)
                    ->notClaimed()
                    ->notExpired()
                    ->with('person')
                    ->whereHas('person', function ($query) use ($email) {
                        $query->where('email', $email);
                    })
                    ->first();

                if (!is_null($emailInvitation)) {
                    return $emailInvitation;
                }
            }

            return static::where(
                [
                    ['social_provider_id', $socialProvider->id],
                    ['person_id', null]
                ]
            )
                ->notClaimed()
                ->notExpired()
                ->with('person')
                ->first();
        }

        return null;
    }
}
