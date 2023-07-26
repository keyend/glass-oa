<?php
namespace mashroom\event;
use think\facade\Event;
/**
 * 全局常量定义
 * 
 * @version 1.0.0
 */
class Constant
{
    public function handle($app)
    {
        if (!isset($_SERVER['REQUEST_TIME']))
            $_SERVER['REQUEST_TIME'] = time();
            
        if (!$app->runningInConsole()) {
            define('MODULE',    $app->http->getName());
            define('CONTROLLER',$app->request->controller());
            define('ACTION',    $app->request->action());
            define('IS_POST',   $app->request->isPost());
            define('IS_PUT',    $app->request->isPut());
            define('IS_DELETE', $app->request->isDelete());
            define('IS_GET',    $app->request->isGet());
            define('IS_AJAX',   $app->request->isAjax());

            $accept = explode(',', $app->request->header('accept'));
            define('CONTENT_TYPE', empty($accept) ? 'text/html' : $accept[0]);
            define('IS_JSON', strpos(CONTENT_TYPE, 'json') !== FALSE ? true: false);

            if ($app->request->method(true) == 'OPTIONS') {
                throw new HttpResponseException(Response::create()->code(200));
            }
        }

        define('TIMESTAMP', $_SERVER['REQUEST_TIME']);
        define('RANGE_PLATFORM', 'platform');
        define('RANGE_GENERATE', 'generate');
    }
}