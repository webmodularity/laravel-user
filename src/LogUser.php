<?php

namespace WebModularity\LaravelUser;

use Illuminate\Database\Eloquent\Model;
use WebModularity\LaravelLog\LogRequest;
use WebModularity\LaravelProviders\SocialProvider;
use Carbon\Carbon;

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
    const ACTION_LINK_SOCIAL = 4;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_action', 'log_request_id', 'user_id', 'social_provider_id'];

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

    public function scopeLogins($query, $loginCount = 3)
    {
        return $query->where('user_action', static::ACTION_LOGIN);
    }

    public function scopeRecentDays($query, $days = 30)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    public function getVia()
    {
        if (!is_null($this->socialProvider)) {
            return $this->socialProvider->provider->name;
        }

        return 'web';
    }
}
