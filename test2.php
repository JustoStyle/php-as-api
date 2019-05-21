<?php
require_once('vendor/autoload.php');
require_once('include/config.php');
require_once('ASAPI.php');

$asapi = new ASAPI([
    'username' => $username,
    'password' => $password,
]);

$data = $asapi->call('nodes/195');

sU::debug($data);
?>
