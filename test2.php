<?php
require_once('vendor/autoload.php');
require_once('../../as-api-creds.secret');
require_once('ASAPI.php');

$asapi = new ASAPI([
    'username' => $username,
    'password' => $password,
]);

$data = $asapi->call('nodes/195');

sU::debug($data);
?>
