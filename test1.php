<?php
require_once('vendor/autoload.php');
require_once('include/config.php');
require_once('ASAPI.php');

$asapi = new ASAPI([
    'username' => $username,
    'password' => $password,
]);

$nodes = $asapi->call('nodes?b');
$nodedata = [];

$i = 0;
foreach($nodes as $node) {
    //echo $i . " " . $node['id'] . " " . $node['name'] .  "\n";

    if(isset($node['id'])) {
        $nodedetails = $asapi->call('nodes/' . $node['id']);

        $nodedata[$node['id']] = [
            'id' => $node['id'],
            'name' => $node['name'],
            'lat' => $node['lat'],
            'lng' => $node['lng'],
            'elev' => $nodedetails['elevation'],
            'as' => $nodedetails['asNum'],
        ];
    }

    $i++;
}
?>
<html>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Lat</th>
            <th>Lng</th>
            <th>Elevation</th>
            <th>AS</th>
        </tr>
<?php foreach($nodedata as $node) { ?>
        <tr>
            <td><?= $node['id'] ?></td>
            <td><?= $node['name'] ?></td>
            <td><?= $node['lat'] ?></td>
            <td><?= $node['lng'] ?></td>
            <td><?= $node['elev'] ?></td>
            <td><?= $node['as'] ?></td>
        <tr>
<?php } ?>
    </table>
</html>
