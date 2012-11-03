<?php

class Test_Utils extends Utils {

    public function initTests() {
        $this->testExtractLinks();
        $this->testCheckSSL();
    }

    protected function testExtractLinks() {
        $expected = 10;
        $file = __DIR__."/testfiles/working_index.html";
        echo "Testing Utils::extractLinks(data)\n";
        $files = parent::extractLinks(file_get_contents($file));
        if (count($files)>10) {
            echo "Utils::extractLinks(data) works as expected!\n";
        } else {
            echo "Utils::extractLinks(data) don't work as expected\n";
        }
    }

    protected function testCheckSSL() {
        $host = 'netdb.i2p2.no';
        $expected_expire = 1571706742;
        echo "Testing Utils::checkSSL(data)\n";
        $res = parent::checkSSL($host);
        if ($res['cn']==$host&&$res['expires']==$expected_expire) {
            echo "Utils::checkSSL(data) works as expected!\n";
        } else {
            echo "Utils::checkSSL(data) don't work as expected\n";
        }
    }

}

