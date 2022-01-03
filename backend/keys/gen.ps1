openssl req -newkey rsa:2048 -new -nodes -keyout key.pem -out csr.pem
openssl x509 -req -days 365 -in csr.pem -signkey key.pem -out server.crt

$Public = Get-Content server.crt
$Private = Get-Content key.pem

Write-Output "" ""
Write-Output "" ""
Write-Output "Public Key"
Write-Output $Public
Write-Output "" ""
Write-Output "Private Key"
Write-Output $Private
Write-Output "" ""
Write-Output "" ""

$PublicBytes = [System.Text.Encoding]::Unicode.GetBytes($Public)
$EncodedPublic = [Convert]::ToBase64String($PublicBytes)

$PrivateBytes = [System.Text.Encoding]::Unicode.GetBytes($Private)
$EncodedPrivate = [Convert]::ToBase64String($PrivateBytes)

Write-Output "/* JWT Keys */"
Write-Output "define('JWT_PUBLIC_KEY', '$EncodedPublic');"
Write-Output "define('JWT_PRIVATE_KEY', '$EncodedPrivate');"
