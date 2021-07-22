<?php

$sessionDB = new mariaSession($host,$username,$password);
$entityDB  = null;
$entityTB  = null;

if($sessionDB && $sessionDB->status() == mariaSession::MARIADB_CONNECTION_STATUS_READY)
{
    if($sessionDB->existDB(DATABASE_NAME_SESSIONS))
    {
        $entityDB = $sessionDB->exposeDB(DATABASE_NAME_SESSIONS);

        if($entityDB)
        {
            if($entityDB->existTB($nameTB))
            {
                $entityTB = $entityDB->exposeTB(DATABASE_TABLE_NAME_SESSIONS);
                /*********************
                 * 添加 SESSION 记录 *
                 *********************/
            }
        }
    }
}

/**
 * 查询记录
 */
$items     = $entityTB->lookupItem("code,time,address,country,region,city,isp,type","code=$product_id");
$item      = $items[0];
$itemIndex = $item['index'];
$codeDate  = $item['date'];

/**
 * 添加记录
 **/
$item = new mariaItem(array("sessionID"=>"'" . $sessionID           . "'",
                            "openID"=>"'"    . $openID              . "'",
                            "address"=>"'"   . $address             . "'",
                            "token"=>"'"     . $token               . "'",
                            "begin"=>"'"     . $begin               . "'",
                            "update"=>"'"    . $update              . "'",
                            "timeout"=>"'"   . $timeout             . "'",
                            "screen"=>"'"    . $screen              . "'",
                            "agent"=>"'"     . $agent               . "'"));
$entityTB->appendItem($sessionItem);

/**
 * 更新记录
 */
$conditions = "id=1024";
$item = new mariaItem(array("sessionID"=>"'" . $sessionID           . "'",
                            "openID"=>"'"    . $openID              . "'",
                            "address"=>"'"   . $address             . "'",
                            "token"=>"'"     . $token               . "'",
                            "begin"=>"'"     . $begin               . "'",
                            "update"=>"'"    . $update              . "'",
                            "timeout"=>"'"   . $timeout             . "'",
                            "screen"=>"'"    . $screen              . "'",
                            "agent"=>"'"     . $agent               . "'"));
$entityTB->updateItem($item,$conditions);
/**
 * 删除记录
 */
$conditions = "id=1024 AND name='someone'";
$entityTB->removeItem($conditions);
?>
