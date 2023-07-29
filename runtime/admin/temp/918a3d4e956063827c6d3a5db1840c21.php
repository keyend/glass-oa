<?php /*a:2:{s:74:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\Order\form.html";i:1690631983;s:68:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\base.html";i:1688009496;}*/ ?>
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
    .layui-table {
        width: 100%!important;
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
                    <li class="layui-this"><a href="<?php echo url('order'); ?>">新增订单</a></li>
                </ul>
                <div class="layui-tab-content">
                    <form
                        action=""
                        class="layui-form layui-tab-item layui-show"
                        style="margin-top: 10px;"
                        method="POST"
                        enctype="application/x-www-form-urlencoded"
                    >
                        <div class="layui-form-item layui-inline" style="margin-bottom: 0px;">
                            <label class="layui-form-label">客户名称</label>
                            <div class="layui-input-inline">
                                <input name="aaaa" id="aaaa" placeholder="" type="hidden" value="22222" />
                                <select class="layui-select" name="customer_id" id="customer_id" lay-verify="required" lay-filter="customer" lay-search>
                                    <?php foreach($customers as $customer): ?>
                                    <option value="<?php echo htmlentities($customer['id']); ?>" data-category='<?php echo $customer['category']; ?>' data-minarea="<?php echo htmlentities(floatval($customer['minarea'])); ?>"><?php echo htmlentities($customer['nickname']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="layui-form-item layui-inline" style="margin-bottom: 0px;">
                            <button class="layui-btn layui-btn-sm layuiadmin-btn-admin layui-btn-normal" lay-filter="form1" lay-submit>
                                <i class="layui-icon layui-icon-release layuiadmin-button-btn"></i>
                                提交订单
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="layui-card">
        <div class="layui-card-body layui-form" style="padding: 15px 10px!important;">
            <table class="layui-table">
                <colgroup>
                    <col width="70" />
                    <col width="200" />
                    <col width="160" />
                    <col width="100" />
                    <col />
                    <col width="180" />
                    <col width="100" />
                    <col width="100" />
                </colgroup>
                <thead> 
                    <tr> 
                        <th>
                            <button type="button" class="layui-btn layui-btn-primary layui-btn-sm" lay-filter="add">
                                <i class="layui-icon layui-icon-addition"></i>
                            </button>
                        </th>
                        <th>品类名称</th>
                        <th>工艺</th>
                        <th>加工费</th>
                        <th>玻璃面积（单位m² = 宽 X 高）X 数量</th>
                        <th>备注</th>
                        <th>单价</th>
                        <th>合计金额</th>
                    </tr> 
                </thead> 
                <tbody id="order-content"></tbody>
                <tfoot>
                    <tr style="background-color:#f7f7f7;">
                        <td colspan="7">总计：</td><td>&yen;
                        <span id="row_count">--.--</span></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script id="row" type="text/html">
    <tr id="item_{{ d.id }}"> 
        <td>
            <button type="button" class="layui-btn layui-btn-primary layui-btn-sm" lay-delete>
                <i class="layui-icon layui-icon-delete"></i>
            </button>
        </td>
        <td>
            <select name="category_id[{{ d.id }}]" data-name="category" class="layui-select" lay-filter="category">
                <?php foreach($categorys as $category): ?>
                <option value="<?php echo htmlentities($category['id']); ?>"><?php echo htmlentities($category['category']); ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <select name="craft_id[{{ d.id }}]" data-name="craft" class="layui-select" lay-filter="craft">
                <?php foreach($crafts as $craft): ?>
                <option value="<?php echo htmlentities($craft['id']); ?>"><?php echo htmlentities($craft['craft']); ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td> 
            <input type="number" name="manual[{{ d.id }}]" data-name="manual" placeholder="0.00" class="layui-input layui-number" />
        </td>
        <td> 
            <input type="number" name="width[{{ d.id }}]" data-name="width" placeholder="宽" class="layui-input layui-number" /> X
            <input type="number" name="height[{{ d.id }}]" data-name="height" placeholder="高" class="layui-input layui-number" /> X
            <input type="number" name="num[{{ d.id }}]" data-name="num" value="1" placeholder="数量" class="layui-input layui-number" /> =
            <input type="hidden" name="area[{{ d.id }}]" data-name="area" value="0" />
            <span class="area">
                <font class="label-area">--.--</font>m² X 
                <font class="label-unitprice">--.--</font>元 = 
                <font class="label-price">--.--</font>元
            </span>
        </td>
        <td>
            <input type="text" name="remark[{{ d.id }}]" data-name="remark" placeholder="" class="layui-input layui-text" />
        </td>
        <td>&yen;
            <span class="label-unitprice">--.--</span>
        </td>
        <td>&yen;
            <span class="label-price">--.--</span>
        </td>
    </tr>
</script>
<script>
    var customer = "<?php echo htmlentities($customer); ?>"
    ,customer_id = "<?php echo htmlentities($customer_id); ?>"
    ,craft_id = "<?php echo htmlentities($craft_id); ?>"
    ,category_id = "<?php echo htmlentities($category_id); ?>"
    ,category = <?php echo $customer_category; ?>
    ,customer_minarea = <?php echo htmlentities(floatval($customer_minarea)); ?>
    ,data = [];

    layui.config({
        base: '/static/admin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'form', 'laytpl', 'table'], function(){
        var $ = layui.$
        ,admin = layui.admin
        ,element = layui.element
        ,form = layui.form
        ,table = layui.table
        ,context = $('#order-content');

        if (customer_id == "0") {
            parent.layer.msg("客户列表为空!");
            return false;
        }

        function float(val) {
            val = parseFloat(val);
            if (isNaN(val)) {
                return 0;
            }
            return val;
        }

        function getUnitPrice(cid) {
            if (typeof(category[cid]) == "undefined") {
                return 0;
            }
            return float(category[cid]);
        }

        function updateTotal() {
            var total = 0;
            Object.values(data).forEach(v => {
                console.log(v),
                total += v.price
            }),
            $('#row_count').text(total)
        }

        function createRow() {
            var id = (new Date()).getTime()
            ,rid = "#item_" + id
            ,templet = layui.laytpl(document.getElementById("row").innerHTML).render({ id: id });

            data[id] = {width: 0, height: 0, num: 0, manual: 0, unitprice: 0, category_id: category_id, craft_id: craft_id, status: 0, remark: ''},
            data[id]["unitprice"] = getUnitPrice(category_id),
            context.append(templet),
            context.find(rid).find('input[type="text"]').on("input propertychange", function() {
                var that = $(this);
                data[id][that.data("name")] = that.val();
            }),
            context.find(rid).find('button[lay-delete]').on('click', function() {
                delete data[id];
                context.find(rid).remove()
            }),
            context.find(rid).find('input[type="number"]').on("focus", function() {
                this.select();
            }).on("input propertychange", function() {
                var that = $(this).parent().parent()
                ,_w = that.find('input[data-name="width"]')
                ,_h = that.find('input[data-name="height"]')
                ,_n = that.find('input[data-name="num"]')
                ,_m = that.find('input[data-name="manual"]')
                ,_a = that.find('input[data-name="area"]')
                ,_r = that.find('input[data-name="remark"]');

                data[id]['width'] = float(_w.val())
                ,data[id]['height'] = float(_h.val())
                ,data[id]['num'] = float(_n.val())
                ,data[id]['manual'] = float(_m.val())
                ,data[id]['area'] = Math.round(float(data[id]['width'] * data[id]['height']) / 10000) / 100
                ,console.log(data[id]['area'])
                ,data[id]['area'] = data[id]['area'] < customer_minarea ? customer_minarea : data[id]['area']
                ,data[id]['price'] = Math.round((float(data[id]['area'] * data[id]['num'] * data[id]['unitprice']) + (data[id]['manual'] * data[id]['num'])) * 100) / 100
                ,data[id]['remark'] = _r.val()
                ,data[id]['status'] = 1
                ,_w.val(data[id]['width'])
                ,_h.val(data[id]['height'])
                ,_n.val(data[id]['num'])
                ,_a.val(data[id]['area'])
                ,that.find(".label-area").text(Math.round(data[id]['area'] * data[id]['num'] * 100) / 100)
                ,that.find(".label-unitprice").text(data[id]['unitprice'])
                ,that.find(".label-price").text(data[id]['price'])
                updateTotal()
            }),

            form.render()
        }

        $('.layui-table').find('button[lay-filter="add"]').on("click", function() {
            createRow()
        }).trigger("click"),

        form.on("select(customer)", function(obj) {
            obj.elem.childNodes.forEach((v,i) => {
                if (v.value == obj.value) {
                    category = JSON.parse(v.getAttribute("data-category"));
                    customer = v.innerText;
                    customer_minarea = parseFloat(v.getAttribute("data-minarea"));
                    if (isNaN(customer_minarea)) {
                        parent.layer.msg("错误：下单的客户默认单件最小面积错误!");
                        return false;
                    }
                }
            }),

            Object.keys(data).forEach(function (id) {
                data[id]['unitprice'] = getUnitPrice(data[id]['category_id']);
                $("#item_" + id).find('input[data-name="num"]').trigger("input")
            })
        }),

        form.on("select(category)", function(obj) {
            if (customer == null) {
                parent.layer.msg("错误：请选择要下单的客户!");
                return false;
            }

            var title = "";
            obj.elem.childNodes.forEach((v,i) => {
                if (v.value == obj.value) {
                    title = v.innerText;
                }
            });

            var that = $(obj.elem).parent().parent(), id = that.attr("id").replace('item_', '');
            if (typeof(category[obj.value]) == "undefined") {
                parent.layer.msg("错误：客户" + customer + "未定义" + title + "单价!");
                return false;
            }

            data[id]['unitprice'] = float(category[obj.value]),
            data[id]['category_id'] = obj.value,

            that.find('input[data-name="num"]').trigger("input")
        }),

        form.on("select(craft)", function(obj) {
            var that = $(obj.elem).parent().parent(), id = that.attr("id").replace('item_', '');
            data[id]['craft_id'] = obj.value;
        }),

        form.on('submit(form1)', function(obj) {
            obj.field["goods"] = [];
            Object.values(data).forEach(function(v) {
                if (v.status) {
                    obj.field["goods"].push(v)
                }
            });
            
            if (obj.field["goods"].length <= 0) {
                parent.layer.msg("入单不能为空订单！");
                return false;
            }

            ns.wost(location.href, obj.field, res => {
                if (res.code == 0) {
                    location.replace("/admin/order.html");
                }
            });
            return false;
        }),

        form.render()
    });
</script>

<script>ns.init();</script>
</body>
</html>