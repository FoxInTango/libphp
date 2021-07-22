<?php
require_once dirname(__FILE__) . "/mariaSession.php";
require_once dirname(__FILE__) . "/mariaDB.php";
require_once dirname(__FILE__) . "/mariaTB.php";
require_once dirname(__FILE__) . "/mariaItem.php";

/***
 * 检查数据库,数据表可用性
 * options : 库,表,列
 **/

class maria {
    public $sessionDB = null;
    
    public $entityDB  = null;
    public $entityTB  = null;

    public $hostDB;
    public $usernameDB;
    public $passwordDB;
    public $nameDB;
    public $namdTB;

    function connectDB() {
        if(!$this->sessionDB) {
            $this->sessionDB = new mariaSession($this->hostDB,$this->usernameDB,$this->passwordDB);
        }

        if(!$this->entityDB && $this->sessionDB && $this->sessionDB->status() == mariaSession::MARIADB_CONNECTION_STATUS_READY)
        {
             if($this->sessionDB->existDB($this->nameDB))
             {
                 $this->entityDB = $this->sessionDB->exposeDB($this->nameDB);
             }
        }

        if(!$this->entityTB && $this->entityDB)
        {
            if($this->entityDB->existTB($this->nameTB))
            {
                $this->entityTB = $this->entityDB->exposeTB($this->nameTB);
            }
        }

        if($this->sessionDB && $this->entityDB && $this->entityTB) { return true; }
        else return false;
    }

    function disconnectDB() {
        if($this->sessionDB) {
            return $this->sessionDB->close();
        }

        return false;
    }
}

class optionItem 
{
    public $name = null;
    public $type = null;
    public function __construct($name,$type)
    {
        $this->name = $name;
        $this->type = $type;
    }
};

/**
 * examineDB($options)
 * 检查 session 
 */
class optionDB {
    public $nameDB = null;
    public $nameTB = null;
    public $fields = null;

    public $entityDB = null;
    public $entityTB = null;

    public $session = null;
    public function __construct($nameD,$nameT=null,$fieldArray=null) {
        $this->nameDB = $nameD;
        $this->nameTB = $nameT;
        $this->fields = $fieldArray;
    }
};

class undealedItemTB {
    public $name;
    public $undealReason;
};
class undealedItemDB {
    public $name;
    public $dealed;
    public $undealReason;
    public $tables = Array();
};
/** options example
    $fileA = new fieldDB($name,$type);
    $fileB = new fieldDB($name,$type);
    $fields = Array();
    array_push($fields,$fieldA);
    array_push($fields,$fieldB);
    $options = new optionDB($nameDB,$nameTB,$fields);
 */
// $session = new mariaSession($host,$username,$password);

function examineDB($nameDB,$session)
{
    if(!$nameDB || !$session || !strlen($namdDB)) return false;

    if($session && $session->status() == mariaSession::MARIADB_CONNECTION_STATUS_READY && $options->nameDB)
    {
        return $session->existDB($nameDB); 
    }

    return false;
}

function examineTB($nameTB,$nameDB,$session)
{
    if(!$nameTB || !$nameDB || !$session || !strlen($namdTB) || !strlen($namdDB)) return false;

    if($session && $session->status() == mariaSession::MARIADB_CONNECTION_STATUS_READY)
    {
        if($session->existDB($nameDB))
        {
            $entityDB = $session->expose($nameDB);

            if($entityDB) {
                return $entityDB->existTB($nameTB);
            }
            return false;
        }

        return false;
    }
}
/***
 * 确保数据库,数据表可用性
 * options : 库,表,列
 */
function prepareDB($nameDB,$session) {
    if(!$nameDB || !$session || !strlen($namdDB)) return false;

    return $session->createDB($nameDB);
}

function prepareTB($namdTB,$nameDB,$fields) {
    if(!$nameTB || !$nameDB || !$session || !strlen($namdTB) || !strlen($namdDB)) return false;

    if($session && $session->status() == mariaSession::MARIADB_CONNECTION_STATUS_READY)
    {
        if($session->existDB($nameDB))
        {
            $entityDB = $session->expose($nameDB);

            if($entityDB && !$entityDB->exist($namdTB)) {
                if(!$entityDB->exist($namdTB)) { 
                    return $entityDB->createTB($nameTB,$fields,$primary);
                } else {
                    return $entityDB->expostTB($nameTB);
                }
            }
            return null;
        }           

        return null;
    }
}
?>
