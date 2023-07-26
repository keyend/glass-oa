<?php
/**
 * 帮助中心
 * @version 1.0.0
 */
namespace app\api\controller;
use app\api\Controller;
use app\common\model\crebo\Helps;
use app\common\model\crebo\HelpsKeywords;
use app\common\model\crebo\HelpsLog;

class Help extends Controller
{
    /**
     * 返回请求的分页信息
     * @return Array
     */
    protected function getPaginator() 
    {
        return array_values(array_keys_filter($this->request->param(), [['page', 1], ['limit', 10]]));
    }

    /**
     * 首页列表
     *
     * @param Helps $help_model
     * @return void
     */
    public function index(Helps $help_model)
    {
        $filter = array_keys_filter($this->request->param(), [ ['parent_id', 0] ]);
        $filter["parent_id"] = (int)$filter["parent_id"];
        $filter["type"] = 0;
        [$page, $limit] = $this->getPaginator();
        if ($filter["parent_id"] !== 0) {
            $res = $help_model->getList($page, $limit, $filter, "help_id,title,create_time,update_time");
        } else {
            $res = $help_model->where("type", $filter["type"])->where("parent_id", $filter["parent_id"])->field("help_id,title")->order("update_time DESC")->select();
            foreach($res as &$row) {
                $children = $help_model->getList($page, $limit, ["parent_id" => $row["help_id"]], "help_id,title,create_time,update_time");
                $row["children"] = $children["list"];
            }
        }

        return $this->success($res);
    }

    /**
     * 内容明细
     *
     * @param Helps $help_model
     * @return void
     */
    public function detail(Helps $help_model)
    {
        $filter = array_keys_filter($this->request->param(), [ ['id', 0] ]);
        $filter["id"] = (int)$filter["id"];
        if ($filter["id"] == 0) {
            return $this->fail("INVALD_PARAM");
        }

        $data = $help_model->find($filter["id"]);
        if (empty($data)) {
            return $this->fail("内容不存在");
        }

        return $this->success($data);
    }

    /**
     * 常见问题
     *
     * @param Helps $help_model
     * @param HelpsKeywords $help_keywrods
     * @return void
     */
    public function question(Helps $help_model, HelpsKeywords $help_keywrods)
    {
        $filter = array_keys_filter($this->request->param(), [ ['kw', ""] ]);
        if ($filter["kw"] === "") {
            $filter = [];
            $filter["type"] = 1;
            $filter["is_recomment"] = 1;
            [$page, $limit] = $this->getPaginator();
            $data = $help_model->getList($page, $limit, $filter, "help_id,title,create_time,update_time");
            $res = $data["list"];
        } else {
            $help_id = $help_keywrods->where("keyword", "LIKE", "%{$filter['kw']}%")->value("help_id");
            if (!empty($help_id)) {
                $res = $help_model->find($help_id);
            } else {
                $res = $help_model->where("title", 'LIke', "%{$filter['kw']}%")->find();
                if ($res) {
                    return $this->fail("NOT_FOUND");
                }
            }
        }

        return $this->success($res);
    }

    /**
     * 用户问题反馈
     * 
     * @param HelpsLog $help_log_model
     * @return void
     */
    public function feedback(HelpsLog $help_log_model)
    {
        try {
            $validate = $this->validate($this->params([ 'content' ], true), [ 'trade_no|订单号' => 'require|min:6' ]);
            if ($validate !== false) {
                return $this->fail($validate);
            }

            $params = $this->params([ "content" ], true);
            $params["content"] = strip_tags($params["content"], "<table><tr><td><span><div><style><hr><a><strong><b><i><ul><li><dl><dd><ol><img>");
            $feedback = [];
            $feedback["id"] = $help_log_model->insertGetId([
                "uid" => S1,
                "token" => S0,
                "content" => $params["content"],
                "create_time" => TIMESTAMP
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }

        return $this->success([
            "id" => $feedback['id']
        ]);
    }
}