<?php
namespace app\api\event;
use think\facade\Log;
use think\facade\Cache;

/**
 * 退出登录
 * 
 * @version 1.0.0
 */
class UserLogout
{
    public function handle()
    {
        $url = request()->domain(true) . "/api/user/logout?access_token=" . S0;
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $url);
        $result = $response->getBody()->getContents();
        Log::write("Logout => {$result}");
    }
}