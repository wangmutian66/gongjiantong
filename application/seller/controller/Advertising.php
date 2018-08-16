<?php
namespace app\seller\controller;

use model\ShopAd as sa;
use model\Region as r;
use model\SellerManagers as sm;
/**
 * 作者：袁中旭
 * 时间：2017-11-21
 * 功能：商户后台招聘管理
 */

class Advertising extends Base
{
    /**
     * 作者：袁中旭
     * 时间：2017-10-21
     * 功能：继承父类构造函数
     */
    protected $sa;
    protected $r;
    protected $sm;
    public function __construct()
    {
        parent::__construct();
        /*广告*/
        $this->sa = new sa();
        /*地区*/
        $this->r = new r();
        /*管理员*/
        $this->sm = new sm();
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-26
     * 功能：广告列表
     */
    public function advertisingList()
    {
        $condition = $this->request->only(["sa_status", "sa_title"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否设置了状态值*/
            if (isset($condition['sa_status']) && ('' !== $condition['sa_status'])) {
                $where['sa_status'] = ['like', "%" . $condition['sa_status'] . "%"];
                /*保存查询条件状态*/
                $pageParam['query']['sa_status'] = $condition['sa_status'];
            }
            /*是否接收到名称信息*/
            if (isset($condition['sa_title']) && ('' != $condition['sa_title'])) {
                /*模糊查询*/
                $where['sa_title'] = ['like', "%" . $condition['sa_title'] . "%"];
                $pageParam['query'][' sa_title'] = $condition['sa_title'];
            }
        }

        $where['ss_id'] = $this->sm_info['ss_id'];

        $list = $this->sa->getAll($where);
        foreach ($list['data'] as $sa_k => $sa_v) {
            $list['data'][$sa_k]['sa_start_time'] = date('Y-m-d',$sa_v['sa_start_time']);
            $list['data'][$sa_k]['sa_end_time'] = date('Y-m-d',$sa_v['sa_end_time']);
            $list['data'][$sa_k]['sa_add_time'] = date('Y-m-d',$sa_v['sa_add_time']);
            $list['data'][$sa_k]['sa_author'] = $this->sm->getOne(['sm_id' => $sa_v['sa_author']],'sm_seller_name');
        }

        return view(
            'advertisingList',
            [
                "data" => $condition, /*查询条件*/
                "list" => $list['data'], /*查询结果*/
                "page" => $list['page'], /*分页和html代码*/
                "lastPage" => $list['lastPage'], /*总页数*/
                "currentPage" => $list['currentPage'], /*当前页码*/
                "total" => $list['total'], /*总条数*/
                "listRows" => $list['listRows'], /*每页显示条数*/
            ]
        );
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-27
     * 功能：广告添加
     */
    public function advertisingAdd()
    {
        if ($this->request->isPost()) {

            /*只获取post以下参数*/
            $post_sa_info = $this->request->only(['sa_title', 'sa_link', 'sa_start_time', 'sa_end_time', 'sa_status', 'sa_position_type','sa_img_path'], "post");
            $path = "shopAd/" . date("y_m_d", time());
            $sa_img = uploadImage($path);
            $post_sa_info['sa_img_path'] = $sa_img['pic_cover'];
            $post_sa_info['sa_start_time'] = strtotime($post_sa_info['sa_start_time']);
            $post_sa_info['sa_end_time'] = strtotime($post_sa_info['sa_end_time']);
            /*验证接到的值有没有问题*/
            $rule = array(
                "sa_title" => 'require|max:20',
                "sa_link" => 'require|max:100',
                "sa_start_time" => 'require',
                "sa_end_time" => 'require',
                "sa_status" => 'require',
                "sa_position_type" => 'require', 
                "sa_img_path" => 'require',                 
            );
            $msg = array(
                "sa_title.require" => "请广告标题噢!~",
                "sa_title.require" => "广告标题不能超过20个字符噢!~",
                "sa_link.require" => "请广告链接噢!~",
                "sa_link.max" => "广告链接不能超过100个字符噢!~",
                "sa_start_time.require" => "请选择开始时间噢!~",
                "sa_end_time.require" => "请选择结束时间噢!~",
                "sa_status.require" => "请选择状态噢!~",
                "sa_position_type.require" => "请选择广告位置噢!~",
                "sa_img_path.require" => "请上传广告图片噢!~",
            );
            $data = verify($post_sa_info, $rule, $msg);


            /*code 等于1 说明成功 否则失败*/
            if ($data['code'] === 1) {
                $post_sa_info['sa_add_time'] = time();
                $post_sa_info['ss_id'] = $this->sm_info['ss_id'];
                $post_sa_info['sa_author'] = $this->sm_id;
                $sa_id = $this->sa->save($post_sa_info);
                if ($sa_id > 0) {
                    $this->sellerManagerLog("添加广告，添加id为".$sa_id);

                    $this->success('添加成功');
                }
            } else {
                /*提示失败信息*/
                $this->error($data['msg']);
            }
        } else {
            $r_value = $this->r->getList(['r_parent_id' => 1]); /*地区*/
            return view(
                "advertisingAdd",
                [
                    "r_value" => $r_value,
                ]
            );
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-27
     * 功能：广告展示
     */
    public function advertisingShow()
    {
        /*是不是ajax*/
        if ($this->request->isAjax()) {

            $info = $this->request->only(['id'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);

            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {

                $ri_show = $this->ri->getRow(['ss_id' => $this->sm_info['ss_id'],'ri_id' => intval($info['id'])]);  
                $ri_show['ui_name'] = $this->ui->getOne(['ui_id' => $ri_show['ui_id']],'ui_name');
                $ri_show['us_name'] = $this->us->getOne(['us_id' => $ri_show['us_id']],'us_name');
                $ri_show['sm_name'] = $this->sm->getOne(['sm_id' => $ri_show['ri_author']],'sm_seller_name');
                $ri_show['ri_add_time'] = date('Y-m-d H:i:s',$ri_show['ri_add_time']);
                $ri_show['ri_refresh_time'] = date('Y-m-d H:i:s',$ri_show['ri_refresh_time']);

                $ri_show['work_province'] = $this->r->getOne(['r_id' => $ri_show['ri_work_province']],'r_name');
                $ri_show['work_city'] = $this->r->getOne(['r_id' => $ri_show['ri_work_city']],'r_name');
                $ri_show['work_area'] = $this->r->getOne(['r_id' => $ri_show['ri_work_area']],'r_name');
                $ri_show['puton_city'] = $this->r->getOne(['r_id' => $ri_show['ri_puton_city']],'r_name');
                $ri_show['puton_province'] = $this->r->getOne(['r_id' => $ri_show['ri_puton_province']],'r_name');
                
                return json(format($ri_show, '1', $data['msg']));
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-27
     * 功能：广告修改
     */
    public function advertisingEdit($id)
    {
        if (isset($id) && $id > 0) {
            $where = ["sa_id" => intval($id)];
            $sa_edit = $this->sa->getRow($where);
            if ($this->request->isPost()) {
                /*只获取post以下参数*/
                $post_sa_info = $this->request->only(['sa_title', 'sa_link', 'sa_start_time', 'sa_end_time', 'sa_status', 'sa_position_type','sa_img_path'], "post");
                $path = "shopAd/" . date("y_m_d", time());
                $sa_img = uploadImage($path);
                $sa_imgs = $this->sa->getOne($where,'sa_img_path');
                if($sa_img['code'] == 200){
                    unlink($_SERVER['DOCUMENT_ROOT'].UPLOAD.'/'.$sa_imgs);
                    $post_sa_info['sa_img_path'] = $sa_img['pic_cover'];
                }else{
                    $post_sa_info['sa_img_path'] = $sa_imgs;
                }

                $post_sa_info['sa_start_time'] = strtotime($post_sa_info['sa_start_time']);
                $post_sa_info['sa_end_time'] = strtotime($post_sa_info['sa_end_time']);
                /*验证接到的值有没有问题*/
                $rule = array(
                    "sa_title" => 'require',
                    "sa_link" => 'require',
                    "sa_start_time" => 'require',
                    "sa_end_time" => 'require',
                    "sa_status" => 'require',
                    "sa_position_type" => 'require', 
                    "sa_img_path" => 'require',                 
                );
                $msg = array(
                    "sa_title.require" => "请广告标题噢!~",
                    "sa_link.require" => "请广告链接噢!~",
                    "sa_start_time.require" => "请选择开始时间噢!~",
                    "sa_end_time.require" => "请选择结束时间噢!~",
                    "sa_status.require" => "请选择状态噢!~",
                    "sa_position_type.require" => "请选择广告位置噢!~",
                    "sa_img_path.require" => "请上传广告图片噢!~",
                );
                $data = verify($post_sa_info, $rule, $msg);
                /*code 等于1 说明成功 否则失败*/
                if ($data['code'] === 1) {
                    $post_ri_info['ri_add_time'] = time();
                    $post_ri_info['ss_id'] = $this->sm_info['ss_id'];
                    $post_ri_info['ri_author'] = $this->sm_id;

                    $sa_id = $this->sa->save($post_sa_info,$where);
                    if ($sa_id > 0) {
                        $this->sellerManagerLog("修改广告，修改id为".$id);
                        $this->success('修改成功');
                    }
                } else {
                    /*提示失败信息*/
                    $this->error($data['msg']);
                }
            } else {
                $sa_edit['sa_author'] = $this->sm->getOne(['sm_id' => $sa_edit['sa_author']],'sm_seller_name');     
                return view(
                    "advertisingEdit",
                    [
                        "sa_edit"  => $sa_edit,
                    ]
                );
            }
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-27
     * 功能：广告删除
     */
    public function advertisingDel()
    {
        /*判断是不死ajax请求*/
        if ($this->request->isAjax()) {
            /*只接收id的值*/
            $info = $this->request->only(['id'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                /*where条件 强制转换整型*/
                $where['sa_id'] = intval($info['id']);
                $sa_del = $this->sa->getRow($where);

                if (isset($sa_del) && !empty($sa_del)) {
                    unlink($_SERVER['DOCUMENT_ROOT'].UPLOAD.'/'.$sa_del['sa_img_path']);
                    $sa_ret = $this->sa->del($where);
                    if (false === $sa_ret) {
                        return json(format('', '-1', '删除失败~!请稍候重试~!'));
                    } else {
                        $this->sellerManagerLog("删除广告,广告id为:".$info['id']);
                        return json(format('', '1', '删除成功~!'));
                    }
                } else {
                    return json(format('', '-1', '数据不存在哦!~'));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }
}

