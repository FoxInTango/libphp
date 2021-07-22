<?php
/**
 * 所有公开访问页面引用
 *     自动进行会话记录
 * 
 * database:sessions table:sessions format:README.md - 数据库 - 会话数据库
 * 首次访问:
 * $host - $host[token]?screen
 * $host?asset  -|
 * $host?reboot - 
 * $host?screen
 * $host?options
 * $host[token]
 * $host[token]?reboot
 * $host[token]?screen
 * $host[token]?options
 * 会话类型:
 *     首次访问
 *     初始访问
 *     正常访问
 *     超时访问
 * 流程 - 检查是否已有令牌 - 根据令牌和IP拉取会话记录 - 超时检测 --------------------------------- 更新激活时间 - 访问继续
 *            |                  |                          |                         ^
 *            N                  N                          N                         |
 *            |                  |                          |                         |
 *        下发令牌           下发令牌                   清理记录                      |
 *            |                  |                      下发令牌                      |
 *            |                  |                          ｜                        |
 *            ------------------------------------------------------初始化重定向 - 更新屏幕信息
*/

/*** 调试配置 *************/
ini_set("display_errors", "On");
ini_set("error_reporting",E_ALL);
ini_set('display_startup_errors',1);
error_reporting(-1);

require_once dirname(__FILE__) . "/token.php";
require_once dirname(__FILE__) . "/../../config/config.php";
require_once dirname(__FILE__) . "/../remote/remote.php";
require_once dirname(__FILE__) . "/../maria/maria.php";
require_once dirname(__FILE__) . "/../geoip/geoip.php";
require_once dirname(__FILE__) . "/../permission/permission.php";

define("LIBPHP_SESSION_STATUS_VOID" ,0);
define("LIBPHP_SESSION_STATUS_READY",1);
/**
 * 设置特殊通行域名机制
 *     1,配置文件
 *     2,数据库
 */

function specialDomain($domain,$specials) {
    if(array_key_exists($domain,$specials))
        return true;
    return false;
}

/**
 * 设置特殊通行令牌机制
 */
function specialToken($token) {
    return true;
}

class HTTPSession {
    public $sessionID      = null;
    public $sourceIP       = null;
    public $sourcePort     = null;
    public $sessionTime    = null;
    public $sessionActive  = null;
    public $sessionTimeout = null;
    public $sessionAgent   = null;
    public $sessionToken   = null;
    public $sessionScreen  = null;
    public $sessionAddress = null;

    public $sessionDB = null;
    public $entityDB  = null;
    public $entityTB  = null;

    public $sessionGEO = null;
    public $permissionMap = null;

    public $remoteToken = null;
    public $remoteOptions = null;

