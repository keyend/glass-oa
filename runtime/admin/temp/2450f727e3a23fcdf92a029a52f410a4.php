<?php /*a:3:{s:74:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\Member\pay.html";i:1690190528;s:68:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\base.html";i:1688009496;s:82:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\Common\uploadImage.html";i:1680162746;}*/ ?>
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
</style>
</head>

<body class="layui-layout-body">
<div class="layui-fluid page-member-form">
    <div class="layui-card">
        <div class="layui-card-body" style="padding: 0px!important;">
            <form class="layui-form" action="" lay-filter="component-form-element" style="padding: 15px;">
                <div class="layui-form-item">
                    <label class="layui-form-label">收款数额</label>
                    <div class="layui-input-block">
                        <input type="number" name="money" required placeholder="0.00" value="<?php echo htmlentities($info['money']); ?>" lay-verify="required" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">补充说明</label>
                    <div class="layui-input-block">
                        <textarea class="layui-textarea" name="remark" placeholder="补充备注说明"><?php echo htmlentities($info['remark']); ?></textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">凭证截图</label>
                    <div class="layui-input-block">
                        <div class="image-uploader" data-limit="1">
                            <input type="hidden" name="voucher" value="<?php echo htmlentities($info['voucher']); ?>" />
                            <div class="inner"></div>
                        </div>
                    </div>
                </div>
                <?php if(!$info['pay_id']): ?>
                <div class="layui-form-item" style="margin-bottom: 0px;">
                    <div class="layui-input-block">
                        <button class="layui-btn layui-btn-sm" lay-submit lay-filter="component-form-element">立即提交</button>
                        <button type="reset" class="layui-btn layui-btn-sm layui-btn-primary">重置</button>
                    </div>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<!-- 图片上传 -->
<script type="text/html" id="uploadImage">
    {{# if(d.list.length){ }}
        {{# for(var i=0;i<d.list.length;i++){ }}
            <div class="item" data-index="{{i}}">
                <div class="img-wrap">
                    <img src="{{ns.img(d.list[i])}}" layer-pid="{{i}}" layer-src />
                </div>
                <div class="operation">
                    <i title="图片预览" class="iconfont icon-eye js-preview"></i>
                    <i title="删除图片" class="layui-icon layui-icon-delete js-delete" data-index="{{i}}"></i>
                </div>
            </div>
        {{# } }}
        {{# if(d.list.length < d.max){ }}
            <div class="item js-add-image">
                <i class="layui-icon layui-icon-upload"></i>
            </div>
        {{# } }}
    {{# }else{ }}
        <div class="item js-add-image">
            <i class="layui-icon layui-icon-upload"></i>
        </div>
    {{# } }}
</script>
<script>
    layui.config({
        base: '/static/admin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'form', 'laytpl', 'table'], function(){
        var $ = layui.$
        ,admin = layui.admin
        ,element = layui.element
        ,form = layui.form
        ,table = layui.table;

        form.on('submit(component-form-element)', function(obj) {
            ns.wost(location.href, obj.field, res => {
                ns.close(res)
            });
            return false;
        })
    });
</script>

<script>ns.init();</script>
</body>
</html>