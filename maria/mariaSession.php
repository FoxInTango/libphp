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
     * 如果参数为空,则尝试重用缓存参数
     * 如果缓存参数为空则返回连接失败
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
     * 由数据库调用
     * TODO 对查询结果进行判别 
     */
    public function query($entityDB,$query)
    {
        /**
         * 1.选择数据库
         * 2.查询数据库 
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
              针对成功的 SELECT、SHOW、DESCRIBE 或 EXPLAIN 查询，将返回一个 mysqli_result 对象。针对其他成功的查询，将返回 TRUE。如果失败，则返回 FALSE。
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
        //echo "授权用户" . $username . " : " . $query . PHP_EOL; 
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
     * 接收依附过来的数据库对象
     */
    public function receiveDB($entityDB)
    {
        $nameDB = $entityDB->getName();
        /**
         * 如果数据库不存在，创建数据库 
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
