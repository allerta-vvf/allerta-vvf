<?php

$baseConfig = [
    'local' => 'server',
    'ignore' => '
		.git*
		.github
		.vscode
        *.json
        !resources/dist/manifest.json
		resources/src
        resources/node_modules
        resources/webpack*
		cypress
        error-log/exception*
        error-log/*.log
		config.old.php
		config.php
		*tests*
		*tests
		*examples*
		*examples
		*Makefile*
		*.md
		*.rst
		*.txt
		*.sh
		*editorconfig
		*doc*
		*.yml
		*.travis*
		*.eslintrc*
		*.phpstorm*
		*.php_cs*
		*.xml
		*LICENSE*
		*CREDITS*
		*VERSION*
		*CHANGELOG*
		*phpcs*
		*phpstan*
		*phpunit*
		vendor/nikic/fast-route/test
		vendor/twig/twig/src/Node/Expression/Test
		vendor/twig/twig/src/Test
		vendor/tracy/tracy/tools
		*.lock
    ',
    'before' =>  [
        'local: cd server && composer update --no-dev -o',
        'local: cd server/resources && npm run prod'
    ],
    'after' => [
        'local: cd server && composer install'
    ],
    'filePermissions' => "0644",
    'dirPermissions' => "0755"
];

try {
    require("deployment_remotes.php");
} catch (\Throwable $th) {
    print("ERROR: no 'deployment_remotes.php' file.".PHP_EOL);
    print("Rename 'deployment_remotes.sample.php' in 'deployment_remotes.php' and edit remotes config.".PHP_EOL);
    exit();
}

$config = [];
foreach ($remotes as $key => $value) {
    $config[$key] = array_merge($value, $baseConfig);
}

return $config;