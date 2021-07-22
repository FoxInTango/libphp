<?php
function remoteIP(){
    $remoteIP = '';
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_FROM', 'REMOTE_ADDR') as $v) {
        if(isset($_SERVER[$v])){
            if(!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $_SERVER[$v])){
                continue;
            }
            $remoteIP = $_SERVER[$v];
        }
    }

    return $remoteIP;
}

function remotePort() {
    return  $_SERVER['REMOTE_PORT'];
}

function remoteAlert($content) {
    echo "<script type=\"text/javascript\">";
    echo "alert('$content')";
    echo "</script>";
}
?>
