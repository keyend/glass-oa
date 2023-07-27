<?php /*a:2:{s:78:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\Order\converge.html";i:1690468216;s:68:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\base.html";i:1688009496;}*/ ?>
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
            <form class="layui-tab-item layui-form layui-show" style="margin-top: 10px;" id="form1" enctype="application/x-www-form-urlencoded" method="POST">
                <div class="layui-form-item layui-inline" style="margin-bottom: 0px;">
                    <label class="layui-form-label">搜索类型</label>
                    <div class="layui-input-inline" style="display: none;">
                        <select name="search_type" lay-verify="required" class="layui-select">
                            <option value="nickname">客户名称</option>
                        </select>
                    </div>
                    <div class="layui-input-inline">
                        <select name="search_value" class="layui-select">
                            <option value="">客户名称</option>
                            <?php foreach($customers as $customer): ?>
                            <option value="<?php echo htmlentities($customer['nickname']); ?>"><?php echo htmlentities($customer['nickname']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="layui-form-item layui-inline" style="margin-bottom: 0px;">
                    <label class="layui-form-label">送货时间</label>
                    <div class="layui-input-inline">
                      <input type="text" class="layui-input" name="search_time" id="search_time" placeholder=" - ">
                    </div>
                  </div>
                <div class="layui-form-item layui-inline" style="margin-bottom: 0px;">
                    <input type="hidden" name="export" id="export" value="" />
                    <input type="hidden" name="print" id="print" value="" />
                    <span class="layui-btn-group">
                        <button class="layui-btn layui-btn-sm layuiadmin-btn-admin layui-btn-normal" lay-submit lay-filter="LAY-list-back-search">
                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                            搜索结果
                        </button>
                        <button class="layui-btn layui-btn-sm layuiadmin-btn-admin" lay-filter="LAY-list-back-export" lay-submit>
                            <i class="layui-icon layui-icon-export layuiadmin-button-btn"></i>
                            导出记录
                        </button>
                        <button class="layui-btn layui-btn-sm layuiadmin-btn-admin" lay-filter="LAY-list-back-print" lay-submit>
                            <i class="layui-icon layui-icon-print layuiadmin-button-btn"></i>
                            打印记录
                        </button>
                    </span>
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

<script type="text/html" id="user-toolbar">
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
    }).use(['table', 'form', 'laydate'], function(){
        var $ = layui.$
        ,form = layui.form
        ,table = layui.table
        ,laydate = layui.laydate
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

        // 渲染表格
        function renderTable() {
            pageTable = table.render({
                elem: "#LAY-list-table",
                url: "<?php echo url('orderConverge'); ?>",
                toolbar: '#user-toolbar',
                cols: [[
                    {title: '#', field: "id", width: 65, align: 'center'},
                    {title: '开单时间', field: 'create_time', width: 160},
                    {title: '送货时间', field: 'delivery_time', width: 160},
                    {title: '客户名称', field: 'customer', width: 120},
                    {title: '订单编号', field: 'trade_no', width: 140},
                    {title: '产品名称', field: 'category', width: 160},
                    {title: '工艺', field: 'craft', width: 160},
                    {title: '宽mm', field: 'width', width: 100},
                    {title: '高mm', field: 'height', width: 100},
                    {title: '面积m²', field: 'area', width: 100},
                    {title: '数量', field: 'num', width: 100},
                    {title: '单价', field: 'unitprice', width: 100},
                    {title: '加工费', field: 'manual_money', width: 100},
                    {title: '金额', field: 'total_money', width: 100},
                    {title: '备注', field: 'remark', width: 300}
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

        // 监听搜索
        form.on('submit(LAY-list-back-search)', function(data){
            var field = data.field;
            // 执行重载
            table.reload('LAY-list-table', {
                where: field
            });
            return false;
        }),

        form.on('submit(LAY-list-back-export)', function(obj) {
            $("#export").val("1"),
            setTimeout(() => $("#export").val(""), 1000);
        }),

        form.on('submit(LAY-list-back-print)', function(obj) {
            $("#print").val("1"),
            $("#form1").attr("target", "_blank");
            setTimeout(() => {
                $("#print").val(""),
                $("#form1").removeAttr("target")
            }, 1000);
            $("#form1").submit()
        }),

        // 搜索时间
        laydate.render({ elem: '#search_time', type: 'datetime', range: true }),
        form.render();
        renderTable();
    });

</script>

<script>ns.init();</script>
</body>
</html>