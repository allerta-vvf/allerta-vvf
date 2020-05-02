<?php
// ** Database settings ** //
/* The name of the database for Allerta-vvf */
define( 'DB_NAME', 'allerta' );

/* Database username */
define( 'DB_USER', 'root' );

/* Database password */
define( 'DB_PASSWORD', '' );

/* Database hostname */
define( 'DB_HOST', 'localhost' );

// ** Url settings ** //
/* The url that you use to reach Allerta */
define( 'WEB_URL', 'http://localhost/allerta-vvf/server/' );

/* Is the server under Cloudflare® */
/* Cloudflare is a registered trademark of Cloudflare, Inc. */
define( 'SERVER_UNDER_CF', true );

// ** Behavior and names ** //
/* Do you want to add every denied access info to database? */
define( 'INTRUSION_SAVE', true );

/* Do you want to add every denied access info to database? */
define( 'INTRUSION_SAVE_INFO', true );

/* Do you want to enbale chat with IT Manager support? */
define( 'ENABLE_TECHNICAL_SUPPORT', false );

/* Leave blank if 'ENABLE_TECHNICAL_SUPPORT' is false, else go to https://www.smartsuppchat.com */
define( 'TECHNICAL_SUPPORT_KEY', '' );

/* Insert your organization name */
define( 'DISTACCAMENTO', 'Distaccamento' );

/* Do you want to use a custom error message? (filename: custom-error.mp3) */
define( 'USE_CUSTOM_ERROR_SOUND', false );