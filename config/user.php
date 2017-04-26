<?php

return [
    /**
     * @var bool|int Allow new user registrations - If int is used it will set default user role ID for new users.
     * Setting to true will use the default guest role ID (0) - which forces users to be approved before login
     */
    'register' => false,
    /** @var bool Allow login via active social providers (requires social provider setup) */
    'social' => true,
    /** @var bool Setting to false will disable "Remember Me" option on login (session destroyed on browser close) */
    'remember' => false
];