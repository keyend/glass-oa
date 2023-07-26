<?php /*a:2:{s:81:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\system\rule\index.html";i:1686882144;s:68:"D:\xampp\cygwin\www\wwwroot\cloud\or.xmr.la\app\admin\view\base.html";i:1688009496;}*/ ?>
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
        .layui-treetable {
            height: 480px;
            overflow: auto;
        }
        .layui-treetable .layui-form {
            position: relative;
        }
        .layui-treetable .layui-table {
            padding-top: 100px;
        }
        .col_id {
            width: 60px;
        }
        .action_col {
            width: 200px;
        }
    </style>
</head>

<body class="layui-layout-body">
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body" style="padding: 15px 10px!important;">
                    <div class="layui-tab layui-tab-brief" lay-filter="component-tabs-brief">
                        <ul class="layui-tab-title">
                            <?php if(checkAccess('sysUser')): ?><li<?php if($rule=='sysUser'): ?> class="layui-this"<?php endif; ?>><a href="<?php echo url('sysUser'); ?>">管理员</a></li><?php endif; if(checkAccess('sysGroup')): ?><li<?php if($rule=='sysGroup'): ?> class="layui-this"<?php endif; ?>><a href="<?php echo url('sysGroup'); ?>">管理组</a></li><?php endif; if(checkAccess('sysRole')): ?><li<?php if($rule=='sysRole'): ?> class="layui-this"<?php endif; ?>><a href="<?php echo url('sysRole'); ?>">角色管理</a></li><?php endif; if(checkAccess('sysRule')): ?><li<?php if($rule=='sysRule'): ?> class="layui-this"<?php endif; ?>><a href="<?php echo url('sysRule'); ?>">权限管理</a></li><?php endif; ?>
                        </ul>
                        <div class="layui-tab-content">
                            <div class="layui-tab-item layui-show">
                                <div class="layui-form layui-card-header layuiadmin-card-header-auto layui-form-filter">
                                    <div class="layui-form-item">
                                        <div class="layui-inline btn-group">
                                            <button class="layui-btn layui-btn-sm layui-btn-normal layuiadmin-btn-admin" data-type="add"><i class="layui-icon">&#xe654;</i> 添加权限</button>
                                        </div>
                                    </div>
                                </div>
                                <!---->
                                <div id="LAY-user-table" lay-filter="LAY-user-table"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 操作 -->
