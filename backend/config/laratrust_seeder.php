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
            'users' => 'c,r,u,ua,d,i,b,h,sc,sd,atc,ame',
            'user' => 'u,ua,h,sc,sd',
            'services' => 'c,r,u,d',
            'trainings' => 'c,r,u,d',
            'alerts' => 'c,r,u',
            'logs' => 'r',
            'admin' => 'r',
            'admin-info' => 'r,u',
            'admin-maintenance' => 'r,u',
            'admin-roles' => 'r,u'
        ],
        'admin' => [
            'users' => 'c,r,u,ua,d,i,b,h,sc,sd,atc,ame',
            'user' => 'u,ua,h,sc,sd',
            'services' => 'c,r,u,d',
            'trainings' => 'c,r,u,d',
            'alerts' => 'c,r,u',
            'logs' => 'lr',
            'admin' => 'r',
            'admin-info' => 'r,u',
            'admin-roles' => 'r,u'
        ],
        'chief' => [
            'users' => 'r,u,sc,sd,atc,ame',
            'user' => 'u,ua',
            'services' => 'c,r,u,d',
            'trainings' => 'c,r,u,d',
            'alerts' => 'c,r,u',
            'logs' => 'lr'
        ],
        'user' => [
            'users' => 'lr',
            'user' => 'u,ua',
            'services' => 'c,r,u,d',
            'trainings' => 'c,r,u,d',
            'alerts' => 'r',
            'logs' => 'lr'
        ]
    ],

    'permissions_map' => [
        'c' => 'create',
        'lr' => 'limited-read',
        'r' => 'read',
        'u' => 'update',
        'ua' => 'update-auth',
        'd' => 'delete',
        'i' => 'impersonate',
        'b' => 'ban',
        'h' => 'hide',
        'sc' => 'set-chief',
        'sd' => 'set-driver',
        'atc' => 'add-training-course',
        'ame' => 'add-medical-examination'
    ]
];
