<?php

namespace WebModularity\LaravelUser\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\ResetsPasswords as BaseResetsPasswords;
use Illuminate\Support\Str;

trait ResetsPasswords
{
    use BaseResetsPasswords;

    protected $userIsPending = false;

    /**
     * Reset the given user's password. Login user unless user is pending.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->forceFill([
            'password' => bcrypt($password),
            'remember_token' => Str::random(60),
        ])->save();

        if ($user->isPending()) {
            $this->userIsPending = true;
        } else {
            $this->guard()->login($user);
        }
    }

    /**
     * Get the response for a successful password reset. Sends back to login if user is pending.
     *
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendResetResponse($response)
    {
        if ($this->userIsPending) {
            session()->flash('success', 'Password reset successfully.');
            session()->flash('warning', 'This user account is pending approval.');
            $redirectPath = route('login');
        } else {
            $redirectPath = $this->redirectPath();
        }

        return redirect($redirectPath)->with('status', trans($response));
    }
}