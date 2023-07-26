<?php
/**
 * 用户接口
 * @version 1.0.0
 */
namespace app\api\controller;
use app\api\Controller;
use app\common\model\crebo\Groups;

class User extends Controller
{
    /**
     * 扫码登录二维码
     *
     * @return void
     */
    public function login_qrcode()
    {
        try {
            $res = $this->model->wechat->getLoginQrcode($this->params([ ['share_id', 0] ], true));
        } catch(\Exception $e) {
            return $this->fail($e->getMessage());
        }

        return $this->success($res);
    }

    /**
     * 扫码登录后返回会员信息
     *
     * @return void
     */
    public function userinfo(Groups $group_model)
    {
        try {
            $user_info = $this->model->user->find(S1);
            $result = [
                "id" => $user_info->id,
                "username" => $user_info->username,
                "nickname" => $user_info->nickname,
                "avatar" => $user_info->avatar,
                "desc" => $user_info->desc,
                "group" => $user_info->group,
                "mobile" => $user_info->mobile,
                "group_name" => $group_model->where("id", $user_info->group)->value("group_name")
            ];
        } catch(\Exception $e) {
            return $this->fail($e->getMessage());
        }

        return $this->success($result);
    }

    /**
     * 新用户注册
     *
     * @return void
     */
    public function register()
    {
        try {
            $data = $this->params([
                "username",
                "mobile",
                "password",
                ["nickname", ""],
                ["email", ""],
                ["openid", ""],     // OPENID
                "device_type",      // APP, MP, H5
                "verify_code"
            ], true);

            $validError = $this->validate( $data, [
                'mobile|手机号' => 'require|mobile',
                'username|用户帐号' => 'require|alphaNum|length:6,26',
                'password|登录密码' => 'require|alphaNum|length:6,18'
            ] );
            if ($validError !== true) {
                throw new \Exception($validError);
            }
            $values = $this->request->user["api"];
            if (!isset($values["sms_code"]) || $values["sms_code"] != $data["verify_code"]) {
                throw new \Exception("短信验证码错误!");
            }
            unset($data["verify_code"]);
            $data["group"] = conf( 'register.default_group' );
            $res = $this->model->user->register($data);
        } catch(\Exception $e) {
            return $this->fail($e->getMessage());
        }

        return $this->success(array_keys_filter($res, [ "id" ]));
    }

