<?php
require_once('include/config.php');

require_once('vendor/autoload.php');
require_once('include/database.php');
require_once('ASAPI.php');

$data = array();

$nodeData = Node::where('asNum', '<>', '0')->get();
// $data = Node::all();
$data['count'] = count($nodeData);
$data['nodes'] = $nodeData;

print_r(json_encode($data, JSON_PRETTY_PRINT));