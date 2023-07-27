<?php /*a:2:{s:75:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\Order\index.html";i:1690467442;s:68:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\base.html";i:1688009496;}*/ ?>
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
        .layui-bg-green, .bg-green {
            background-color: rgb(1, 129, 1)!important;
        }
        .layui-bg-green1 {
            background-color: rgb(93, 122, 78)!important;
        }
        .layui-bg-dark {
            background-color: rgb(119, 120, 119)!important;
        }
        .layui-bg-red {
            background-color: rgb(175, 1, 1)!important;
        }
        .layui-badge:hover {
            color: white !important;
        }
    </style>
</head>

<body class="layui-layout-body">
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-body" style="padding: 15px 10px!important;">
            <div class="layui-form layui-tab layui-tab-brief" lay-filter="component-tabs-brief">
                <ul class="layui-tab-title">
                    <li<?php if($status_id=='all'): ?> class="layui-this"<?php endif; ?>><a href="<?php echo url('order'); ?>">全部订单</a></li>
                    <li<?php if($status_id==0 && $status_id!='all'): ?> class="layui-this"<?php endif; ?>><a href="<?php echo url('orderList', ['id' => 0]); ?>">待配送</a></li>
                    <li<?php if($status_id==1): ?> class="layui-this"<?php endif; ?>><a href="<?php echo url('orderList', ['id' => 1]); ?>">配送中</a></li>
                    <li<?php if($status_id==2): ?> class="layui-this"<?php endif; ?>><a href="<?php echo url('orderList', ['id' => 2]); ?>">已完成</a></li>
                    <li<?php if($status_id==3): ?> class="layui-this"<?php endif; ?>><a href="<?php echo url('orderList', ['id' => 3]); ?>">已作廢</a></li>
                </ul>
                <div class="layui-tab-content">
                    <div class="layui-tab-item layui-show" style="margin-top: 10px;">
                        <div class="layui-form-item layui-inline" style="margin-bottom: 0px;">
                            <label class="layui-form-label">搜索类型</label>
                            <div class="layui-input-inline">
                                <select name="search_type" lay-verify="required" class="layui-select">
                                    <option value="trade_no">订单编号</option>
                                    <option value="nickname">客户名称</option>
                                    <option value="address">配送地址</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-form-item layui-inline" style="margin-bottom: 0px;">
                            <label class="layui-form-label">搜索内容</label>
                            <div class="layui-input-inline">
                                <input type="text" name="search_value" placeholder="请输入搜索内容" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item layui-inline" style="margin-bottom: 0px;">
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
    <div class="layui-card">
        <div class="layui-card-body" style="padding: 15px 10px!important;">
            <table id="LAY-list-table" lay-filter="LAY-list-table"></table>
        </div>
    </div>
</div>

<script type="text/html" id="user-toolbar">
    <button class="pear-btn pear-btn-primary pear-btn-md layui-btn layuiadmin-btn-admin" lay-event="add" data-type="add">
        <i class="layui-icon layui-icon-add-1"></i>新增订单
    </button>
</script>

<script type="text/html" id="user-bar">
    <a class="layui-badge layui-bg-blue" lay-event="detail" href="javascript:void(0);">详情</a>
    {{# if (d.is_trash == 0 && d.status != 2) { }}
    <a class="layui-badge layui-bg-red" lay-event="trash" href="javascript:void(0);">作废</a>
    {{# } }}
    {{# if (d.is_trash == 0) { }}
    {{# if (d.status == 0) { }}
    <a class="layui-badge layui-bg-dark" lay-event="delivery" href="javascript:void(0);">待配送{{ d.process }}%</a>
    {{# } }}
    {{# if (d.status == 1) { }}
    <a class="layui-badge layui-bg-green1" lay-event="delivery" href="javascript:void(0);">配送中{{ d.process }}%</a>
    {{# } }}
    {{# if (d.status == 2) { }}
    <a class="layui-badge layui-bg-green" lay-event="delivery" href="javascript:void(0);">已完成</a>
    {{# } }}
    {{# } }}
</script> 

<script type="text/html" id="user-auth">
    {{#if (d.is_trash == 1) { }}
    <span class="layui-badge layui-bg-dark">已作废</span>
    {{# }else if(d.status == 0){ }}
    <span class="layui-badge layui-bg-blue">待配送</span>
    {{# }else if(d.status == 1){ }}
    <span class="layui-badge layui-bg-green">配送中</span>
    {{# }else if(d.status == 2){ }}
    <span class="layui-badge layui-bg-red">已完成</span>
    {{# } }}
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
            add: function() {
                location.href = "<?php echo url('orderAdd'); ?>";
            },
            trash: function(e) {
                parent.layer.confirm("确认要作废本单吗，作废后无法恢复！",  {icon: 3, title:'提示'}, index => {
                    parent.layer.close(index);
                    var loader = parent.layer.load(2, { shade: ["#fff", .3] });
                    ns.silent("<?php echo url('orderTrash'); ?>", {id: e.data.id}, res => {
                        parent.layer.close(loader);
                        if (res.code == 0) {
                            setTimeout(() => renderTable(), 100)
                        } else {
                            layer.alert(res.message)
                        }
                    })
                })
            },
            delivery: function(e) {
                location.href = "<?php echo url('orderDelivery'); ?>?order_id=" + e.data.id
            },
            detail: function(e) {
                location.href = "<?php echo url('orderDetail'); ?>?order_id=" + e.data.id
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
                url: "<?php echo url('orderList', ['id' => $status_id]); ?>",
                toolbar: '#user-toolbar',
                cols: [[
                    {type: 'checkbox'},
                    {title: '订单号', field: 'trade_no', width: 115},
                    {title: '客户', field: 'customer', width: 220},
                    {title: '客户手机', field: 'mobile', width: 110},
                    {title: '订单金额', field: 'order_money', width: 100},
                    {title: '已收金额', field: 'pay_money', width: 100},
                    {title: '优惠金额', field: 'discount_money', width: 100},
                    {title: '状态', field: 'status', align: 'center', templet: '#user-auth', width: 82},
                    {title: '下单时间', field: 'create_time', align: 'center'},
                    {title: '操作', toolbar: '#user-bar', width: 220}
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
                active[e.event] ? active[e.event].call(this, e) : '';
            });
        }
    });

</script>

<script>ns.init();</script>
</body>
</html>