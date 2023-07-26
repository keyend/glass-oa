<?php /*a:2:{s:76:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\Order\detail.html";i:1690289723;s:68:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\base.html";i:1688009496;}*/ ?>
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

<style>
    .page-member-form .layui-tab-content {
        padding: 15px 15px 0 0!important;
    }
    .page-member-form .layui-card {
        padding-bottom: 5px;
    }
    .layui-number {
        width: 60px; 
        display: inline-block; 
        text-align: center; 
        margin-right: 6px;
    }
    .layui-number:not(:first-of-type) {
        margin-left: 6px;
    }
    .order-delivery .layui-elem-quote.title span {
        margin-right: 12px;
    }
    .order-delivery .layui-elem-quote.title span.layui-badge {
        margin-left: -12px;
    }
    .order-delivery .layui-number {
        text-align: left;
        width: 100%;
    }
    .layui-btn-fill {
        padding: 0px!important;
        margin: -1px;
    }
    .layui-btn-fill .layui-btn {
        width: 100%;
    }
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
</style>
</head>

<body class="layui-layout-body">
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-body" style="padding: 15px 10px!important;">
            <div class="layui-tab layui-tab-brief">
                <ul class="layui-tab-title">
                    <li><a href="<?php echo url('order'); ?>">全部订单</a></li>
                    <li class="layui-this"><a>订单明细</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="layui-card order-delivery">
        <form class="layui-card-body layui-form" method="POST" enctype="application/x-www-form-urlencoded">
            <blockquote class="layui-elem-quote title">
                <p>
                    <span>订单号：<?php echo htmlentities($order['trade_no']); ?></span>
                    <?php if($order['is_trash']): ?>
                    <span class="layui-badge layui-bg-dark">已作废</span>
                    <?php endif; ?>
                    <span>录单人员：<?php echo htmlentities($order['user']['username']); ?></span>
                    <span>总金额：<?php echo htmlentities($order['order_money']); ?></span>
                    <span>优惠金额：<?php echo htmlentities($order['discount_money']); ?></span>
                    <span>实际金额：<?php echo number_format($order['order_money'] - $order['discount_money'], 2, '.', ''); ?></span>
                    <span>已付金额：<?php echo htmlentities($order['pay_money']); ?></span>
                </p>
            </blockquote>
            <table class="layui-table">
                <colgroup>
                    <col>
                    <col width="160">
                    <col width="120">
                    <col width="120">
                    <col width="120">
                    <col width="120">
                    <col width="120">
                    <col width="120">
                    <col width="120">
                </colgroup>
                <thead> 
                    <tr>
                        <th>品类名称</th>
                        <th>工艺</th>
                        <th>宽</th>
                        <th>高</th>
                        <th>面积</th>
                        <th>单价</th>
                        <th>加工费</th>
                        <th>数量</th>
                        <th></th>
                    </tr> 
                </thead> 
                <tbody id="order-content">
                    <?php foreach($order['goods'] as $goods): ?>
                    <tr>
                        <td><?php echo htmlentities($goods['category']); ?></td>
                        <td><?php echo htmlentities($goods['craft']); ?></td>
                        <td><?php echo htmlentities($goods['width']); ?>mm</td>
                        <td><?php echo htmlentities($goods['height']); ?>mm</td>
                        <td><?php echo htmlentities($goods['area']); ?>m²</td>
                        <td><?php echo htmlentities($goods['unitprice']); ?>元</td>
                        <td><?php echo htmlentities($goods['manual_money']); ?>元</td>
                        <td><?php echo htmlentities($goods['num']); ?></td>
                        <td>
                            <button type="button" class="layui-btn layui-btn-sm layui-btn-normal" lay-filter="print" data-id="<?php echo htmlentities($goods['id']); ?>" data-orderid="<?php echo htmlentities($order['id']); ?>">打印标签</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background-color:#f7f7f7;">
                        <td colspan="9">
                            <span>总计应收：</span>
                            <span><?php echo htmlentities($order['order_money']); ?>元</span>
                            <span> + </span>
                            <span><?php echo htmlentities($order['manual_money']); ?>元</span>
                            <span> = </span>
                            <span><?php echo round($order['order_money'] + $order['manual_money'], 2); ?>元</span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="9" class="layui-btn-fill">
                            <input type="hidden" name="delivery_data" id="delivery_data_<?php echo htmlentities($order['id']); ?>" value='<?php echo json_encode($order, JSON_UNESCAPED_UNICODE); ?>' />
                            <button type="button" class="layui-btn layui-btn-normal" lay-filter="form1" lay-submit>打印所有标签</button>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </div>
</div>

<script>
    layui.config({ base: '/static/admin/' }).use(['form', 'laytpl', 'table'], function(){
        var $ = layui.$, goodsNums = {}, form = layui.form;

        function getPrintArr(data, id) {
            var result = {};
            id = id || 0,
            result.data = [],
            data.goods.forEach(v => {
                result.data.push({
                    KH: data.customer,
                    DD: data.out_trade_no,
                    MC: v.category,
                    GG: parseFloat(v.width) + "宽X" + parseFloat(v.height) + "高=" + v.area + "m²",
                    GY: v.craft,
                    XH: v.remark
                })
            });
            return result;
        }

        function requestPrint(data) {
            var loader = parent.layer.load(2, { shade: ['#fff', .3] });
            console.log(JSON.stringify(data));
            $.ajax({
                url: 'http://192.168.1.21:31580/Printer',
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

        form.on('submit(form1)', function(obj) {
            var field = JSON.parse(obj.field.delivery_data) ,data = getPrintArr(field);
            requestPrint(getPrintArr(field));
        }),

        $('button[lay-filter="print"]').on("click", function(e) {
            var that = $(this), field = JSON.parse($("#delivery_data_" + that.data("orderid")).val());
            requestPrint(getPrintArr(field), that.data("id"));
        })
    })
</script>

<script>ns.init();</script>
</body>
</html>