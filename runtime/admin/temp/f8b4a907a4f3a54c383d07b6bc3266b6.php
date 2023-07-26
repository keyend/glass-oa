<?php /*a:1:{s:74:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\Auth\login.html";i:1689573327;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>管理后台登录</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/static/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="/static/admin/style/admin.css" media="all">
    <link rel="stylesheet" href="/static/admin/style/login.css" media="all">
    <script>if(window.top !== window.self){ window.top.location = window.location;}</script>
    <style>
        body {background: url(/static/common/images/admin_bg.jpg) no-repeat 50%;background-size: cover;}
        .login-box{border: 1px solid white;background: white;padding: 20px;box-shadow: #999 0 2px 10px}
    </style>
</head>
<body>

<div class="layadmin-user-login layadmin-user-display-show" id="LAY-user-login" style="display: none;">

    <div class="layadmin-user-login-main login-box" style="border-radius: 3px;">
        <div class="layadmin-user-login-box layadmin-user-login-header">
            <h1>订单管理系统</h1>
            <p>订单管理系统</p>
        </div>
        <div class="layadmin-user-login-box layadmin-user-login-body layui-form">
            <div class="layui-form-item">
                <label class="layadmin-user-login-icon layui-icon layui-icon-username" for="LAY-user-login-username"></label>
                <input type="text" name="username" id="LAY-user-login-username" lay-verify="required" placeholder="用户名" class="layui-input" autocomplete="off">
            </div>
            <div class="layui-form-item">
                <label class="layadmin-user-login-icon layui-icon layui-icon-password" for="LAY-user-login-password"></label>
                <input type="password" name="password" id="LAY-user-login-password" lay-verify="required" placeholder="密码" class="layui-input" autocomplete="off">
            </div>
            <div class="layui-form-item">
                <div class="layui-row">
                    <div class="layui-col-xs7">
                        <label class="layadmin-user-login-icon layui-icon layui-icon-vercode" for="LAY-user-login-vercode"></label>
                        <input type="text" name="code" id="LAY-user-login-vercode" lay-verify="required" placeholder="图形验证码" class="layui-input" autocomplete="off">
                        <input type="hidden" name="key" id="LAY-user-login-key" value="" />
                    </div>
                    <div class="layui-col-xs5">
                        <div style="margin-left: 10px; padding-top: 4px;">
                            <img alt="captcha" id="captcha" width="100%"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <button class="layui-btn layui-btn-fluid" lay-submit lay-filter="LAY-user-login-submit">登 入</button>
            </div>
        </div>
    </div>
</div>
<script src="/static/common/js/jquery.min.js"></script>
<script src="/static/layui/layui.js"></script>
<script>

    document.onkeydown=function(event){
        var e = event || window.event || arguments.callee.caller.arguments[0];
        if(e && e.keyCode==13){ // enter 键
            $('.layui-btn').click();
        }
    };

    layui.config({
        base: '/static/admin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'user'], function(){
        var $ = layui.$
            ,setter = layui.setter
            ,admin = layui.admin
            ,form = layui.form
            ,router = layui.router()
            ,search = router.search
            ,verifyImgUrl = "<?php echo url('sysVerifyCode'); ?>";

        $('#captcha').on('click', function() {
          $.getJSON(verifyImgUrl, r => {
            if (r.code !== 0) {
              layer.alert(r.message);
            } else {
              this.setAttribute('src', r.data.captcha),
              document.getElementById('LAY-user-login-key').value = r.data.key
            }
          })
        }).trigger('click'),

        form.render(),
        form.on('submit(LAY-user-login-submit)', function(obj){
            $.post("<?php echo url('sysLogin'); ?>", obj.field, function (res) {
                if(0 === res.code) {
                    // 登入成功的提示与跳转
                    layer.msg('登入成功', {
                        offset: '15px'
                        ,icon: 1
                        ,time: 1000
                    }, function(){
                        location.href = '/admin'; //后台主页
                    });
                } else {
                    layer.msg(res.message, {anim: 6});
                    $("#captcha").click();
                }
            }, 'json');
        });
    });

</script>
</body>
</html>