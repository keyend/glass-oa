<?php
namespace app\admin\controller;
use app\admin\Controller;
use app\common\model\crebo\Helps;
use app\common\model\crebo\HelpsLog;
/**
 * 帮助中心
 * @package admin.controller.tipoff
 * @version 1.0.0
 */
class Help extends Controller
{
    /**
     * 返回记录列表
     *
     * @param Helps $help_model
     * @return void
     */
    public function getList(Helps $help_model)
    {
        if ($this->request->isAjax()) {
            $filter = array_keys_filter($this->request->param(), [
                ['search_type', ""],
                ["search_value", ""]
            ]);
            [$page, $limit] = $this->getPaginator();
            $data = $help_model->getList($page, $limit, $filter);
            return $this->success($data);
        } else {
            $this->assign("status_id", $id);
            return $this->fetch('index');
        }
    }

    /**
     * 帮助中心首页
     *
     * @param Helps $help_model
     * @return void
     */
    public function index(Helps $help_model)
    {
        return $this->fetch();
    }

    /**
     * 添加内容
     * 
     * @param Helps $help_model
     * @return void
     */
    public function add(Helps $help_model)
    {
        if($this->request->isPost()){
            $data = array_keys_filter(input("post."), [
                "title",
                ["type", 0],
                ["parent_id", 0],
                ["keywords", ""],
                ["content", ""]
            ]);
			$validate = $this->validate($data, [ 'title|内容标题' => 'require|length:4,128' ]);
            if ($validate !== true) {
                return $this->fail($validate);
            }
            $help = $help_model->addContent($data);
            $this->logger('logs.help.add', 'CREATEED', $help);
            return $this->success();
        }
        $default = [ "keywords" => [] ];
        $parentList = $help_model->where("parent_id", 0)->where("type", 0)->select();
        $this->assign("e", $default);
        $this->assign("parentList", $parentList);
        return $this->fetch('form');
    }

    /**
     * 编辑内容
     * 
     * @param Helps $help_model
     * @return void
     */
    public function edit(Helps $help_model)
    {
        $id = (int)input('get.id');
		$info = $help_model->with(['keywords'])->find($id);
		if (empty($info)) {
			$this->error('内容数据不存在');
		}

        if($this->request->isPost()) {
            $data = array_keys_filter(input("post."), [
                ["parent_id", 0],
                ["keywords", ""],
                ["content", ""]
            ]);
            $info->updateContent($data);
            $this->logger('logs.help.edit', 'UPDATED', $info);
            return $this->success();
        }

        $this->assign("e", $info);
        return $this->fetch('form');
    }

    /**
     * 删除内容
     *
     * @param Helps $help_model)
     * @return void
     */
    public function delete(Helps $help_model)
    {
        if (IS_AJAX) {
            $ids = $this->request->post('id');
            if (!is_array($ids)) {
                $ids = [ intval($ids) ];
            }

            $helps = $help_model->with(['keywords'])->where([["help_id", "IN", $ids]])->select();
            if (!empty($helps)) {
                foreach($helps as $help) {
                    $this->logger('logs.member.delete', 'DELETE', $help);
                    $help->together(['keywords'])->delete();
                }
            }
        }
        return $this->success();
    }

    /**
     * 反馈列表
     *
     * @return void
     */
    public function getFeedbackList($id, HelpsLog $help_log_model)
    {
        if ($this->request->isAjax()) {
            $filter = array_keys_filter($this->request->param(), [
                ['search_type', ""],
                ["search_value", ""]
            ]);
            if ($id != 'all') {
                $filter["status"] = $id;
            }
            [$page, $limit] = $this->getPaginator();
            $data = $help_log_model->getList($page, $limit, $filter);
            return $this->success($data);
        } else {
            $this->assign("status_id", $id);
            return $this->fetch('feedback');
        }
    }

    /**
     * 用户反馈
     *
     * @return void
     */
    public function feedback()
    {
        $this->assign("status_id", "all");
        return $this->fetch();
    }

    /**
     * 反馈明细
     *
     * @param HelpsLog $help_log_model
     * @return void
     */
    public function getFeedbackDetail(HelpsLog $help_log_model)
    {
        $id = input('get.id');
        $info = $help_log_model->with(['member'])->find($id);
        if(empty($info)){
            $this->fail('记录不存在');
        }

        if($this->request->isPost()) {
            $data = array_keys_filter(input("post."), [ ["reply", ""] ]);
            $data["id"] = $info->id;
            $data["reply"] = strip_tags($data["reply"], "<table><tr><td><span><div><style><hr><a><strong><b><i><ul><li><dl><dd><ol><img>");
            $data["status"] = 1;
            $data["reply_time"] = TIMESTAMP;
            $info->update($data);
            $this->logger('logs.feedback.edit', 'UPDATED', $info);
            return $this->success();
        }

        $this->assign('info',$info);
        return $this->fetch('feedback_detail');
    }
}