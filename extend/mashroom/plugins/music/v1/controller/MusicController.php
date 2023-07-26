<?php
namespace mashroom\plugins\music\v1\controller;
/**
 * 音乐管理
 * 
 * @date: 2021-05-10 20:19:31
 * @author: k.
 * @version 1.0.1
 * @package easyzf
 */

use app\api\Controller;
use mashroom\plugins\music\v1\model\MusicModel;
use mashroom\plugins\music\v1\model\MusicProviderModel;

class MusicController extends Controller
{
    /**
     * 返回列表
     * 
     * @param MusicModel $model
     * @return mixed
     */
    public function lst(MusicModel $model)
    {
        [$page, $limit] = $this->getPaginator();
        $filter = array_keys_filter($this->request->get(), [
            'kw',
            'provider'
        ]);

        return $this->success($model->getList($page, $limit, $filter));
    }

    /**
     * 获取表单
     *
     * @param MusicModel $model
     * @return mixed
     */
    public function form(MusicModel $model)
    {
        $query = array_keys_filter($this->request->post(), [
            ['id', 0]
        ]);
        $id = (int)$query['id'];

        return $this->success($model->getForm($id));
    }

    /**
     * 服务商列表
     *
     * @param MusicProviderModel $model
     * @return void
     */
    public function provider(MusicProviderModel $model) 
    {
        [$page, $limit] = $this->getPaginator();
        return $this->success($model->getList($page, $limit));
    }

    /**
     * 服务商表单
     *
     * @param MusicProviderModel $model
     * @return mixed
     */
    public function providerForm(MusicProviderModel $model) 
    {
        $data = array_keys_filter($this->request->post(), [
            ['id', 0]
        ]);
        $data['id'] = (int)$data['id'];

        return $this->success($model->getForm($data['id']));
    }

    /**
     * 服务商增加
     *
     * @param MusicProviderModel $model
     * @return mixed
     */
    public function providerCreate(ArticleBannerModel $model)
    {
        $data = array_keys_filter($this->request->post(), [
            'provider',
            'remark',
            ['sort', 0],
            ['params', []]
        ]);

        $data['provider'] = trim($data['provider']);
        $data['remark'] = trim($data['remark']);
        $data['params'] = json_encode($data['params'], JSON_UNESCAPED_UNICODE);
        $data['sort'] = (int)$data['sort'];

        // 添加记录
        $data['provider_id'] = $model->insertGetId($data);
        // 记录日志
        $this->app->make(\app\api\model\LogsModel::class)->info('logs.music.provider.create', 'CREATEED', $data);

        return $this->success($data);
    }

    /**
     * 服务商更新
     *
     * @param MusicProviderModel $model
     * @return mixed
     */
    public function providerUpdate(int $id, MusicProviderModel $model)
    {
        $data = array_keys_filter($this->request->post(), [
            'remark',
            ['sort', 0],
            ['params', []]
        ]);

        $data['remark'] = trim($data['remark']);
        $data['params'] = json_encode($data['params'], JSON_UNESCAPED_UNICODE);
        $data['sort'] = (int)$data['sort'];

        [$orignal, $query] = $model->getRecordById($id);
        if (!$query) {
            return $this->fail(lang('no exist'));
        }

        // 保存记录
        $query->save($data);
        // 记录日志
        $this->app->make(\app\api\model\LogsModel::class)->info('logs.music.provider.update', 'UPDATED', [$orignal, $data]);

        return $this->success();
    }

    /**
     * 内容横幅删除
     * 
     * @param int $id
     * @param MusicProviderModel $model
     * @return mixed
     */
    public function providerDelete($id, MusicProviderModel $model)
    {
        $id = (int)$id;

        [$orignal, $query] = $model->getRecordById($id);
        if (!$query) {
            return $this->fail(lang('no exist'));
        }

        // 删除记录
        $query->delete();
        // 记录日志
        $this->app->make(\app\api\model\LogsModel::class)->info('logs.music.provider.delete', 'DELETE', $orignal);

        return $this->success();
    }
}
