<?php /*a:2:{s:84:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\Order\converge_print.html";i:1690466583;s:68:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\base.html";i:1688009496;}*/ ?>
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
    .layui-table {
        border-color: #000000!important;
        border-spacing: 1px 1px;
    }
    .layui-table td, .layui-table th {
        text-align: center;
        padding: 2.25px 3.25px!important;
    }
</style>
</head>

<body class="layui-layout-body">
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-body">
            <h1 style="text-align: center; line-height: 60px; margin-bottom: 10px; margin-top: -20px;">
                <span style="display: inline-block; border-bottom: 2px solid #eee;"><?php echo htmlentities($option['site_title']); ?>订单汇总</span>
            </h1>
            <div class="layui-row">
                <div class="layui-col-xs6">
                    当前时间：<?php echo date("Y-m-d H:i"); ?><br />
                </div>
                <div class="layui-col-xs6">
                    操作员：<?php echo S2; ?>
                </div>
            </div>
            <table cellspacing="0" cellpadding="0" border="0" class="layui-table" style="width: 100%;">
                <thead>
                <tr>
                    <th data-field="id" data-key="1-0-0" title="#">
                    <div class="layui-table-cell laytable-cell-1-0-0" align="center">
                    <span>#</span>
                    </div></th>
                    <th data-field="create_time" data-key="1-0-1" title="开单时间">
                    <div class="layui-table-cell laytable-cell-1-0-1">
                    <span>开单时间</span>
                    </div></th>
                    <th data-field="delivery_time" data-key="1-0-2" title="送货时间">
                    <div class="layui-table-cell laytable-cell-1-0-2">
                    <span>送货时间</span>
                    </div></th>
                    <th data-field="customer" data-key="1-0-3" title="客户名称">
                    <div class="layui-table-cell laytable-cell-1-0-3">
                    <span>客户名称</span>
                    </div></th>
                    <th data-field="trade_no" data-key="1-0-4" title="订单编号">
                    <div class="layui-table-cell laytable-cell-1-0-4">
                    <span>订单编号</span>
                    </div></th>
                    <th data-field="category" data-key="1-0-5" title="产品名称">
                    <div class="layui-table-cell laytable-cell-1-0-5">
                    <span>产品名称</span>
                    </div></th>
                    <th data-field="craft" data-key="1-0-6" title="工艺">
                    <div class="layui-table-cell laytable-cell-1-0-6">
                    <span>工艺</span>
                    </div></th>
                    <th data-field="width" data-key="1-0-7" title="宽mm">
                    <div class="layui-table-cell laytable-cell-1-0-7">
                    <span>宽mm</span>
                    </div></th>
                    <th data-field="height" data-key="1-0-8" title="高mm">
                    <div class="layui-table-cell laytable-cell-1-0-8">
                    <span>高mm</span>
                    </div></th>
                    <th data-field="area" data-key="1-0-9" title="面积m&sup2;">
                    <div class="layui-table-cell laytable-cell-1-0-9">
                    <span>面积m&sup2;</span>
                    </div></th>
                    <th data-field="num" data-key="1-0-10" title="数量">
                    <div class="layui-table-cell laytable-cell-1-0-10">
                    <span>数量</span>
                    </div></th>
                    <th data-field="unitprice" data-key="1-0-11" title="单价">
                    <div class="layui-table-cell laytable-cell-1-0-11">
                    <span>单价</span>
                    </div></th>
                    <th data-field="manual_money" data-key="1-0-12" title="加工费">
                    <div class="layui-table-cell laytable-cell-1-0-12">
                    <span>加工费</span>
                    </div></th>
                    <th data-field="total_money" data-key="1-0-13" title="金额">
                    <div class="layui-table-cell laytable-cell-1-0-13">
                    <span>金额</span>
                    </div></th>
                    <th data-field="remark" data-key="1-0-14" title="备注" width="300">
                    <div class="layui-table-cell laytable-cell-1-0-14">
                    <span>备注</span>
                    </div></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($data['list'] as $i => $v): ?>
                  <tr data-index="0">
                    <td data-field="id" data-key="1-0-0">
                      <div class="layui-table-cell laytable-cell-1-0-0" align="center"><?php echo htmlentities($i+1); ?></div>
                    </td>
                    <td data-field="create_time" data-key="1-0-1">
                      <div class="layui-table-cell laytable-cell-1-0-1"><?php echo htmlentities($v['create_time']); ?></div>
                    </td>
                    <td data-field="delivery_time" data-key="1-0-2">
                      <div class="layui-table-cell laytable-cell-1-0-2"><?php echo htmlentities($v['delivery_time']); ?></div>
                    </td>
                    <td data-field="customer" data-key="1-0-3">
                      <div class="layui-table-cell laytable-cell-1-0-3"><?php echo htmlentities($v['customer']); ?></div>
                    </td>
                    <td data-field="trade_no" data-key="1-0-4">
                      <div class="layui-table-cell laytable-cell-1-0-4"><?php echo htmlentities($v['trade_no']); ?></div>
                    </td>
                    <td data-field="category" data-key="1-0-5">
                      <div class="layui-table-cell laytable-cell-1-0-5"><?php echo htmlentities($v['category']); ?></div>
                    </td>
                    <td data-field="craft" data-key="1-0-6">
                      <div class="layui-table-cell laytable-cell-1-0-6"><?php echo htmlentities($v['craft']); ?></div>
                    </td>
                    <td data-field="width" data-key="1-0-7">
                      <div class="layui-table-cell laytable-cell-1-0-7"><?php echo htmlentities($v['width']); ?></div>
                    </td>
                    <td data-field="height" data-key="1-0-8">
                      <div class="layui-table-cell laytable-cell-1-0-8"><?php echo htmlentities($v['height']); ?></div>
                    </td>
                    <td data-field="area" data-key="1-0-9">
                      <div class="layui-table-cell laytable-cell-1-0-9"><?php echo htmlentities($v['area']); ?></div>
                    </td>
                    <td data-field="num" data-key="1-0-10">
                      <div class="layui-table-cell laytable-cell-1-0-10"><?php echo htmlentities($v['num']); ?></div>
                    </td>
                    <td data-field="unitprice" data-key="1-0-11">
                      <div class="layui-table-cell laytable-cell-1-0-11"><?php echo htmlentities($v['unitprice']); ?></div>
                    </td>
                    <td data-field="manual_money" data-key="1-0-12">
                      <div class="layui-table-cell laytable-cell-1-0-12"><?php echo htmlentities($v['manual_money']); ?></div>
                    </td>
                    <td data-field="total_money" data-key="1-0-13">
                      <div class="layui-table-cell laytable-cell-1-0-13"><?php echo htmlentities($v['total_money']); ?></div>
                    </td>
                    <td data-field="remark" data-key="1-0-14">
                      <div class="layui-table-cell laytable-cell-1-0-14"><?php echo htmlentities($v['remark']); ?></div>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr bgcolor="#f0f0f0">
                        <td colspan="10">
                            <div class="layui-table-cell" style="text-align: left;">
                                合计共有 <?php echo htmlentities($data['count']); ?> 条记录
                            </div>
                        </td>
                        <td><?php echo array_sum(array_values(array_column($data['list'], "num"))); ?></td>
                        <td></td>
                        <td><?php echo array_sum(array_values(array_column($data['list'], "manual_money"))); ?></td>
                        <td><?php echo array_sum(array_values(array_column($data['list'], "total_money"))); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
    layui.config({ base: '/static/admin/' }).use(['form', 'laytpl', 'table'], function(){
        window.print();
    })
</script>

<script>ns.init();</script>
</body>
</html>