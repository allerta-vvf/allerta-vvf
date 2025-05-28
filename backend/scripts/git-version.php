<?php
// scripts/git-version.php
// Usage: composer run-script git-version

$revision = trim(shell_exec('git rev-parse --short HEAD'));
$revision_timestamp = (int)trim(shell_exec('git log -1 --format="%at"')) * 1000;
$branch = trim(shell_exec('git rev-parse --abbrev-ref HEAD'));
$remote_url = trim(shell_exec('git config --get remote.origin.url'));

$data = [
    'revision' => $revision,
    'revision_timestamp' => $revision_timestamp,
    'branch' => $branch,
    'remote_url' => $remote_url,
];

$file = __DIR__ . '/../storage/app/git-version.json';
file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "Git version info written to $file\n";
