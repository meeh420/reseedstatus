<?php

require __DIR__.'/../common.php';
require __DIR__.'/test-checks.php';
require __DIR__.'/test-utils.php';

echo "\n\nRI Checks\n\n";

$test = new Test_Check();
$test->initTests();

echo "\n\nUtils\n\n";

$test = new Test_Utils();
$test->initTests();

