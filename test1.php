<?php
require_once('vendor/autoload.php');
require_once('../../as-api-creds.secret');

use PHPHtmlParser\Dom;

$api_root = 'https://nodedb.walker.wan/api/';
$cookie_file = 'cookiejar.txt';
$curlopts = [
    //CURLOPT_HEADER => 1,
    //CURLOPT_VERBOSE => 1,

    CURLOPT_SSL_VERIFYPEER => 0,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_FOLLOWLOCATION => 1,

    CURLOPT_COOKIEJAR => $cookie_file,
    CURLOPT_COOKIEFILE => $cookie_file,
];
$cache = new Cache('as-api-cache');

function make_api_call($api_url) {
    global $api_root;
    global $cookie_file;
    global $curlopts;
    global $cache;

    if($cached = $cache->retrieve($api_url)) {
        return $cached;
    }
    else {
        $curl = curl_init();
        curl_setopt_array($curl, $curlopts);
        
        if(do_login()){
            $url = $api_root . $api_url;

            curl_setopt($curl, CURLOPT_URL, $url);
            $result = curl_exec($curl);
            if($result !== false) {
                $dom = new Dom;
                $dom->load($result);

                $tag = $dom->find('meta')[0];
                @$meta_content = $tag->content;
                $meta_redirect = strpos($meta_content, '1;url=');
                if($meta_redirect !== false){
                    unset($result);
                    $redirect_url = substr($meta_content, 6);

                    curl_setopt($curl, CURLOPT_URL, $redirect_url);
                    $result = curl_exec($curl);
                }
            }
        }

        curl_close($curl);

        $cache->store($api_url, $result, 86400);
        return ($result);
    }
}

function do_login() {
    global $cookie_file;
    global $curlopts;
    global $username;
    global $password;
    global $cache;

    if($cache->retrieve('logged_in')) {
        return true;
    }
    else {

        $curl = curl_init();
        curl_setopt_array($curl, $curlopts);

        // Start by getting the _token variable so we can login
        $logged_in = false;
        $url = 'https://members.air-stream.org/login';

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 0);
        $result = curl_exec($curl);
        if ($result !== false) {
            $dom = new Dom;
            $dom->load($result);

            // Check if we're already logged in
            $tag = $dom->find('h1')[0];
            if(@$tag->innerHtml == 'Sorry, the page you are looking for could not be found.') {
                $logged_in = true;
            }

            if(!$logged_in) {
                $tag = $dom->find('input[name=_token]')[0];

                if(!$token = $tag->value) {
                    die('No token found');
                }

                // Attempt to login
                if($username && $password && $token) {
                    curl_setopt_array($curl, [
                        CURLOPT_URL => $url,
                        CURLOPT_POST => 1,

                        CURLOPT_POSTFIELDS => [
                            '_token' => $token,
                            'username' => $username,
                            'password' => $password,
                        ],
                    ]);

                    $result = curl_exec($curl);
                    if($result === false) {
                        die('Not logged in?');
                    }
                    else {
                        $logged_in = true;
                    }
                }
            }
        }

        curl_close($curl);

        if($logged_in) {
            $cache->store('logged_in', true, 3600);
        }
        
        return $logged_in;
    }
}

$nodes = json_decode(make_api_call('nodes?b'), true);
$nodedata = [];

foreach($nodes as $node) {
    $nodedetails = json_decode(make_api_call('nodes/' . $node['id']), true);

    $nodedata[$node['id']] = [
        'id' => $node['id'],
        'name' => $node['name'],
        'lat' => $node['lat'],
        'lng' => $node['lng'],
        'elev' => $nodedetails['elevation'],
        'as' => $nodedetails['asNum'],
    ];
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
