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
use mashroom\middleware\BaseMiddleware;

class Base extends BaseMiddleware
{
    /**
     * 交互对接
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, \Closure $next) 
    {
        return $next($request);
    }
}