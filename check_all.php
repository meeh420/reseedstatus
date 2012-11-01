<?php

require 'common.php';

$hosts = $history->getHosts();

foreach ($hosts as $host_obj) {
    if (!is_object($host_obj)) {echo "Error fetching host object from database\n";continue;}
    $url = $host_obj->addr;
    echo "Trying $url\n";
    $check = new Check($url,$history,$host_obj->id);
    $ok = $check->initCheck();
    echo "Code: ".$ok[0].". Message: ".$ok[1]."\n";
}


