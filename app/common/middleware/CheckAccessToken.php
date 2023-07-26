<?php
namespace app\common\middleware;
use app\common\model\auth\OAuth;
use mashroom\middleware\BaseMiddleware;
use think\Request;
/**
 * API 授权校验中间件
 * @version 1.0.0
 */
class CheckAccessToken extends BaseMiddleware
{
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
     * 交互对接
     * @access public
     * @param Request $request
     * @param Closure $next
     * @param ...$args
     * @return Response
     */
    public function handle(Request $request, \Closure $next, ...$args) 
    {
        $access_token = $request->get('access_token');
        if (empty($access_token)) {
            throw new \Exception("INVALID_PARAM", 50001);
        }

        $authroize = app()->make(OAuth::class)->where('access_token', $access_token)->order('id DESC')->find();
        if (empty($authroize)) {
            throw new \Exception("INVALID_TOKEN", 50002);
        }

        $user = redis()->get("usr.{$access_token}");
        if (empty($user)) {
            throw new \Exception("access_token expired", 50003);
        }

        define("S0", $access_token);
        define("S1", $authroize->user_id);
        $request->user = $user;

        if ($args) {
            $checkLogined = $this->getArgument($args, 0, false);
            if (is_bool($checkLogined)) {
                if (S1 == 0) {
                    throw new \Exception("未登录", 50037);
                }
            }
        }

        return $next($request);
    }
}