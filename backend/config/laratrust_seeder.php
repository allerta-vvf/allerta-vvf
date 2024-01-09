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
            'users' => 'c,r,u,d,i,b,h,sc,sd,atc,ame',
            'user' => 'u,h,sc,sd',
            'services' => 'c,r,u,d',
            'trainings' => 'c,r,u,d',
            'alerts' => 'c,r,u',
            'logs' => 'r'
        ],
        'admin' => [
            'users' => 'c,r,u,d,i,b,h,sc,sd,atc,ame',
            'user' => 'u,h,sc,sd',
            'services' => 'c,r,u,d',
            'trainings' => 'c,r,u,d',
            'alerts' => 'c,r,u',
            'logs' => 'lr'
        ],
        'chief' => [
            'users' => 'r,u,sc,sd,atc,ame',
            'user' => 'u',
            'services' => 'c,r,u,d',
            'trainings' => 'c,r,u,d',
            'alerts' => 'c,r,u',
            'logs' => 'lr'
        ],
        'user' => [
            'users' => 'lr',
            'user' => 'u',
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
