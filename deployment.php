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
        debug_storage/exception*
        debug_storage/*.log
		debug_storage/*.json
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
		vendor
		*.lock
		*.zip
    ',
    'before' =>  [
        'local: cd server && composer update --no-dev -o'
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
	if(isset($value["before"]) && $value["before"] === false){
		$config[$key]["before"] = [];
	} else {
		$env = isset($config[$key]["sentry_env"]) ? $config[$key]["sentry_env"] : "prod";
		$config[$key]["before"][] = "local: cd server/resources && npm i && npm run prod -- --env sentry_environment=".$env;
	}
	if(isset($value["after"]) && $value["after"] === false){
		$config[$key]["after"] = [];
	}
}

return $config;