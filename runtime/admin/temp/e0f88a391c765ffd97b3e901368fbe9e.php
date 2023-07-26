<?php /*a:2:{s:81:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\system\role\index.html";i:1686882132;s:68:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\base.html";i:1688009496;}*/ ?>
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
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body" style="padding: 15px 10px!important;">
                    <div class="layui-tab layui-tab-brief" lay-filter="component-tabs-brief">
                        <ul class="layui-tab-title">
                            <?php if(checkAccess('sysUser')): ?><li<?php if($rule=='sysUser'): ?> class="layui-this"<?php endif; ?>><a href="<?php echo url('sysUser'); ?>">管理员</a></li><?php endif; if(checkAccess('sysGroup')): ?><li<?php if($rule=='sysGroup'): ?> class="layui-this"<?php endif; ?>><a href="<?php echo url('sysGroup'); ?>">管理组</a></li><?php endif; if(checkAccess('sysRole')): ?><li<?php if($rule=='sysRole'): ?> class="layui-this"<?php endif; ?>><a href="<?php echo url('sysRole'); ?>">角色管理</a></li><?php endif; if(checkAccess('sysRule')): ?><li<?php if($rule=='sysRule'): ?> class="layui-this"<?php endif; ?>><a href="<?php echo url('sysRule'); ?>">权限管理</a></li><?php endif; ?>
                        </ul>
                        <div class="layui-tab-content">
                            <div class="layui-tab-item layui-show">
                                <div class="layui-form layui-card-header layuiadmin-card-header-auto layui-form-filter">
                                    <div class="layui-form-item">
                                        <div class="layui-inline btn-group">
                                            <button class="layui-btn layui-btn-sm layui-btn-normal layuiadmin-btn-admin" data-type="add"><i class="layui-icon">&#xe654;</i> 添加角色</button>
                                        </div>
                                        <div class="layui-inline">
                                            <label class="layui-form-label">角色名称</label>
                                            <div class="layui-input-block">
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
    </div>
</div>

<!-- 用户组 -->
<script type="text/html" id="userGroup">
    <span>{{d.group.group}}</span>
</script>

<!-- 用户状态 -->
<script type="text/html" id="userStatus">
    {{# if (!d.is_delete) { }}
    <input type="checkbox" name="switch" lay-skin="switch" lay-text="启用|禁用" lay-filter="status" value="1" data-id="{{d.user_id}}" data-filtered="filtered" disabled="true" checked />
    {{# } else if(d.status == 1) { }}
    <input type="checkbox" name="switch" lay-skin="switch" lay-text="启用|禁用" lay-filter="status" value="1" data-id="{{d.user_id}}" data-filtered="filtered" checked />
    {{# } else { }}
    <input type="checkbox" name="switch" lay-skin="switch" lay-text="启用|禁用" lay-filter="status" value="1" data-id="{{d.user_id}}" data-filtered="filtered" />
    {{# } }}
</script>

<!-- 操作 -->
<script type="text/html" id="actionTpl">
    <div class="layui-btn-group">
        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i>编辑</a>
        {{#  if(d.internal != 1){ }}
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del"><i class="layui-icon layui-icon-delete"></i>删除</a>
        {{#  } }}
    </div>
</script>

<script>
    layui.config({
        base: '/static/admin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'table'], function(){
        var $ = layui.$ ,form = layui.form ,table = layui.table;
        var active = {
            add: function(){
                ns.open("<?php echo url('sysRoleAdd'); ?>", '添加角色').then(() => {
                    renderTable()
                })
            },
            status: function(uid, status){
                ns.silent('<?php echo url("sysRoleStatus"); ?>?id=' + uid, {status: status?1:0});
            }
        };

        $('.layui-btn.layuiadmin-btn-admin').on('click', function(){
            var type = $(this).data('type');
            active[type] ? active[type].call(this) : '';
        });

        form.on('switch(status)', function(obj){
            active.status(obj.elem.getAttribute('data-id'), obj.elem.checked)
        }),

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
        layui.use(['table', 'form'], function () {
            var $ = layui.$ 
                ,form = layui.form 
                ,table = layui.table
                ,pageTable = table.render({
                    elem: "#LAY-user-table",
                    url: "<?php echo url('sysRole'); ?>",
                    cols: [
                        [{
                            field: "role_id",
                            title: "ID",
                            width: "6%"
                        }, {
                            field: "role",
                            title: "角色",
                            width: 200
                        }, {
                            field: "remark",
                            title: "备注",
                        }, {
                            title: "操作",
                            width: 150,
                            fixed: "right",
                            toolbar: "#actionTpl"
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

                console.log(pageTable);

                table.on("tool(LAY-user-table)", function(e) {
                    if ("del" === e.event) {
                        parent.layer.confirm('您确定要删除该角色？', {
                            title: '友情提示',
                            icon: 3,
                            btn: ['是的', '再想想']
                        }, function(i) {
                            parent.layer.close(i),
                            ns.silent("<?php echo url('sysRoleDelete'); ?>", {id: e.data.role_id}, res => {
                                if (res.code == 0) {
                                    setTimeout(() => pageTable.reload(), 100)
                                } else {
                                    layer.alert(res.message)
                                }
                            })
                        });
                    } else if ("edit" === e.event) {
                        ns.open("<?php echo url('sysRoleEdit'); ?>?id=" + e.data.role_id, '编辑角色').then(() => {
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