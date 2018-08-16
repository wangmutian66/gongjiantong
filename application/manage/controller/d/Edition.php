<?php
namespace app\manage\controller;


use model\Edition as e;
/**
 * [manager] 用户管理users
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-29
 */
class Edition extends Base
{
    protected $e;
   

    public function __construct()
    {
        parent::__construct();
        /*用户基本信息表*/
        $this->e = new e();
        /*历史浏览表*/
      
    }
    /**
     * 版本号
     * @author 李鑫
     * @date   2018-05-12
     */
    public function editionList()
    {
        $where = ["ed_id" => '1'];
        $list = $this->e->getRow($where);
        $this->managerLog("查看版本号");
        return view(
            "usersList",
            [
                "list" => $list, /*查询条件*/
              
            ]
        );
    }
    /**
     * 修改版本号
     * @author 李鑫
     * @date   2018-05-12
     */
    public function editionEdit()
    {
        if ($this->request->isPost()) {
            /*只获取post以下参数*/
            $post_ss_info = $this->request->only(['ed_name','ed_code','ed_about','ed_protocol'], "post");
            $id = $this->e->save($post_ss_info, ['ed_id' => '1']);
            if ($id > 0) {
                $this->success("修改成功");
            } else {
                $this->error("修改失败");
            }
        }
    }
}
