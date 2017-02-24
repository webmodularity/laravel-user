<?php

namespace WebModularity\LaravelUser;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use WebModularity\LaravelContact\Person;

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
 * @method static Builder|UserInvitation notClaimed()
 * @method static Builder|UserInvitation notExpired()
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
        return $this->belongsTo(Person::class);
    }

    /**
     * Get the role associated with this UserInvitation.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
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
     * Finds all active invitations matching passed parameters.
     * @param string|null $email
     * @param int|null $socialProviderId
     * @return Collection|null
     */
    public static function findInvitations($email = null, $socialProviderId = null)
    {
        $query = static::notClaimed()
            ->notExpired()
            ->with('person');

        if (is_null($socialProvider)) {
            $query->whereNull('social_provider_id');
        } else {
            $query->where('social_provider_id', $socialProvider->id);
        }

        if (is_null($email)) {
            return $query->whereNull('person_id')->get();
        } else {
            return $query->where(function ($query) use ($email) {
                $query->whereNull('person_id')
                    ->whereHas('person', function ($query) use ($email) {
                        $query->where('email', $email);
                    });
            })->get();
        }
    }

    /**
     * Finds an invitation record matching the provided inviteKey or null if no records match.
     * @param string $inviteKey
     * @return static|null
     */
    public static function findInvitationByKey($inviteKey)
    {
        if (empty($inviteKey) || !is_string($inviteKey) || strlen($inviteKey) > 255) {
            return null;
        }

        return static::where(['invite_key', $inviteKey])
            ->notClaimed()
            ->notExpired()
            ->first();
    }
}
