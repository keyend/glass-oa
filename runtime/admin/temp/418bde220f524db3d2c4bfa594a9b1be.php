<?php /*a:2:{s:77:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\order\printer.html";i:1690294898;s:68:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\base.html";i:1688009496;}*/ ?>
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
                <span style="display: inline-block; border-bottom: 2px solid #eee;"><?php echo htmlentities($option['site_title']); ?></span>
            </h1>
            <div class="layui-row">
                <div class="layui-col-xs6">
                    收货人：<?php echo htmlentities($delivery['order']['member']['nickname']); ?><br />
                    收货地址：<?php echo htmlentities($delivery['order']['member']['desc']); ?>
                </div>
                <div class="layui-col-xs6">
                    单号：<?php echo htmlentities($delivery['order']['trade_no']); ?><br />
                    日期：<?php echo date('Y年m月d日',strtotime($delivery['create_time'])); ?>
                </div>
            </div>
            <table class="layui-table" width="100%" border-color="#666666" border="1" style="margin-top: 10px;">
                <colgroup>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col width="200">
                </colgroup>
                <thead>
                    <tr>
                        <th rowspan="2">
                        <p class="MsoNormal"><span>序号</span></p>
                        </th>
                        <th rowspan="2">
                        <p class="MsoNormal"><span>产品名称</span></p>
                        </th>
                        <th colspan="2">
                        <p class="MsoNormal"><span>规格</span></p>
                        </th>
                        <th rowspan="2">
                        <p class="MsoNormal"><span>数量</span></p>
                        </th>
                        <th rowspan="2">
                        <p class="MsoNormal"><span>面积</span></p>
                        </th>
                        <th rowspan="2">
                        <p class="MsoNormal"><span>单价</span></p>
                        </th>                   
                        <th rowspan="2">
                        <p class="MsoNormal"><span>加工费</span></p>
                        </th>
                        <th rowspan="2">
                        <p class="MsoNormal"><span>金额</span></p>
                        </th>
                        <th rowspan="2">
                        <p class="MsoNormal"><span>备注</span></p>
                        </th>
                       </tr>
                       <tr>
                        <th>
                        <p class="MsoNormal"><span>宽</span><span lang="EN-US">mn</span></p>
                        </th>
                        <th>
                        <p class="MsoNormal"><span>高</span><span lang="EN-US">mm</span></p>
                        </th>
                       </tr>
                </thead>
                <tbody>
                <?php foreach($delivery['goods'] as $key => $goods): ?>
                <tr>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US"><?php echo $key+1; ?></span></p>
                 </td>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US"><?php echo htmlentities($goods['category']); ?></span></p>
                 </td>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US"><?php echo htmlentities(floatval($goods['width'])); ?></span></p>
                 </td>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US"><?php echo htmlentities(floatval($goods['height'])); ?></span></p>
                 </td>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US"><?php echo htmlentities(intval($goods['num'])); ?></span></p>
                 </td>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US"><?php echo htmlentities(floatval($goods['area'])); ?>m²</span></p>
                 </td>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US"><?php if($manual == 1): ?><?php echo htmlentities($goods['unitprice']); ?>元<?php endif; ?></span></p>
                 </td>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US"><?php if($manual == 1): ?><?php echo htmlentities($goods['manual_money']); ?>元<?php endif; ?></span></p>
                 </td>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US"><?php if($manual == 1): ?><?php echo htmlentities($goods['delivery_money']+$goods['manual_money']); ?>元<?php endif; ?></span></p>
                 </td>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US"><?php echo htmlentities($goods['remark']); ?></span></p>
                 </td>
                </tr>
                <?php endforeach; ?>
                <tr>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US">&nbsp;</span></p>
                 </td>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US">合计</span></p>
                 </td>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US">&nbsp;</span></p>
                 </td>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US">&nbsp;</span></p>
                 </td>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US"><?php echo htmlentities($delivery['delivery_num']); ?></span></p>
                 </td>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US">&nbsp;</span></p>
                 </td>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US">&nbsp;</span></p>
                 </td>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US"><?php if($manual == 1): ?><?php echo htmlentities($delivery['manual_money']); ?>元<?php endif; ?></span></p>
                 </td>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US"><?php if($manual == 1): ?><?php echo htmlentities($delivery['delivery_money']); ?>元<?php endif; ?></span></p>
                 </td>
                 <td>
                 <p class="MsoNormal"><span lang="EN-US">&nbsp;</span></p>
                 </td>
                </tr>
               </tbody>
            </table>
            <div class="layui-row" style="margin-top: 12px;">
                <div class="layui-col-xs12">
                    注：<?php echo htmlentities($delivery['remark']); ?>
                </div>
            </div>
            <div class="layui-row">
                <div class="layui-col-xs6">
                    地址：<?php echo htmlentities($option['site_address']); ?><br />
                    电话：<?php echo htmlentities($option['site_contact']); ?>
                </div>
                <div class="layui-col-xs6">
                    收货人：<br />
                    收货日期：
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    layui.config({ base: '/static/admin/' }).use(['index', 'form', 'laytpl', 'table'], function(){
        // window.print();
    })
</script>

<script>ns.init();</script>
</body>
</html>