<?php
namespace mashroom\plugins\music\v1\model;
/**
 * 音乐服务商
 * 
 * @package app.extend.plugins.music.model
 * @Author: k.
 * @Date: 2021-05-10 20:19:31
 */
use think\Model;
use think\facade\Route;
use mashroom\exception\HttpException;
use FormBuilder\Exception\FormBuilderException;
use FormBuilder\Factory\Elm;
use FormBuilder\Form;

class MusicProviderModel extends Model
{
    protected $name = 'music_provider';
    protected $pk = 'provider_id';

    public function music()
    {
        return $this->hasMany(MusicModel::class, 'provider_id', 'provider_id');
    }

    /**
     * 返回列表
     *
     * @param integer $page
     * @param integer $limit
     * @param array $filter
     * @return array
     */
    public function getList(int $page = 1, int $limit = 9999)
    {
        return loadCache('music.provider', function() use($page, $limit) {
            // 倒序
            $query = self::order('sort DESC');
            // 记录条数
            $count = $query->count();
            // 获取所有记录
            $list = $query->page($page, $limit)->select()->toArray();

            return compact('count', 'list');
        }, true);
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
            $query = self::find($id);
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
        $rule = '/' . request()->rule()->getRule();
        $url = \think\facade\Route::buildUrl(dirname($rule) . '/' . ($id !== 0?'update':'create'), ['id' => $id])->build();

        return $url;
    }

    /**
     * 返回表单类型
     *
     * @param integer $id
     * @return string
     */
    protected function getFormType(int $id = 0)
    {
        return $id === 0 ? 'create' : 'update';
    }

    /**
     * 返回请求方式列表
     *
     * @return array
     */
    protected function getMethodList()
    {
        $options = [];
        $options[] = [
            'label' => 'POST',
            'value' => 'POST'
        ];
        $options[] = [
            'label' => 'GET',
            'value' => 'GET'
        ];

        return $options;
    }

    /**
     * 返回表单
     *
     * @param integer $id
     * @return array
     */
    public function getForm(int $id = 0)
    {
        $type = $this->getFormType();
        $url = $this->createFormUrl($id);
        $form = Elm::createForm($url);
        $line = new \mashroom\component\LineComponent('line', [
            'title' => 'REQUEST配置'
        ]);

        $rules = [
            Elm::input('provider', '服务名称')->required(true),
            Elm::textarea('remark', '备注说明'),
            Elm::number('sort', '图片排序'),
            $line,
            Elm::select('params[method]', '请求方式')->options($this->getMethodList())->required(TRUE),
            Elm::input('params[url]', 'URL')->required(TRUE),
            Elm::input('params[referer]', 'Referer')->required(TRUE),
            Elm::textarea('params[user-agent]', 'UA'),
            Elm::switches('params[proxy]', '代理')->activeValue(true)->inactiveValue(false)
        ];

        [$data, $query] = $this->getRecordById($id);

        if ($id !== 0) {
            $data['params'] = json_decode($data['params']);
            if ($data['params'] !== null) {
                foreach ($data['params'] as $key => $val) {
                    $data["params[{$key}]"] = $val;
                }
            }
        } else {
            $data['params[method]'] = 'POST';
            $data['params[url]'] = '';
            $data['params[referer]'] = '';
            $data['params[user-agent]'] = '';
            $data['params[proxy]'] = false;
        }

        $form->formData($data);
        $form->setTitle($type === 'create'?'新增服务商':'编辑服务商');
        $form->setRule($rules);
        $form->setConfig([
            'domain' => request()->domain(TRUE)
        ]);

        return formToData($form, $type);
    }
}
