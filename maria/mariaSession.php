<?php
require_once dirname(__FILE__) . "/mariaDB.php";

function mariaSelectExamine($result) {

    if(!$result) return FALSE;

    return mysqli_num_rows($result) > 0 ? TRUE :FALSE;
}

class mariaSession
{
    const MARIADB_CONNECTION_STATUS_UNKNOWN   = 0;
    const MARIADB_CONNECTION_STATUS_READY     = 1;
    const MARIADB_CONNECTION_STATUS_UNREACHED = 2;
    const MARIADB_CONNECTION_STATUS_CLOSED    = 3;

    const MARIADB_CONNECTION_STATUS_STRING_UNKNOWN    = "MARIADB_CONNECTION_STATUS_UNKNOWN";
    const MARIADB_CONNECTION_STATUS_STRING_READY      = "MARIADB_CONNECTION_STATUS_READY";
    const MARIADB_CONNECTION_STATUS_STRING_UNREACHED  = "MARIADB_CONNECTION_STATUS_UNREACHED";
    const MARIADB_CONNECTION_STATUS_STRING_CLOSED     = "MARIADB_CONNECTION_STATUS_CLOSED";

    const MARIADB_CONNECTION_METHOD_RESULT_FAILED          = 0;
    const MARIADB_CONNECTION_METHOD_RESULT_OK              = 1;
    const MARIADB_CONNECTION_METHOD_RESULT_DB_EXISTENT     = 2;
    const MARIADB_CONNECTION_METHOD_RESULT_DB_NON_EXISTENT = 3;

    const MARIADB_CONNECTION_METHOD_RESULT_STRING_FAILED          = "MARIADB_CONNECTION_METHOD_RESULT_FAILED";
    const MARIADB_CONNECTION_METHOD_RESULT_STRING_OK              = "MARIADB_CONNECTION_METHOD_RESULT_OK";
    const MARIADB_CONNECTION_METHOD_RESULT_STRING_DB_EXISTENT     = "MARIADB_CONNECTION_METHOD_RESULT_DB_EXISTENT";
    const MARIADB_CONNECTION_METHOD_RESULT_STRING_DB_NON_EXISTENT = "MARIADB_CONNECTION_METHOD_RESULT_DB_NON_EXISTENT";

    private $link;
    private $status;
    private $host;
    private $username;
    private $password;

    public function __construct($host,$username,$password)
    {
        if(!$host || !$username || !$password) return null;
        $this->link = mysqli_connect($host,$username,$password);
        if(!$this->link)
        {
            $this->status = mariaSession::MARIADB_CONNECTION_STATUS_UNREACHED;
            exit;
        }

        $this->host     = $host;
        $this->username = $username;
        $this->password = $password;
        $this->status   = mariaSession::MARIADB_CONNECTION_STATUS_READY;
    }
    /**
     * ??????????????????,???????????????????????????
     * ?????????????????????????????????????????????
     **/
    public function connect($host=null,$username=null,$password=null)
    {
        $theHost = null;
        $theUsername = null;
        $thePassword = null;

        if(!$password) {
            if($this->password && strlen($this->password)) $thePassword = $this->password;
            else return false;
        }
        if(!$username) {
            if($this->username && strlen($this->username)) $theUsername = $this->username;
            else return false;
        }
        if(!$host) {
            if($this->host && strlen($this->host)) $theHost = $this->host;
            else return false;
        }

        if($this->link)
        {
            $this->close();
        }

        $this->link = mysqli_connect($host,$username,$password);

        if(!$this->link)
        {
            $this->status = mariaSession::MARIADB_CONNECTION_STATUS_UNREACHED;
            exit;
        }

        $this->status = MARIADB_CONNECTION_STATUS_READY;
    }

    public function close()
    {
        if(mysqli_close($this->link))
        {
            $this->status = mariaSession::MARIADB_CONNECTION_STATUS_CLOSED;

            return true;
        }

        return false;
    }

    public function status()
    {
        return $this->status;
    }


    /**
     * ??????????????????
     * TODO ??????????????????????????? 
     */
    public function query($entityDB,$query)
    {
        /**
         * 1.???????????????
         * 2.??????????????? 
         */
        if($this->status === mariaSession::MARIADB_CONNECTION_STATUS_READY)
        {
            $nameDB = $entityDB->getName();

            if($nameDB)
            {
                if($this->selectDB($nameDB))
                {
                    return $this->realQuery($query);
                }
            }
        }

        return null;
    }

