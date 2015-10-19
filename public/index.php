<?php

require '../vendor/autoload.php';
include '../config.php';

$statsd = new League\StatsD\Client();

$statsd->configure(array(
    'host' => isset($statsd_host) ? $statsd_host : '127.0.0.1',
    'port' => isset($statsd_port) ? $statsd_port : 8125,
    'namespace' => isset($statsd_namespace) ? $statsd_namespace : null
));

if (isset($_SERVER['REQUEST_URI'])) {
    $filename = substr($_SERVER['REQUEST_URI'], 1);
    $noExt    = str_replace('.gif', '', $filename);
    $parts    = explode('_', $noExt);
    if ( isset($parts[0]) && isset($parts[1]) &&
        (strlen($parts[0]) == 3 || strlen($parts[0]) == 4) && strlen($parts[1]) >= 16
        ) {
        $counters = array (
            'cloudflare.errors',
            'cloudflare.errors.' . $parts[0],
        );
        $statsd->increment($counters, 1, 1);
    }
}

$fp = fopen('../transparent.gif', 'rb');

header('Content-type: image/gif');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
fpassthru($fp);
exit;
