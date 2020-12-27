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

echo json_encode($data);