    public $SYSTEM_SPECIAL_DOMAINS = array("https://ssl.saosaole.com.cn");
    function __construct(){
        /*
        $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
        $uri  = parse_url('http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);
        $path = $uri['path'];
        file_put_contents('/home/www/logs/request.log', "The SELF : " . $_SERVER['PHP_SELF'] . "\n",FILE_APPEND);
        if($uri['path'] !== "/index.php") {
            require_once dirname(__FILE__) . "/null.php";
            exit();
        }*/

        /**
         if(specialDomain($_SERVER['HTTP_HOST'],$this->SYSTEM_SPECIAL_DOMAINS)) {
            echo "SYSTEM SPECIAL DOMAIN" .  $_SERVER['HTTP_HOST'] . "<br>";
            return;
         }
         */
        //if(isset($_GET['token'])) {
        if(isset($_COOKIE['token'])) {
            $this->remoteToken = $_COOKIE['token'];
        }

        //echo "参数 : options 解析错误.程序退出";
        /**TODO
         * 1,[*]根据IP地址设置时区
         * 2,[o]解决curl访问及scm访问时的鉴别问题
         *      方案:对scm请求进行过滤
         * 3,[o]解决同一局域网不同终端访问鉴别问题
         * 4,[o]token机制采取url附加方式
         * 5,[o]解决重复pull的问题
         *      原因:参考<7>
         * 6,[o]微信浏览器图片访问异常 options=\{%22api%22:%22img%22,%22path%22:%22/images/scan.png%22\}
         *      原因:微信缓存同一图片成功下载请求,导致按照原来方式进行请求
         * 7,[o]网页多次reboot /home/www/logs/request.log
         *      原因:sessionActive字符串转换方式错误
         * 8,[o]排查push 和 active之间的时间差导致的二次访问问题
         *      原因:问题不存在,参考<7>
         */
        date_default_timezone_set('Asia/Shanghai');

        if(!$this->connectDB()) {
            echo "会话数据库初始化异常 <br>";
            exit();
        }
        /** 资源请求
         * 判断访问类型,是否为初始化阶段访问:1,请求初始化页面 2,上报初始化信息
         **/
        if(isset($_GET['asset'])){
            $option=$_GET['asset'];
            file_put_contents("/home/www/logs/asset.log",'option : '."$option" . "    ",FILE_APPEND);
            $path = $_GET['path'];
            file_put_contents("/home/www/logs/asset.log","path : " . "$path" . "\n",FILE_APPEND);
            switch($option) {
                case 'image':
                {
                    $path = $_GET['path'];
                    require_once dirname(__FILE__) . "/../image/image.php";
                    //$path = "/home/www/ssl/www" . $path; 
                    $path = $_SERVER['DOCUMENT_ROOT'] . "$path";
                    file_put_contents("/home/www/logs/image.log","$path" . "\n",FILE_APPEND);
                    $image = new image($path);
                    if($image) {
                        $image->flush();
                    } else {
                    }
                }break;
                case 'css':{
                require_once dirname(__FILE__) . "/../file/file.php";
                $path = $_GET['path'];
                $path = $_SERVER['DOCUMENT_ROOT'] . "$path";
                file_put_contents("/home/www/logs/css.log","$path" ."\n",FILE_APPEND);
                header('Content-type: text/css');  
                sendCSS($path);
                }break;
                case 'js':{
                require_once dirname(__FILE__) . "/../file/file.php";
                $path = $_GET['path'];
                $path = $_SERVER['DOCUMENT_ROOT'] . "$path";
                file_put_contents("/home/www/logs/js.log","$path" . "\n",FILE_APPEND);
                sendJS($path);
                }break;
                case 'jsmap':{
                require_once dirname(__FILE__) . "/../file/file.php";
                $path = $_GET['path'];
                $path = $_SERVER['DOCUMENT_ROOT'] . "$path";
                file_put_contents("/home/www/logs/jsmap.log","$path" . "\n",FILE_APPEND);
                sendJS($path);
                }break;
                default:break;
            }
            exit();
        }

        if(isset($_GET['screen'])) {
            $host = $_SERVER['HTTP_HOST'];
            $screen = json_decode($_GET["screen"]);
            if($this->pushScreen($screen)) {
                "屏幕信息更新成功";
            }else {
                echo "屏幕信息更新失败";
            }
            
            if(isset($_GET['options'])) {
                header("Location: https://$host" . "?options=" . $_GET['options']);
            } else {
                header("Location: https://$host");
            }
            exit();
        }

        if(isset($_GET['reboot'])) {
            require_once dirname(__FILE__) . "/reboot.php";
            exit();
        }

        if(isset($_GET['option'])){
            $option=$_GET['option'];
            switch($option) {
                case 'image':
                {
                    $path = $_GET['path'];
                    require_once dirname(__FILE__) . "/../image/image.php";
                    //$path = "/home/www/ssl/www" . $path;
                    $path = $_SERVER['DOCUMENT_ROOT'] .'/'. $path;
                    $image = new image($path);
                    if($image) {
                        $image->flush();
                    } else {
                    }
                }break;
                case 'excelL':{
                    $action = "update";
                    $target = "company";
                    $name   = "some one linke you";
                    $token  = "null";
                    if(isset($_COOKIE['token'])) { $token = $_COOKIE['token']; } else {
                        setcookie("token","excelToken",time() + SSL_SESSION_TIMEOUT_DEFAULT);//单位:秒
                    }
                    echo '{"action":"' . $action . '","target":"' . $target . '","name":"' . $name . '","token":"' . $token . '"}';
                    return;
                }break;
                default:break;
            }
            exit();
        }

        if(isset($_GET['options'])) {
            if($this->remoteToken) $this->active();
            $this->remoteOptions = json_decode($_GET['options']);
            if($this->remoteOptions) {
                if(isset($this->remoteOptions->action)) {
                    switch($this->remoteOptions->action) {
                    case "reboot":{
                        require_once dirname(__FILE__) . "/reboot.php";
                        exit();
                    }break;
                    case "screen":{
                        /**
                         * options={'action':'reboot','screen':{'width':'110','height':'110'}}"
                         */
                        $screen = $this->remoteOptions->screen;
                
                        if($this->pushScreen($screen)) {
                        }else {
                        }
                        /**
                         * 导航至用户访问页面
                         */
                        $host = $_SERVER['HTTP_HOST'];
                        if($this->pull()) {
                            header("Location: https://$host" . "?options=" . $this->remoteOptions); //?token=$this->remoteToken");
                        }
                        exit();
                    }break;
                    case "scm":{
                        return;
                    }break;
                    default:{ return ;}break;
                  }
                }
                /***/
            } else {
                remoteAlert("参数 : options 解析错误.程序退出");
                exit();
            }
        }
        /**
         * TODO:清理超期会话数据
         *      sessionID创建时重复排除机制
         */
        if($this->remoteToken && strlen($this->remoteToken)) {
        //echo "令牌存在,尝试拉取会话记录 <br>";
            //remoteAlert("令牌存在,尝试拉取会话记录");
            if($this->pull()) {
            //echo "会话记录拉取成功,进行超时检测 <br>";
                //remoteAlert("会话记录拉取成功,进行超时检测");
                /**
                 * 超时检测
                 */
                $now    = time();
                $active = intval($this->sessionActive);
                if($this->remoteToken === $this->sessionToken && ($now - $active) < SSL_SESSION_TIMEOUT_DEFAULT) {
                //echo "超时检测通过,激活会话,访问继续 <br>";
                    /**
                     * 会话激活,访问继续
                     */
                    //remoteAlert("超时检测通过,激活会话,访问继续");
                    //remoteAlert("count(TABLE_NAME) : " . $this->entityDB->countTB());
                    $this->active();
                    return;
                } else {
                //echo "会话超时,清理会话记录,重建会话,初始化访问 <br>";
                    //remoteAlert("会话超时,清理会话记录,重建会话,初始化访问");
                    $this->clear();
                    $this->build();
                    $this->push();
                    //$this->active();
                    //$this->reboot("1,Session timeout ,clear session record,rebuild session,initialize request. now - '$now',active - '$active' ");
                    //$options = array("action"=>"reboot");
                    $this->reboot($this->remoteOptions,null);
                    exit();
                }
            } else {
              //echo "会话拉取失败,构建会话,记录会话,初始化访问 <br>";
                //remoteAlert("会话拉取失败,构建会话,记录会话,初始化访问");
                $this->build();
                $this->push();
                //$this->active();
                //$this->reboot("2,Pull session record failed,build session,push session,initialize request.");
                //$options = array("action"=>"reboot");
                //$this->reboot(json_encode($options));
                $this->reboot($this->remoteOptions,null);
                exit();
            }
        } else {
        //echo "令牌不存在,构建会话,记录会话,初始化会话 <br>";
            //remoteAlert("令牌不存在,构建会话,记录会话,初始化会话 <br>");
            //echo "令牌不存在,构建会话,记录会话,初始化会话 <br>";
            $this->build();
            $this->push();
            //$this->active();
            //$this->reboot("3,Token missed,build session,push session,initialize request.");
            //$options = array("action"=>"reboot");
            //$this->reboot(json_encode($options));
            $this->reboot($this->remoteOptions,null);
            exit();
        }
    }

