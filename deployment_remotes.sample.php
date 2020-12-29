<?php
// you can use ftps://, sftp://, file:// or phpsec:// protocols (sftp requires SSH2 extension; phpsec uses phpseclib library)
$remotes = [
    'testing' => [
        'remote' => 'sftp://user:secretpassword@ftp.example.com/directory_testing',
    ],
    'production' => [
        'remote' => 'ftp://ftp.example.com/directory',
        'user' => 'user',
        'password' => 'secretpassword'
    ]
];