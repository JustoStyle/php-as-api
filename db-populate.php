<?php
require_once('include/config.php');

require_once('vendor/autoload.php');
require_once('include/database.php');
require_once('ASAPI.php');

$asapi = new ASAPI([
    'username' => $username,
    'password' => $password,
]);

$nodes_all = $asapi->call('nodes?b');
//$nodes_all = [0 => ['id' => 195]]; $config['debug'] = 1;

echo "Fetching Node list...\n";
$nodes_data = [];
foreach($nodes_all as $node_all) {
    if(@$node_id = $node_all['id']) {
        // Get node data
        $data = $asapi->call("nodes/$node_id");
        if($data !== false) {
            // Build subnets array
            $subnets_data = [];
            $nodes_subnets_data = [];
            foreach ($data['hosts'] as $host) {
                $subnets_data[$host['subnet']['id']] = [
                    'id' => $host['subnet']['id'],
                    'addr' => ip2long($host['subnet']['addr']),
                    'mask' => $host['subnet']['mask'],
                    'type' => $host['subnet']['type'],
                ];
            }
            $subnets_data = array_values($subnets_data);

            // Build hosts array
            $hosts_data = [];
            foreach ($data['hosts'] as $host) {
                $hosts_data[$host['id']] = [
                    'id' => $host['id'],
                    'node_id' => $host['node_id'],
                    'subnet_id' => $host['subnet']['id'],
                    'name' => $host['name'],
                    'fqdn' => $host['fqdn'],
                    'addr' => ip2long($host['addr']),
                ];
            }
            $hosts_data = array_values($hosts_data);

            // Build links array
            $links_data = [];
            foreach ($data['links'] as $link) {
                $links_data[$link['id']] = [
                    'id' => $link['id'],
                    'name' => $link['name'],
                    'type' => $link['type'],
                    'freq' => $link['freq'],
                ];
            }
            $links_data = array_values($links_data);

            // Build Node Data array
            $node_data = [
                'node' => [
                    'id' => $data['id'],
                    'suburb_id' => isset($data['suburb']['id']) ? $data['suburb']['id'] : 0,
                    'user_id' => isset($data['manager']['id']) ? $data['manager']['id'] : 0,
                    'status_id' => isset($data['status']['id']) ? $data['status']['id'] : 0,
                    'name' => $data['name'],
                    'region' => $data['region'],
                    'zone' => $data['zone'],
                    'lat' => $data['lat'],
                    'lng' => $data['lng'],
                    'elevation' => $data['elevation'],
                    'antHeight' => $data['antHeight'],
                    'asNum' => $data['asNum'],
                ],
                'suburb' => [
                    'id' => isset($data['suburb']['id']) ? $data['suburb']['id'] : 0,
                    'name' => isset($data['suburb']['id']) ? $data['suburb']['name'] : 'UNSET',
                    'state' => isset($data['suburb']['id']) ? $data['suburb']['state'] : 'UNSET',
                    'postcode' => isset($data['suburb']['id']) ? $data['suburb']['postcode'] : 0,
                ],
                'user' => [
                    'id' => isset($data['manager']['id']) ? $data['manager']['id'] : 0,
                    'name' => isset($data['manager']['id']) ? $data['manager']['username'] : 'UNSET',
                ],
                'status' => [
                    'id' => isset($data['status']['id']) ? $data['status']['id'] : 0,
                    'name' => isset($data['status']['status']) ? $data['status']['status'] : 'UNKNOWN',
                ],
                'subnets' => $subnets_data,
                'hosts' => $hosts_data,
                'links' => $links_data,
            ];

            // Add to $nodes_data
            $nodes_data[] = $node_data;
        }
    }
}

// Output test data
if($config['debug']) {
    sU::debug($nodes_data);
}
//exit();

// Update database with node data
foreach ($nodes_data as $node_data) {
    echo "Updating " . $node_data['node']['id'] . ' ' . $node_data['node']['name'] . "\n";

    // Node
    $user = User::updateOrCreate(['id' => $node_data['user']['id']], $node_data['user']);
    $status = Status::updateOrCreate(['id' => $node_data['status']['id']], $node_data['status']);
    $suburb = Suburb::updateOrCreate(['id' => $node_data['suburb']['id']], $node_data['suburb']);
    $node = Node::updateOrCreate(['id' => $node_data['node']['id']], $node_data['node']);

    // Subnets
    foreach($node_data['subnets'] as $subnet_data) {
        $subnet = Subnet::updateOrCreate(['id' => $subnet_data['id']], $subnet_data);

        // Associate subnet with Node
        $node = Node::find($node_data['node']['id']);
        $subnet = Subnet::find($subnet_data['id']);
        $node->subnet()->syncWithoutDetaching($subnet);
    }

    // Host
    foreach($node_data['hosts'] as $host_data) {
        $host = Host::updateOrCreate(['id' => $host_data['id']], $host_data);
    }

    // Links
    foreach($node_data['links'] as $link_data) {
        $link = Link::updateOrCreate(['id' => $link_data['id']], $link_data);

        // Associate link with node
        $node = Node::find($node_data['node']['id']);
        $link = Link::find($link_data['id']);
        $node->link()->syncWithoutDetaching($link);
    }
}

echo "Done.\n";
?>