    public function __destruct() {
        $this->disconnectDB();
    }
    /**
     * 初始化数据库会话
     */
    function connectDB() {
        if(!$this->sessionDB) {
            $host     = DATABASE_ADDRESS_SESSIONS;
            $username = DATABASE_USERNAME_SESSIONS;
            $password = DATABASE_PASSWORD_SESSIONS;
            $nameDB   = DATABASE_NAME_SESSIONS;
            $nameTB   = DATABASE_TABLE_NAME_SESSIONS;

            $this->sessionDB = new mariaSession($host,$username,$password);
        }

        if(!$this->entityDB && $this->sessionDB && $this->sessionDB->status() == mariaSession::MARIADB_CONNECTION_STATUS_READY)
        {
             if($this->sessionDB->existDB(DATABASE_NAME_SESSIONS))
             {
                 $this->entityDB = $this->sessionDB->exposeDB(DATABASE_NAME_SESSIONS);
             }
        }

        if(!$this->entityTB && $this->entityDB)
        {
            if($this->entityDB->existTB($nameTB))
            {
                $this->entityTB = $this->entityDB->exposeTB(DATABASE_TABLE_NAME_SESSIONS);
            }
        }

        if($this->sessionDB && $this->entityDB && $this->entityTB) { 
            //echo "sessionDB OK.";
            return true; 
        } else { 
            //echo "sessionDB BAD.";
            return false;
        }
    }

