<?php /*a:2:{s:79:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\Member\category.html";i:1689577689;s:68:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\base.html";i:1688009496;}*/ ?>
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

    <link rel="stylesheet" href="/static/component/pear/css/pear.css" />
</head>

<body class="layui-layout-body">
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-body" style="padding: 15px 10px!important;">
            <table id="LAY-list-table" lay-filter="LAY-list-table"></table>
        </div>
    </div>
</div>

<script type="text/html" id="user-toolbar">
    <button class="pear-btn pear-btn-primary pear-btn-md layui-btn layuiadmin-btn-admin" lay-event="add" data-type="add">
        <i class="layui-icon layui-icon-add-1"></i>新增品类
    </button>
    <button class="pear-btn pear-btn-danger pear-btn-md layui-btn layuiadmin-btn-admin" lay-event="batchRemove" data-type="batchRemove">
        <i class="layui-icon layui-icon-delete"></i>
        批量删除
    </button>
</script>

<script type="text/html" id="user-bar">
    <button class="pear-btn pear-btn-primary pear-btn-sm" lay-event="edit"><i class="layui-icon layui-icon-edit"></i></button>
    <button class="pear-btn pear-btn-danger pear-btn-sm" lay-event="remove"><i class="layui-icon layui-icon-delete"></i></button>
</script> 

<!---->
<script>

    layui.config({
        base: '/static/admin/'
    }).use(['table', 'form'], function(){
        var $ = layui.$
            ,form = layui.form
            ,table = layui.table
            ,pageTable;

        var active = {
            add: function(){
                ns.open("<?php echo url('categoryAdd'); ?>", '添加品类').then(() => {
                    renderTable()
                })
            },
            batchRemove: function(v) {
                parent.layer.confirm('您确定要删除这些品类？', {
                    title: '友情提示',
                    icon: 3,
                    btn: ['是的', '再想想']
                }, function(i) {
                    parent.layer.close(i),
                    ns.silent("<?php echo url('categoryDel'); ?>", {id: ns.tableSelected(v, "id")}, res => {
                        if (res.code == 0) {
                            setTimeout(() => pageTable.reload(), 100)
                        } else {
                            layer.alert(res.message)
                        }
                    })
                });
            }
        };

        // 监听搜索
        form.on('submit(LAY-list-back-search)', function(data){
            var field = data.field;

            // 执行重载
            table.reload('LAY-list-table', {
                where: field
            });
        }),

        renderTable();

        // 渲染表格
        function renderTable() {
            pageTable = table.render({
                elem: "#LAY-list-table",
                url: "<?php echo url('category'); ?>",
                toolbar: '#user-toolbar',
                cols: [[     
                    {type: 'checkbox'},
                    {title: '品类名称', field: 'category', align: 'center'},
                    {title: '分组', field: 'group', align: 'center'},
                    {title: '排序', field: 'sort', align: 'center'},
                    {title: '创建时间', field: 'create_time', width: 160},
                    {title: '操作', toolbar: '#user-bar', align: 'center', width: 130}
                ]],
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
                text: "对不起，加载出现异常！"
            });

            table.on('toolbar(LAY-list-table)', function(o) {
                active[o.event] ? active[o.event].call(this, o) : '';
            }),

            table.on("tool(LAY-list-table)", function(e) {
                if ("remove" === e.event) {
                    parent.layer.confirm('您确定要删除该品类？', {
                        title: '友情提示',
                        icon: 3,
                        btn: ['是的', '再想想']
                    }, function(i) {
                        parent.layer.close(i),
                        ns.silent("<?php echo url('categoryDel'); ?>", {id: e.data.id}, res => {
                            if (res.code == 0) {
                                setTimeout(() => pageTable.reload(), 100)
                            } else {
                                layer.alert(res.message)
                            }
                        })
                    });
                } else if ("edit" === e.event) {
                    ns.open("<?php echo url('categoryEdit'); ?>?id=" + e.data.id, '编辑品类').then(() => {
                        var ret = ns.getReload();
                        ret && pageTable.reload()
                    })
                }
            });
        }
    });

</script>

<script>ns.init();</script>
</body>
</html>