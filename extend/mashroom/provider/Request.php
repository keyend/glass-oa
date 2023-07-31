<?php
namespace mashroom\provider;
/**
 * 应用请求对象类
 * @package mashroom.provider
 */
use think\App;

class Request extends \think\Request
{
    // 用户登录信息存储
    public $user = null;

    /**
     * 验证是否存在登录信息
     * @return Boolean
     */
    public function isLogin() {
        if (empty($this->user) || !is_array($this->user)) {
            return false;
        } elseif(is_array($this->user) && !isset($this->user['user_id'])) {
            return false;
        } elseif($this->user['user_id'] == 0) {
            return false;
        }

        return true;
    }

    /**
     * 当前是否JSON请求
     * @access public
     * @return bool
     */
    public function isJson(): bool
    {
        if (config('app.response_data_type') == 'json') {
            return true;
        }

        return parent::isJson();
    }

    /**
     * 设置为登录用户
     * @return mixed
     */
    public function login($user)
    {
        if ($this->user === null) {
            $token = defined('S0') ? S0 : cookie('token');
            $this->user = redis()->get("usr.{$token}");
            if (!isset($this->user['SESSION_ID'])) {
                throw new \Exception('INVALID PARAM');
            }
        }

        $this->user = array_merge($this->user, $user);
        $expireTime = 43200;
        redis()->tag("login")->set("usr.{$this->user['SESSION_ID']}", $this->user, $expireTime);

        return [
            'ign' => TIMESTAMP,
            'token' => isset($user['token']) ? $user['token'] : ""
        ];
    }

    /**
     * 更新登录缓存
     *
     * @param array $params
     * @return void
     */
    public function merge($params = [])
    {
        if (!empty($this->user)) {
            $this->user = array_merge($this->user, $params);
            \think\facade\Log::info("MERGER => " . json_encode($this->user));
            $expireTime = 3600;
            redis()->tag("login")->set("usr.{$this->user['SESSION_ID']}", $this->user, $expireTime);
        }
    }

    /**
     * 设置当前请求的pathinfo
     * @access public
     * @param  string $pathinfo
     * @return $this
     */
    public function setPathinfo(string $pathinfo)
    {
        $path = $this->pathinfo();

        // var_dump($pathinfo);
        $map  = config('app.app_map', []);
        $name = current(explode('/', $path));

        if (isset($map[$name])) {
            $pathinfo = $name . '/' . $pathinfo;
        }

        $this->pathinfo = $pathinfo;
        return $this;
    }
}