    function disconnectDB() {
        if($this->sessionDB) {
            return $this->sessionDB->close();
        }

        return false;
    }

    function loadPermissions($openID) {

    }
    
    /**
     * 构建会话内容
     */
    function build() {
        if($this->sessionDB && $this->sessionDB->status() === mariaSession::MARIADB_CONNECTION_STATUS_READY)
        {
            $this->sessionGEO     = new CurrentGeoIPInfo();

            /**
             * GeoIP 访问过滤器
             */

            if($this->sessionGEO) {
                $this->sessionAddress = array("country" => $this->sessionGEO->countryName,"province" => $this->sessionGEO->provinceName,"city" => $this->sessionGEO->cityName);
            }
            $this->sourceIP   = remoteIP();
            $this->sourcePort = remotePort();
            
            $index = 0;

            while(!$this->sessionID && $index < 1000) {
                $this->sessionID = $this->sessionID();
                $index ++;
            }

            $this->sessionTime    = strval(time());//date("Y.m.d:H.i.s");
            $this->sessionActive  = $this->sessionTime;
            $this->sessionTimeout = LIBPHP_SESSION_TIMEOUT_DEFAULT;
            $this->sessionAgent   = $_SERVER['HTTP_USER_AGENT'];
            $this->sessionToken   = md5($this->sourceIP . $this->sessionTime . $this->sessionID);
            setcookie("token",$this->sessionToken,time() + SSL_SESSION_TIMEOUT_DEFAULT);//单位:秒
        }
    }
    //function reboot($reason) {
    function reboot($options,$reasons) {
        $host = $_SERVER['HTTP_HOST'];
        $params = '';

        if($options && $reasons) {
            $params = "?reboot=$reasons&options=$options";
        } else if($options) {
            $params = "?options=" . json_encode($options);
        } else if($reasons) {
            $params = "?reboot=$reasons";
        } else {
            
        }

        header("Location: https://$host$params");
        //header("Location: https://$host?token=$this->sessionToken&options={\"action\":\"reboot\"}");
        //header("Location: https://$host?token=$this->sessionToken&options={\"action\":\"reboot\"}&reason=$reason");
        exit();
    }
    function sessionID() {
        $id = md5($this->sessionGEO->ip . $this->sessionTime . $this->sessionID . strval(time()));
        return $id;
        /**
         * 查询数据库是否已存在
         */
        $remoteIP   = remoteIP();
        $remotePort = remotePort();
        $conditions = "sessionID='$id'";// AND sourcePort='$remotePort'";
        if($this->entityTB) {
            $items = $this->entityTB->lookupItem("sourceIP",$conditions);
            if($items && count($items)) {
                return null;
            } else {
                return $id;
            }
        }

        return null;
    }

