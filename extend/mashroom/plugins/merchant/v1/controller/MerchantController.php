<?php
namespace mashroom\plugins\merchant\v1\controller;
/**
 * 商户管理
 * 
 * @date: 2021-05-10 20:19:31
 * @author: k.
 * @version 1.0.1
 * @package easyzf
 */
use app\api\Controller;
use mashroom\plugins\merchant\v1\model\MerchantModel;

class MerchantController extends Controller
{
    /**
     * 返回列表
     * 
     * @return mixed
     */
    public function lst(MerchantModel $model)
    {
        [$page, $limit] = $this->getPaginator();
        $filter = array_keys_filter($this->request->get(), [
            ['kw', '']
        ]);

        return $this->success($model->getList($page, $limit, $filter));
    }

    /**
     * 检索结果
     *
     * @param MerchantModel $model
     * @return void
     */
    public function search(MerchantModel $model)
    {
        $filter = array_keys_filter($this->request->get(), [
            ['kw', '']
        ]);
        $filter['kw'] = trim($filter['kw']);

        return $this->success($model->search($filter));
    }

    /**
     * 获取表单
     * 
     * @return mixed
     */
    public function form(MerchantModel $model)
    {
        $query = array_keys_filter($this->request->post(), [
            ['id', 0]
        ]);
        $id = (int)$query['id'];

        return $this->success($model->getForm($id));
    }

    /**
     * 添加记录
     * 
     * @return mixed
     */
    public function create(MerchantModel $model)
    {
        $data = array_keys_filter($this->request->post(), [
            'mer_name',
            ['mer_phone', ''],
            ['mer_remark', '']
        ]);

        $data['mer_name'] = trim($data['mer_name']);
        $data['mer_phone'] = trim($data['mer_phone']);
        $data['mer_remark'] = trim($data['mer_remark']);
        $data['create_time'] = TIMESTAMP;

        // 相似的记录
        if ($model->where('mer_name', $data['mer_name'])->find()) {
            return $this->fail(lang('exist record'));
        }

        // 添加记录
        $data['mer_id'] = $model->insertGetId(array_keys_filter($data, [
            'mer_name',
            'mer_phone',
            'mer_remark',
            'create_time'
        ]));
        // 记录日志
        $this->app->make(\app\api\model\LogsModel::class)->info('logs.merchant.create', 'CREATEED', $data);

        return $this->success($data);
    }

    /**
     * 更新记录
     * 
     * @return mixed
     */
    public function update(int $id, MerchantModel $model)
    {
        $data = array_keys_filter($this->request->post(), [
            ['mer_phone', ''],
            ['mer_remark', '']
        ]);

        $data['mer_phone'] = trim($data['mer_phone']);
        $data['mer_remark'] = trim($data['mer_remark']);

        [$orignal, $query] = $model->getRecordById($id);

        if (!$query) {
            return $this->fail(lang('no exist'));
        }

        // 保存记录
        $query->save(array_keys_filter($data, [
            'mer_phone',
            'mer_remark'
        ]));
        // 记录日志
        $this->app->make(\app\api\model\LogsModel::class)->info('logs.merchant.update', 'UPDATED', [$orignal, $data]);

        return $this->success();
    }

    /**
     * 删除
     * 
     * @param int $id
     * @return mixed
     */
    public function delete($id, MerchantModel $model)
    {
        $id = (int)$id;

        [$orignal, $query] = $model->getRecordById($id);
        if (!$query) {
            return $this->fail(lang('no exist'));
        }

        // 删除记录
        $query->with(['merchantUser'])->delete();
        // 记录日志
        $this->app->make(\app\api\model\LogsModel::class)->info('logs.merchant.delete', 'DELETE', $orignal);

        return $this->success();
    }
}
