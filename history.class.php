<?php

class History {
    
    private $db;
    private $hosts=false;
    
    /**
     * 
     * @param array $login
     * @return boolean|object
     */
    public function __construct(array $login) {
        $this->db = new databaseHandle($login['host'],$login['user'],$login['pass'],$login['data']);
        if (!$this->db->ping()) { echo $this->db->error; return false; }
        return $this;
    }
    
    /**
     * 
     * @return array host objects
     */
    public function getHosts() {
        if ($this->hosts==false) {
            $this->db->doQuery('select id,addr from hosts;');
            $this->hosts=$this->db->fetchObjects();
        }
        return $this->hosts;
    }
    
    public function getHistory($host_id) {
        $host_id = intval($host_id);
        if ($host_id<1) return false;
        $sql = sprintf('select h.host_id, h.time, h.msg_id, ho.addr, '.
                    'he.ri_max, he.ri_min, he.msg from history h '.
                    'left join hist_extra he on h.id=he.history_id '.
                    'left join hosts ho on h.host_id=ho.id where '.
                    'h.host_id=%d order by h.id desc limit 168;',$host_id);
        // 168 = a week checked each hour
        $this->db->doQuery($sql);
        $tmp = $this->db->fetchObjects();
        if (is_array($tmp)&&is_object($tmp[0])) return $tmp;
        return false;
    }
    
    public function getLastHistory() {
        $hosts = $this->getHosts();
        $host_array=array();
        foreach ($hosts as $host) {
            $sql = sprintf('select h.host_id, h.time, h.msg_id, ho.addr, '.
                    'he.ri_max, he.ri_min, he.msg from history h '.
                    'left join hist_extra he on h.id=he.history_id '.
                    'left join hosts ho on h.host_id=ho.id where '.
                    'h.host_id=%d order by h.id desc limit 1;',$host->id);
            $this->db->doQuery($sql);
            $tmp = $this->db->fetchObject();
            if (is_object($tmp)) $host_array[] = $tmp;
        }
        if (count($host_array)<1) return false;
        return $host_array;
        //TODO: Unfinished function
    }
    
    /**
     * 
     * @param int $host_id
     * @param int $msg_id
     * @param string $options
     * @return boolean status
     */
    public function addHistory($host_id,$msg_id,$options=false) {
        $input = array(
            'time' => time(),
            'host_id' => intval($host_id),
            'msg_id' => intval($msg_id),
        );
        if (is_array($options)||  is_string($options)) {
            $ok = $this->db->insert('history', $input);
            if (!$ok) return false;
            if (is_array($options)) {
                $ok = $this->db->insert('hist_extra', array(
                    'history_id' => $this->db->insert_id,
                    'ri_max' => $options[1],
                    'ri_min' => $options[0]));
            } else if (is_string($options)) {
                $ok = $this->db->insert('hist_extra', array(
                    'history_id' => $this->db->insert_id,
                    'msg' => $options));
            }
        } else { $ok = $this->db->insert('history', $input); }
        return $ok;
    }
    
    public function cleanup() {
        // 608400 = a week + 1 hour
        $sql = "select he.id from history h right join ".
               "hist_extra he on h.id=he.history_id where time ".
               "< (UNIX_TIMESTAMP()-608400);";
        $this->db->doQuery($sql);
        $tmp = $this->db->fetchObjects();
        if (is_array($tmp)) {
            if (is_object(next($tmp))) {
                foreach ($tmp as $t) {
                    $this->db->doQuery(sprintf("delete from hist_extra where id = %d limit 1;",$t->id));
                }
            }
        }
        $this->db->doQuery("delete from history where time < (UNIX_TIMESTAMP()-608400);");
    }

}
