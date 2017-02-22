<?php

namespace WebModularity\LaravelUser;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\Str;

class UserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials)) {
            return null;
        }
        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // Eloquent User "model" that will be utilized by the Guard instances.
        $query = $this->createModel()->newQuery();
        foreach ($credentials as $key => $value) {
            if (! Str::contains($key, 'password')) {
                if ($key == 'email') {
                    $query->whereHas('person', function ($query) use ($key, $value) {
                        $query->where($key, $value);
                    });
                } else {
                    $query->where($key, $value);
                }
            }
        }
        return $query->first();
    }
}
