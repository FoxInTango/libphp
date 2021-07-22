<?php
/**
 * $str 原字符串
 * $sub 被移除子串
 **/
function removeSubstr($str,$sub) {
    $pos = 0;
    while($pos !== FALSE) {
        $pos = strpos($str,$sub);
        $len = strlen($sub);
        if($pos !== FALSE) $str = substr_replace($str,"",$pos,$len);
    }

    return $str;
}

/**
 * $str 原字符串
 * $sub 被替代子串
 * $rep 替代字符串
 **/
function replaceSubstr($str,$sub,$rep) {
    $pos = 0;
    while($pos !== FALSE) {
        $pos = strpos($str,$sub);
        $len = strlen($sub);
        if($len !== FALSE) $str = substr_replace($str,$rep,$pos,$len);
    }
}
?>
