<?php
require_once('vendor/autoload.php');
require_once('include/config.php');
require_once('ASAPI.php');

$asapi = new ASAPI([
    'username' => $username,
    'password' => $password,
]);

$nodes = $asapi->call('nodes?b');
sU::debug($nodes);
?>