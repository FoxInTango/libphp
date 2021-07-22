<?php
function http_curl($url, $type='get', $format='json',$fields='')
{
    $handle = curl_init();

    curl_setopt($handle, CURLOPT_URL, $url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);

    if($type == 'post')
    {
        curl_setopt($handle, CURLOPT_POST, 1);

        if($array != null)
        {
            curl_setopt($handle, CURLOPT_POSTFIELDS,$fields);
        }

        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
    }
    
    $output = curl_exec($handle);
    
    if(curl_errno($handle))
    {
       $result = curl_error($handle);
       curl_close($handle);
       return $result;
    }
    else 
    {
        $result = json_decode($output,true);
        curl_close($handle);
        return $result;
    }
    curl_close($handle);
}
?>
