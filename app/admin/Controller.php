<?php
/*
 * 控制器
 * @Author: k.
 * @Date: 2021-05-10 20:19:31
 */
namespace app\admin;
use app\BaseController;

class Controller extends BaseController
{
    /**
     * 魔术方法
     *
     * @var array
     */
    private function magic()
    {
        return [
            'logger' => [
                'call' => 'info', 
                'class' => \app\common\model\system\LogsModel::class
            ]
        ];
    }

    // 初始化
    protected function initialize()
    {
        // 只显示严重的错误
        error_reporting(E_ERROR | E_PARSE);

        $this->assign('rule', $this->request->rule()->getName());
        $this->assign('admin', $this->request->user);
    }

    /**
     * 遍历返回值
     * @param Array   $args 参数列表
     * @param String  $message 默认返回MSG
     * @param Integer $status 默认返回状态值
     * @return ResponseArray
     */
    protected function get_defined_vals($args, $message = '', $code = 0)
    {
        if (count($args) > 0) {
            if (is_string($args[0])) {
                $message = array_splice_value($args);
            } elseif(is_integer($args[0]) || is_numeric($args)) {
                $code = array_splice_value($args);
            }

            foreach ($args as $i => $arg) {
                if (is_array($arg)) {
                    $data = array_splice_value($args, $i);
                } elseif(is_callable([$this, $args[0]])) {
                    $method = array_splice_value($args, $i);
                    $data = $this->$method(...$args);
                } elseif(is_callable($args[0])) {
                    $method = array_splice_value($args, $i);
                    $data = call_user_func($method, $args);
                } elseif(is_integer($args[0]) || is_numeric($args)) {
                    $code = array_splice_value($args, $i);
                } elseif (is_string($args[0])) {
                    $message = array_splice_value($args, $i);
                }
            }
        }

        if (isset($data)) {
            if ($data instanceof Arrayable) {
                $data = $data->toArray();
            } elseif ($data instanceof Response) {
                return $data;
            } elseif(!is_array($data)) {
                $data = (array)$data;
            }
        } else {
            $data = null;
        }

        return compact('code', 'message', 'data');
    }

    protected function success(...$args)
    {
        if (IS_AJAX) {
            return $this->get_defined_vals($args, 'success', 0);
        } else {
            $data = $this->get_defined_vals($args, 'success', 0);
            $this->assign($data);
            return $this->fetch('Common/success');
        }
    }

    protected function fail(...$args)
    {
        if (IS_AJAX) {
            return $this->get_defined_vals($args, 'failed', 500);
        } else {
            $data = $this->get_defined_vals($args, 'success', 0);
            $this->assign($data);
            return $this->fetch('Common/error');
        }
    }

    /**
     * 返回请求的分页信息
     * @return Array
     */
    protected function getPaginator() 
    {
        return array_values(array_keys_filter($this->request->param(), [['page', 1], ['limit', 10]]));
    }

    /**
     * 调用魔术方法
     *
     * @param string $name
     * @param mixed $arguments
     * @return object
     */
    public function __call($name, $arguments)
    {
        $magic = $this->magic();
        if (in_array($name, array_keys($magic))) {
            $params = $magic[$name];
            $class = $this->app->make($params['class']);
            return call_user_func_array([$class, $params['call']], $arguments);
        }
    }
}
