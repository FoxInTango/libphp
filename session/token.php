<?php
function encodeToken($token) {
    echo "<script type=\"text/javascript\">";
    echo "var token=" . "'$token'";
    echo "</script>";
}
?>
