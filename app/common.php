<?php
// 应用公共文件
if (!function_exists('array_splice_value')) {
    /**
     * 删除数组中的一个元素
     * @author k.
     * @version 1.0.0
     * @return mixed
     */
    function array_splice_value(&$arr, $index = 0, $length = 1, $replace = []) {
        $pre = array_splice($arr, $index, $length, $replace);
        return current($pre);
    }
}

if (!function_exists('conf')) {
    /**
     * 返回缓存配置
     *
     * @param string $name
     * @param string $default
     * @return void
     */
    function conf($name, $default = '', $prefix = "config") {
        $value = redis()->get("{$prefix}.{$name}");
        if (!$value) {
            $value = $default;
        }
        return $value;
    }
}

if (!function_exists('addCron')) {
    /**
     * 创建消息队列
     * addCron(__METHOD__, $argv)
     * @return mixed
     */
    function addCron($method, $params = [], $later = 1, $queue = 'EntityMQService') {
        app()->make(\app\common\model\system\CronModel::class)->addCron($queue, [$method, $params], $later);
    }
}

if (!function_exists('logger')) {
    /**
     * 记录日志
     *
     * @param [type] ...$args
     * @return void
     */
    function logger(...$args) {
        static $logger = null;
        if ($logger === null) {
            $logger = new \app\common\model\system\LogsModel();
        }
        call_user_func_array([$logger, "info"], [$args]);
    }
}

if (!function_exists('triggerAsync')) {
    /**
     * 异步事件
     *
     * @param array $called
     * @param array $params
     * @return void
     */
    function triggerAsync($called = [], $params = [], $delay = 0)
    {
        $length = count($called);
        $data = [ "require" => $called[0] ];
        if ($length === 3) {
            $data["params"] = $called[1];
            $data["method"] = $called[2];
        } else {
            $data["method"] = $called[1];
        }

        if ($delay > 0) {
            $data["delay"] = $delay;
        }

        if (!empty($params)) {
            $data["argv"] = $params;
        }

        if (defined("S0")) {
            $data["token"] = S0;
        }

        \app\cron\job\JobManager::push($data);
    }
}

if (!function_exists('password_check')) {
    /**
     * 密码校验
     * @param String $ori
     * @param String $salt
     * @param String $verify
     * @return String
     */
    function password_check($ori = '', $salt = '', $verify = null) {
        if ($verify === null) {
            return md5(md5($ori) . $salt);
        } else {
            if ($salt === '') {
                $salt = uniqid();
                return [md5(md5($ori) . $salt), $salt];
            }

            return md5(md5($verify) . $salt) == $ori;
        }
    }
}

if (!function_exists('getToken')) {
    /**
     * 获取TOKEN
     * @param String $prepare
     * @return String
     */
    function getToken($prepare = '') {
        return md5(uniqid() . "$" . $prepare);
    }
}

if (!function_exists('rand_string')) {
    /**
     * 获取随机字串
     * @param String $prepare
     * @return String
     */
    function rand_string($length=5, $indent=0) {
        $dict = array(
            '_0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            '0123456789',
            'abcdefghijklmnopqrstuvwxyz',
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'in:simplified',
            'in:traditional'
        );
        if(substr($dict[$indent] , 0 , 3) == 'in:'){
            require_once(dirname(__FILE__).'/functions/Tradition/'.substr($dict[$indent] ,3).'.php');
            $_t = tradition.'_'.substr($dict[$indent],3);
            $dict[$indent] = $t();
        }
        $result = '';
        while($length-- > 0)$result .= substr($dict[$indent] ,mt_rand(0 ,strlen($dict[$indent])-1) ,1);
        return $result;
    }
}

if (!function_exists('redis')) {
    /**
     * 应用Redis存储
     * @return std
     */
    function redis() {
        return \think\facade\Cache::store('redis');
    }
}

if (!function_exists('fileCache')) {
    /**
     * 返回相应的缓存
     *
     * @param string $name
     * @param function $fallback
     * @return void
     */
    function fileCache(string $name, $fallback, $force = false) {
        static $cache = [];

        if (!isset($cache["fc.{$name}"]) || $force === true) {
            $data = cache("fc.{$name}");

            if (!$data || $force === true) {
                if (is_callable($fallback)) {
                    $data = $fallback($name);
                } else {
                    $data = $fallback;
                }

                cache("fc.{$name}", $data, 31536000);
            }

            $cache["fc.{$name}"] = $data;
        }

        return $cache["fc.{$name}"];
    }
}

if (!function_exists('array_columns')) {
    /**
     * 过滤多维数组
     *
     * @param array $arr
     * @param array $filters
     * @author k. <email@email.com>
     * @return void
     */
    function array_columns(&$arr = [], $filters = []) {
        $res = [];
        $st = 0;
        $filters = is_string($filters) ? explode(",", $filters) : $filters;
        foreach($arr as $key => $stack) {
            if ($st === 0) {
                if (is_array($stack)) {
                    $st = 1;
                } else {
                    $st = 2;
                }
            }

            if ($st === 1) {
                $row = [];
                foreach($filters as $filter) {
                    if (is_array($filter)) {
                        $row[$filter[1]] = $stack[$filter[0]];
                    } elseif(is_string($filter)) {
                        $row[$filter] = $stack[$filter];
                    }
                }
                $res[] = $row;
            } else {
                foreach($filters as $filter) {
                    if (is_array($filter)) {
                        if ($filter[0] == $key) {
                            $res[$filter[1]] = $stack;
                        }
                    } elseif(is_string($filter)) {
                        if ($filter == $key) {
                            $res[$filter] = $stack;
                        }
                    }
                }
            }
        }
        $arr = $res;
        return $res;
    }
}

