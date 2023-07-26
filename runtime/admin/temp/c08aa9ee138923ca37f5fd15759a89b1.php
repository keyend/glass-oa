<?php /*a:2:{s:75:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\Index\index.html";i:1689598196;s:68:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\base.html";i:1688009496;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>云服务系统后台管理</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/static/layui-v2.8.4/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="/static/admin/style/admin.css" media="all">
    <link rel="stylesheet" href="/static/admin/style/custom.css" media="all">
    <link rel="stylesheet" href="/static/common/fonts/iconfont.css" />
    <script src="/static/layui-v2.8.4/layui/layui.js"></script>
    <script src="/static/admin/ns.js"></script>

    <style>#LAY_app {zoom: .96;}</style>
</head>

<body class="layui-layout-body">
<div id="LAY_app">
    <div class="layui-layout layui-layout-admin">
        <div class="layui-header">
            <!-- 头部区域 -->
            <ul class="layui-nav layui-layout-left">
                <li class="layui-nav-item layadmin-flexible" lay-unselect>
                    <a href="javascript:;" layadmin-event="flexible" title="侧边伸缩">
                        <i class="layui-icon layui-icon-shrink-right" id="LAY_app_flexible"></i>
                    </a>
                </li>
                <li class="layui-nav-item layui-hide-xs" lay-unselect>
                    <a href="/" target="_blank" title="前台">
                        <i class="layui-icon layui-icon-website"></i>
                    </a>
                </li>
                <li class="layui-nav-item" lay-unselect>
                    <a href="javascript:;" layadmin-event="refresh" title="刷新">
                        <i class="layui-icon layui-icon-refresh-3"></i>
                    </a>
                </li>
            </ul>
            <ul class="layui-nav layui-layout-right" lay-filter="layadmin-layout-right">
                <li class="layui-nav-item layui-hide-xs" lay-unselect>
                    <a href="javascript:;" layadmin-event="theme">
                        <i class="layui-icon layui-icon-theme"></i>
                    </a>
                </li>
                <li class="layui-nav-item layui-hide-xs" lay-unselect>
                    <a href="javascript:;" layadmin-event="fullscreen">
                        <i class="layui-icon layui-icon-screen-full"></i>
                    </a>
                </li>
                <li class="layui-nav-item" lay-unselect>
                    <a href="javascript:;">
                        <cite><?php echo htmlentities($admin['username']); ?></cite>
                    </a>
                    <dl class="layui-nav-child">
                        <dd><a href="javascript:;" onclick="editPwd();">修改密码</a></dd>
                        <dd><a href="javascript:;" onclick="clearCache();">清除缓存</a></dd>
                        <dd><a href="javascript:;" onclick="logout();">退出</a></dd>
                    </dl>
                </li>

                <!--<li class="layui-nav-item layui-hide-xs" lay-unselect>-->
                <!--    <a href="javascript:;" layadmin-event="about"><i class="layui-icon layui-icon-more-vertical"></i></a>-->
                <!--</li>-->
                <!--<li class="layui-nav-item layui-show-xs-inline-block layui-hide-sm" lay-unselect>-->
                <!--    <a href="javascript:;" layadmin-event="more"><i class="layui-icon layui-icon-more-vertical"></i></a>-->
                <!--</li>-->
            </ul>
        </div>
        <!-- 侧边菜单 -->
        <div class="layui-side layui-side-menu">
            <div class="layui-side-scroll">
                <div class="layui-logo" style="background-size: 20px 20px; text-align: unset; text-indent: 1.8rem;">
                    <span>订单管理系统</span>
                </div>

                <ul class="layui-nav layui-nav-tree" lay-shrink="all" id="LAY-system-side-menu" lay-filter="layadmin-system-side-menu">
                    <li data-name="template" class="layui-nav-item">
                        <a href="javascript:;" lay-tips="系统管理" lay-direction="2">
                            <i class="layui-icon layui-icon-app"></i>
                            <cite>系统配置</cite>
                        </a>
                        <dl class="layui-nav-child">
                            <?php if(checkAccess('sysConfigBasic') || checkAccess('sysConfigConnect') || checkAccess('sysConfigPay') || checkAccess('sysConfigMail') || checkAccess('sysConfigVip')): ?>
                            <dd><a lay-href="<?php echo url('sysConfig'); ?>">参数配置</a></dd>
                            <?php endif; if(checkAccess('sysUser') || checkAccess('sysGroup') || checkAccess('sysRole') || checkAccess('sysRule')): ?>
                            <dd><a lay-href="<?php echo url('sysSecurity'); ?>">账户安全</a></dd>
                            <?php endif; if(checkAccess('sysLogsOperator') || checkAccess('sysLogsLogin')): ?>
                            <dd><a lay-href="<?php echo url('sysLogs'); ?>">日志管理</a></dd>
                            <?php endif; ?>
                        </dl>
                    </li>
                    <li data-name="template" class="layui-nav-item">
                        <a href="javascript:;" lay-tips="用户管理" lay-direction="2">
                            <i class="layui-icon layui-icon-user"></i>
                            <cite>品类客户</cite>
                        </a>
                        <dl class="layui-nav-child">
                            <?php if(checkAccess('category')): ?>
                            <dd><a lay-href="<?php echo url('category'); ?>">品类管理</a></dd>
                            <?php endif; if(checkAccess('craft')): ?>
                            <dd><a lay-href="<?php echo url('craft'); ?>">工艺管理</a></dd>
                            <?php endif; if(checkAccess('member')): ?>
                            <dd><a lay-href="<?php echo url('member'); ?>">客户列表</a></dd>
                            <?php endif; ?>
                        </dl>
                    </li>
                    <li data-name="template" class="layui-nav-item">
                        <a href="javascript:;" lay-tips="订单配送" lay-direction="2">
                            <i class="layui-icon layui-icon-chart"></i>
                            <cite>订单配送</cite>
                        </a>
                        <dl class="layui-nav-child">
                            <?php if(checkAccess('order')): ?>
                            <dd><a lay-href="<?php echo url('order'); ?>">订单管理</a></dd>
                            <?php endif; if(checkAccess('delivery')): ?>
                            <dd><a lay-href="<?php echo url('delivery'); ?>">配送列表</a></dd>
                            <?php endif; ?>
                        </dl>
                    </li>
                </ul>
            </div>
        </div>
        <!-- 页面标签 -->
        <div class="layadmin-pagetabs" id="LAY_app_tabs">
            <div class="layui-icon layadmin-tabs-control layui-icon-prev" layadmin-event="leftPage"></div>
            <div class="layui-icon layadmin-tabs-control layui-icon-next" layadmin-event="rightPage"></div>
            <div class="layui-icon layadmin-tabs-control layui-icon-down">
                <ul class="layui-nav layadmin-tabs-select" lay-filter="layadmin-pagetabs-nav">
                    <li class="layui-nav-item" lay-unselect>
                        <a href="javascript:;"></a>
                        <dl class="layui-nav-child layui-anim-fadein">
                            <dd layadmin-event="closeThisTabs"><a href="javascript:;">关闭当前标签页</a></dd>
                            <dd layadmin-event="closeOtherTabs"><a href="javascript:;">关闭其它标签页</a></dd>
                            <dd layadmin-event="closeAllTabs"><a href="javascript:;">关闭全部标签页</a></dd>
                        </dl>
                    </li>
                </ul>
            </div>
            <div class="layui-tab" lay-unauto lay-allowClose="true" lay-filter="layadmin-layout-tabs">
                <ul class="layui-tab-title" id="LAY_app_tabsheader">
                    <li lay-id="<?php echo url('sysIndex'); ?>" lay-attr="<?php echo url('sysIndex'); ?>" class="layui-this"><i class="layui-icon layui-icon-home"></i></li>
                </ul>
            </div>
        </div>
        <!-- 主体内容 -->
        <div class="layui-body" id="LAY_app_body">
            <div class="layadmin-tabsbody-item layui-show">
                <iframe src="<?php echo url('sysDashboard'); ?>" frameborder="0" class="layadmin-iframe"></iframe>
            </div>
        </div>
        <!-- 辅助元素，一般用于移动设备下遮罩 -->
        <div class="layadmin-body-shade" layadmin-event="shade"></div>
    </div>
</div>

<script type="text/javascript">
    var app = { value: null };

    layui.config({
        base: '/static/admin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'form']);

    function editPwd() {
        layer.open({
            type: 2,
            title: '修改密码',
            shade: 0.6,
            area: ['40%', '50%'],
            content: '<?php echo url("sysUserSecurity"); ?>'
        });
    }

    function clearCache() {
        layer.load(2),
        layui.$.getJSON("<?php echo url('sysClearCache'); ?>", r => {
            layer.closeAll(),
            r.code != 0 ? layer.alert(r.message) : parent.layer.msg("SUCCESS");
        })
    }

    function logout() {
        layui.$.ajax({
            type: 'POST',
            url: "<?php echo url('sysLogout'); ?>",
            dataType: 'json',
            beforeSend: () => layer.load(2),
            success: r => {
                if (r.code === 0) {
                    location.reload();
                }
            }
        })
    }
</script>

<script>ns.init();</script>
</body>
</html>