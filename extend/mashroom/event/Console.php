<?php
namespace mashroom\event;
use think\facade\Log;
/**
 * 控制台事件
 * @version 1.0.0
 */
class Console
{
    public function handle($params = [])
    {
        if (defined('TIMESTAMP')) {
            return false;
        }

        if (!isset($_SERVER['REQUEST_TIME'])) {
            $_SERVER['REQUEST_TIME'] = time();
        }

        define('TIMESTAMP', $_SERVER['REQUEST_TIME']);

        $app = app();
        if (isset($params['token']) && !empty($params['token'])) {
            define("S0", $params['token']);
            $app->request->login([]);
            if ($app->request->isLogin()) {
                define("S1", $app->request->user["user_id"]);
            }
        }
    }
}