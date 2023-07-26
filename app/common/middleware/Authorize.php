<?php
namespace app\common\middleware;
/*
 * 商户验证中间件
 * @Author: k
 * @Date: 2020-11-10
 */
use think\App;
use think\Lang;
use think\Request;
use app\common\model\crebo\Users;
use mashroom\middleware\BaseMiddleware;

class Authorize extends BaseMiddleware
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

    /**
    * @param int $num
    * @param mixed $default
    * @return mixed
    * @author xaboy
    * @day 2020-04-10
    */
    protected function getArgument($args, $num, $default = null)
    {
        return isset($args[$num]) ? $args[$num] : $default;
    }

    /**
     * 权限验证
     *
     * @param Request $request
     * @return boolean
     */
    private function checkAccess($request)
    {
        return true;
    }

    /**
     * 交互对接
     * @access public
     * @param Request $request
     * @param Closure $next
     * @param ...$args
     * @return Response
     */
    public function handle(Request $request, \Closure $next, ...$args) 
    {
        $session_id = cookie(config("session.name"));
        if (empty($session_id)) {
            $session_id = md5(uniqid());
            cookie(config("session.name"), $session_id);
        }

        if (conf("base.site_close") == 1) {
            throw new ValidateException('The service has been closed or is undergoing maintenance and upgrade.', 50001);
        }

        define('S0', $session_id);
        $request->user = redis()->get("usr.{$session_id}");
        if (!empty($request->user)) {
            if (isset($request->user["api"])) {
                if (TIMESTAMP < $request->user["api"]["expire_at"]) {
                    define("S5", $request->user["api"]["access_token"]);
                }
            }
        } else {
            $request->user = [ "SESSION_ID" => $session_id ];
            $request->login($request->user);
        }

        if ($args) {
            $checkAccess = $this->getArgument($args, 0, false);
            if (is_bool($checkAccess)) {
                if (empty($request->user) || !isset($request->user["user_id"])) {
                    redirect(url('login'))->send();
                    die;
                } elseif(!isset($request->user["username"])) {
                    $user_info = Users::getInfo($request->user["user_id"]);
                    if (empty($user_info)) {
                        die("<h1>USER_NOT_FOUND</h1>");
                    }
                    $request->user = array_merge($request->user, $user_info->toArray());
                    $request->login($request->user);
                    $user_info->login_time = TIMESTAMP;
                    $user_info->login_real_ip = $request->ip();
                    $user_info->save();
                    $this->logger('logs.member.login', 'LOGGED', $user_info);
                }

                define('S1', $request->user['user_id']);
                define('S2', $request->user['username']);
                define('S3', $request->user['parent_id']);
                define('S4', $request->user['group']);

                if ($checkAccess) {
                    if (!$this->checkAccess($request)) {
                        throw new \Exception(lang("no access"));
                    }
                }
            }
        }

        return $next($request);
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