<?php

require 'utils.class.php';
require 'databasehandle.class.php';
require 'history.class.php';
require 'check.class.php';
require 'config.php';

// DON'T TOUCH
$mysql_login = array(
    'host' => $mysql_hostname,
    'user' => $mysql_username,
    'pass' => $mysql_password,
    'data' => $mysql_database
);

$history = new History($mysql_login);
