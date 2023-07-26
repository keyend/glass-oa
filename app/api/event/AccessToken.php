<?php
namespace app\api\event;
use think\facade\Log;
use think\facade\Cache;
use mashroom\provider\Request;

/**
 * 通讯授权令牌
 * 
 * @version 1.0.0
 */
class AccessToken
{
    /**
     * Undocumented variable
     *
     * @var mashroom\provider\Request;
     */
    private $request;

    /**
     * 构造函数
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * 返回交互令牌
     *
     * @return void
     */
    public function handle()
    {
        if (!defined("S5")) {
            $url = $this->request->domain(true) . "/api/oauth/access_token";
            $key = conf("api.secret_key", "");
            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', $url, [
                "query" => [
                    "key" => $key,
                    "session_id" => S0,
                    "scope" => "userinfo"
                ]
            ]);
            $result = $response->getBody()->getContents();
            Log::info("AUTH => {$result}");
            $res = json_decode($result, true);
            return $res ? $res["data"] : [];
        } else {
            return ["access_token" => S5];
        }
    }
}