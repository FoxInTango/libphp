<html>
  <head>
  </head>
  <body>
<?php
    /**
     * 查询partner数据库是否存在
     * 查询partner数据表是否存在
     *
     * 添加合作伙伴信息
     *
     * */
    ?>

<script>
function sendSMS(){
  
}
</script>
<form action="jump.php" method="GET">
    <p>用户名:
        <input type="text" name="uesrname">
    </p>
    <p>手机号:
        <input type="text" name="mobile">
    </p>
    <p>密码:
        <input type="password" name="password"> <!--密码框-->
    </p>
    <p>验证码:
        <input type="text" name="mobile">
        <button type="button" onclick="sendSMS(mobile)">获取验证码</button>
    </p>
    <p>
        <input type="submit" value="提交"></input>
        <input type="reset" value="重置"></input>
        <input type="hidden" name="type" value="signup">   <!--隐藏域-->
    </p>
</form>
  </body>
</html>
