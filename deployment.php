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
		resources/images/logo.png
		resources/images/owner.png
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
		*LICENSE*
		*CREDITS*
		*VERSION*
		*CHANGELOG*
		*.md
		*.rst
		*.txt
		!resources/dist/*.LICENSE.txt
		!robots.txt
		*.sh
		*editorconfig
		*doc*
		*.yml
		*.travis*
		*.eslintrc*
		*.phpstorm*
		*.php_cs*
		*.xml
		*phpcs*
		*phpstan*
		*phpunit*
		vendor/nikic/fast-route/test
		vendor/twig/twig/src/Node/Expression/Test
		vendor/twig/twig/src/Test
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
		$config[$key]["before"][] = "local: cd server/resources && npm i && npm run prod -- --env sentryEnvironment=".$env;
	}
	if(isset($value["after"]) && $value["after"] === false){
		$config[$key]["after"] = [];
	}
	if(isset($config[$key]["skip_composer_upload"]) && $config[$key]["skip_composer_upload"]){
		$config[$key]["ignore"]  .= "
		vendor";
	}
}

return $config;