<?php
namespace app\api\event;
use think\facade\Log;
use think\facade\Cache;
use mashroom\provider\Request;
use PHPQRCode\Qrcode;

/**
 * 微信登录二维码图片
 * 
 * @version 1.0.0
 */
class WeixinLoginQrCode
{
    /**
     * 附带参数展示二维码
     *
     * @param array $params
     * @return void
     */
    public function handle($params = [])
    {
        $url = request()->domain(true) . "/api/user/login/weixin/qrcode";
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $url, [
            "query" => $params
        ]);
        $result = $response->getBody()->getContents();
        Log::write("QRCODE => {$result}");
        $res = json_decode($result, true);
        if (!empty($res)) {
            if ($res["code"] == 0) {
                $res["data"]["imgUrl"] = Qrcode::png($res['url'], false, 'L', 4, 2);
                return $res["data"];
            }
        }
    }
}