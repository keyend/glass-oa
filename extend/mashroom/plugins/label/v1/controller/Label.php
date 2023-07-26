<?php
namespace app\api\controller\admin;
/**
 * 联系方式
 * 
 * @date: 2021-05-10 20:19:31
 * @author: k.
 * @version 1.0.1
 * @package easyzf
 */
use app\api\Controller;
use app\api\model\LabelModel;

class Label extends Controller
{
    /**
     * 返回列表
     * 
     * @return mixed
     */
    public function lst(LabelModel $model)
    {
        [$page, $limit] = $this->getPaginator();
        return $this->success($model->getList($page, $limit, true));
    }

    /**
     * 获取表单
     * 
     * @return mixed
     */
    public function form(LabelModel $model)
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
    public function create(LabelModel $model)
    {
        // 上传分类图
        if ($this->request->post('X-ACTION-STATE')) {
            $upload = $this->app->make(\app\common\model\AttachmentModel::class)->uploadImage($this->request->file('file'), $this->request->post('X-ACTION-STATE'), S1);
            return $this->success($upload);
        }

        $data = array_keys_filter($this->request->post(), [
            'label',
            ['label_title', ''],
            ['content', '']
        ]);

        $data['content'] = $model->filterContent($data['content']);
        $data['create_time'] = TIMESTAMP;

        if (empty($data['label'])) {
            $data['label'] = uniqid();
        }

        if ($model->where('label', $data['label'])->find()) {
            return $this->fail(lang('exist record'));
        }

        // 添加记录
        $data['label_id'] = $model->insertGetId($data);
        // 记录日志
        $this->app->make(\app\api\model\LogsModel::class)->info('logs.label.create', 'CREATEED', $data);
        // 写入缓存
        fileCache("label.{$data['label']}", $data['content'], true);

        return $this->success($data);
    }

    /**
     * 更新记录
     * 
     * @return mixed
     */
    public function update(int $id, LabelModel $model)
    {
        // 上传分类图
        if ($this->request->post('X-ACTION-STATE')) {
            $upload = $this->app->make(\app\common\model\AttachmentModel::class)->uploadImage($this->request->file('file'), $this->request->post('X-ACTION-STATE'), S1);
            return $this->success($upload);
        }

        $data = array_keys_filter($this->request->post(), [
            'label',
            ['label_title', ''],
            ['content', '']
        ]);

        $data['content'] = $model->filterContent($data['content']);
        $data['update_time'] = TIMESTAMP;

        [$orignal, $query] = $model->getRecordById($id);

        if (!$query) {
            return $this->fail(lang('no exist'));
        }

        // 保存记录
        $query->save($data);
        // 记录日志
        $this->app->make(\app\api\model\LogsModel::class)->info('logs.label.update', 'UPDATED', [$orignal, $data]);
        // 写入缓存
        fileCache("label.{$data['label']}", $data['content'], true);

        return $this->success();
    }

    /**
     * 删除
     * @param int $id
     * @return mixed
     */
    public function delete($id, LabelModel $model)
    {
        $id = (int)$id;

        [$orignal, $query] = $model->getRecordById($id);
        if (!$query) {
            return $this->fail(lang('no exist'));
        }

        // 删除记录
        $query->delete();
        // 记录日志
        $this->app->make(\app\api\model\LogsModel::class)->info('logs.label.delete', 'DELETE', $orignal);

        return $this->success();
    }
}