if (!function_exists('array_keys_filter')) {
    /**
     * 数组键名过滤
     * @param array $stack
     * @param array $filters
     * @param bool $force
     * @description
     * 
     * array_keys_filter([], [
     *      'a',
     *      ['b', []],
     *      ['c', function($value) { ... }],
     *      ['d' => function($value) { ... }],
     *      'e.name'
     * ], true)
     * @return array
     */
    function array_keys_filter($stack = [], $filters, $force = false) {
        if (is_string($filters)) {
            $filters = explode(",", $filters);
        }

        foreach($stack as $key => $value) {
            if (preg_match('/[\w\-]+\[[\w\-]+\]/', $key)) {
                $keys = explode('[', $key);
                if (!isset($stack[$keys[0]])) {
                    $stack[$keys[0]] = [];
                }

                $keys[1] = substr($keys[1], 0, strlen($keys[1]) - 1);
                $stack[$keys[0]][$keys[1]] = $value;

                unset($stack[$key]);
            }
        }

        $res = [];

        foreach($filters as $filter) {
            if (is_array($filter)) {
                if (is_string($filter[0])) {
                    if (isset($stack[$filter[0]])) {
                        if (is_callable($filter[1])) {
                            $res[$filter[0]] = $filter[1]($stack[$filter[0]]);
                        } else {
                            $res[$filter[0]] = $stack[$filter[0]];
                        }
                    } else {
                        if ($force && !isset($filter[1])) {
                            throw new \Exception("参数{$filter[0]}不能为空!");
                        } else {
                            $res[$filter[0]] = $filter[1];
                        }
                    }
                } elseif(is_callable($filter[0])) {
                    foreach($filter as $key => $val) {
                        if (isset($stack[$key])) {
                            $res[$key] = $val($stack[$key]);
                        } elseif ($force) {
                            throw new \Exception("参数{$key}不能为空!");
                        }
                    }
                } else {
                    throw new \Exception("过滤器" . json_encode($filter) . "错误!");
                }
            } elseif (is_string($filter)) {
                if (isset($stack[$filter])) {
                    $res[$filter] = $stack[$filter];
                } elseif($force) {
                    throw new \Exception("参数{$filter}不能为空!");
                }
            } elseif (is_callable($filter)) {
                $ret = $filter($stack);
                if (!empty($ret)) {
                    $res = array_merge($res, $ret);
                }
            } else {
                throw new \Exception("过滤器" . json_encode($filter) . "错误!");
            }
        }

        return $res;
    }
}

if (!function_exists('parseTree')) {
    /**
     * 无线级分解
     * 
     * @param array $data 数据源
     * @param string $id 主键
     * @param string $parentId 父级
     * @param string $children 子类
     * @return Array
     */
    function parseTree(Array $data, $id = "id", $parentId = 'parent_id', $children = 'children')
    {
        $rows = $res = [];
        foreach ($data as $row)
            $rows[$row[$id]] = $row;

        foreach ($rows as $row) {
            if (isset($rows[$row[$parentId]])) {
                $rows[$row[$parentId]][$children][] = &$rows[$row[$id]];
            } else if($row[$parentId] == 0){
                $res[] = &$rows[$row[$id]];
            }
        }

        return $res;
    }
}

if (!function_exists('forMapIds')) {
    /**
     * 无限向下遍历树形
     *
     * @param collect $model
     * @param string  $pk
     * @return array [1,2,3,4,5,6]
     */
    function forMapIds($m, $value, $pk = 'id', $pid = 'parent_id') {
        // 当前所有IDS
        $ids = $m->where($pid, $value)->value("GROUP_CONCAT(`{$pk}`)");

        if (!empty($ids)) {
            $cids = $ids;

            while(!empty($cids)) {
                $cids = $m->where($pid, 'IN', $cids)->value("GROUP_CONCAT(`{$pk}`)");
                if (!empty($cids)) {
                    $ids .= ",{$cids}";
                }
            }
        }

        return explode(",", $ids);
    }
}

if (!function_exists('getLocationByIp')) {
    /**
     * 根据ip定位
     * @param $ip
     * @param $type
     * @return string | array
     * @throws Exception
     */
    function getLocationByIp($ip, $type = 1)
    {
        $ip2region = new \Ip2Region();
        $info = $ip2region->btreeSearch($ip);
        $info = explode('|', $info['region']);
        $address = '';
        foreach($info as $vo) {
            if('0' !== $vo) {
                $address .= $vo . '-';
            }
        }

        if (2 == $type) {
            return ['province' => $info['2'], 'city' => $info['3']];
        }

        return rtrim($address, '-');
    }
}
