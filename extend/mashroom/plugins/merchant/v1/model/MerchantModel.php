<?php
namespace mashroom\plugins\merchant\v1\model;
/**
 * 商户
 * 
 * @package app.api.model
 * @Author: k.
 * @Date: 2021-05-10 20:19:31
 */
use think\facade\Route;
use mashroom\exception\HttpException;
use mashroom\component\Tinymce;
use FormBuilder\Exception\FormBuilderException;
use FormBuilder\Factory\Elm;
use FormBuilder\Form;
use app\common\model\UserModel;
use think\Model;

class MerchantModel extends Model
{
    protected $name = 'sys_merchant';
    protected $pk = 'mer_id';

    public function merchantUser()
    {
        return $this->hasMany(MerchantUserModel::class, 'mer_id', 'mer_id');
    }

    public function users()
    {
        return $this->hasManyThrough(UserModel::class, MerchantUserModel::class, 'mer_id', 'user_id', 'mer_id', 'user_id');
    }

    /**
     * 返回列表
     *
     * @param integer $page
     * @param integer $limit
     * @param array $filter
     * @return array
     */
    public function getList(int $page = 1, int $limit = 9999, array $filter = [])
    {
        // 倒序
        $query = self::with(['users'])->order('mer_id DESC');
        // 过滤器
        if (isset($filter['kw']) && $filter['kw'] !== '') {
            $query->where('mer_name|mer_remark', 'like', "%" . str_replace(' ', '%', $filter['kw']) . "%");
        }
        // 记录条数
        $count = $query->count();
        // 获取所有记录
        $list = $query->page($page, $limit)->select()->withAttr('create_time', function($value) {
            return date('Y-m-d H:i', $value);
        })->toArray();

        return compact('count', 'list');
    }

    /**
     * 返回记录
     *
     * @param integer $id
     * @return array
     */
    public function getRecordById(int $id = 0)
    {
        $data = [];
        $query = null;

        if ($id !== 0) {
            $query = self::with(['merchantUser'])->find($id);
            if ($query) $data = $query->toArray();
        }

        return [$data, $query];
    }

    /**
     * 返回表单地址
     *
     * @param int $id
     * @return void
     */
    protected function createFormUrl(int $id = 0)
    {
        return \think\facade\Route::buildUrl(substr($this->name, 4) . ($id !== 0?'Update':'Add'), ['id' => $id])->build();
    }

    /**
     * 返回表单类型
     *
     * @param integer $id
     * @return void
     */
    protected function getFormType(int $id = 0)
    {
        return $id === 0 ? 'create' : 'update';
    }

    /**
     * 返回表单数据
     *
     * @param integer $id
     * @return array
     */
    public function getForm(int $id = 0)
    {
        $type = $this->getFormType($id);
        $url = $this->createFormUrl($id);
        $form  = Elm::createForm($url);

        $rules = [
            Elm::input('mer_title', '商户名称')->required(),
            Elm::input('mer_name', '商户标识'),
            Elm::input('mer_phone', '联系方式'),
            Elm::textarea('mer_remark', '备注信息')
        ];

        [$data, $query] = $this->getRecordById($id);

        $form->formData($data);
        $form->setTitle(lang($this->name . ".form.{$type}"));
        $form->setRule($rules);
        $form->setConfig([
            'domain' => request()->domain(TRUE)
        ]);

        return formToData($form, $type);
    }
}
