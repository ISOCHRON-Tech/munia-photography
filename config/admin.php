<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Credentials
    |--------------------------------------------------------------------------
    | Set ADMIN_EMAIL and ADMIN_PASSWORD_HASH in your .env file.
    | Generate the hash with:
    |   php artisan tinker --execute="echo bcrypt('yourpassword');"
    */
    'email'         => env('ADMIN_EMAIL'),
    'password_hash' => env('ADMIN_PASSWORD_HASH'),
];
