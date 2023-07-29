<?php /*a:2:{s:75:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\Order\label.html";i:1690472332;s:68:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\base.html";i:1688009496;}*/ ?>
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
        .layui-table {
            width: 100%!important;
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
                    <div class="layui-input-inline">
                        <select name="search_type" lay-verify="required" class="layui-select">
                            <option value="customer">客户名称</option>
                            <option value="trade_no">订单编号</option>
                            <option value="category">品类名称</option>
                            <option value="craft">工艺名称</option>
                        </select>
                    </div>
                    <div class="layui-input-inline">
                        <input type="text" name="search_value" placeholder="请输入搜索内容" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item layui-inline" style="margin-bottom: 0px;">
                    <label class="layui-form-label">下单时间</label>
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
                        <button class="layui-btn layui-btn-sm layuiadmin-btn-admin layui-btn-danger" lay-filter="LAY-list-back-print" lay-submit>
                            <i class="layui-icon layui-icon-print layuiadmin-button-btn"></i>
                            打印所有标签
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
    <button class="layui-btn layuiadmin-btn-admin layui-btn-sm layui-btn-danger" lay-event="print" data-type="print">
        <i class="layui-icon layui-icon-print"></i>打印标签
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
            print(o) {
                var id = o.config.id;
                var checkStatus = table.checkStatus(id);
                requestPrint(active.getData(checkStatus.data));
            },
            getData(data, id) {
                var result = { data: [] };
                data.forEach(v => {
                    result.data.push({
                        KH: v.customer,
                        DD: v.trade_no,
                        MC: v.category,
                        GG: parseFloat(v.width) + "宽X" + parseFloat(v.height) + "高→" + v.num,
                        GY: v.craft,
                        XH: v.remark
                    })
                });
                return result;
            }
        };

        function requestPrint(data) {
            var loader = parent.layer.load(2, { shade: ['#fff', .3] });
            console.log(JSON.stringify(data));
            $.ajax({
                url: 'http://127.0.0.1:31580/Printer',
                type: 'POST',
                dataType: 'json',
                data: JSON.stringify(data),
                processData: false,
                contentType: "application/json; charset=utf-8",
                complete: function(xhr) {
                    parent.layer.close(loader);
                    var r = JSON.parse(xhr.responseText);
                    if (r.ret == 0) {
                        parent.layer.msg("SUCCESS");
                    } else {
                        parent.layer.alert(r.msg)
                    }
                }
            });
        }

        // 渲染表格
        function renderTable() {
            pageTable = table.render({
                elem: "#LAY-list-table",
                url: "<?php echo url('orderLabel'); ?>",
                toolbar: '#user-toolbar',
                cols: [[
                    {type: 'checkbox'},
                    {title: '开单时间', field: 'create_time', width: 160},
                    {title: '品类名称', field: 'category', width: 160},
                    {title: '工艺', field: 'craft', width: 160},
                    {title: '宽mm', field: 'width', width: 100},
                    {title: '高mm', field: 'height', width: 100},
                    {title: '面积m²', field: 'area', width: 100},
                    {title: '数量', field: 'num', width: 100},
                    {title: '单价', field: 'unitprice', width: 100},
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

        form.on('submit(LAY-list-back-print)', function(obj) {
            var field = obj.field, loader = top.layer.load(2, { shade: ["#fff", .3] });
            field["print"] = 1;
            $.ajax({
                type: 'POST',
                url: location.href,
                data: field,
                dataType: 'json',
                contentType: 'application/json',
                success: function(r) {
                    if (r.code == 0) {
                        requestPrint(active.getData(r.data.list));
                        top.layer.close(loader);
                    } else {
                        top.layer.close(loader);
                        top.layer.msg(r.message);
                    }
                }
            });
            return false;
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