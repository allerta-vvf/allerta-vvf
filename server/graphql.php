<?php

declare(strict_types=1);

// Test this using following command
// php -S localhost:8080 ./graphql.php &
// curl http://localhost:8080 -d '{"query": "query { echo(message: \"Hello World\") }" }'
// curl http://localhost:8080 -d '{"query": "mutation { sum(x: 2, y: 2) }" }'
require_once __DIR__ . '/vendor/autoload.php';

use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

$users = [[
    'email' => "email",
    'username' => "username",
    'name' => "Name",
    'available' => true,
    'chief' => true,
    'driver' => true,
    'phoneNumber' => "+11234567891",
    'services' => 0,
    'trainings' => 0,
    'availabilityMinutes' => 0,
    'verified' => true,
    'hidden' => false,
    'disabled' => false,
],[
    'email' => "email2",
    'username' => "username2",
    'name' => "Name2",
    'available' => true,
    'chief' => true,
    'driver' => true,
    'phoneNumber' => "+11234567892",
    'services' => 0,
    'trainings' => 0,
    'availabilityMinutes' => 0,
    'verified' => true,
    'hidden' => false,
    'disabled' => false,
]];

try {
    $userType = new ObjectType([
        'name' => 'User',
        'description' => 'Allerta User',
        'fields' => [
            'id' => Type::int(),
            'email' => Type::string(),
            'username' => Type::string(),
            'name' => Type::string(),
            'available' => Type::boolean(),
            'chief' => Type::boolean(),
            'driver' => Type::boolean(),
            'phoneNumber' => Type::string(), //TODO: custom type
            'services' => Type::int(),
            'trainings' => Type::int(),
            'availabilityMinutes' => Type::int(),
            'verified' => Type::boolean(),
            'hidden' => Type::boolean(),
            'disabled' => Type::boolean(),
        ]
    ]);

    $queryType = new ObjectType([
        'name' => 'Query',
        'fields' => [
            'echo' => [
                'type' => Type::string(),
                'args' => [
                    'message' => ['type' => Type::string()],
                ],
                'resolve' => static function ($rootValue, array $args): string {
                    return $rootValue['prefix'] . $args['message'];
                },
            ],
            'user' => [
                'type' => $userType,
                'args' => [
                    'id' => [
                        'type' => Type::int(),
                    ]
                ],
                'resolve' => function ($rootValue, array $args) {
                    global $users;
                    return $users[0];
                },
            ],
            'Users' => [
                'type' => Type::listOf($userType),
                'args' => [
                    'id' => [
                        'type' => Type::int(),
                    ],
                    'username' => [
                        'type' => Type::string(),
                    ],
                    'available' => [
                        'type' => Type::boolean(),
                    ],
                    'chief' => [
                        'type' => Type::boolean(),
                    ],
                    'driver' => [
                        'type' => Type::boolean(),
                    ],
                    'services' => [
                        'type' => Type::int(),
                    ],
                    'trainings' => [
                        'type' => Type::int(),
                    ],
                    'availabilityMinutes' => [
                        'type' => Type::int(),
                    ],
                    'verified' => [
                        'type' => Type::boolean(),
                    ],
                    'hidden' => [
                        'type' => Type::boolean(),
                    ],
                    'disabled' => [
                        'type' => Type::boolean()
                    ]
                ],
                'resolve' => function ($rootValue, array $args) {
                    global $db, $user;
                    $profiles = $db->select("SELECT * FROM `".DB_PREFIX."_profiles`");
                    $users = $db->select("SELECT * FROM `".DB_PREFIX."_users`");
                    $result = [];
                    for ($i=0; $i < sizeof($profiles); $i++) {
                        $result[] = [
                            "id" => $users["id"],
                            "email" => $profiles["email"],
                            "username" => $users["username"],
                            "name" => $user->nameById($users["id"]),
                            "available" => $profiles["available"],
                            "chief" => $profiles["chief"],
                            "driver" => $profiles["driver"],
                            "phoneNumber" => $profiles["phone_number"],
                            "services" => $profiles["services"],
                            "trainings" => $profiles["trainings"],
                            "availabilityMinutes" => $profiles["availabilityMinutes"],
                            "verified" => $users["verified"],
                            "hidden" => $profiles["hidden"],
                            "disabled" => $profiles["disabled"],
                        ];
                    }
                    return $result;
                },
            ]
        ],
    ]);

    $mutationType = new ObjectType([
        'name' => 'Calc',
        'fields' => [
            'sum' => [
                'type' => Type::int(),
                'args' => [
                    'x' => ['type' => Type::int()],
                    'y' => ['type' => Type::int()],
                ],
                'resolve' => static function ($calc, array $args): int {
                    return $args['x'] + $args['y'];
                },
            ],
        ],
    ]);

    // See docs on schema options:
    // https://webonyx.github.io/graphql-php/type-system/schema/#configuration-options
    $schema = new Schema([
        'query' => $queryType,
        'mutation' => $mutationType,
    ]);

    $rawInput       = file_get_contents('php://input');
    $input          = json_decode($rawInput, true);
    $query          = $input['query'];
    $variableValues = $input['variables'] ?? null;

    $rootValue = ['prefix' => 'You said: '];
    $result    = GraphQL::executeQuery($schema, $query, $rootValue, null, $variableValues);
    $output    = $result->toArray();
} catch (Throwable $e) {
    $output = [
        'error' => [
            'message' => $e->getMessage(),
            "object" => $e
        ],
    ];
}

header('Content-Type: application/json; charset=UTF-8');
echo json_encode($output);