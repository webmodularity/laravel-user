<?php

namespace WebModularity\LaravelUser\Http\Controllers\Auth;

use WebModularity\LaravelContact\Person;
use WebModularity\LaravelUser\User;
use Validator;
use Illuminate\Foundation\Auth\RegistersUsers as BaseRegistersUsers;

trait RegistersUsers
{
    use BaseRegistersUsers;

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
        return !is_null($person)
            ? User::create(
                [
                    'person_id' => $person->id,
                    'role_id' => User::getNewUserRoleId(),
                    'password' => bcrypt($data['password'])
                ]
            )
            : null;
    }
}
