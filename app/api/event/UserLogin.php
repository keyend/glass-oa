<?php
namespace app\api\event;
use think\facade\Log;
use think\facade\Cache;

/**
 * 用户登录
 * 
 * @version 1.0.0
 */
class UserLogin
{
    public function handle($params = [])
    {
        $data = [
            "username" => $params["username"],
            "password" => $params["password"],
            "verify_code" => $params["code"]
        ];
        $url = request()->domain(true) . "/api/user/login?access_token=" . S0;
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $url, [
            "form_params" => $data
        ]);
        $result = $response->getBody()->getContents();
        Log::write("Login => {$result}");
        $res = json_decode($result, true);
        if (!empty($res)) {
            return $res;
        }
    }
}