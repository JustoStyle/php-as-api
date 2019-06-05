<?php
require_once('vendor/autoload.php');
require_once('include/config.php');
require_once('ASAPI.php');

$node_id = isset($argv[1]) ? $argv[1] : false;

$asapi = new ASAPI([
    'username' => $username,
    'password' => $password,
]);

// Enable or disable caching
$nocache = false;

if ($node_id) {
    $data = $asapi->call('nodes/' + $node_id, $nocache);
}

// Ridgehaven - large working Node
//$data = $asapi->call('nodes/195', $nocache);

// Ridgehaven 2 - small Node
//$data = $asapi->call('nodes/196', $nocache);

// Rostrevor - large broken Node
//$data = $asapi->call('nodes/472', $nocache);

// Non-existent Node
//$data = $asapi->call('nodes/700', $nocache);

sU::debug($data);
?>
