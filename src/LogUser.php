<?php

namespace WebModularity\LaravelUser;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * WebModularity\LaravelUser\LogUser
 *
 * @property int $id
 * @property int $log_request_id
 * @property int $user_id
 * @property int $user_action
 * @property string $created_at
 * @property-read \WebModularity\LaravelLog\LogRequest $logRequest
 * @property-read \WebModularity\LaravelUser\User $user
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
    protected $fillable = ['log_request_id', 'user_id', 'user_action'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('withLogRequest', function (Builder $builder) {
            $builder->with(['logRequest']);
        });
    }

    public function logRequest()
    {
        return $this->belongsTo('WebModularity\LaravelLog\LogRequest');
    }

    public function user()
    {
        return $this->belongsTo('WebModularity\LaravelUser\User');
    }

    public function scopeRecentLogins($query, $loginCount = 3)
    {
        return $query->where('user_action', static::ACTION_LOGIN)
            ->latest()
            ->limit($loginCount);
    }

    public function getLoginVia()
    {
        if ($this->user_action !== static::ACTION_LOGIN) {
            return null;
        }
        $urlPath = $this->logRequest->urlPath->url_path;

        if (substr($urlPath, 0, 13) == 'social/handle') {
            return substr($urlPath, 14);
        }

        return 'web';
    }
}
