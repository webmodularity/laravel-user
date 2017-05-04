<?php

namespace WebModularity\LaravelUser\Validators;

use WebModularity\LaravelContact\Person;
use WebModularity\LaravelUser\User;

class UserPersonUniqueValidator
{

    public function validate($attribute, $value, $parameters, $validator)
    {
        \Log::warning($parameters);
        $person = Person::where('email', $value)->first();
        if (is_null($person)) {
            return true;
        }
        $query = User::query();
        $query->where('person_id', $person->id);
        if (is_array($parameters) && count($parameters)) {
            $query->where('id', '<>', array_shift($parameters));
        }

        return $query->count() == 0;
    }
}
