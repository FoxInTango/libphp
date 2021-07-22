<?php
require_once dirname(__FILE__) . "/mariaSession.php";

class mariaDB
{
    private $sessionDB;
    private $nameDB;
    private $currentTB;
    private $result = null;

    const DB_DATABASE_STATUS_UNKNOWN  = 0;
    const DB_DATABASE_STATUS_ACTIVE   = 1;
    const DB_DATABASE_STATUS_INACTIVE = 2;
    
    private $status;

    public function __construct($session,$name=null)
    {
        $this->nameDB    = $name;
        $this->sessionDB = $session;

        if($this->sessionDB)
        {
            $this->sessionDB->receiveDB($this);
        }
    }

    public function setName($name)
    {
        $this->nameDB = $name;
    }

    public function getName()
    {
        return $this->nameDB;
    }

    public function attach($session)
    {
        $this->separate();

        if($session)
        {
            if($session->receiveDB($this))
            {
                $this->sessionDB = $session;
            }
        }
    }

    public function separate()
    {
        $this->session = null;
    }

    public function query($query)
    {
        if($this->sessionDB)
        {
            return $this->sessionDB->query($this,$query);
        }

        return null;
    }

    public function countResult()
    {
        return mysqli_num_rows($result);
    }

    public function fetchResult()
    {
        return mysqli_fetch_array($result);
    }

    public function releaseResult()
    {
        return mysqli_free_result($result);
    }

    public function countTB() {
        $query = "SELECT count(TABLE_NAME) FROM information_schema.TABLES WHERE TABLE_SCHEMA='$this->nameDB';";

        if($this->sessionDB)
        {
            $result = $this->sessionDB->query($this,$query);
/*
            $items = new array();

            while($item = mysqli_fetch_array($result)) {
                array_push($items,$item);
            }

            if(count($items)) {
                return $items[0]['count(TABLE_NAME)'];
            }
*/
            $item = mysqli_fetch_array($result);

            if($item) return $item['count(TABLE_NAME)'];
            return FALSE;
        }

        return FALSE;
    }

    public function existTB($name)
    {
        $query = "SELECT DISTINCT t.table_name,
                  n.SCHEMA_NAME FROM information_schema.TABLES t,
                  information_schema.SCHEMATA n WHERE t.table_name = '$name' 
                  AND n.SCHEMA_NAME = '$this->nameDB';";

        if($this->sessionDB)
        {
            $result = $this->sessionDB->query($this,$query);
            return mysqli_num_rows($result) > 0 ? TRUE :FALSE;
        }

        return FALSE;
    }

    public function createTB($name,$itemArray,$primary=null)
    {
        if(!$itemArray) {
            return false;
        }
        //判断表是否存在
        if($this->existTB($name) || !$this->sessionDB)
        {
            return false;
        }

        $space         = " ";
        $comma         = ",";
        $bracket_left  = "(";   
        $bracket_right = ")";
        $semicolon     = ";";
        $utf8_support  = "default charset=utf8";

        $primary_string = "PRIMARY KEY ";

        $query         = "CREATE TABLE " . $name . $bracket_left; //. $space;

        $itemIndex = 0;
        $itemCount = count($itemArray);
        foreach($itemArray as $item)
        {
            // 字段名，数据类型，属性
            $itemLength = count($item);

            if($itemLength < 2)
            {
                return false;
            }

            for($i = 0;$i < $itemLength;$i ++)
            {
                if($i !== $itemLength -1) {
                $query .= $item[$i] . $space;
                } else { $query .= $item[$i]; } 

                if($i == $itemLength - 1 && $itemIndex !== $itemCount - 1)
                {
                    $query .= $comma;
                }
            }

            $itemIndex ++;
        }

        if(!empty($primary))
        {
            $query .= $comma . $primary_string . $bracket_left . $primary . $bracket_right;
        }

        $query .= $bracket_right . $utf8_support . $semicolon;

        //echo "创建表 : " . $query . PHP_EOL;
        //if($name === 'groupsTB') echo "创建表 $name: " . $query . PHP_EOL;
        return  $this->sessionDB->query($this,$query);
    }

    public function selectTB($name)
    {
        if($this->existTB($name))
        {
            $this->currentTB = $name;

            return true;
        }

        return false;
    }

    public function deleteTB($name)
    {
        if($this->sessionDB && $this->existTB($name))
        {
            $query = "DROP TABLE " . $name;

            $result = $this->sessionDB->query($this,$query);

            return $result;
        }

        return false;
    }

    public function exposeTB($name)
    {
        if($this->existTB($name))
        {
            return new mariaTB($this,$name);
        }

        return null;
    }

    public function alterTB($name,$alter)
    {
        
    }
}
?>
