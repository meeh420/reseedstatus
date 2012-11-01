<?php

require __DIR__.'/../common.php';

/*
Testing for checks
*/

class Check_Test extends Check {

    public function initTests() {
        $this->testRI_isNew();
        $this->testRI_isThreedays();
        $this->checkFrontPage();
    }

    public function checkFrontPage() {
        echo "Testing html link errors\n";
        $expected = -2;
        $this->frontcontent = '<html></html>';
        $ok = $this->checkRILinks();
        $res  = $ok[0];
        echo "Expected: -2 Got: $res\n";
    }

    public function testRI_isNew() {

    }

    public function testRI_isTwodays() {
        // TODO: Find a way to test this
    }

    public function testRI_isThreedays() {
        echo "Testing three days+ old RI file\n";
        $expected_result = '-6';
        $this->tmpdir = __DIR__.'/oldri/';
        $this->pyric = __DIR__.'/../ridate/ripubd.py';
        $res = $this->pyCheckRI();
        if ($res['max']<(time()-259200)) {
            $result = -6;
        } else { $result = 'Something else'; }
        echo "Expected: -6 Got: $result\n";
    }
}


$test = new Check_Test();
$test->initTests();