    /**
      object(mysqli_result) (5) {
          ["current_field"]=>int(0)
          ["field_count"]=>int(1)
          ["lengths"]=>NULL
          ["num_rows"]=>int(0)
          ["type"]=>int(0)
       }
     */
    private function realQuery($query)
    {
        if($this->status === mariaSession::MARIADB_CONNECTION_STATUS_READY)
        {
             //echo $query . PHP_EOL;
             /**
              ??????????????? SELECT???SHOW???DESCRIBE ??? EXPLAIN ???????????????????????? mysqli_result ???????????????????????????????????????????????? TRUE??????????????????????????? FALSE???
              */
             $result = mysqli_query($this->link,$query);

             return $result;
        }
        else
        {
            return null;
        }
    }

    public function createUser($username,$hostname,$password)
    {
        $query = "CREATE USER " . "'" .$username . "'" . "@" . "'" . $hostname . "'" .  "IDENTIFIED BY" . "'" . $password . "'" . ";";
        return $this->realQuery($query);
    }

    public function grantUser($username,$hostname,$nameDB,$nameTB,$permissions,$password) {
        //$query  = "GRANT " . "'" . $permissions . "'" . " ON " . "'" . $nameDB . "'" . "." . "'" .$nameTB . "'" . " TO " . "'" . $username . "'" . '@' . "'" . $hostname . "'" .  " IDENTIFIED BY " . "'" . $password . "'" . " WITH GRANT OPTION;";
        $query  = "GRANT " . $permissions . " ON " . $nameDB . "." . $nameTB . " TO " . $username . '@' . "'" . $hostname . "'" .  " IDENTIFIED BY " . "'" . $password . "'" . " WITH GRANT OPTION;";
        //echo "????????????" . $username . " : " . $query . PHP_EOL; 
        if($this->realQuery($query)) {
            $query = "FLUSH PRIVILEGES;";
            
            return $this->realQuery($query);
        }
        return FALSE;
    }

    public function revokeUser($username,$hostname,$nameDB,$nameTB,$permissions) {
        $query  = "REVOKE " . $permissions . " ON " . $nameDB . "." . $nameTB . " FROM " . $username . '@' . "'" . $hostname . "';";
        if($this->realQuery($query)) {
            $query = "FLUSH PRIVILEGES;";

            return $this->realQuery($query);
        }
        return FALSE;
    }

    public function deleteUser($username)
    {
        $query = "DROP USER " . "'" . $username . "'" . "@" . "'" . $hostname . "'" . ";";
        return $this->realQuery($query);
    }

    public function existDB($nameDB)
    {
        $query  = "SELECT information_schema.SCHEMATA.SCHEMA_NAME FROM information_schema.SCHEMATA where SCHEMA_NAME=";
        $query .= "'" . $nameDB . "';";

        return mariaSelectExamine($this->realQuery($query));
    }

    public function createDB($nameDB)
    {
        if($this->existDB($nameDB))
        {
            return $this->exposeDB($nameDB);
        }

        $query = "CREATE DATABASE IF NOT EXISTS " . $nameDB . " default charset utf8 COLLATE utf8_general_ci;";

        if($this->realQuery($query))
        {
            return new mariaDB($this,$nameDB);
        }

        return null;
    }

    public function deleteDB($nameDB)
    {
        $query = "DROP DATABASE IF EXISTS " . $nameDB . ";";

        return $this->realQuery($query);
    }

    public function selectDB($nameDB)
    {
        if($this->existDB($nameDB))
        {
            if(mysqli_select_db($this->link,$nameDB))
            {
                return TRUE;
            }
            else return FALSE;
        }

        return FALSE;
    }

    public function exposeDB($nameDB)
    {
        if($this->existDB($nameDB))
        {
            return new mariaDB($this,$nameDB);
        }
    }

    /**
     * ????????????????????????????????????
     */
    public function receiveDB($entityDB)
    {
        $nameDB = $entityDB->getName();
        /**
         * ?????????????????????????????????????????? 
         */
        if(!$this->existDB($nameDB))
        {
            if($this->createDB($nameDB))
            {
                return TRUE;
            }
            else return FALSE;
        }
        /**
         
         */
        return TRUE;
    }
}
?>
