<?php

$baseConfig = [
    'local' => 'backend',
    'ignore' => '
		.git*
		.github
		.vscode
        *.json
		config.old.php
		config.php
		keys
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
		*.lock
		*.zip
    ',
	'allowDelete' => false,
    'before' =>  [
        'local: cd backend && composer update --no-dev -o'
    ],
    'after' => [
        'local: cd backend && composer install'
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
		$config[$key]["before"][] = "local: cd frontend && npm ci && npm run build";
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