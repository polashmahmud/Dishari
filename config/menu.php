<?php

return [
    /*
|--------------------------------------------------------------------------
| Vue JS Component Directory
|--------------------------------------------------------------------------
|
| resource/js directory name where the Vue JS components will be published.
|
*/
    'directory_name' => 'dishari',

    /*
    |--------------------------------------------------------------------------
    | Authentication Required
    |--------------------------------------------------------------------------
    |
    | If set to true, the menu will only be visible to logged-in users.
    | If false, the menu will be visible to guests as well.
    |
    */
    'auth_required' => true,

    /*
    |--------------------------------------------------------------------------
    | Auto Share via Inertia
    |--------------------------------------------------------------------------
    |
    | If set to true, the menu data will be automatically shared to all Inertia
    | responses globally. If false, you have to manually share it.
    |
    */
    'auto_share' => true,

    /*
    |--------------------------------------------------------------------------
    | Menu Caching
    |--------------------------------------------------------------------------
    |
    | Enable caching to reduce database queries.
    |
    */
    'cache' => [
        'enabled' => true,
        'key' => 'dishari_sidebar_menu', // cache key
        'ttl' => 60 * 60, // cache time in seconds
    ],
];
