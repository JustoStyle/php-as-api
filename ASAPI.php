<?php
use PHPHtmlParser\Dom;

class ASAPI
{
    private $api_root = 'https://nodedb.walker.wan/api';
    private $cookie_file = '/tmp/as-api-cookie.txt';
    private $curlopts;
    private $ch;
    private $cache;
    private $config;

    public function __construct($config)
    {
        $this->config = $config;

        $this->curlopts = [
            /* Enable for further debugging */
            //CURLOPT_HEADER => 1,
            //CURLOPT_VERBOSE => 1,

            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_TIMEOUT => 60,

            CURLOPT_COOKIEJAR => $this->cookie_file,
            CURLOPT_COOKIEFILE => $this->cookie_file,
        ];

        $this->ch = curl_init();
        curl_setopt_array($this->ch, $this->curlopts);

        $this->cache = new Cache('as-api-cache');
    }

    public function call($api_url, $nocache = false) {
        $api_call = $this->api_root . '/' . $api_url;

        $is_cached = [
            false,
        ];

        // Retrieve from local cache is possible
        if(!$nocache && $cached = $this->cache->retrieve($api_url)) {
            $json = $cached;

            $is_cached = [
                true,
            ];
        }
        else {
            if($this->doLogin()) {
                curl_setopt($this->ch, CURLOPT_URL, $api_call);
                curl_setopt($this->ch, CURLOPT_POST, 0);
                
                $result = curl_exec($this->ch);

                if($result !== false) {
                    $dom = new Dom;
                    $dom->load($result);

                    $tag = $dom->find('meta')[0];
                    @$meta_content = $tag->content;
                    $meta_redirect = strpos($meta_content, '1;url=');
                    if($meta_redirect !== false){
                        unset($result);
                        $redirect_url = substr($meta_content, 6);

                        curl_setopt($this->ch, CURLOPT_URL, $redirect_url);
                        $result = curl_exec($this->ch);
                    }
                }
            }

            // Check for errors
            // Error within CURL response
            $result_json = json_decode($result, true);
            if(@$result_json['error']) {
                $result = false;
            }

            // Error within HTML body
            $dom = new Dom;
            $dom->load($result);

            $tag = $dom->find('html');
            if(sizeof($tag)) {
                $result = false;
            }

            // If this is a request for a node, and we have some data for it, return the small amount we have
            if ($result === false && strpos($api_url, 'nodes/') !== false) {
                $node_id = substr($api_url, 6);

                // Get full node list and pluck out the one we want
                $nodes_data = $this->call('nodes?b', $nocache);
                foreach ($nodes_data as $node_data) {
                    if(@$node_data['id'] == $node_id) {
                        $node = [
                            'id' => $node_id,
                            'name' => $node_data['name'],
                            'region' => 'UNKNOWN',
                            'zone' => 'UNKNOWN',
                            'lat' => $node_data['lat'],
                            'lng' => $node_data['lng'],
                            'elevation' => NULL,
                            'antHeight' => NULL,
                            'asNum' => NULL,
                            'ord' => $node_data['ord'],
                            'zabbixGroudId' => NULL,
                            'suburb' => [],
                            'manager' => [],
                            'status' => [],
                            'hosts' => [],
                            'devices' => $node_data['devices'],
                            'links' => $node_data['links'],
                            'subnets' => [],
                        ];

                        $result = json_encode($node);
                        break;
                    }
                }
            }

            // Record in cache for a dat + random part of an hour
            $this->cache->store($api_url, $result, 86400 + rand(0, 3600));

            $json = $result;
        }

        if($data = json_decode($json, true)) {
            $data['is_cached'] = $is_cached;

            return $data;
        }
        else {
            return false;
        }
    }

    private function doLogin()
    {
        if($this->cache->retrieve('logged_in')) {
            return true;
        }
        else {
            // Start by getting the _token variable so we can login
            $logged_in = false;
            $url = 'https://members.air-stream.org/login';

            curl_setopt($this->ch, CURLOPT_URL, $url);
            curl_setopt($this->ch, CURLOPT_POST, 0);
            $result = curl_exec($this->ch);
            if ($result !== false) {
                $dom = new Dom;
                $dom->load($result);

                // Check if we're already logged in
                $tag = $dom->find('h1')[0];
                if(@$tag->innerHtml == 'Sorry, the page you are looking for could not be found.') {
                    $logged_in = true;
                }

                if(!$logged_in) {
                    // Start by getting the _token variable so we can login
                    $tag = $dom->find('input[name=_token]')[0];

                    if(!$token = $tag->value) {
                        die('No token found');
                    }

                    // Attempt to login
                    if($this->config['username'] && $this->config['password'] && $token) {
                        curl_setopt_array($this->ch, [
                            CURLOPT_URL => $url,
                            CURLOPT_POST => 1,

                            CURLOPT_POSTFIELDS => [
                                '_token' => $token,
                                'username' => $this->config['username'],
                                'password' => $this->config['password'],
                            ],
                        ]);

                        $result = curl_exec($this->ch);
                        if($result === false) {
                            die('Not logged in?');
                        }
                        else {
                            $logged_in = true;
                        }
                    }
                }
            }

            if($logged_in) {
                $this->cache->store('logged_in', true, 3600);
            }
            
            return $logged_in;
        }
    }
}
