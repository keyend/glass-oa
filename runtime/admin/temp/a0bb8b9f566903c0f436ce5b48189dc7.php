<?php /*a:2:{s:81:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\system\logs\index.html";i:1687838584;s:68:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\base.html";i:1688009496;}*/ ?>
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
</head>

<body class="layui-layout-body">
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-body">
            <div class="layui-tab layui-tab-brief" lay-filter="component-tabs-brief">
                <ul class="layui-tab-title">
                    <?php if(checkAccess('sysLogsLogin')): ?><li<?php if($rule=='sysLogsLogin'): ?> class="layui-this"<?php endif; ?>><a href="<?php echo url('sysLogsLogin'); ?>">账户登录</a></li><?php endif; if(checkAccess('sysLogsOperator')): ?><li<?php if($rule=='sysLogsOperator'): ?> class="layui-this"<?php endif; ?>><a href="<?php echo url('sysLogsOperator'); ?>">操作事件</a></li><?php endif; ?>
                </ul>
                <div class="layui-tab-content">
                    <div class="layui-tab-item layui-show">
                        <div class="layui-form layui-card-header layuiadmin-card-header-auto layui-form-filter">
                            <div class="layui-form-item">
                                <div class="layui-inline">
                                    <label class="layui-form-label" style="width: auto;">时间</label>
                                    <div class="layui-input-block" style="margin-left: 60px;">
                                        <input type="text" name="date" id="date" class="layui-input" readonly />
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label">搜索</label>
                                    <div class="layui-input-block">
                                        <input type="hidden" name="type" value="<?php echo htmlentities($type); ?>" />
                                        <input type="text" name="keyword" placeholder="请输入" autocomplete="off" class="layui-input" />
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <button class="layui-btn layui-btn-sm layuiadmin-btn-admin" lay-submit lay-filter="LAY-user-back-search">
                                        <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!---->
                        <table id="LAY-user-table" lay-filter="LAY-user-table"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/html" id="username">
    <span>{{d.params.username}}</span>
</script>
<script>
    layui.config({
        base: '/static/admin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'table'], function(){
        var $ = layui.$ ,form = layui.form ,table = layui.table;
        // 监听搜索
        form.on('submit(LAY-user-back-search)', function(data){
            var field = data.field;
            // 执行重载
            table.reload('LAY-user-table', {
                where: field
            });
        });
    });

    renderTable();
    // 渲染表格
    function renderTable() {
        layui.use(['table', 'form', 'laydate'], function () {
            var $ = layui.$ 
                ,form = layui.form 
                ,table = layui.table
                ,laydate = layui.laydate
                ,pageTable = table.render({
                    elem: "#LAY-user-table",
                    where: { type: "<?php echo htmlentities($type); ?>" },
                    url: "<?php echo url('sysLogs'); ?>",
                    cols: [
                        [{
                            field: "id",
                            title: "ID",
                            width: "6%"
                        }, {
                            field: "username",
                            title: "用户",
                            width: 140,
                            templet: function(d) {
                                return d.params.username;
                            }
                        }, {
                            field: "create_time",
                            title: "时间",
                            width: 180
                        }, {
                            field: "content",
                            title: "明细",
                        }]
                    ],
                    response: {
                        msgName: 'message'
                    },
                    parseData: function(res) {
                        return {
                            code: res.code,
                            msg: res.message,
                            count: res.data.count,
                            data: res.data.list
                        }
                    },
                    done: function() {
                        ns.page = this.page.curr;
                    },
                    page: !0,
                    limit: 20,
                    height: "full-220",
                    text: "对不起，加载出现异常！",
                });

                laydate.render({
                    elem: '#date',
                    range: '~'
                }),

                table.on("tool(LAY-user-table)", function(e) {
                    if ("del" === e.event) {
                        parent.layer.confirm('您确定要删除该用户组？', {
                            title: '友情提示',
                            icon: 3,
                            btn: ['是的', '再想想']
                        }, function(i) {
                            parent.layer.close(i),
                            ns.silent("<?php echo url('sysGroupDelete'); ?>", {id: e.data.group_id}, res => {
                                if (res.code == 0) {
                                    setTimeout(() => pageTable.reload(), 100)
                                } else {
                                    layer.alert(res.message)
                                }
                            })
                        });
                    } else if ("edit" === e.event) {
                        ns.open("<?php echo url('sysGroupEdit'); ?>?id=" + e.data.group_id, '编辑用户组').then(() => {
                            pageTable.reload();
                        })
                    }
                });
        })
    }
</script>

<script>ns.init();</script>
</body>
</html>