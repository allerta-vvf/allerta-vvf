<?php

return [
    /**
     * Control if the seeder should create a user per role while seeding the data.
     */
    'create_users' => false,

    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'roles_structure' => [
        'superadmin' => [
            'users' => 'c,r,u,d,i',
        ],
        'admin' => [
            'users' => 'c,r,u'
        ],
        'chief' => [
            'users' => 'r'
        ],
        'user' => [
            'users' => 'lr'
        ]
    ],

    'permissions_map' => [
        'c' => 'create',
        'lr' => 'limitedRead',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete',
        'i' => 'impersonate'
    ]
];
