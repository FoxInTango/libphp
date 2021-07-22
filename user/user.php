<?php
require_once dirname(__FILE__) . "/../../config/config.php";
require_once dirname(__FILE__) . "/../remote/remote.php";
require_once dirname(__FILE__) . "/../maria/maria.php";

/**
 * 用户创建流程
 *     用户类型:原始用户
 *              身份用户
 *              群组用户
 *   1, 用户提交注册信息
 *   2, 检测各项信息是否符合既定规则
 *   3, 检测数据库是否存在冲突
 *   4, 检测注册类型 原始用户:
 *                   记录该用户通用信息
 *                   为该用户创建信息详情表
 *                   为该用户创建成员记录表
 *                   为该用户创建权限记录表
 *                   为该用户创建昵称记录表
 *                   为该用户创建头像记录表
 *                   为该用户创建用户目录
 *                   身份用户:
 *                   
 *                   
 *                    
 */

function createOriginID($entityTB) {
    $id = null;

    while(!$id) {
        $id = strval(rand(10000000,99999999));
        $conditions = "originID='$id'";
        if($entityTB) {
            $items = $entityTB->lookupItem("originID",$conditions);
            if($items && count($items)) {
                $id = null;
            } else {
                break;
            }
        }
    }

    return $id;
}

/**
 * 参考固话号码
 * 0376 + xxxxxxxx
 */
function createUnionID($entityTB) {
    $id = null;

    while(!$id) {
        $id = strval(rand(10000000,99999999));
        $conditions = "unionID='$id'";
        if($entityTB) {
            $items = $entityTB->lookupItem("unionID",$conditions);
            if($items && count($items)) {
                $id = null;
            } else {
                break;
            }
        }
    }   

    return $id;
}

/**
 * OpenID 参考手机号码
 * 1 + xx(2位) + x + 区号(3位) + xxxx随机码(4位)
 */
function createOpenID($entityTB) {
    $id = null;

    while(!$id) {
        $id = strval(rand(10000000,99999999));
        $conditions = "openID='$id'";
        if($entityTB) {
            $items = $entityTB->lookupItem("openID",$conditions);
            if($items && count($items)) {
                $id = null;
            } else {
                break;
            }
        }
    }

    return $id;
}

/** 子表名 后缀
  表名机制重新设计(MD5过长)
  检查表名称重复
  */
function createSubnameTB($username,$entityTB) {
    if(!$username || !$entityTB) return null;
    $nameTB = null;

    while(!$nameTB) {
        $nameTB = strval(rand(10000000,99999999));//md5($username . strval(time()) . strval($index));
        $conditions = "subnameTB='$nameTB'";
        $items = $entityTB->lookupItem("subnameTB",$conditions);
        if($items && count($items)) {
            $id = null;
        } else {
            break;
        }
    }

    return $nameTB;
}
/**
 * 建立不文明语汇词库,品牌词库,知名人士姓名词库进行过滤
 */

function usernameRuleCheck($username) {
    return true;
}

function passwordRuleCheck($password) {
    return true;
}

function nicknameRuleCheck($nickname) {

}

/**
 * 调用图像识别API进行头像过滤(腾讯鉴黄API)
 * https://ai.qq.com/product/yellow.shtml
 */
function avatarRuleCheck($path) {

}

class UserDetails {

}

class User extends maria {
    public $username;
    public $password;

    public $details;

    public $options;
    public $visitor;

    /**
     * 注册结果
     */
    public $originID;
    public $unionID;
    public $openID;
    public function __construct($options,$visitor) {
        $this->options = $options;
        $this->visitor = $visitor;
        $this->prepareDB();
    }
    public function __destruct() {
        $this->unloadDB();
    }

    public function prepareDB() {
        $this->hostDB     = DATABASE_ADDRESS_USERS;
        $this->usernameDB = DATABASE_USERNAME_USERS;
        $this->passwordDB = DATABASE_PASSWORD_USERS;
        $this->nameDB     = DATABASE_NAME_USERS;
        $this->nameTB     = DATABASE_TABLE_NAME_USERS;

        $this->connectDB();
    }

    public function unloadDB() {
        $this->disconnectDB();
    }

    /** 返回说明:
     *  1,记录客户端类型
     *  2,根据客户断类型,导航或者返回数据
     */
    public function active() {
        if($this->options && $this->options->action) {
            switch($this->options->action) {
                case "signup":{
                    //remoteAlert("signup");
                    if($this->signup()) {
                        //remoteAlert("signup");
                        /**
                         * 导航至 登录 页面
                         */
                        //header("Location: https://$host?token=$this->remoteToken&page=login");
                    } else {
                        /**
                         * 导航至 注册失败提示 页面
                         */
                    }
                }break;
                case "branch":{
                    //remoteAlert("signup");
                    if($this->branch()) {
                        //remoteAlert("signup");
                        /**
                         * 导航至 子账户注册成功 页面
                         */
                        //header("Location: https://$host?token=$this->remoteToken&page=login");
                    } else {
                        /**
                         * 导航至 子账户注册失败 页面
                         */
                    }
                }break;
                case "signin":{
                    //remoteAlert("signin");
                    if($this->signin()){
                        //remoteAlert("登录成功");

                        /**
                         * 导航至 登录成功 页面
                         */
                    } else {
                        /**
                         * 导航至 登录失败 页面
                         */
                    }
                }break;
                case "signout":{
                    //remoteAlert("signout");
                    if($this->signout()) {
                        /**
                         * 导航至 登录 页面
                         */
                    } else {
                        /**
                         * 保持本页
                         */
                    }
                }break;
                case "destroy":{
                    //remoteAlert("destroy");
                    return $this->destroy();
                }break;
            }
        }
        return true;
    }

