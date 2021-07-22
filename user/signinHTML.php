<script>
function signup(){
    //导航至注册页
    window.location.href = 'signup.php';
}

function update(){
    //导航至重置页
}
</script>

<!--登录表格-->
<form action="jump.php" method="GET">
    <p>用户名:
        <input type="text" name="uesrname">
    </p>
    <p>密码:
        <input type="password" name="password">
    </p>
    <p>
        <input type="submit" value="登录"></input>
        <input type="hidden" name="type" value="signin">   <!--隐藏域-->
    </p>
    <button type="button" onclick="signup()">免费注册</input>
    <button type="button" onclick="update()">忘记密码</input>
</form>