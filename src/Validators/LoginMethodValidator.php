<?php

namespace WebModularity\LaravelUser\Validators;

use WebModularity\LaravelUser\UserSocialProvider;

class LoginMethodValidator
{

    public function validate($attribute, $value, $parameters, $validator)
    {
        if ($value == 0) {
            // Local
            return true;
        }

        return UserSocialProvider::where('status', true)->andWhere('id', $value)->count() > 0;
    }
}
