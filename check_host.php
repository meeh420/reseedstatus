#!/usr/bin/php -q
<?php

if ($argc<=1) {
    die("Usage: ".$argv[0]." <host>\n");
}

$url = $argv[1];

require 'common.php';

echo "Trying $url\n";
$check = new Check($url);
$ok = $check->initCheck();
echo "Code: ".$ok[0].". Message: ".$ok[1]."\n";
