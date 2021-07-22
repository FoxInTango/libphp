<?php
/**
 * 权限类型:
 *     文件系统权限 fs:/root/
 *     数据系统权限
 *     接口访问权限
 *
 * 与 session 搭配,确定操作权限
 * 1, 会话创建时 加入一般性权限
 * 2, 用户登录时 根据用户角色,加入相应权限
 **/
class permission {
    
}

class permissionMap {
    public $permissions = array();
    public function examine($value) {

    }
};

class permissionOption {
    public $target;
    public $type;
    public function __construct() {

    }
};
/**
 * $openID 访问者公开编码
 * $option 操作
 *  {
 *  }
 **/
function authorize($openID,$option) {
    return false;
};
?>
