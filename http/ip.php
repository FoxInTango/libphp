<?php

function get_remote_ip()
{
    $ip = "0.0.0.0";

    if(getenv("HTTP_X_FORWARDED_FOR"))
    {
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    }
    elseif(getenv("HTTP_CLIENT_IP"))
    {
        $ip = getenv("HTTP_CLIENT_IP");
    }
    elseif(getenv("REMOTE_ADDR"))
    {
        $ip = getenv("REMOTE_ADDR");
    }
    else
    {
        $ip = "Unknown";
    }
    
    return  $ip;
}