    /**
     * 发送忘记密码短信
     *
     * @return void
     */
    public function verifyForgetCode()
    {
        try {
            $validate = $this->validate($this->params([ 'mobile' ], true), [ 'mobile|手机号' => 'require|mobile' ]);
            if ($validate !== true) {
                return $this->fail($validate);
            }

            $data = $this->params([ "username", "mobile" ], true);
            $user = $this->model->user->where("mobile", $data["mobile"])->find();
            if (empty($user)) {
                throw new \Exception("用户不存在(请检查手机号与用户名是否匹配)!", 404);
            } elseif($user->username != $data["username"]) {
                throw new \Exception("用户不存在(请检查手机号与用户名是否匹配)!", 404);
            }

            $values = $this->request->user["api"];
            $times = 60;
            if (isset($values["sms_code"])) {
                if (TIMESTAMP < $values["forget_sms_expire_at"]) {
                    return $this->fail("操作太频繁", [
                        "times" => $values["forget_sms_expire_at"] - TIMESTAMP
                    ]);
                }
            }

            $code = mt_rand(1000, 9999);
            // event("SendMobileCode", [ 'mobile' => $this->params['mobile'], 'code' => $code ]);
            unset($values["captcha_key"]);
            $values = array_merge($values, [ 
                "forget_sms_code" => $code,
                "forget_sms_expire_at" => TIMESTAMP + 60,
                "forget_id" => $user->id
            ]);
            $this->request->user["api"] = $values;
            $this->request->login($this->request->user);

            return $this->success([
                "times" => $times
            ]);
        } catch(\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新密码
     *
     * @return void
     */
    public function forgetUpdate()
    {
        try {
            $data = $this->params([ "password", "verify_code" ], true);
            $validate = $this->validate($this->params([ 'password' ], true), [ 'password|新密码' => 'require|alphaNum|length:6,18' ]);
            if ($validate !== true) {
                return $this->fail($validate);
            }
            $values = $this->request->user["api"];
            if (!isset($values["forget_sms_code"]) || $values["forget_sms_code"] != $data["verify_code"]) {
                throw new \Exception("短信验证码错误!");
            } elseif (!isset($values["forget_id"])) {
                return $this->fail("INVALID_PARAM");
            }
            $user = $this->model->user->where("id", $values["forget_id"])->find();
            if (empty($user)) {
                return $this->fail("INVALID_PARAM");
            }
            $user->password = md5($data["password"]);
            $user->save();
            unset($values["forget_sms_code"], $values["forget_sms_expire_at"], $values["forget_id"]);
            $this->request->user["api"] = $values;
            $this->request->login($this->request->user);
        } catch(\Exception $e) {
            return $this->fail($e->getMessage());
        }

        return $this->success();
    }

    /**
     * 会员登录
     *
     * @return void
     */
    public function login()
    {
        if ($this->request->isLogin()) {
            return $this->success();
        }

        try {
            if (isset($this->params["mobile"]) && !empty($this->params["mobile"])) {
                $validate = $this->validate($this->params([ 'mobile' ], true), [ 'mobile|手机号' => 'require|mobile' ]);
                $values = $this->request->user["api"];
                if (!isset($values["sms_code"]) || $values["sms_code"] != $this->params["verify_code"]) {
                    throw new \Exception("短信验证码错误!");
                }

                $user = $this->model->user->where("mobile", $this->params["mobile"])->find();
                if (empty($user)) {
                    return $this->fail("用户不存在");
                }
            } else {
                $data = $this->params([ "username", "password", ["verify_code", ""] ], true);
                \think\facade\Log::info("CHECK => " . json_encode($data));
                if (conf("login.img_verify") == 1) {
                    $values = $this->request->user["api"];
                    if (isset($values["captcha_key"])) {
                        $this->app->session->set('captcha', [ 'key' => $values["captcha_key"] ]);
                    }
                    if (empty($data["verify_code"]) || !captcha_check($data["verify_code"])) {
                        return $this->fail("验证码错误");
                    }
                }
    
                $user = $this->model->user->where("username", $data["username"])->find();
                if (empty($user)) {
                    return $this->fail("用户不存在");
                } elseif($user->password != md5($data["password"])) {
                    return $this->fail("密码错误");
                }
            }

            $oauth = $this->model->oauth->where("access_token", S0)->order('id DESC')->find();
            $oauth->user_id = $user->id;
            $oauth->save();

            $this->request->user["user_id"] = $user->id;
            $this->request->login($this->request->user);
        } catch(\Exception $e) {
            return $this->fail($e->getMessage());
        }

        return $this->success();
    }

    /**
     * 退出登录
     *
     * @return void
     */
    public function logout() 
    {
        $this->logger('logs.user.logout', 'LOGOUT', $this->request->user);
        redis()->delete("usr." . S0);
    }

    /**
     * 更新会员信息
     *
     * @return void
     */
    public function update()
    {
        try {
            $user_info = $this->model->user->find(S1);
            $data = $this->params([ "nickname", "avatar", ["desc", ""] ], true);
            $validate = $this->validate($this->params([ 'avatar', 'nickname' ], true), [ 'avatar|封面头像' => 'require', 'nickname|呢称' => 'require' ]);
            if ($validate !== true) {
                return $this->fail($validate);
            }
            $user_info->forceUpdate($data);
        } catch(\Exception $e) {
            return $this->fail($e->getMessage());
        }

        return $this->success();
    }

    /**
     * 更新登录密码
     *
     * @return void
     */
    public function changePassword()
    {
        try {
            $data = $this->params([ "password" ], true);
            $validate = $this->validate($this->params([ 'password' ], true), [ 'password|新密码' => 'require|alphaNum|password|length:6,18' ]);
            if ($validate !== true) {
                return $this->fail($validate);
            }
            $user_info = $this->model->user->find(S1);
            if ($user_info->password != md5($this->params["old_password"])) {
                return $this->fail("旧密码错误");
            }
            $data["password"] = md5($data["password"]);
            $user_info->forceUpdate($data);
        } catch(\Exception $e) {
            return $this->fail($e->getMessage());
        }

        return $this->success();
    }
}