<?php
namespace app\admin\validate;
use think\Validate;

class RateValidate extends Validate
{
    protected $rule =   [
        'title'   => 'require'
    ];

    protected $message  =   [
        'title.require' => '模板标题不能为空',
    ];

    protected $scene = [
        'add'  =>  ['title']
    ];
}