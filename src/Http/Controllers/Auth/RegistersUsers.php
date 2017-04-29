<?php

namespace WebModularity\LaravelUser\Http\Controllers\Auth;

use WebModularity\LaravelContact\Person;
use WebModularity\LaravelUser\User;
use Validator;

trait RegistersUsers
{
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|max:255|userPersonUnique',
            'password' => 'required|min:6|confirmed'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $person = Person::firstOrCreate(
            ['email' => $data['email']],
            [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name']
            ]
        );
        $roleId = config('wm.user.register') === true || config('wm.user.register', 0) < 1
            ? 0
            : config('wm.user.register', 0);
        return !is_null($person)
            ? User::create(
                [
                    'person_id' => $person->id,
                    'role_id' => $roleId,
                    'password' => bcrypt($data['password'])
                ]
            )
            : null;
    }
}
