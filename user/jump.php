<html>
  <head>
  </head>
  <body>
<?php
/**
 * 登录，注册 结果跳转页
 * 
 * 判断跳转类型
 * 登录:
 *   查询数据库
 *   登录通过:跳转至管理后台
 *   登录失败:显示失败原因
 * 
 * 注册:
 *   查询数据库是否重名
 *     查询通过:
 *     查询失败:
*/
?>

<p><span id="remaining"></span>秒后自动跳转至管理控制台…</p>
<script language="javascript" type="text/javascript">
  var keepTime = 5;

  function autoLoacation()
  {
    document.getElementById("remaining").innerText=keepTime;

    if(keepTime==0)
    window.location.href = 'https://console.saosaole.com.cn?username=***&token=****';
    else
    {
      keepTime--;
    }
    setTimeout(autoLoacation,1000);
 }
 
 autoLoacation();
 </script>
</body>
</html>
