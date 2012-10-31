<?php

class Check {
    
    private $hostname;
    private $pyric,$numri,$id;
    private $history=false;
    private $frontcontent=false;
    private $tmpdir,$rilinks,$filelist;


    public function Check($host = false,$history=false,$id=false,$tmp=false) {
        if (substr($host,-1)!='/') $host = $host.'/';
        if ($host) $this->hostname = $host;
        if ($history) $this->history=$history;
        $this->id = $id;
        // Python script read folders, so best to separate folders
        if ($tmp) { $this->tmpdir = $tmp; } else { $this->tmpdir=__DIR__.'/tmp/'.md5($host).'/'; }
        // Create dir if not exists
        if (!is_dir($this->tmpdir)) { mkdir($this->tmpdir); }
        $this->pyric = __DIR__.'/ridate/ripubd.py';
    }
    
    public function setHost($host) {
        $this->hostname = $host;
    }
    
    public function initCheck($num_ri=30) {
        $this->numri=$num_ri;
        $ok = $this->checkFrontpage();
        if ($ok!=0) return array($ok,'Network problems or problems loading front page');
        $ok = $this->checkRILinks();
        if ($ok[0]!=0) { $this->cleanup(); return array($ok[0],$ok[1]); }
        $ok = $this->checkRIs();
    }
    
    protected function checkFrontpage() {
        $content = Utils::curl_request($this->hostname);
        // Checks for 204 or fail
        if (strlen($content)<100||$content===false) {
            // Retry 5 times before returning error
            for ($i=0;$i<5;$i++) {
                $content = Utils::curl_request($url);
                if (strlen($content)>100||$content!==false) break;
            }
            if ($content===false) {
                // No content returned or network error
                if (is_object($this->history)) $this->history->addHistory($this->id,-5);
                return -5;
            }
            if (strlen($content)<100) {
                // Too little content returned
                if (is_object($this->history)) $this->history->addHistory($this->id,-1);
                return -1;
            }
        }
        $this->frontcontent = $content;
        // All ok
        return 0;
    }
    
    protected function checkRILinks() {
        $find = '<a href="routerInfo-';
        if (strstr($this->frontcontent,$find)===FALSE) {
            // Could not find <a href="routerInfo- in content from server
            if (is_object($this->history)) $this->history->addHistory($this->id,-2);
            return array(-2,0);
        }
        $this->rilinks=Utils::extractLinks($this->frontcontent);
        $files = array();
        
        foreach (array_rand($this->rilinks,  $this->numri) as $urlid) {
            $t = Utils::downloadToFile($this->hostname.$this->rilinks[$urlid], $this->tmpdir);
            // TODO Rewrite better code
            if ($t!=false) { if (!strstr(file_get_contents($t),'html')||$t!='..'||filesize($t)>220) $files[] = $t; }
        }
        // if problems with dat files, return -3, $t should be the last file it tried to fetch.
        if (count($files)<5) {
            if (is_string($t)) {
                $msg=htmlspecialchars(file_get_contents($t));
            } else {
                $msg = 'It could be 404 or 500 from server.';
            }
            if (is_object($this->history)) $this->history->addHistory($this->id,-3,$msg);
            return array(-3,$msg);
        }
    }
    
    protected  function checkRIs() {
        $res = $this->pyCheckRI();
        if ($res===false) {
            if (is_object($this->history)) $this->history->addHistory($this->id,-3);
            return array(-3,'Couldn\'t decode downloaded RIs.');
        }
        // 3 days check
        if ($res['max']<(time()-259200)) {
            if (is_object($this->history)) $this->history->addHistory($this->id,-6,array($res['min'],$res['max']));
            return array(-6,'RIs are over three days old.');
        } else if ($res['max']<(time()-172800)) {
            // routerInfo file older than two days
        }
    }
    
    protected function pyCheckRI($debug=false) {
        // Example: ('Wed Oct 31 03:11:06 2012', 1351653066.758)
        $cmd = $this->pyric.' '.$this->tmpdir;
        if ($debug) echo "debug: $cmd\n";
        $ok = exec($cmd,$output);
        $s = implode("\n",$output);
        $matches = array();
        $dates = array();
        $t = preg_match_all('/\'(.*?)\'/s', $s, $matches);
        foreach ($matches[0] as $ndate) {
            $dates[] = strtotime(str_replace("'",'',$ndate));
        }
        return array(
            'max' => max($dates),
            'min' => min($dates)
        );
    }
    
    protected function cleanup() {
        // Cleanup downloaded stuff etc.
    }
}

