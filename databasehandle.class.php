<?php


class databaseHandle extends mysqli {
	
	private $connection;
	private $result;
	private $query;
	private $insertID;

	/**
	 * databaseHandle::databaseHandle()
	 * 
	 * @param mixed $hostname
	 * @param mixed $username
	 * @param mixed $password
	 * @param mixed $database
	 * @return none
	 */
	public function databaseHandle($hostname,$username,$password,$database) {
		$this->connection = $this->connect($hostname,$username,$password,$database);
	}

	/**
	 * databaseHandle::doQuery()
	 * 
	 * @param mixed $sql
	 * @param mixed $values
	 * @return object $result
	 */
	public function doQuery($sql, $values = null) {
		if(!is_null($values)) {
	        $sql = $this->parseSQL($sql, $values);
        }
        $this->result = $this->query($sql);
        return $this->result;
	}

	/**
	 * databaseHandle::fetchArray()
	 * 
	 * @param bool $result
	 * @return array $result
	 */
	public function fetchArray($result = false) {
		$result = !$result ? $this->result : $result;
		return $result->fetch_array();
	}

	/**
	 * databaseHandle::fetchObject()
	 * 
	 * @param bool $result
	 * @return object $result
	 */
	public function fetchObject($result = false) {
		$result = !$result ? $this->result : $result;
		return $result->fetch_object();
	}
	
	/**
	 * databaseHandle::numRows()
	 * 
	 * @param bool $reslut
	 * @return int $num
	 */
	public function numRows($reslut = false) {
		$result = !$result ? $this->result : $result;
		return $result->num_rows;
	}
	
	/**
	 * databaseHandle::fetchObjects()
	 * 
	 * @param bool $result
	 * @return array $objects
	 */
	public function fetchObjects($result = false) {
		$result = !$result ? $this->result : $result;
		$return = array();
		while($r = $result->fetch_object()) {
			$return[] = $r;
		}
		return $return;
	}
	
	/**
	 * databaseHandle::fetchArrays()
	 * 
	 * @param bool $result
	 * @return array $arrays
	 */
	public function fetchArrays($result = false) {
		$result = !$result ? $this->result : $result;
		$return = array();
		while($r = $result->fetch_array()) {
			$return[] = $r;
		}
		return $return;
	}
	/**
	 * databaseHandle::insert()
	 * 
	 * @param mixed $table
	 * @param mixed $input
	 * @return bool 
	 */
	public function insert($table,$input) {
        $tmp1='';$tmp2='';
		foreach($input as $key => $value) {
			$tmp1 .= "`".$key."`,";
			$tmp2 .= "'".$value."',";
		}
		$tmp1 = substr($tmp1, 0, -1);
		$tmp2 = substr($tmp2, 0, -1);
		$result = $this->query("INSERT INTO `".$table."` (".$tmp1.") VALUES (".$tmp2.");");
		$this->insertID = $this->insert_id;
		if (!$result) return false;
		return true;
	}
	
	/**
	 * databaseHandle::delete()
	 * 
	 * @param mixed $table
	 * @param string $where
	 * @param mixed $values
	 * @return bool
	 */
	public function delete($table, $where = '', $values = null) {
        $sql = 'DELETE FROM `' . $table . '`';
        if (!empty($where)) {
            $sql .= ' WHERE ' . $where;
        }
        
        return $this->query($sql, $values);
    }
		
	/**
	 * databaseHandle::insertID()
	 * 
	 * @return int
	 */
	public function insertID() {
		return $this->insetID;
	}
	
	/**
	 * databaseHandle::refreshHandle()
	 * 
	 * @return none
	 */
	public function refreshHandle() {
		$this->insertID = null;
		$this->query = null;
		$this->result = null;
	}
	
	/**
	 * databaseHandle::result()
	 * 
	 * @param bool $result
	 * @param integer $row
	 * @return object $result
	 */
	public function result($result = false, $row = 0) {
        $result = !$result ? $this->result : $result;		
        return $result;
    }
	
	/**
	 * databaseHandle::close()
	 * 
	 * @return none
	 */
	public function close() {
		$this->close();
	}
	
	/**
	 * databaseHandle::free()
	 * 
	 * @param bool $result
	 * @return bool
	 */
	public function free($result = false) {
		$result = !$result ? $this->result : $result;
		return $result->free();
	}
	
    /**
     * databaseHandle::parseSQL()
     * 
     * @param mixed $sql
     * @param mixed $values
     * @return mixed $string
     */
    private function parseSQL($sql, $values) {
        if (is_array($values)) {
            $sql = str_replace('?', '%s', $sql);
            
            // Runs quoteSmart() on every value in $values, and pulling them togeter
            $vars = $this->quoteSmart($values);
            array_unshift($vars, $sql);
            
            // Replacing all values in the query
            $sql = call_user_func_array('sprintf', $vars);
        }
        
        // If it's only one value we're going to check
        else {
            $value = $this->quoteSmart($values);
            // Replacing the value back to the SQL
            $sql = str_replace('?', $value, $sql);
        }

        return $sql;
    }
	
    /**
     * databaseHandle::quoteSmart()
     * 
     * @param mixed $value
     * @return mixed $string
     */
    private function quoteSmart($value) {
        if (is_array($value)) {
            return array_map(array($this, 'quoteSmart'), $value);
        }
        if (get_magic_quotes_gpc()) {
            $value = stripslashes($value);
        }
        //Change decimal values from , to . if applicable
        if (is_numeric($value) && strpos($value, ',') !== false ) {
            $value = str_replace(',', '.', $value);
        }
        if ($value === 'NULL') {
            $value = 'NULL';
        }
        // Quote if not integer or null
        elseif (!is_numeric($value)) {
            $value = '\'' . mysql_real_escape_string($value) . '\'';

        }        
        return $value; 
    }

}


?>


