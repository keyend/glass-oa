<?php
namespace app\admin\validate;
use think\Validate;

class PluginComboValidate extends Validate
{
    protected $rule =   [
        'title'         => 'require',
        'rate_id'       => 'require',
        'rate_title'    => 'require',
        'rate_quantity' => 'require'
    ];

    protected $message  =   [
        'title.require'     => '套餐标题不能为空',
        'rate_id.require'   => '套餐应用模板不能为空',
        'rate_title.require'   => '套餐应用模板不能为空',
        'rate_quantity.require'   => '套餐应用模板数值不能为空',
    ];

    protected $scene = [
        'add'  =>  ['rate_id', 'title', 'rate_title', 'rate_quantity']
    ];
}