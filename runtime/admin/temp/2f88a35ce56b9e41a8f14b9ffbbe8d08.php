<?php /*a:4:{s:83:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\system\config\basic.html";i:1690292861;s:68:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\base.html";i:1688009496;s:82:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\system\config\tabs.html";i:1689573915;s:82:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\Common\uploadImage.html";i:1680162746;}*/ ?>
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
    <script src="/static/component/pear/pear.js"></script>
</head>

<body class="layui-layout-body">
<div class="layui-fluid" style="padding-top: 15px;">
    <div class="layui-card">
        <div class="layui-card-body">
            <div class="layui-tab layui-tab-brief" lay-filter="component-tabs-brief">
                <ul class="layui-tab-title">
    <?php if(checkAccess('sysConfigBasic')): ?><li<?php if($rule=='sysConfigBasic'): ?> class="layui-this"<?php endif; ?>><a href="<?php echo url('sysConfigBasic'); ?>">基础参数</a></li><?php endif; if(checkAccess('sysConfigApi')): ?><li<?php if($rule=='sysConfigApi'): ?> class="layui-this"<?php endif; ?>><a href="<?php echo url('sysConfigApi'); ?>">API参数</a></li><?php endif; ?>
</ul>
                <div class="layui-tab-content">
                    <div class="layui-tab-item layui-show">
                        <form class="layui-form" action="" lay-filter="component-form-element">
                            <input type="hidden" name="domain" value="<?php echo request()->domain(true); ?>" />
                            <div class="layui-form-item">
                                <label class="layui-form-label">系统名称</label>
                                <div class="layui-input-block" style="max-width: 800px;">
                                    <input type="text" name="site_title" value="<?php echo htmlentities($option['site_title']); ?>" required  lay-verify="required" autocomplete="off" class="layui-input">
                                    <div class="layui-form-mid layui-word-aux">系统的名称，将会展示在个页面的标题尾部、邮件中。</div>
                                </div>
                            </div>

                            <div class="layui-form-item">
                                <label class="layui-form-label">系统图标</label>
                                <div class="layui-input-block" style="max-width: 800px;">
								    <div class="image-uploader" data-limit="1">
                                        <input type="hidden" name="site_logo" value="<?php echo htmlentities($option['site_logo']); ?>" required lay-verify="required" />
                                        <div class="inner"></div>
                                    </div>
                                    <div class="layui-form-mid layui-word-aux">系统logo路径</div>
                                </div>
                            </div>

                            <div class="layui-form-item" style="display: none;">
                                <label class="layui-form-label">网站首页</label>
                                <div class="layui-input-block" style="max-width: 800px;">
                                    <select name="index_theme" lay-verify="required">
                                        <option value="default" <?php if($option['index_theme'] == 'default'): ?>selected<?php endif; ?>>新版主题</option>
                                        <option value="new1" <?php if($option['index_theme'] == 'new1'): ?>selected<?php endif; ?>>旧版主题</option>
                                    </select>
                                    <div class="layui-form-mid layui-word-aux">网站首页主题</div>
                                </div>
                            </div>

                            <br>

                            <div class="layui-form-item">
                                <label class="layui-form-label">厂家地址</label>
                                <div class="layui-input-block" style="max-width: 800px;">
                                    <input type="text" name="site_address" value="<?php echo htmlentities($option['site_address']); ?>" autocomplete="off" class="layui-input">
                                    <div class="layui-form-mid layui-word-aux"></div>
                                </div>
                            </div>

                            <div class="layui-form-item">
                                <label class="layui-form-label">厂家电话</label>
                                <div class="layui-input-block" style="max-width: 800px;">
                                    <input type="text" name="site_contact" value="<?php echo htmlentities($option['site_contact']); ?>" autocomplete="off" class="layui-input">
                                    <div class="layui-form-mid layui-word-aux"></div>
                                </div>
                            </div>

                            <div class="layui-form-item">
                                <div class="layui-input-block">
                                    <button class="layui-btn layui-btn-normal" lay-submit lay-filter="save">保存设置</button>
                                </div>
                            </div>
                        </form>
                    </div>
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
    layui.use(['form','jquery'],function(){
        let form = layui.form;
        let $ = layui.jquery;

        form.on('submit(save)', function(data){
            $.ajax({
                data:JSON.stringify(data.field),
                dataType:'json',
                contentType:'application/json',
                type:'post',
                success:function(res){
                    if(res && res.code == 999){
                        parent.layer.msg(res.message, {
                            icon: 5,
                            time: 2000,
                        })
                        return false;
                    }else if(res.code == 0){
                        parent.layer.msg(res.message,{icon:1,time:1000},function(){
                            parent.layer.close(parent.layer.getFrameIndex(window.name));
                        });
                    }else{
                        parent.layer.msg(res.message,{icon:2,time:1000});
                    }
                }
            })
            return false;
        });
    })
</script>

<script>ns.init();</script>
</body>
</html>