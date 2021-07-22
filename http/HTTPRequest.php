<?php

function http($url,$type='get',$res='json',$arry='')
{
    //初始化curl
    $ch = curl_init();
    //设置curl参数  下面的方式是get请求
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
         
    //post请求
    if($type == 'post')
    {
        curl_setopt($ch, CURLOPT_POST, 1);
        if($arry != null)
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS,$arry);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
    }
    
    //采集curl
    $output = curl_exec($ch);
    
    if($res='json') 
    {
        //请求失败返回错误信息
        if(curl_errno($ch))
        {
           $result = curl_error($ch);
           //关闭
           curl_close($ch);
           return $result;
        }//返回成功
        else 
        {
            //加上参数true 将json对象转化成数组而不仅仅是object类型
            $result = json_decode($output,true);
            //关闭
            curl_close($ch);
            return $result;
        }
    }
    //关闭
    curl_close($ch);
    //var_dump($output); 
}
?>