    function save() {
        /**
         * $session = json_encode($this);
         */
    }

    function load() {
        /**
         * $sessionStr = null;
         */
    }

    function active() {
        $remoteIP   = remoteIP();
        $remotePort = remotePort();
        $this->sourceIP   = $remoteIP;
        $this->sourcePort = $remotePort;
        //$token = $_COOKIE['token'];
        //echo "remoteIP : " . $remoteIP . "<br>";
        //echo "remotePort : " . $remotePort . "<br>";
        $activeTime = strval(time());
        //$conditions = "sourceIP='$remoteIP'";// AND sourcePort='$remotePort'";
        $conditions = "token='$this->remoteToken'";
        //$item = new mariaItem(array("sourceIP"=>$remoteIP,"sourcePort"=>$remotePort,"active"=>$activeTime));
        $item = new mariaItem(array("active"=>$activeTime));
        if($this->entityTB->updateItem($item,$conditions)) { 
            //echo "激活成功";
            return true ;
        } else { 
            //remoteAlert("激活时间更新失败");return true; 
        }
   }

   function echoToken() {
       echo encodeToken($this->sessionToken);
   }
    /**
     * 向数据库注入
     */
    function push() {
        if($this->entityTB) {
        $item = new mariaItem(array("sessionID"=>"'"  . $this->sessionID                                          . "'",
                                    "sourceIP"=>"'"   . $this->sourceIP                                           . "'",
                                    "sourcePort"=>"'" . $this->sourcePort                                         . "'",
                                    "token"=>"'"      . $this->sessionToken                                       . "'",
                                    "time"=>"'"       . $this->sessionTime                                        . "'",
                                    "active"=>"'"     . $this->sessionActive                                      . "'",
                                    //"screen"=>"'"     . json_encode($this->sessionScreen)                         . "'",
                                    "agent"=>"'"      . $this->sessionAgent                                       . "'",
                                    "address"=>"'"    . json_encode($this->sessionAddress,JSON_UNESCAPED_UNICODE) . "'"));
        return $this->entityTB->appendItem($item);
        }
        return false;
    }

    function pushScreen($screen){
        $remoteIP   = remoteIP();
        $remotePort = remotePort();
        $screenStr  = json_encode($screen);
        //$conditions = "sourceIP='$remoteIP'";// AND sourcePort='$remotePort'";
        $conditions = "token='$this->remoteToken'";
        $item = new mariaItem(array("screen"=>"'" . $screenStr . "'"));
        if($this->entityTB->updateItem($item,$conditions)) { 
            return true ;
        }
        else { 
            return false; 
        }
    }
    /**
     * 由数据库拉取
     */
    function pull() {
        /**
         * 根据 token 从数据库拉取数据
         */
        
        if($this->entityTB) {
            $remoteIP   = remoteIP();
            $remotePort = remotePort();
            //$conditions = "sourceIP='$remoteIP'";// AND sourcePort='$remotePort'";
            $conditions = "token='$this->remoteToken'";
            $items = $this->entityTB->lookupItem("sessionID,token,time,active,screen,agent,address",$conditions);
            if($items && count($items)) {

                /**
                 * TODO : 填充处理
                 */
                $item  = $items[0];
                $this->sessionID      = $item['sessionID'];
                $this->sourceIP       = $remoteIP;
                $this->sourcePort     = $remotePort;
                $this->sessionActive  = $item['active'];
                $this->sessionToken   = $item["token"];
                $this->sessionAddress = json_decode($item['address']);
                return true;
            }
        }

        return false;
    }

    /**
     * 清理数据库
     */ 
    function clear(){
        $remoteIP = remoteIP();
        //$conditions = "sourceIP='$remoteIP'"; // AND sourcePort='$remotePort'";
        $conditions = "token='$this->sessionToken'";
        $this->entityTB->removeItem($conditions);
    }
}
?>
