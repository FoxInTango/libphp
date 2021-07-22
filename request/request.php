<?php
require_once dirname(__FILE__) . "/../visitor/visitor.php";
require_once dirname(__FILE__) . "/../permission/permission.php";
?>
<?php
class HTTPResponse {
    public $status;

    public function flush() {
        echo json_encode($this);
    }
}

class HTTPRequest {
    public $visitor = null;
    public $host    = null;
    public $url     = null;
    public $options = null;
    public $status  = null;

    public $sessionDB = null;
    public $entityDB  = null;
    public $entityTB  = null;

    public function __construct() {
        $this->visitor = new HTTPVisitor();
        $this->host    = $_SERVER["HTTP_HOST"];
        $this->url     = $_SERVER["HTTP_HOST"] . $_SERVER['PHP_SELF'];
        $this->options = $_SERVER["QUERY_STRING"];
    }

    public function prepareDB() {
        $this->sessionDB = new mariaSession(DATABASE_ADDRESS_REQUESTS,DATABASE_USERNAME_REQUESTS,DATABASE_PASSWORD_REQUESTS);
        
        if(!$this->sessionDB) {
            echo "请求数据库连接失败 ";

            return false;
        }

        if($this->sessionDB->existDB(DATABASE_NAME_REQUESTS))
        {
            $this->entityDB = $this->sessionDB->exposeDB(DATABASE_NAME_REQUESTS);
            if(!$this->entityDB) {
                echo "数据库: " . DATABASE_NAME_REQUESTS . "查找失败";
                return false;
            }

            if($this->entityDB->existTB(DATABASE_TABLE_NAME_REQUESTS))
            {
                $this->entityTB = $this->entityDB->exposeTB(DATABASE_TABLE_NAME_REQUESTS);

                if(!$this->entityTB) {
                    echo "数据表: " . DATABASE_TABLE_NAME_REQUESTS . "查找失败";
                    return false;
                }
            }
        }
    }

    public function unloadDB() {
        if($this->sessionDB) {
            $this->sessionDB->close();
        }
    }

    /**
     * 权限过滤 并设置请求结果状态
     */
    public function pass() {
        if($this->visitor->session->remoteOptions) {
            /**
             * 公共功能操作 用户注册/用户登录/用户注销/用户登出
             */
            $options = $this->visitor->session->remoteOptions;
            //remoteAlert("PASS.");

            /*
            if($options && isset($options->action)) {
                remoteAlert("ACTION.");
                if($options->action === "signup" || $options->action === "signin" || $options->action === "signout" || $options->action === "destroy") {
                    remoteAlert("TO USER.");
                    
                    require_once dirname(__FILE__) . "/../user/user.php";
                    $user = new User($options,$this->visitor);
                    $user->active();
                    exit();
                    $this->status = 'PASSED';
                    return true;
                }
            */
            if ($options && isset($options->api)) {
                $this->status = 'PASSED';
                return true;
            } else if ($options && isset($options->page)) {
                $this->status = 'PASSED';
                return true;
            } else {
                $this->status = 'FAILED';
                $response = new HTTPResponse();
                $response->flush();
                exit();
            }
        }
        return true;
    }

    public function push() {
        if($this->entityTB) {
            $session  = $this->visitor->session;
            $openID   = $this->visitor->openID;
            $sourceIP = $session->sourceIP;
            $url      = $this->url;
            $options  = $this->options;
            $status   = $this->status;
            $time = strval(time());
            $item = new mariaItem(array("openID"=>"'"   . $openID    . "'",
                                        "sourceIP"=>"'" . $sourceIP  . "'",
                                        "url"=>"'"      . $url       . "'",
                                        "options"=>"'"  . $options   . "'",
                                        "status"=>"'"   . $status    . "'",
                                        "time"=>"'"     . $time      . "'"));
            echo "request pushed ." . __FILE__;
            return $this->entityTB->appendItem($item);
        }
        //echo "request push failed ." . __FILE__ . __LINE__;
        return false;
    }

    public function active() {
        if($this->visitor) {
           $this->visitor->active();
        }

        $this->prepareDB();
        $passed = $this->pass();
        $this->push();
        $this->unloadDB();
        if($passed) {
            return;
        }else {
            /** 根据访问类型,返回相应内容,或导航至相应页面*/
            exit();
        }
    }
}
?>
