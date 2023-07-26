<?php /*a:2:{s:76:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\Member\palst.html";i:1690389407;s:68:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\base.html";i:1688009496;}*/ ?>
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
            <form class="layui-form layui-tab layui-tab-brief" lay-filter="component-tabs-brief" enctype="application/x-www-form-urlencoded" method="post" action="">
                <ul class="layui-tab-title">
                    <li><a href="<?php echo url('member'); ?>">会员列表</a></li>
                    <li class="layui-this"><a href="<?php echo url('memberPalst'); ?>">收款账单</a></li>
                </ul>
                <div class="layui-form layui-tab layui-tab-brief" lay-filter="component-tabs-brief" method="POST">
                    <div class="layui-tab-item layui-show" style="margin: 25px 0 10px 15px;">
                        <div class="layui-form-item" style="margin-bottom: 0px;">
                            <div class="layui-form-item layui-inline">
                                <label class="layui-form-label">搜索类型</label>
                                <div class="layui-input-inline">
                                    <select name="search_type" lay-verify="required" class="layui-select">
                                        <?php if($customer_id == 0): ?>
                                        <option value="mobile">联系电话</option>
                                        <option value="nickname">客户名称</option>
                                        <?php endif; ?>
                                        <option value="remark">付款备注</option>
                                    </select>
                                </div>
                                <div class="layui-input-inline">
                                    <input type="text" name="search_value" placeholder="请输入搜索内容" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item layui-inline" style="margin-bottom: 0px;">
                                <label class="layui-form-label">收款时间</label>
                                <div class="layui-input-inline">
                                    <input type="text" class="layui-input" name="search_time" id="search_time" placeholder=" - ">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <input type="hidden" name="customer_id" value="<?php echo htmlentities($customer_id); ?>" />
                                <button class="layui-btn layui-btn-sm layuiadmin-btn-admin layui-btn-normal" lay-submit lay-filter="LAY-list-back-search">
                                    <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                                    搜索结果
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="layui-card">
        <div class="layui-card-body" style="padding: 15px 10px!important;">
            <table id="LAY-list-table" lay-filter="LAY-list-table"></table>
        </div>
    </div>
</div>

<script type="text/html" id="user-bar">
    <div class="layui-btn-group">
        <button type="button" class="layui-btn layui-btn-normal layui-btn-xs" lay-event="voucher">
            <i class="layui-icon layui-icon-eye"></i>查看收据
        </button>
    </div>
</script>

<script type="text/html" id="user-createTime">
    {{layui.util.toDateString(d.createTime, 'yyyy-MM-dd')}}
</script>

<script type="text/html" id="user-avatar">
    <img src="{{ d.avatar }}" style="width: 30px;height: 30px;border-radius: 50%">
</script>

<!---->
<script>
    layui.config({
        base: '/static/admin/'
    }).use(['table', 'form', 'laydate'], function(){
        var $ = layui.$
        ,form = layui.form
        ,table = layui.table
        ,laydate = layui.laydate
        ,pageTable;

        form.render();

        var active = {};

        // 渲染表格
        function renderTable() {
            pageTable = table.render({
                elem: "#LAY-list-table",
                url: "<?php echo url('memberPalst'); ?>",
                toolbar: '#user-toolbar',
                where: { customer_id: <?php echo htmlentities($customer_id); ?> },
                cols: [[     
                    {type: 'checkbox'},
                    {title: '客户名称', field: 'customer', width: 160, align: 'left'},
                    {title: '联系电话', field: 'mobile', width: 160},
                    {title: '收款金额', field: 'pay_money', width: 100},
                    {title: '备注说明', field: 'remark', templet: function(data) {
                        return data.pay_info.remark;
                    }},
                    {title: '收款金额', field: 'pay_time', width: 160},
                    {title: '操作', toolbar: '#user-bar', align: 'center', width: 106}
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

        // 监听搜索
        form.on('submit(LAY-list-back-search)', function(data){
            var field = data.field;
            // // 执行重载
            table.reload('LAY-list-table', {
                where: field
            });
            return false;
        }),
        
        // 搜索时间
        laydate.render({ elem: '#search_time', type: 'datetime', range: true }),
        renderTable();
    })
</script>

<script>ns.init();</script>
</body>
</html>