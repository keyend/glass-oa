<?php
namespace app\admin\controller\system;
use app\admin\Controller;
use app\common\model\system\ConfigModel;
use app\common\model\crebo\Groups;
use think\facade\Cache;
/**
 * 参数配置
 * @package admin.controller.config
 * @version 1.0.0
 */
class Config extends Controller
{
    /**
     * 日志列表
     *
     * @param LogsModel $logs_model
     * @return void
     */
    public function index(ConfigModel $config_model)
    {
        if (IS_AJAX) {
        } elseif(checkAccess('sysConfigBasic')) {
            return redirect(url("sysConfigBasic")->build());
        } elseif(checkAccess('sysConfigConnect')) {
            return redirect(url("sysConfigConnect")->build());
        } elseif(checkAccess('sysConfigPay')) {
            return redirect(url("sysConfigPay")->build());
        } elseif(checkAccess('sysConfigMail')) {
            return redirect(url("sysConfigMail")->build());
        } elseif(checkAccess('sysConfigVip')) {
            return redirect(url("sysConfigVip")->build());
        } elseif(checkAccess('sysConfigLogin')) {
            return redirect(url("sysConfigLogin")->build());
        }
    }

    /**
     * 返回参数列表
     *
     * @param ConfigModel $config_model
     * @param string $name
     * @return void
     */
    private function getOptions($config_model, $name = '')
    {
        $settings = $config_model->where('parent', $name)->select();
        $options = [];
        foreach ($settings as $item){
            $options[$item['name']] = $item['value'];
        }
        return $options;
    }

    /**
     * 更新参数
     *
     * @param ConfigModel $config_model
     * @param string $name
     * @return void
     */
    private function saveOptions($config_model, $type = '') {
        if ($this->request->isPost()){
            $post = input('post.');
            try {
                foreach ($post as $key => $item){
                    if ($config_model->where('name', $key)->where('parent', $type)->count() > 0){
                        $config_model->where('name',$key)->where('parent', $type)->update(['value' => $item]);
                    }else{
                        throw new Exception('更新配置[{$key}]错误!');
                    }
                }
            }catch (\Throwable $e){
                $this->returnError($e->getMessage());
            }
            $this->logger('logs.sys.config', 'UPDATED', $post);
            event("ConfigChange");
            $this->returnSuccess('保存成功');
        }
    }

    /**
     * 基础配置
     *
     * @param ConfigModel $config_model
     * @return void
     */
    public function basic(ConfigModel $config_model)
    {
        $this->saveOptions($config_model, __FUNCTION__);
        $this->assign('option', $this->getOptions($config_model, __FUNCTION__));
        return $this->fetch(__FUNCTION__);
    }

    /**
     * 注册访问
     *
     * @param ConfigModel $config_model
     * @return void
     */
    public function register(ConfigModel $config_model)
    {
        $this->saveOptions($config_model, __FUNCTION__);
        $this->assign('option', $this->getOptions($config_model, __FUNCTION__));
        $group = Groups::field('id,group_name')->select()->toArray();
        $this->assign('group',$group);
        return $this->fetch(__FUNCTION__);
    }

    /**
     * 支付参数
     *
     * @param ConfigModel $config_model
     * @return void
     */
    public function pay(ConfigModel $config_model)
    {
        $this->saveOptions($config_model, __FUNCTION__);
        $this->assign('option', $this->getOptions($config_model, __FUNCTION__));
        return $this->fetch(__FUNCTION__);
    }

    /**
     * 邮件参数
     *
     * @param ConfigModel $config_model
     * @return void
     */
    public function email(ConfigModel $config_model)
    {
        $this->saveOptions($config_model, __FUNCTION__);
        $this->assign('option', $this->getOptions($config_model, __FUNCTION__));
        return $this->fetch(__FUNCTION__);
    }

    /**
     * 套餐参数
     *
     * @param ConfigModel $config_model
     * @return void
     */
    public function vip(ConfigModel $config_model)
    {
        $this->saveOptions($config_model, __FUNCTION__);
        $this->assign('option', $this->getOptions($config_model, __FUNCTION__));
        $group = Groups::field('id,group_name')->select()->toArray();
        $this->assign('group',$group);
        return $this->fetch(__FUNCTION__);
    }

    /**
     * API参数
     *
     * @param ConfigModel $config_model
     * @return void
     */
    public function api(ConfigModel $config_model)
    {
        $this->saveOptions($config_model, __FUNCTION__);
        $this->assign('option', $this->getOptions($config_model, __FUNCTION__));
        return $this->fetch(__FUNCTION__);
    }

    /**
     * 登录参数
     *
     * @param ConfigModel $config_model
     * @return void
     */
    public function login(ConfigModel $config_model)
    {
        $this->saveOptions($config_model, __FUNCTION__);
        $this->assign('option', $this->getOptions($config_model, __FUNCTION__));
        return $this->fetch(__FUNCTION__);
    }
}