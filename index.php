<?php
require_once('include/config.php');

require_once('vendor/autoload.php');
require_once('include/database.php');
require_once('ASAPI.php');

$data = array();

$nodeData = Node::where('asNum', '<>', '0')->with([
    'user',
    'status',
    'suburb',
    'subnet',
    'subnet.host',
    'subnet.host.alias',
    'subnet.host.interface',
    'link',
])->get();
// $data = Node::all();
$data['nodes'] = $nodeData;
$data['count'] = count($nodeData);

echo json_encode($data, JSON_PRETTY_PRINT);