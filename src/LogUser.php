<?php

namespace WebModularity\LaravelUser;

use Illuminate\Database\Eloquent\Model;
use WebModularity\LaravelLog\LogRequest;
use Carbon\Carbon;

/**
 * WebModularity\LaravelUser\LogUser
 *
 * @property int $id
 * @property int $user_action_id
 * @property int $log_request_id
 * @property int $user_id
 * @property int $social_provider_id
 * @property string $created_at
 * @property-read LogUserAction $userAction
 * @property-read LogRequest $logRequest
 * @property-read User $user
 * @property-read UserSocialProvider $socialProvider
 */
class LogUser extends Model
{
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_action_id', 'log_request_id', 'user_id', 'social_provider_id'];

    public function logRequest()
    {
        return $this->belongsTo(LogRequest::class);
    }

    public function userAction()
    {
        return $this->belongsTo(LogUserAction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function socialProvider()
    {
        return $this->belongsTo(UserSocialProvider::class);
    }

    public function scopeLogins($query, $loginCount = 3)
    {
        return $query->whereHas('userAction', function ($query) {
            $query->where('slug', 'login');
        });
    }

    public function scopeRecentDays($query, $days = 30)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    public function getVia()
    {
        if (!is_null($this->socialProvider)) {
            return $this->socialProvider->slug;
        }

        return 'web';
    }
}
