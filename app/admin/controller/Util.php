<?php
namespace app\admin\controller;
use app\admin\Controller;
use app\common\model\system\UploadModel;
use app\common\model\system\UploadGroupModel;
/**
 * 后台管理控件
 * @package admin.controller.index
 * @version 1.0.0
 */
class Util extends Controller
{
    /**
     * 相册浏览器
     *
     * @param UploadModel $upload_model
     * @return void
     */
    public function album(UploadModel $upload_model)
    {
        if (IS_AJAX) {
            $filter = array_keys_filter($this->request->post(), [ ['album_id', 0],['kw', ''] ]);
            [$page, $limit] = $this->getPaginator();
            $data = $upload_model->getList($page, $limit, $filter);
            return $this->success($data);
        } else {
            $uploadGroupModel = app()->make(UploadGroupModel::class);
            $uploadGroup = $uploadGroupModel->getAlbumList();
            if ($uploadGroup['count'] == 0) {
                $uploadGroup = $uploadGroupModel->getDefaultGroup();
            }
            $this->assign('uploadGroup', $uploadGroup['list']);
        }
        return $this->fetch();
    }

    /**
     * 图片上传
     *
     * @param UploadImage $upload_model
     * @return void
     */
    public function uploadImage(UploadModel $upload_model)
    {
        $result = [];
        if (IS_POST) {
            $album_id = (int)$this->request->get('album_id');
            $files = $this->request->file('file');
            $result = $upload_model->uploadFile($files, $album_id);
        }
        return $this->success($result);
    }

    /**
     * 添加分组
     *
     * @param UploadGroupModel $upload_group_model
     * @return void
     */
    public function addAlbumGroup(UploadGroupModel $upload_group_model)
    {
        if (IS_POST) {
            $data = array_keys_filter($this->request->post(), [ ['group_name', ''] ]);
            if (empty($data['group_name'])) {
                return $this->fail('请输入分组名');
            }
            $data['user_id'] = S1;
            $upload_group_model->create($data);
        }

        return $this->success();
    }
}