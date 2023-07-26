<?php
/**
 * 工具接口
 * @version 1.0.0
 */
namespace app\api\controller;
use app\api\Controller;
use app\common\model\system\UploadModel;
use app\common\model\system\UploadGroupModel;

class Util extends Controller
{
    /**
     * 返回图形验证码
     *
     * @return void
     */
    public function verifyImage()
    {
        if ($this->request->isAjax()) {
            $this->app->config->set($this->params([ ['length', 4], ['math', 0] ]), "captcha");
            try {
                $response = captcha();
                $result = [];
                $result["imgUrl"] = "data:image/png;base64," . base64_encode($response->getData());
                $values = $this->request->user["api"];
                $values["captcha_key"] = session('captcha.key');
                $this->request->login([ "api" => $values ]);
            } catch(\Exception $e) {
                return $this->fail($e->getMessage());
            }

            return $this->success($result);
        }
    }

    /**
     * 发送短信验证码
     *
     * @return void
     */
    public function verifyCode()
    {
        $values = $this->request->user["api"];
        if (conf("register.img_verify") == 1) {
            if (isset($values["captcha_key"])) {
                $this->app->session->set('captcha', [ 'key' => $values["captcha_key"] ]);
            }
            if (!captcha_check($this->params["code"])) {
                return $this->fail("验证码错误");
            }
        }

        $times = 60;
        if (isset($values["sms_code"])) {
            if (TIMESTAMP < $values["sms_expire_at"]) {
                return $this->fail("操作太频繁", [
                    "times" => $values["sms_expire_at"] - TIMESTAMP
                ]);
            }
        }
        $validate = $this->validate($this->params([ 'mobile' ], true), [ 'mobile|手机号' => 'require|mobile' ]);
        if ($validate !== true) {
            return $this->fail($validate);
        }

        $code = mt_rand(1000, 9999);
        // event("SendMobileCode", [ 'mobile' => $this->params['mobile'], 'code' => $code ]);
        unset($values["captcha_key"]);
        $values = array_merge($values, [ 
            "sms_code" => $code,
            "sms_expire_at" => TIMESTAMP + 60
        ]);
        $this->request->user["api"] = $values;
        $this->request->login($this->request->user);

        return $this->success([
            "times" => $times
        ]);
    }

    /**
     * 图片上传
     *
     * @return void
     */
    public function uploadImage(UploadGroupModel $upload_group_model, UploadModel $upload_model)
    {
        $ablumGroup = $upload_group_model->getAlbumDefaultGroup();
        if (empty($ablumGroup)) {
            return $this->fail("上传失败::50291");
        }

        try {
            $files = $this->request->file('file');
            $result = $upload_model->uploadFile($files, $ablumGroup["id"]);
        } catch(\Exception $e) {
            return $this->fail($e->getMessage());
        }

        return $this->success(current($result));
    }
}