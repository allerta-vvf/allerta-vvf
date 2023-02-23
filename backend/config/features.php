<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Owner name
    |--------------------------------------------------------------------------
    |
    | This options is used in reports generation.
    |
    */

    'owner' => env('OWNER', null),

    /*
    |--------------------------------------------------------------------------
    | Custom owner image
    |--------------------------------------------------------------------------
    |
    | This options can be used for replacing the default logo displayed on the
    | list page and reports.
    | Set this to the filename (with extension) of a file saved in
    | "resources/images".
    | Name it custom_SOMETHING.EXTENSION or SOMETHING.custom.EXTENSION because
    | this is added to the .gitignore file
    |
    */

    'owner_image' => env('OWNER_IMAGE', 'owner.png'),
];