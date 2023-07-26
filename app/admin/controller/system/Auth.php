<?php
namespace app\admin\controller\system;
use app\admin\Controller;
use app\admin\validate\UserValidate;
use app\common\model\system\UserModel;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;

class Auth extends Controller
{
    /**
     * 创建一枚可通讯的令牌
     * @return void
     */
    public function generateToken()
    {
        $token = cookie('token');
        if (!empty($token)) {
            $value = redis()->get("usr.{$token}");
        }

        if (empty($token) || empty($value)) {
            $token = getToken();
            $value = [
                'SESSION_ID' => $token,
                'REMOTE_ADDR' => $this->request->ip()
            ];
            redis()->set("usr.{$token}", $value);
            cookie('token', $token);
        }

        return $this->success($value);
    }

    /**
     * 注解登录
     * @return mixed
     */
    public function adminLogout()
    {
        if (IS_POST) {
            $this->logger('logs.user.logout', 'LOGOUT', $this->request->user);
            redis()->delete("usr.{$this->request->user['SESSION_ID']}");
            cookie('token', null);
        }

        return $this->success();
    }

    /**
     * 获取验证码
     * @return Array
     */
    public function verifyImage()
    {
        $builder = new CaptchaBuilder(null, new PhraseBuilder(4));
        $captcha = $builder->build()->inline();
        $key = substr(getToken('verify'), 8, 16);
        $expireTime = config('admin.user_vi_time', 1) * 60;
        redis()->tag("temp")->set("sys.captcha.{$key}", $builder->getPhrase(), $expireTime);

        return $this->success(compact('key', 'captcha'));
    }

    /**
     * AES解密CBC字串
     * @param [type] $str
     * @return void
     */
    protected function aesDecrypt($str, $key = '') 
    {
        // $decryptStr = openssl_decrypt(base64_decode($str), 'AES-128-CBC', substr(S0, 8, 16), true, substr($key, 0, 16));
        $rsa = $this->app->make(\mashroom\component\algorithm\Rsa::class);
        $decryptStr = $rsa->setPrivateKey()->decrypt($str);

        return $decryptStr;
    }

    /**
     * 行为验证
     * @param string
     * @return Boolean
     */
    protected function validateBehaviour($code)
    {
        $points = explode(',', $code);
        $commitTime = end($points);
        // 结构
        if (count($points) < 13) return '1';
        if (!is_numeric(current($points))) return '2';
        // 操作时间太快(一秒内)
        if ($commitTime - $points[10] < 800) return '3';
        // 时间验证(验证码超时)
        $commitTime = ceil($commitTime / 1000);
        if (TIMESTAMP < $commitTime) return '4';
        // 时间验证(验证码超时)
        if (TIMESTAMP - $commitTime > 20) return '5';

        return true;
    }

    /**
     * 管理登录
     *
     * @return void
     */
    public function login(UserValidate $validate, UserModel $model)
    {
        if (IS_AJAX) {
            $data = $validate->scene('login')->post([], [
                'username',
                'password',
                'code',
                'key'
            ]);
            $key = $data['key'];
            $verifyType = config('admin.verifyType', 2);
            $valid = $verifyType == 1 ? $this->validateBehaviour($this->aesDecrypt($data['code'], $key)) : $data['code'];
            if ($verifyType == 2) {
                $name = "sys.captcha.{$key}";
                $code = strtolower(redis()->get($name));
                redis()->delete($name);
                $valid = strtolower($valid);
            }
            $verifyType = 3;

            if (($verifyType === 1 && true !== $valid) || 
                ($verifyType === 2 && $valid != $code)) {
                return $this->fail("验证码填写不正确{$code}::{$valid}");
            } elseif (true !== $this->request->isLogin()) {
                $this->request->login($model->validate($data));
                $this->logger('logs.user.login', 'LOGGED', $this->request->user);
            } else {
                $this->request->login($this->request->user);
            }
        } else {
            $userToken = $this->generateToken();
            if (isset($userToken['data']['user_id'])) {
                return redirect(url('sysIndex'));
            }
            $this->assign('token', $userToken['data']);
        }

        return $this->fetch();
    }
}