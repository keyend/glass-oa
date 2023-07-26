<?php
namespace app;
// 应用模型对象类
class Model extends \think\Model
{
    /**
     * 记录日志
     *
     * @param mixed $args
     * @return void
     */
    protected function logger(...$args)
    {
        $class = app()->make(\app\common\model\system\LogsModel::class);
        $params = [];
        $arguments = [];
        foreach($args as $i => $val) {
            if ($i > 1) {
                $params[] = $val;
            } else {
                $arguments[] = $val;
            }
        }
        $arguments[] = $params;
        call_user_func_array([$class, 'info'], $args);
    }
}
