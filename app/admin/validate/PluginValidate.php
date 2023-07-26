<?php
namespace app\admin\validate;
use think\Validate;

class PluginValidate extends Validate
{
    protected $rule =   [
        'name'  => 'require',
        'title'   => 'require',
        'type'   => 'require'
    ];

    protected $message  =   [
        'name.require' => '插件标识不能为空',
        'title.require' => '插件名称不能为空',
        'type.require' => '插件类型不能为空',
    ];

    protected $scene = [
        'add'  =>  ['name', 'title', 'type'],
        'edit'  =>  ['title']
    ];
}