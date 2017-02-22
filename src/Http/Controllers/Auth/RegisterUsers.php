<?php

namespace WebModularity\LaravelUser\Http\Controllers\Auth;

use WebModularity\LaravelContact\Person;
use WebModularity\LaravelUser\User;
use Validator;

trait RegisterUsers
{
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $person = Person::create([
            'first_name' => $data['person']['first_name'],
            'last_name' => $data['person']['last_name'],
            'email' => $data['person']['email']
        ]);

        $user = User::create([
            'person_id' => $person->id,
            'password' => bcrypt($data['password'])
        ]);

        return $user;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:people',
            'password' => 'required|min:6|confirmed',
        ]);
    }
}
