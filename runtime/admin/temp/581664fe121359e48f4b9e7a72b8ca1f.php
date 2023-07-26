<?php /*a:2:{s:78:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\order\delivery.html";i:1690294876;s:68:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\base.html";i:1688009496;}*/ ?>
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
</style>
</head>

<body class="layui-layout-body">
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-body" style="padding: 15px 10px!important;">
            <div class="layui-tab layui-tab-brief">
                <ul class="layui-tab-title">
                    <li><a href="<?php echo url('order'); ?>">全部订单</a></li>
                    <li class="layui-this"><a>订单配送</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="layui-card order-delivery">
        <form class="layui-card-body layui-form" method="POST" enctype="application/x-www-form-urlencoded">
            <blockquote class="layui-elem-quote title">
                <p>
                    <span>订单号：<?php echo htmlentities($order['trade_no']); ?></span>
                    <span>录单人员：<?php echo htmlentities($order['user']['username']); ?></span>
                    <span>总金额：<?php echo htmlentities($order['order_money']); ?></span>
                    <span>优惠金额：<?php echo htmlentities($order['discount_money']); ?></span>
                    <span>实际金额：<?php echo number_format($order['order_money'] - $order['discount_money'], 2, '.', ''); ?></span>
                    <span>已付金额：<?php echo htmlentities($order['pay_money']); ?></span>
                </p>
            </blockquote>
            <table class="layui-table">
                <colgroup>
                    <col width="160">
                    <col width="160">
                    <col>
                    <col width="120">
                    <col width="120">
                    <?php if($order['status'] < 2): ?>
                    <col width="120">
                    <?php endif; ?>
                </colgroup>
                <thead> 
                    <tr>
                        <th>品类名称</th>
                        <th>工艺</th>
                        <th>规格</th>
                        <th>加工费</th>
                        <th>已送数量</th>
                        <?php if($order['status'] < 2): ?>
                        <th>本次配送数量</th>
                        <?php endif; ?>
                    </tr> 
                </thead> 
                <tbody id="order-content">
                    <?php foreach($order['goods'] as $goods): ?>
                    <tr>
                        <td><?php echo htmlentities($goods['category']); ?></td>
                        <td><?php echo htmlentities($goods['craft']); ?></td>
                        <td><?php echo htmlentities(floatval($goods['width'])); ?>mm X <?php echo htmlentities(floatval($goods['height'])); ?>mm X <?php echo htmlentities($goods['num']); ?> = <?php echo round($goods['area']*$goods['num'],2); ?>m² X <?php echo htmlentities($goods['unitprice']); ?>元 = <?php echo htmlentities($goods['order_money']); ?>元</td>
                        <td><?php echo htmlentities($goods['manual']); ?>元</td>
                        <td><?php echo htmlentities($goods['deductnum']); ?></td>
                        <?php if($order['status'] < 2): ?>
                        <td>
                            <?php if($goods['max'] > 0): ?>
                            <input type="number" name="num[<?php echo htmlentities($goods['id']); ?>]" data-id="<?php echo htmlentities($goods['id']); ?>" data-max="<?php echo htmlentities($goods['max']); ?>" lay-filter="delivery-num" placeholder="0" class="layui-input layui-number" />
                            <?php else: ?>
                            配送完成
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background-color:#f7f7f7;">
                        <td colspan="4">总计：</td>
                        <td><span><?php echo htmlentities($order['deductnum']); ?></span></td>
                        <?php if($order['status'] < 2): ?>
                        <td><span id="totalNum">--.--</span></td>
                        <?php endif; ?>
                    </tr>
                    <?php if($order['status'] < 2): ?>
                    <tr>
                        <td colspan="6" class="layui-btn-fill">
                            <button type="button" class="layui-btn" lay-filter="form1" lay-submit>生成配送单</button>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tfoot>
            </table>
        </form>
    </div>

    <?php foreach($order['delivery'] as $delivery): ?>
    <div class="layui-card order-delivery">
        <form class="layui-card-body layui-form">
            <blockquote class="layui-elem-quote title">
                <p>
                    <span>配送号：<?php echo htmlentities($delivery['trade_no']); ?></span>
                    <span>配送时间：<?php echo htmlentities($delivery['create_time']); ?></span>
                    <span>配送数量：<?php echo htmlentities($delivery['delivery_num']); ?></span>
                    <span>配送金额：<?php echo htmlentities($delivery['delivery_money']); ?></span>
                    <span>加工金额：<?php echo htmlentities($delivery['manual_money']); ?></span>
                </p>
            </blockquote>
            <table class="layui-table">
                <colgroup>
                    <col width="160">
                    <col width="160">
                    <col>
                    <col width="120">
                    <col width="120">
                </colgroup>
                <thead> 
                    <tr>
                        <th>品类名称</th>
                        <th>工艺</th>
                        <th>规格</th>
                        <th>加工费</th>
                        <th>配送数量</th>
                    </tr> 
                </thead> 
                <tbody id="order-content">
                    <?php foreach($delivery['goods'] as $goods): ?>
                    <tr>
                        <td><?php echo htmlentities($goods['category']); ?></td>
                        <td><?php echo htmlentities($goods['craft']); ?></td>
                        <td><?php echo htmlentities(floatval($goods['width'])); ?>mm X <?php echo htmlentities(floatval($goods['height'])); ?>mm X <?php echo htmlentities($goods['num']); ?> = <?php echo round($goods['area']*$goods['num'],2); ?>m² X <?php echo htmlentities($goods['unitprice']); ?>元 = <?php echo htmlentities($goods['delivery_money']); ?>元</td>
                        <td><?php echo htmlentities($goods['manual_money']); ?>元</td>
                        <td><?php echo htmlentities($goods['num']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background-color:#f7f7f7;">
                        <td colspan="2">
                            <input type="checkbox" name="manual" value="1" data-id="<?php echo htmlentities($delivery['id']); ?>" lay-filter="delivery" title="打印价格" lay-skin="tag" /> 
                            <span style="margin-left: 36px;">总计应收：<?php echo round($delivery['delivery_money'] + $delivery['manual_money'], 2); ?>元</span>
                        </td>
                        <td><span><?php echo htmlentities($delivery['delivery_money']); ?>元</span></td>
                        <td><span><?php echo htmlentities($delivery['manual_money']); ?>元</span></td>
                        <td><span><?php echo htmlentities($delivery['delivery_num']); ?></span></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="layui-btn-fill">
                            <input type="hidden" name="delivery_data" value='<?php echo json_encode($delivery, JSON_UNESCAPED_UNICODE); ?>' />
                            <button type="button" class="layui-btn layui-btn-normal" lay-filter="print" lay-submit>打印配送单</button>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </div>
    <?php endforeach; ?>

</div>

<script>
    var printUrl = "<?php echo url('orderPrint'); ?>";

    layui.config({ base: '/static/admin/' }).use(['form', 'laytpl', 'table'], function(){
        var $ = layui.$, goodsNums = {}, form = layui.form;

        $('input[lay-filter="delivery-num"]').on("input propertychange", function() {
            var id = this.getAttribute("data-id")
            , num = parseInt(this.value)
            , total = 0
            , max = parseInt(this.getAttribute("data-max"));
            num = isNaN(num) ? 0 : num;
            num > max && (num = max, this.value = num),
            goodsNums[id] = num;
            Object.values(goodsNums).forEach(v => total += v);
            document.getElementById("totalNum").innerText = total;
        }),

        form.on('submit(form1)', function(obj) {
            if (Object.values(goodsNums).length < 1) {
                return false;
            }

            parent.layer.prompt({title: '核算无误进行生成配送单？', formType: 2, value: '玻璃加工正常误差±2mm,如发现驶腐质量问题应在验收当天反映，逾期不予受理零'}, function(text, i){
                parent.layer.close(i),
                obj.field["remark"] = text,
                ns.silent(location.href, obj.field, res => {
                    if (res.code == 0) {
                        setTimeout(() => location.reload(), 100)
                    } else {
                        layer.alert(res.message)
                    }
                })
            });
        }),

        form.on('submit(print)', function(obj) {
            var data = JSON.parse(obj.field.delivery_data);
            window.open(printUrl + '?id=' + data.id + '&manual=' + (obj.field.manual || 0))
        })
    })
</script>

<script>ns.init();</script>
</body>
</html>