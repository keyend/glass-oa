<?php /*a:2:{s:76:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\Member\index.html";i:1690386238;s:68:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\base.html";i:1688009496;}*/ ?>
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
    <style>
        .layui-btn-group {
            font-size: 0%!important;
        }
        .layui-table-box button {
            font-size: 12px!important;
        }
    </style>
</head>

<body class="layui-layout-body">
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-body" style="padding: 15px 10px!important;">
            <div class="layui-form layui-tab layui-tab-brief" lay-filter="component-tabs-brief">
                <ul class="layui-tab-title">
                    <li class="layui-this"><a href="<?php echo url('member'); ?>">会员列表</a></li>
                    <li><a href="<?php echo url('memberPalst'); ?>">收款账单</a></li>
                </ul>
                <div class="layui-form layui-tab layui-tab-brief" lay-filter="component-tabs-brief">
                    <div class="layui-tab-item layui-show" style="margin: 25px 0 10px 15px;">
                        <div class="layui-form-item" style="margin-bottom: 0px;">
                            <div class="layui-form-item layui-inline">
                                <label class="layui-form-label">搜索类型</label>
                                <div class="layui-input-inline">
                                    <select name="search_type" lay-verify="required" class="layui-select">
                                        <option value="mobile">联系电话</option>
                                        <option value="nickname">客户名称</option>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item layui-inline">
                                <label class="layui-form-label">搜索内容</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="search_value" placeholder="请输入搜索内容" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <button class="layui-btn layui-btn-sm layuiadmin-btn-admin layui-btn-normal" lay-submit lay-filter="LAY-list-back-search">
                                    <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                                    搜索结果
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="layui-card">
        <div class="layui-card-body" style="padding: 15px 10px!important;">
            <table id="LAY-list-table" lay-filter="LAY-list-table"></table>
        </div>
    </div>
</div>

<script type="text/html" id="user-toolbar">
    <button class="pear-btn pear-btn-primary pear-btn-md layui-btn layuiadmin-btn-admin" lay-event="add" data-type="add">
        <i class="layui-icon layui-icon-add-1"></i>新增客户
    </button>
    <button class="pear-btn pear-btn-danger pear-btn-md layui-btn layuiadmin-btn-admin" lay-event="batchRemove" data-type="batchRemove">
        <i class="layui-icon layui-icon-delete"></i>
        批量删除
    </button>
</script>

<script type="text/html" id="user-bar">
    <div class="layui-btn-group">
        <button type="button" class="layui-btn layui-btn-normal layui-btn-xs" lay-event="payable">
            <i class="layui-icon layui-icon-note"></i>收款
        </button>
        <button type="button" class="layui-btn layui-btn-normal layui-btn-xs" lay-event="palst">
            <i class="layui-icon layui-icon-chart-screen"></i>账单
        </button>
        <button type="button" class="layui-btn layui-btn-xs" lay-event="edit">
            <i class="layui-icon layui-icon-edit"></i>编辑
        </button>
        <button type="button" class="layui-btn layui-btn-xs" lay-event="remove">
            <i class="layui-icon layui-icon-delete"></i>删除
        </button>
    </div>
</script> 

<script type="text/html" id="user-enable">
    <input type="checkbox" name="status" value="{{d.id}}" lay-skin="switch" lay-text="启用|禁用" lay-filter="user-enable" {{ d.status == 1 ? 'checked' : '' }}>
</script>

<script type="text/html" id="user-createTime">
    {{layui.util.toDateString(d.createTime, 'yyyy-MM-dd')}}
</script> 

<script type="text/html" id="user-avatar">
    <img src="{{ d.avatar }}" style="width: 30px;height: 30px;border-radius: 50%">
</script> 

<!---->
<script>
    var defaultGroup = 0;

    layui.config({
        base: '/static/admin/'
    }).use(['table', 'form'], function(){
        var $ = layui.$
            ,form = layui.form
            ,table = layui.table
            ,pageTable;

        var active = {
            add: function(){
                ns.open("<?php echo url('memberAdd'); ?>", '添加客户').then(() => {
                    renderTable()
                })
            },
            batchRemove: function(v) {
                parent.layer.confirm('您确定要删除这些客户？', {
                    title: '友情提示',
                    icon: 3,
                    btn: ['是的', '再想想']
                }, function(i) {
                    parent.layer.close(i),
                    ns.silent("<?php echo url('memberDel'); ?>", {id: ns.tableSelected(v, "id")}, res => {
                        if (res.code == 0) {
                            setTimeout(() => pageTable.reload(), 100)
                        } else {
                            layer.alert(res.message)
                        }
                    })
                });
            },
            status: function(id, status) {
                ns.silent('<?php echo url("memberUpdate"); ?>?id=' + id, {status: status?1:0});
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

        form.on('switch(user-enable)', function(obj){
            console.log(obj);
            active['status'].call(this, obj.elem.value, obj.elem.checked)
        }),

        renderTable();

        // 渲染表格
        function renderTable() {
            pageTable = table.render({
                elem: "#LAY-list-table",
                url: "<?php echo url('member'); ?>",
                toolbar: '#user-toolbar',
                cols: [[     
                    {type: 'checkbox'},
                    {title: '客户名称', field: 'nickname', align: 'left'},
                    {title: '联系电话', field: 'mobile', width: 160},
                    {title: '应付', field: 'payable_money', width: 100},
                    {title: '已付', field: 'paid_money', width: 100},
                    {title: '结余', field: 'surplus_money', width: 100},
                    {title: '状态', field: 'status', align: 'center', templet: '#user-enable', width: 100},
                    {title: '创建时间', field: 'create_time', width: 160},/*  templet: '#user-createTime' */
                    {title: '操作', toolbar: '#user-bar', align: 'center', width: 235}
                ]],
                parseData: function(res) {
                    defaultGroup = res.data.default_group || 0;
                    console.log(defaultGroup, "@222");
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
                    parent.layer.confirm('您确定要删除该客户？', {
                        title: '友情提示',
                        icon: 3,
                        btn: ['是的', '再想想']
                    }, function(i) {
                        parent.layer.close(i),
                        ns.silent("<?php echo url('memberDel'); ?>", {id: e.data.id}, res => {
                            if (res.code == 0) {
                                setTimeout(() => pageTable.reload(), 100)
                            } else {
                                layer.alert(res.message)
                            }
                        })
                    });
                } else if ("edit" === e.event) {
                    ns.open("<?php echo url('memberEdit'); ?>?id=" + e.data.id, '编辑客户').then(() => {
                        var ret = ns.getReload();
                        ret && pageTable.reload()
                    })
                } else if("payable" === e.event) {
                    ns.open("<?php echo url('memberPay'); ?>?id=" + e.data.id, '记录【' + e.data.nickname + '】收款').then(() => {
                        var ret = ns.getReload();
                        ret && pageTable.reload()
                    })
                } else if("palst" === e.event) {
                    location.href = "<?php echo url('memberPalst'); ?>?mid=" + e.data.id;
                }
            });
        }
    });

</script>

<script>ns.init();</script>
</body>
</html>