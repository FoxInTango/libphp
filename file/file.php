<?php
/**
 * Header Type : https://www.cnblogs.com/supermanxu/articles/9020200.html
 */
function sendFile($path) {
    if(!file_exists($path)) {
        header('HTTP/1.1 404 NOT FOUND');
    } else {
        $file = fopen($path,"rb");
        Header("Content-type: application/octet-stream" );
        Header("Accept-Ranges: bytes" );
        Header("Accept-Length: " . filesize($path));
        Header("Content-Disposition: attachment; filename=" . pathinfo($path,PATHINFO_FILENAME));
        echo fread($file,filesize($path));
        fclose($file);
        exit ();
    }
}

function sendCSS($path) {
    if(!file_exists($path)) {
        header('HTTP/1.1 404 NOT FOUND');
    } else {
        $file = fopen($path,"rb");
        Header("Content-type: text/css");
        Header("Accept-Ranges: bytes" );
        Header("Accept-Length: " . filesize($path));
        Header("Content-Disposition: attachment; filename=" . pathinfo($path,PATHINFO_FILENAME));
        echo fread($file,filesize($path));
        fclose($file);
        exit ();
    }
}

function sendJS($path) {
    if(!file_exists($path)) {
        header('HTTP/1.1 404 NOT FOUND');
    } else {
        $file = fopen($path,"rb");
        Header("Content-type: text/javascript");
        Header("Accept-Ranges: bytes" );
        Header("Accept-Length: " . filesize($path));
        Header("Content-Disposition: attachment; filename=" . pathinfo($path,PATHINFO_FILENAME));
        echo fread($file,filesize($path));
        fclose($file);
        exit ();
    }
}
?>
