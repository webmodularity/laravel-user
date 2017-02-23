<?php

namespace WebModularity\LaravelUser;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use WebModularity\LaravelLog\LogRequest;
use WebModularity\LaravelProviders\SocialProvider;
use WebModularity\LaravelUser\User;

/**
 * WebModularity\LaravelUser\LogUser
 *
 * @property int $id
 * @property int $user_action
 * @property int $log_request_id
 * @property int $user_id
 * @property int $social_provider_id
 * @property string $created_at
 * @property-read LogRequest $logRequest
 * @property-read User $user
 * @property-read SocialProvider $socialProvider
 */

class LogUser extends Model
{
    const UPDATED_AT = null;

    const ACTION_LOGIN = 1;
    const ACTION_LOGOUT = 2;
    const ACTION_REGISTER = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_action', 'log_request_id', 'user_id', 'social_provider_id'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('withLogRequest', function (Builder $builder) {
            $builder->with(['logRequest']);
        });
        static::addGlobalScope('withSocialProvider', function (Builder $builder) {
            $builder->with(['socialProvider']);
        });
    }

    public function logRequest()
    {
        return $this->belongsTo(LogRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function socialProvider()
    {
        return $this->belongsTo(SocialProvider::class);
    }

    public function scopeRecentLogins($query, $loginCount = 3)
    {
        return $query->where('user_action', static::ACTION_LOGIN)
            ->latest()
            ->limit($loginCount);
    }

    public function getVia()
    {
        if (!is_null($this->socialProvider)) {
            return $this->socialProvider->provider->name;
        }

        return 'web';
    }
}
