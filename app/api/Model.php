<?php
/**
 * Model 自动化模型应用入口
 * @version 1.0.0
 * @description
 * 
 * $this->model->user->$anycall
 * $this->model->order->$anycall
 */
namespace app\api;

class Model
{
    /**
     * 已应用的模型表
     *
     * @var array
     */
    protected $bind = [];

    /**
     * 模型指向
     *
     * @var array
     */
    private $alias = [
        'oauth'         => \app\common\model\auth\OAuth::class,
        'oauth_codes'   => \app\common\model\auth\OAuthCodes::class,
        'user'          => \app\common\model\crebo\Users::class,
    ];

    /**
     * 构造函数
     *
     * @param string $name 引用控制器名
     */
    public function __construct($name = '')
    {}

    /**
     * 获取方法
     *
     * @param string $name
     * @return void
     */
    public function __get($name)
    {
        if (isset($this->bind[$name])) {
            return $this->bind[$name];
        } else {
            return $this->__call($name, []);
        }
    }

    /**
     * 魔术方法
     * ->model->names->call
     * @param string $method
     * @param mixed  $args
     * @return void
     */
    public function __call($method, $args)
    {
        if (!isset($this->bind[$method])) {
            if (isset($this->alias[$method])) {
                $class = $this->alias[$method];
            } else {
                $class = "\\app\\common\\model\\" . ucfirst(toCamelCase($method));
                if (!class_exists($class)) {
                    throw new \Exception("模型{$class}未定义!");
                }
            }

            $this->bind[$method] = app()->make($class, $args);
        }
 
        return $this->bind[$method];
    }
}