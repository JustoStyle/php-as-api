<?php
require_once('vendor/autoload.php');
require_once('include/config.php');
require_once('ASAPI.php');

$asapi = new ASAPI([
    'username' => $username,
    'password' => $password,
]);

// Enable or disable caching
$nocache = false;

// Ridgehaven - large working Node
$data = $asapi->call('nodes/195', $nocache);

// Brown - Node with client link
//$data = $asapi->call('nodes/824', $nocache);

// Ridgehaven 2 - small Node
//$data = $asapi->call('nodes/196', $nocache);

// Rostrevor - large broken Node
//$data = $asapi->call('nodes/472', $nocache);

// Non-existent Node
//$data = $asapi->call('nodes/700', $nocache);

//sU::debug($data);

$interfaces = [];
foreach ($data['devices'] as $dd) {
    foreach ($dd['interfaces'] as $di) {
        $interface = [];
        if (sizeof($di['hosts'])) {
            $link_ids = [];
            foreach ($di['hosts'][0]['links'] as $hl) {
                $link_ids[] = $hl['id'];
            }

            $interface = [
                'id' => $di['id'],
                'node_id' => $di['id'],
                'subnet_id' => $di['hosts'][0]['subnet']['id'],
                'host_id' => $di['hosts'][0]['id'],
                'link_ids' => $link_ids,
                'type' => $di['type'],
                'ssid' => $di['radio']['ssid'],
                'mode' => $di['radio']['mode'],
                'protocol' => $di['radio']['band'],
                'freq' => $di['radio']['freq'],
                'passphrase' => $di['radio']['nwkey'],
            ];

            $interfaces[] = $interface;
        }
    }
}

sU::debug($interfaces);
