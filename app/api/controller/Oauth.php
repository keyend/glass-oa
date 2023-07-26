<?php
/**
 * Oauth 认证
 * @version 1.0.0
 */
namespace app\api\controller;
use app\api\Controller;

class Oauth extends Controller
{
    /**
     * 返回CODE
     * @description /oauth/code
     * @param string $key Oauth密钥
     * @return void
     */
    public function code($key = '')
    {
        try {
            $params = $this->params([ ['key', $key], ['scope', ''] ], true);
            $params["client"] = $this->request->ip();
            $res = $this->model->oauth_codes->build($params);
        } catch(\Exception $e) {
            return $this->fail($e->getMessage());
        }

        return $res;
    }

    /**
     * 获取令牌交互
     *
     * @return void
     */
    public function access_token()
    {
        $code = $this->code($this->params['key']);
        try {
            if (!isset($this->params['session_id']) || empty($this->params['session_id'])) {
                $session_id = cookie(config("session.name"));
                if (empty($session_id)) {
                    $session_id = md5(uniqid());
                }
                $this->params['session_id'] = $session_id;
                $this->request->user = [ "SESSION_ID" => $this->params['session_id'] ];
                $this->request->login($this->request->user);
                cookie(config("session.name"), $this->params['session_id']);
            }

            $res = $this->model->oauth->access_token(array_keys_filter($code, [
                'code',
                'scope',
                'expire_in',
                ['session_id', $this->params['session_id']],
                ['grant_type', 'authorization_code'],
                ['client_id', "10000"],
                ["client_secret", $this->params["key"]]
            ], true));

            if ($res instanceof $this->model->oauth) {
                define("S0", $res["access_token"]);
                $this->request->login([ "api" => $res->toArray() ]);
            }
        } catch(\Exception $e) {
            return $this->fail($e->getMessage());
        }

        return $this->success($res);
    }
}