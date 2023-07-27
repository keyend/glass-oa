<?php /*a:2:{s:83:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\Order\delivery_list.html";i:1690387450;s:68:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\base.html";i:1688009496;}*/ ?>
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
            <div class="layui-form layui-tab layui-tab-brief" lay-filter="component-tabs-brief">
                <ul class="layui-tab-title">
                    <li<?php if($is_trash==0): ?> class="layui-this"<?php endif; ?>><a href="<?php echo url('delivery'); ?>">全部配送单</a></li>
                    <li<?php if($is_trash==1): ?> class="layui-this"<?php endif; ?>><a href="<?php echo url('delivery', ['is_trash' => 1]); ?>">已作廢</a></li>
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
                            <label class="layui-form-label">配送时间</label>
                            <div class="layui-input-inline">
                              <input type="text" class="layui-input" name="search_time" id="search_time" placeholder=" - ">
                            </div>
                          </div>
                        <div class="layui-form-item layui-inline" style="margin-bottom: 0px;">
                            <input type="hidden" name="page" value="1" id="list-page" />
                            <input type="hidden" name="is_trash" value="<?php echo htmlentities($is_trash); ?>" />
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
    <div id="LAY-list-table" lay-filter="LAY-list-table" style="margin-bottom: 20px;"></div>
    <div class="layui-card order-delivery-page">
        <div class="layui-card-body">
            <div id="LAY-list-page" layer-filter="LAY-list-page"></div>
        </div>
    </div>
</div>
<script type="text/html" id="tpl_table_list">
    {{#  layui.each(d.list, function(index, v){ }}
    <div class="layui-card order-delivery">
        <form class="layui-card-body layui-form">
            <blockquote class="layui-elem-quote title">
                <p>
                    <span>配送号：{{= v.trade_no }}</span>
                    <span>配送时间：{{= v.create_time }}</span>
                    <span>配送数量：{{= v.delivery_num }}</span>
                    <span>配送金额：{{= v.delivery_money }}</span>
                    <span>加工金额：{{= v.manual_money }}</span>
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
                    {{#  layui.each(v.goods, function(index, goods){ }}
                    <tr>
                        <td>{{= goods.category }}</td>
                        <td>{{= goods.craft }}</td>
                        <td>{{= goods.umb }}</td>
                        <td>{{= goods.manual_money }}</td>
                        <td>{{= goods.num }}</td>
                    </tr>
                    {{#  }) }} 
                </tbody>
                <tfoot>
                    <tr style="background-color:#f7f7f7;">
                        <td colspan="2">
                            <input type="checkbox" name="manual" value="1" data-id="{{= v.id}}" lay-filter="delivery" title="打印价格" lay-skin="tag" /> 
                            <span style="margin-left: 36px;">总计应收：{{= v.total_money }}元</span>
                        </td>
                        <td><span>{{= v.delivery_money }}元</span></td>
                        <td><span>{{= v.manual_money }}元</span></td>
                        <td><span>{{= v.delivery_num }}</span></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="layui-btn-fill">
                            <input type="hidden" name="delivery_data" value="{{= JSON.stringify(v) }}" />
                            <button type="button" class="layui-btn layui-btn-normal" lay-filter="print" lay-submit>打印配送单</button>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </div>
    {{#  }) }} 
</script>

<script>
    var printUrl = "<?php echo url('orderPrint'); ?>";

    layui.config({ base: '/static/admin/' }).use(['table', 'form', 'laydate', 'laytpl', 'laypage'], function(){
        var $ = layui.$
        ,form = layui.form
        ,table = layui.table
        ,laytpl = layui.laytpl
        ,laypage = layui.laypage
        ,pageTable
        ,laydate = layui.laydate
        ,init = 0;

        // 渲染表格
        function renderTable(filter) {
            console.log(filter);
            $.ajax({
                url: location.href,
                type: 'POST',
                data: filter,
                dataType: 'json',
                beforeSend: () => layer.load(2, { shade: ['#fff', .2] }),
                success(r) {
                    layer.closeAll();
                    if (r.code != 0) {
                        layer.msg(r.message);
                    } else {
                        init === 0 && (init = 1,laypage.render({
                            elem: 'LAY-list-page',
                            count: r.data.count,
                            jump(obj, first) {
                                !first && ($('#list-page').val(obj.curr),
                                $('button[lay-filter="LAY-list-back-search"]').trigger('click'))
                            }
                        })),
                        laytpl(document.getElementById("tpl_table_list").innerHTML).render(r.data, function(context) {
                            $("#LAY-list-table").html(context),
                            form.render()
                        })
                    }
                }
            })
        }

        // 搜索时间
        laydate.render({ elem: '#search_time', type: 'datetime', range: true });

        // 监听搜索
        form.on('submit(LAY-list-back-search)', function(data){
            var field = data.field;
            renderTable(field);
        }),

        form.on('submit(print)', function(obj) {
            var data = JSON.parse(obj.field.delivery_data);
            window.open(printUrl + '?id=' + data.id + '&manual=' + (obj.field.manual || 0))
        })

        $('button[lay-filter="LAY-list-back-search"]').trigger("click")
    })
</script>

<script>ns.init();</script>
</body>
</html>