<?php
namespace app\common\model\system;
/**
 * 日志
 *
 * @package app.common.model
 * @author: k.
 * @date: 2021-05-10 20:19:31
 */
use app\Model;

class LogsModel extends Model
{
    protected $name = 'sys_logs';
    protected $pk = 'id';

    /**
     * 添加操作日志
     * @param string type 动作类型
     * @param Array  params 参数
     * @param string message 事件内容
     * @access public
     */
    public function info($message, ...$args)
    {
        foreach($args as $arg) {
            if(gettype($arg) === 'number' || gettype($arg) === 'int' || (is_string($arg) && is_numeric($arg))) {
                $user_id = $arg;
            } elseif (is_string($arg)) {
                $type = $arg;
            } elseif(is_array($arg)) {
                $params = $arg;
            } elseif($arg instanceof \think\Model) {
                $params = $arg->toArray();
            }
        }

        if (!isset($params)) $params = [];
        if (!isset($type)) $type = 'DOSOME';

        if (defined('S2')) {
            $params['username'] = S2;
            $params['realname'] = S7;
            $params['ip'] = SA;
        }

        if (isset($params['lastlogin_ip'])) {
            $ip = $params['lastlogin_ip'];
        } elseif(defined('SA')) {
            $ip = SA;
        } else {
            $ip = request()->ip();
        }

        if (!isset($user_id)) {
            if (defined('S1'))
                $user_id = S1;
            elseif(isset($params['user_id']))
                $user_id = $params['user_id'];
            else
                $user_id = 0;
        }

        parent::create([
            'label' => $type,
            'ip' => $ip,
            'user_id' => $user_id,
            'content' => $message,
            'params' => json_encode($params, JSON_UNESCAPED_UNICODE),
            'create_time' => TIMESTAMP
        ]);
    }

    /**
     * 解析日志内容中的字符
     * @param String message 内容
     * @return String
     * @access protected
     */
    public function parse($message = '', $params = [])
    {
        foreach($params as $key => $value) {
            if (!is_string($value)) {
                $value = json_encode($value);
            }
            $message = str_replace("{{$key}}", $value, $message);
        }

        return $message;
    }

    /**
     * 获取事件类型
     * @param String label  类型标识
     * @return String
     */
    protected function getLabelType($label)
    {
        return lang("logs.type.{$label}");
    }
    
    /**
     * 返回列表
     * @param int page
     * @param int limit 页码
     * @param Array 筛选条件
     * @return array
     */
    public function getList(int $page, int $limit, Array $filter = [])
    {
        $query = $this->order('id DESC')->where('user_id', S1);
        if ($filter['date'] !== '') {
            $query->where('create_time', 'between', getTime($filter['date']));
        }
        if (isset($filter['keyword']) && $filter['keyword'] !== '') {
            $query->where('params', 'like', '%' . $filter['keywords'] . '%');
        }
        if (isset($filter['labels']) && !empty($filter['labels'])) {
            $query->where('label', 'IN', $filter['labels']);
        }

        $count = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        array_walk($list, function(&$item) {
            $item['label'] = $this->getLabelType($item['label']);
            $item['params'] = json_decode($item['params'], true);
            $item['content'] = $this->parse(lang($item['content']), $item['params']);
            $item = array_keys_filter($item, ['id', 'label', 'content', 'params', 'create_time']);
        });

        return compact('count', 'list');
    }
}