<script type="text/html" id="actionTpl">
    <div class="layui-btn-group">
        <a class="layui-btn layui-btn-success layui-btn-xs" onclick="active.add('{{d.id}}')"><i class="layui-icon layui-icon-add-1"></i>添加</a>
        <a class="layui-btn layui-btn-normal layui-btn-xs" onclick="active.edit('{{d.id}}')"><i class="layui-icon layui-icon-edit"></i>编辑</a>
        {{#  if(!d.children){ }}
        <a class="layui-btn layui-btn-danger layui-btn-xs" onclick="active.delete('{{d.id}}')"><i class="layui-icon layui-icon-delete"></i>删除</a>
        {{#  } }}
    </div>
</script>

<script>
    var table, active;
    layui.config({
        base: '/static/admin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'form', 'treetable', 'laytpl'], function(){
        var $ = layui.$ ,form = layui.form, laytpl = layui.laytpl;

        active = {
            add(id) {
                id = id||0,
                ns.open("<?php echo url('sysRuleAdd'); ?>?id=" + id, '添加权限').then(() => {
                    renderTable()
                })
            },
            edit(id) {
                ns.open("<?php echo url('sysRuleEdit'); ?>?id=" + id, '编辑权限').then(() => {
                    renderTable()
                })
            },
            delete(id) {
                layer.confirm('您确定要删除该权限？', {
                    title: '友情提示',
                    icon: 3,
                    btn: ['是的', '再想想']
                }, function(i) {
                    layer.close(i),
                    ns.silent("<?php echo url('sysRuleDelete'); ?>", {id: id}, res => {
                        if (res.code == 0) {
                            setTimeout(() => renderTable(), 100)
                        } else {
                            layer.alert(res.message)
                        }
                    })
                });
            }
        },

        table = {
            options: {
                where: {},
                params: {
                    page: 1,
                    limit: 9999
                },
                callback: {
                    beforeCollapse(o) {
                        table.set('rule_' + o.item.id, null);
                        return true
                    },
                    beforeExpand(o) {
                        table.set('rule_' + o.item.id, '1');
                        return true
                    }
                }
            },
            getTreeOption(res) {
                return $.extend(this.options, {
                    elem: this.options.elem,
                    nodes: this.parseData(res),
                    layout: this.options.cols[0]
                })
            },
            parseData(data) {
                data.forEach((v,i) => {
                    data[i].spread = this.getSpread(v.id),
                    v.children&&(data[i].children=this.parseData(v.children))
                });
                return data;
            },
            getSpread(id) {
                return this.get('rule_' + id) == '1';
            },
            get(name) {
                if (window['localStorage']) {
                    let obj = JSON.parse(localStorage.getItem(name)||'{"value": ""}');
                    return obj.value;
                }
                return ''
            },
            set(name, value) {
                if (window['localStorage']) {
                    if (value === null) {
                        localStorage.removeItem(name)
                    } else {
                        let obj = {value: value};
                        localStorage.setItem(name, JSON.stringify(obj))
                    }
                }
            },
            render(options) {
                this.options = $.extend(this.options, options),
                this.renderTable(this.options.elem, this.options)
            },
            reload(elem, params) {
                this.options.elem = '#' + elem,
                this.options = $.extend(this.options, params),
                this.renderTable(this.options.elem, this.options)
            },
            ps(url,obj){
                Object.keys(obj).map(v => (url=url+(url.indexOf('?')==-1?'?':'&')+v+'='+encodeURIComponent(obj[v])));
                return url;
            },
            renderTable(e, opts) {
                var url = this.options.url;
                url = this.ps(url,this.options.where),
                url = this.ps(url,this.options.params),
                ns.silent(url, {}, res => {
                    $(this.options.elem).empty(),
                    res.data.list = this.getTreeOption(res.data.list),
                    layui.treetable(res.data.list)
                })
            },
            getAction(row) {
                let template = document.getElementById('actionTpl').innerHTML;
                return laytpl(template).render(row);
            }
        }

        $('.layui-btn.layuiadmin-btn-admin').on('click', function(){
            var type = $(this).data('type');
            active[type] ? active[type].call(this) : '';
        }),

        form.on('switch(status)', function(obj){
            active.status(obj.elem.getAttribute('data-id'), obj.elem.checked)
        }),

        // 监听搜索
        form.on('submit(LAY-user-back-search)', function(data){
            var field = data.field;
            // 执行重载
            table.reload('LAY-user-table', {
                where: field
            });
        }),

        renderTable();
    });

    // 渲染表格
    function renderTable() {
        layui.use(['form'], function () {
            var $ = layui.$ ,form = layui.form;
            table.render({
                elem: "#LAY-user-table",
                url: "<?php echo url('sysRule'); ?>",
                cols: [
                    [{
                        name: "ID",
                        field: 'id',
                        headerClass: 'col_id',
                        colClass: 'value_col',
                        style: ''
                    },{
                        name: "权限名",
                        field: 'label',
                        treeNodes: true,
                        headerClass: 'value_col',
                        colClass: 'value_col',
                        style: ''
                    },{
                        name: "操作",
                        headerClass: 'action_col',
                        colClass: 'value_col',
                        style: '',
                        render: function(rowText, row) {
                            return table.getAction(row);
                        }
                    }]
                ]
            });
        })
    }
</script>

<script>ns.init();</script>
</body>
</html>