    public function signup() {
        if(!$this->entityTB) { 
            remoteAlert("数据库连接失败.");
            exit();
        }
        if($this->entityTB && usernameRuleCheck($this->options->username) && passwordRuleCheck($this->options->password)) {
            $username = $this->options->username;
            $password = $this->options->password;
            $password = password_hash($password,PASSWORD_DEFAULT);
            $wxID = null;
            /** 添加wxID */
            if(isset($this->options->wxID)) {
                $wxID = $this->options->wxID;
            }
            $conditions = "username='$username'";
            $items = $this->entityTB->lookupItem("username",$conditions);
            if(!$items || !count($items)) {
               $originID = createOriginID($this->entityTB);
               $unionID  = createUnionID($this->entityTB);
               $openID   = createOpenID($this->entityTB);
               /**子表名称*/
               $subnameTB   = createSubnameTB($username,$this->entityTB);
               $connector   = "@";
               $usernames   = "username"    . "$connector" . $subnameTB; //createNameTB($username,$this->entityDB);
               $passwords   = "passwords"   . "$connector" . $subnameTB; //createNameTB($username,$this->entityDB);
               $nicknames   = "nicknames"   . "$connector" . $subnameTB; //createNameTB($username,$this->entityDB);
               $avatars     = "avatars"     . "$connector" . $subnameTB; //createNameTB($username,$this->entityDB);
               $permissions = "permissions" . "$connector" . $subnameTB; //createNameTB($username,$this->entityDB);
               $groups      = "groups"      . "$connector" . $subnameTB; //createNameTB($username,$this->entityDB);
               $detailes    = "detailes"    . "$connector" . $subnameTB; //createNameTB($username,$this->entityDB);
               $banks       = "banks"       . "$connector" . $subnameTB; //createNameTB($username,$this->entityDB);
               $internets   = "internets"   . "$connector" . $subnameTB; //createNameTB($username,$this->entityDB);
               $addresses   = "addresses"   . "$connector" . $subnameTB; //createNameTB($username,$this->entityDB);
               $home        = "home"        . "$connector" . $subnameTB; //createNameTB($username,$this->entityDB);
               $time        = strval(time());
               /**
                * 创建用户子表
                */
               if($this->entityTB) {//修改数据表: 添加username [userSSL]
                   $item = new mariaItem(array("originID"=>"'"    . $originID      . "'",
                                               "unionID"=>"'"     . $unionID       . "'",
                                               "openID"=>"'"      . $openID        . "'",
                                               "subnameTB"=>"'"   . $subnameTB     . "'",
                                               "username"=>"'"    . $username      . "'",
                                               "password"=>"'"    . $password      . "'",
                                               //"usernames"=>"'"   . $usernames     . "'",
                                               //"passwords"=>"'"   . $passwords     . "'",
                                               //"nicknames"=>"'"   . $nicknames     . "'",
                                               //"avatars"=>"'"     . $avatars       . "'",
                                               //"permissions"=>"'" . $permissions   . "'",
                                               //"groups"=>"'"      . $groups        . "'",
                                               //"detailes"=>"'"    . $detailes      . "'",
                                               //"banks"=>"'"       . $banks         . "'",
                                               //"internets"=>"'"   . $internets     . "'",
                                               //"addresses"=>"'"   . $addresses     . "'",
                                               "home"=>"'"        . $home          . "'",
                                               "time"=>"'"        . $time          . "'"));
                    
                   if($this->entityTB->appendItem($item)) {
                       //导航至注册成功页面
                       //remoteAlert("注册成功");
                       $status = array(
                           "status"=>"sucess",
                           "reason"=>"$openID"
                       );
                       $this->originID = $originID;
                       $this->unionID  = $unionID;
                       $this->openID   = $openID;
                       echo json_encode($status);
                       return true;
                   } else {
                       //导航至注册失败页面
                       remoteAlert("注册失败" . $usernames);

                       return false;
                   }
               } else {
                   //导航至注册失败页面
                   remoteAlert("数据库故障,注册失败");
                   return false;
               }
            } else {
                //导航至注册失败页面
                //remoteAlert("用户名已被其他用户注册,注册失败");
                $status = array(
                    "status"=>"failed",
                    "reason"=>"用户名已被其他用户注册,注册失败"
                );
                echo json_encode($status);
                return false;
            }
        }
        //导航至注册失败页面
        remoteAlert("注册信息错误,注册失败");
        return false;
    }

    public function branch(){

    }

    public function signin() {
        if($this->entityTB && $this->options && $this->options->username && $this->options->password) {
            $username   = $this->options->username;
            $password   = $this->options->password;
            $conditions = "username='$username'";
            
            $items = $this->entityTB->lookupItem("openID,password",$conditions);

            if($items && count($items)) {
                if(password_verify($password,$items[0]['password'])) {
                    /**
                     * 更新visitor,openID
                     */
                    if($this->visitor) {
                        $this->visitor->openID = $items[0]['openID'];
                        $this->visitor->pushID();
                    }
                    $status = array(
                    "status"=>"sucess",
                    "reason"=>"登录成功"
                    );
                    echo json_encode($status);
                    return true;
                } else {
                    $status = array(
                    "status"=>"failed",
                    "reason"=>"密码错误"
                    );
                    echo json_encode($status);
                    return false;
                }
            }
            return false;
        }
        return true;
    }

    public function signout() {
    return true;
    }

    public function destroy() {

    }

    public function push() {
    }

    public function pull() {

    }

    public function update($options) {

    }

    public function updateUsername($username) {

    }

    public function updatePassword($password) {

    }
 }
 ?>
