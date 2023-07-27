<?php /*a:6:{s:75:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\Member\form.html";i:1689577962;s:68:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\base.html";i:1688009496;s:80:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\member\tabs\base.html";i:1690097958;s:81:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\member\tabs\price.html";i:1689597080;s:80:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\member\tabs\attr.html";i:1689575155;s:82:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\Common\uploadImage.html";i:1680162746;}*/ ?>
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
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body" style="padding: 0px!important;">
                    <form class="layui-form" action="" lay-filter="component-form-element">
                        <input type="hidden" name="id" value="<?php echo htmlentities($data['id']); ?>"/>
                        <div class="layui-tab layui-tab-brief" lay-filter="component-tabs-brief">
                            <ul class="layui-tab-title">
                                <?php if($info['id']): ?>
                                <li class="layui-this"><a>基本信息</a></li>
                                <li><a>价格参数</a></li>
                                <li><a>其它信息</a></li>
                                <?php else: ?>
                                <li class="layui-this"><a>会员信息</a></li>
                                <li><a>价格参数</a></li>
                                <?php endif; ?>
                            </ul>
                            <!---->
                            <div class="layui-tab-content">
                                <div class="layui-tab-item layui-show">
                                    
<div class="layui-form-item">
    <label class="layui-form-label">客户头像</label>
    <div class="layui-input-block">
        <div class="image-uploader" data-limit="1">
            <input type="hidden" name="avatar" value="<?php echo htmlentities($info['avatar']); ?>" />
            <div class="inner"></div>
        </div>
    </div>
</div>

<div class="layui-form-item" style="display: none;">
    <label class="layui-form-label">用户帐号</label>
    <div class="layui-input-block">
        <?php if($info['id']): ?>
        <div class="layui-form-mid layui-word-aux"><?php echo htmlentities($info['username']); ?></div>
        <?php else: ?>
        <input type="text" name="username" lay-verify="required" placeholder="请输入用户帐号" autocomplete="off" class="layui-input" value="<?php echo htmlentities($info['username']); ?>" />
        <?php endif; ?>
    </div>
</div>

<div class="layui-form-item">
    <label class="layui-form-label">客户名称</label>
    <div class="layui-input-block">
        <input type="text" name="nickname" lay-verify="required" placeholder="请输入客户名称" autocomplete="off" class="layui-input" value="<?php echo htmlentities($info['nickname']); ?>" />
    </div>
</div>

<?php if($info['id']): ?>
<div class="layui-form-item">
    <label class="layui-form-label">联系电话</label>
    <div class="layui-input-block">
        <input type="mobile" name="mobile" value="<?php echo htmlentities($info['mobile']); ?>"  placeholder="请输入手机号，如果不修改请忽略本参数" class="layui-input">
    </div>
</div>
<?php else: ?>
<div class="layui-form-item">
    <label class="layui-form-label">联系电话</label>
    <div class="layui-input-block">
        <input type="mobile" name="mobile" value="<?php echo htmlentities($info['mobile']); ?>"  placeholder="请输入手机号" class="layui-input">
    </div>
</div>
<?php endif; ?>

<div class="layui-form-item">
    <label class="layui-form-label">配送地址</label>
    <div class="layui-input-block">
        <input type="text" name="desc" lay-verify="required" placeholder="请输入客户名称" autocomplete="off" class="layui-input" value="<?php echo htmlentities($info['desc']); ?>" />
    </div>
</div>

<div class="layui-form-item">
    <label class="layui-form-label">保底面积</label>
    <div class="layui-input-block">
        <input type="number" name="minarea" lay-verify="required" autocomplete="off" class="layui-input" value="<?php echo htmlentities($info['minarea']); ?>" />
    </div>
</div>
                                </div>
                                <div class="layui-tab-item">
                                    <?php foreach($categorys as $category): ?>
<div class="layui-form-item">
    <label class="layui-form-label"><?php echo htmlentities($category['group']); ?></label>
    <div class="layui-input-block">
        <?php foreach($category['list'] as $vo): ?>
        <div class="layui-inline layui-input-inline-group">
            <label class="layui-form-label"><?php echo htmlentities($vo['category']); ?></label>
            <div class="layui-input-inline layui-input-wrap">
                <input type="number" name="category[<?php echo htmlentities($vo['id']); ?>]" autocomplete="off" lay-affix="clear" class="layui-input" value="<?php echo htmlentities($info['category'][$vo['id']]); ?>" />
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div> 
<?php endforeach; ?>

                                </div>
                                <?php if($info['id']): ?>
                                <div class="layui-tab-item">
                                    
<div class="layui-form-item">
    <label class="layui-form-label">截止日期</label>
    <div class="layui-input-block">
        <input type="text" name="group_expire" id="group_expire" value="<?php echo htmlentities(date("Y-m-d",!is_numeric($info['group_expire'])? strtotime($info['group_expire']) : $info['group_expire'])); ?>"  placeholder="请输入密码，如果不修改请忽略本参数" class="layui-input" />
    </div>
</div>

<div class="layui-form-item">
    <label class="layui-form-label">创建时间</label>
    <div class="layui-input-block">
        <div class="layui-form-mid layui-word-aux"><?php echo htmlentities(date("Y-m-d H:i:s",!is_numeric($info['create_time'])? strtotime($info['create_time']) : $info['create_time'])); ?></div>
    </div>
</div>

<script>
    layui.use('laydate', function() {
        layui.laydate.render({ 
            elem: '#group_expire'
            ,format: 'yyyy-MM-dd'
        });
    })
</script>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <div class="layui-input-block">
                                <button class="layui-btn layui-btn-sm" lay-submit lay-filter="component-form-element">立即提交</button>
                                <button type="reset" class="layui-btn layui-btn-sm layui-btn-primary">重置</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
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