<?php
require_once dirname(__FILE__) . "/../user/user.php";
require_once dirname(__FILE__) . "/../session/session.php";
/**
 * 所有公开访问页面引用
 *     自动记录访客
 * 
 * database:visitors table:visitors format:README.md - 数据库 - 访客数据库
 * 
*/

class HTTPVisitor {
    public $openID;
    public $session;

    public $sessionDB = null;
    public $entityDB  = null;
    public $entityTB  = null;

    public function __construct() {
        $this->session = new HTTPSession();
        if(!$this->session) {
            echo "会话异常,程序退出";
            exit();
        }
    }

    public function prepareDB() {
    $this->sessionDB = new mariaSession(DATABASE_ADDRESS_VISITORS,DATABASE_USERNAME_VISITORS,DATABASE_PASSWORD_VISITORS);
    if($this->sessionDB->existDB(DATABASE_NAME_VISITORS))
        {
            $this->entityDB = $this->sessionDB->exposeDB(DATABASE_NAME_VISITORS);

            if($this->entityDB->existTB(DATABASE_TABLE_NAME_VISITORS))
            {
                $this->entityTB = $this->entityDB->exposeTB(DATABASE_TABLE_NAME_VISITORS);
            }
        }
    }

    public function unloadDB() {
        if($this->sessionDB) {
            $this->sessionDB->close();
        }
    }

    public function pull() {
        if($this->entityTB) {
            $sessionID  = $this->session->sessionID;
            $conditions = "sessionID='$sessionID'";
            $items = $this->entityTB->lookupItem("openID",$conditions);
            if($items && count($items)) {
                /**
                 * TODO : 填充处理
                 */
                $item  = $items[0];
                $this->openID      = $item['openID'];
                return true;
            }
        }
        return false;
    }

    public function push() {
        if($this->entityTB) {
            $sessionID = $this->session->sessionID;
            $sourceIP  = $this->session->sourceIP;
            $date = strval(time());
            $item = new mariaItem(array("sessionID"=>"'"  . $sessionID . "'",
                                        "date"=>"'"       . $date      . "'"));
            return $this->entityTB->appendItem($item);
        }

        return false;
    }

    public function pushID() {
        $this->prepareDB();
        $sessionID  = $this->session->sessionID;
        $conditions = "sessionID='$sessionID'";
        $item = new mariaItem(array("openID"=>"'" . $this->openID . "'"));
        if($this->entityTB->updateItem($item,$conditions)) {
            //echo "visitor=>pushID:OK. openID: " . $this->openID;
            return true ;
        } else {
            //echo "visitor=>pushID:BAD";
            return false;
        }

        $this->unloadDB();
    }
    
    public function active() {
        //echo "LIBPHP.VISITOR.ACTIVE FILE: " . __FILE__;
        $this->prepareDB();

        if(!$this->pull()) {
            $this->push();
        } else {
            //remoteAlert("ID : " . $this->session->sessionID);
        }
        $this->unloadDB();
        return true;
    }
}
?>
