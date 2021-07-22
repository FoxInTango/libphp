<?php

echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

$url =  'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
$url_arry = parse_url($url);
print_r($url_arry);
echo "<br>";
?>
