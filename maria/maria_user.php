<?php 
class maria_user
{
    public $username;
    public $password;
    public $userhosts = array();//可登录主机
    public $databases = array();//拥有权限的数据库
    public function __construct($name,$password,$databases)
    {
    }
}
?>