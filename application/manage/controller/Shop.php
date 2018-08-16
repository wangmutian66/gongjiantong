<?php
namespace app\manage\controller;

use model\SellerCompanyBank as scb; /*商户注册*/
use model\SellerRegistered as sr; /*商户注册开户行*/
use model\SellerShop as ss; /*商户店铺*/
use model\ShopCheckInProcess as scip; /*店铺消费等级*/
use model\ShopLevel as sl; /*商户收入明细*/
use model\StoreIncomeDetails as sid;
use model\SellerManagers as sm;
use model\Users; /*总后台分类表*/
use \model\ManageGoodsCategory as mgc;
use model\ManagersPrivilegesGroup as mpg;
use model\Guarantee as gu;
use model\ShopBrandApplication as sba;
use model\Goods as g;
/**
 * [Shop] 店铺管理
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-26
 */

class Shop extends Base
{
    protected $sr;
    protected $scb;
    protected $ss;
    protected $sl;
    protected $sid;
    protected $users;
    protected $scip;
    protected $mgc;
    protected $mpg;
    protected $sm;
    protected $gu;
    protected $sba;
    protected $g;
    /**
     * 继承父类构造函数
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-26
     */
    public function __construct()
    {
        parent::__construct();
        $this->sr = new sr();
        $this->scb = new scb();
        $this->ss = new ss();
        $this->sl = new sl();
        $this->sid = new sid();
        $this->users = new Users();
        $this->scip = new scip();
        $this->mgc = new mgc();
        $this->sm = new sm();
        $this->mpg = new mpg();
        $this->gu = new gu();
        $this->sba = new sba();
        $this->g = new g();
    }
    /**
     * 入住流程
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-27
     */
    public function checkInProcessList()
    {
        $list = $this->scip->getAll();
        $this->managerLog("查看入住流程列表");
        return view(
            "checkInProcessList",
            [
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
     * 添加入住流程
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-27
     */
    public function checkInProcessAdd()
    {
        if ($this->request->isPost()) {
            $add = $this->request->only(['cip_type', 'cip_title', 'a_id', 'cip_next_title', 'cip_status', 'cip_sort'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "cip_type" => 'require|in:0,1,2',
                "cip_title" => 'require|max:30',
                "a_id" => 'number',
                "cip_next_title" => "require|max:30",
                'cip_status' => "alpha",
                'cip_sort' => 'between:1,10',
            );
            $msg = array(
                "cip_type.require" => "所属流程是必须要选择的噢!~",
                "cip_type.in" => "所属流程选择有误噢!~",
                "cip_title.require" => "流程信息标题是必须要填写的噢!~",
                "cip_title.max" => "流程信息标题最大程度不能超过30个字符噢!~",
                "a_id.number" => "文章ID必须是数字噢!~",
                "cip_next_title.require" => "下一步标题是必须要填写的噢!~",
                "cip_next_title.max" => "下一步标题最大长度不能超过30个字符噢!~",
                "cip_status.alpha" => "是否开启选择有误!~",
                "cip_sort.between" => "排序只能是1到10的数字噢!~",
            );
            $data = verify($add, $rule, $msg);
            if ($data['code'] === 1) {
                ($add['cip_status'] == 'on') ? $add['cip_status'] = 1 : $add['cip_status'] = 0;
                $id = $this->scip->save($add);
                if ($id > 0) {
                    $this->managerLog("入住流程添加成功,id为:".$id);
                    $this->success("添加成功!~", url('manage/Shop/checkInProcessList'));
                } else {
                    $this->error('添加失败!~');
                }
            } else {
                $this->error($data['msg']);
            }
            exit();
        }
        return view(
            'checkInProcessAdd'
        );
    }
    /**
     * 入住流程修改
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-27
     */
    public function checkInProcessEdit($id)
    {
        if (isset($id) && $id > 0) {
            $where = ['cip_id' => intval($id)];
            $cip_info = $this->scip->getRow($where);
            if (empty($cip_info)) {
                $this->error("该信息不存在,请联系管理员!~");
                exit();
            }
            if ($this->request->isPost()) {
                $save = $this->request->only(['cip_type', 'cip_title', 'a_id', 'cip_next_title', 'cip_status', 'cip_sort'], 'post');
                /*验证接到的值有没有问题*/
                $rule = array(
                    "cip_type" => 'require|in:0,1,2',
                    "cip_title" => 'require|max:30',
                    "a_id" => 'number',
                    "cip_next_title" => "require|max:30",
                    'cip_status' => "alpha",
                    'cip_sort' => 'between:1,10',
                );
                $msg = array(
                    "cip_type.require" => "所属流程是必须要选择的噢!~",
                    "cip_type.in" => "所属流程选择有误噢!~",
                    "cip_title.require" => "流程信息标题是必须要填写的噢!~",
                    "cip_title.max" => "流程信息标题最大程度不能超过30个字符噢!~",
                    "a_id.number" => "文章ID必须是数字噢!~",
                    "cip_next_title.require" => "下一步标题是必须要填写的噢!~",
                    "cip_next_title.max" => "下一步标题最大长度不能超过30个字符噢!~",
                    "cip_status.alpha" => "是否开启选择有误!~",
                    "cip_sort.between" => "排序只能是1到10的数字噢!~",
                );
                $data = verify($save, $rule, $msg);
                if ($data['code'] === 1) {
                    ($save['cip_status'] == 'on') ? $save['cip_status'] = 1 : $save['cip_status'] = 0;
                    $ret = $this->scip->save($save, $where);
                    if (false !== $ret) {
                        $this->managerLog("入住流程修改成功!ID为:".$id);
                        $this->success("修改成功!~", url('manage/Shop/checkInProcessList'));
                    } else {
                        $this->error('修改失败!~');
                    }
                } else {
                    $this->error($data['msg']);
                }
                exit();
            }
            return view(
                'checkInProcessEdit',
                [
                    'info' => $cip_info,
                ]
            );
        } else {
            $this->error("程序员都累吐血了,也没有接收到任何数据!~");
        }
    }
    /**
     * 入住流程删除
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-27
     */
    /**
     *public function checkInProcessDel()
     *{
     *if ($this->request->isAjax()) {
     *$info = $this->request->only(['id'], 'post');
     *$rule = array(
     *"id" => 'require',
     *);
     *$msg = array(
     *"id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
     *);
     *$data = verify($info, $rule, $msg);
     *if ($data['code'] === 1) {
     *$where['cip_id'] = intval($info['id']);
     *$cip_id = $this->scip->getOne($where, "cip_id");
     *if ($cip_id < 0) {
     *return json(format('', '-1', '删除失败~!该信息不存在噢~!'));
     *} else {
     *$ret = $this->scip->del($where);
     *if (false === $ret) {
     *return json(format('', '-1', '删除失败~!请稍候重试~!'));
     *} else {
     *return json(format('', '1', '删除成功~!'));
     *}
     *}
     *} else {
     *return json(format('', '-1', $data['msg']));
     *}
     *}
     *}
     */

    /**
     * 店铺列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-27
     */
    public function shopList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["ss_approval_status", "ss_name"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $mpg_ids=session("m_info");
        $mpg_id=$mpg_ids['mpg_id'];
        if ($mpg_id !='' and $mpg_id !='0') {
            $mgc_lists = $this->mpg->getRow(['mpg_id' => $mpg_id]);
            $where = ['ss.mgc_id' => $mgc_lists['mpg_class']];

        }else{
            $where = [];

        }

        // dump($where);exit();

        if (is_array($condition)) {
            /*是否设置了状态值*/
            if (isset($condition['ss_approval_status']) && ('' !== $condition['ss_approval_status'])) {
                $where['ss_approval_status'] = $condition['ss_approval_status'];
                /*保存查询条件状态*/
                $pageParam['query']['ss_approval_status'] = $condition['ss_approval_status'];
            }
            /*是否接收到名称信息*/
            if (isset($condition['ss_name']) && ('' != $condition['ss_name'])) {
                /*模糊查询*/
                $where ['ss_name|ss_id'] = ['like', "%" . $condition['ss_name'] . "%"];


                $pageParam['query']['ss_name'] = $condition['ss_name'];
            }
        }

        $join = [
            ["gjt_manage_goods_category mgc", "mgc.mgc_id = ss.mgc_id"],
            ["gjt_seller_registered sr", "sr.sr_id = ss.sr_id"],
        ];
        $alias = "ss";
        $field = "ss.*,mgc.mgc_name,sr.sr_reason";

        $list = $this->ss->joinGetAll($join, $alias, $where, $pageParam, [], 0, $field,"");


        $this->managerLog("浏览店铺列表");


        return view(
            "shopList",
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
     * 修改店铺信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-27
     */
    public function shopEdit($id)
    {
        if (isset($id) && $id > 0) {
            $where = ["ss_id" => intval($id)];
            $join = [
                ["gjt_seller_registered sr", "sr.sr_id = ss.sr_id","left"],
                ["gjt_manage_goods_category mgc", "mgc.mgc_id = ss.mgc_id","left"],
                ["gjt_users u", "u.u_id = sr.u_id","left"],
                ['gjt_seller_company_bank scb', "scb.sr_id = sr.sr_id","left"],

            ];
            $alias = "ss";
            $field = "ss.*,sr.*,u.u_name,mgc.*,scb.*";
            $info = $this->ss->joinGetRow($join, $alias, $where, $field);
            $info['sr_operating_period'] = explode(",", $info['sr_operating_period']);
            $mgc_list = $this->mgc->getList(['mgc_parent_id' => 0]);

            $settledIn = $this->ss->getRow($where);
            $second_mgc_list = $this->mgc->getList(['mgc_parent_id' =>["in",$settledIn['mgc_id']]]);

            $second_mgc_list_news=array();
            $ss_mgc_ids = explode(",", $info['ss_mgc_ids']);
            foreach ($second_mgc_list as $key => $value) {
                foreach ($ss_mgc_ids as $k => $v) {
                    if ($value['mgc_id'] == $v) {
                        $second_mgc_list[$key]['is_checked'] = 1;
                    }
                }
                $second_mgc_list_news[$value['mgc_parent_id']][$key] = $second_mgc_list[$key];
            }



            if ($this->request->isPost()) {
                $post = $this->request->only([ "ss_goods_verify", "ss_approval_status", "ss_reconsideration", "sr_reason"], "post");
                //dump($post["ss_shop_province"]); exit();
                $rule = array(
//                    "ss_name" => 'require|chsAlphaNum',
//                    "ss_shop_province" => 'require|number',
//                    "ss_shop_city" => "require|number",
//                    'ss_shop_area' => "require|number",
//                    'ss_shop_address' => "chsAlphaNum",
//                    'ss_sort' => "require|between:1,100",
                    'ss_goods_verify' => "require|in:0,1",
                    'ss_approval_status' => "require|in:0,1,2",
                    'ss_reconsideration' => "require|in:0,1",
                );
                $msg = array(
//                    "ss_name.require" => "商户名称是必须要填写的哦!~",
//                    "ss_name.chsAlphaNum" => "商户名称只允许填写字母,汉字,数字哦!~",
//                    "ss_shop_province.require" => "省份是必须要选择的哦!~",
//                    "ss_shop_province.number" => "省份选择有误!~",
//                    "ss_shop_city.require" => "城市是必须要选择的哦!~",
//                    "ss_shop_city.number" => "城市选择有误!~",
//                    'ss_shop_area.require' => "地区是必须选择的哦!~",
//                    'ss_shop_area.number' => "地区选择有误",
//                    'ss_shop_address.chsAlphaNum' => "地址只允许填写字母,汉字,数字哦!~",
//                    'ss_sort.require' => "排序是必须要填写的哦!~",
//                    'ss_sort.between' => "排序只能填写1-100的数字哦!~",
                    'ss_goods_verify.require' => "审核商品必须选择!~",
                    'ss_goods_verify.in' => "审核商品选择有误!~",
                    'ss_approval_status.require' => "店铺审核必须选择!~",
                    'ss_approval_status.in' => "店铺审核选择有误!~",
                    'ss_reconsideration.require' => "是否允许店铺重新审核必须选择!~",
                    'ss_reconsideration.in' => "是否允许店铺重新审核选择有误!~",
                );
                $data = verify($post, $rule, $msg);
                if ($data['code'] === 1) {
                    $AppKey = config("plugin")['sms']['AppKey'];
                    $AppSecret = config("plugin")['sms']['AppSecret'];
                    $sms = new \sms\ServerAPI($AppKey, $AppSecret, 'curl');
                    $user_info = $this->users->getRow(['u_id' =>$info['u_id']]);
                    if ($post['ss_approval_status'] == 1) { //审核通过

                        if (!empty($user_info)) {
                            $sm_info = $this->sm->getRow(["sm_seller_name" => $user_info['u_mobile']]);
                            if (empty($sm_info)) {
                                $sm_save['sm_seller_name'] = $user_info['u_mobile'];
                                $sm_save['sm_seller_passwd'] = md5("123456"); //$user_info['u_passwd']
                                $sm_save['sr_id'] = $info['sr_id'];
                                $sm_save['ss_id'] = $info['ss_id'];
                                $sm_save['sm_status'] = 9;
                                $sm_save['sm_last_time'] = $sm_save['sm_registered_time'] = time();
                                $sm_save['sm_last_ip'] = $sm_save['sm_registered_ip'] = getIp();
                                $id = $this->sm->save($sm_save);
                                //发送短信提示审核通过
                                $sms->sendSMSTemplate("3872837", [$user_info["u_mobile"]], []);

                            }
                        }else{
                            $this->error("注册店铺的用户不存在!~");
                        }
                        $post['ss_confirm_time'] = time();
                    }elseif ($post['ss_approval_status'] == 2) { //审核未通过
                        if(isset($post['sr_reason']) && empty($post['sr_reason'])){
                            $this->error("审核不通过原因必须要填写");
                        }else{
                            $this->sr->save(["sr_reason" => $post['sr_reason']],["sr_id" => $info['sr_id']]);
                            //发送短信提示审核未通过
                            $sms->sendSMSTemplate("3872838", [$user_info["u_mobile"]], []);
                        }
                    }
                    unset($post['sr_reason']);
                    $ret = $this->ss->save($post,$where);
                    if (false !== $ret) {
                        $this->managerLog("修改店铺信息,店铺id为:".$id);
                        $this->success("修改成功!~",url("manage/Shop/shopList"));
                    }else{
                        $this->error("修改失败!~");
                    }
                } else {
                    $this->error($data['msg']);
                }
            }

            return view(
                "shopEdit",
                [
                    "info" => $info,
                    'mgc_id_array'=>explode(",",$settledIn["mgc_id"]),
                    "mgc_list" => $mgc_list,
                    "second_mgc_list" => $second_mgc_list_news,
                ]
            );
        } else {
            $this->error("程序员都累吐血了,也没有接收到任何数据!~");
        }
    }
    /**
     * 锁定店铺
     * @author 李鑫
     * @e-mail 83795552@qq.com
     * @date   2018-03-23
     */
    public function suoding(){

        /*是不是ajax*/
        if ($this->request->isAjax()) {

            $info = $this->request->only(['id','frozen'], 'post');
            $this->ss->save(["state" => '1',"frozen" => $info['frozen']],["ss_id" => $info['id']]);
            // dump($this->ss->getlastsql());
              return json(format('', '1', '冻结成功！'));
            
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 解锁店铺
     * @author 李鑫
     * @e-mail 83795552@qq.com
     * @date   2018-03-23
     */
    public function jiesuo(){

        /*是不是ajax*/
        if ($this->request->isAjax()) {

            $info = $this->request->only(['id'], 'post');
            $this->ss->save(["state" => '0'],["ss_id" => $info['id']]);
              return json(format('', '1', '解冻成功！'));
            
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 删除店铺信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-27
     */
    public function shopDel()
    {

    }
    /**
     * 店铺开户行
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-11
     */
    public function shopBank()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["scb_company_name"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否接收到名称信息*/
            if (isset($condition['scb_company_name']) && ('' != $condition['scb_company_name'])) {
                /*模糊查询*/
                $where['scb_company_name'] = ['like', "%" . $condition['scb_company_name'] . "%"];
                $pageParam['query']['scb_company_name'] = $condition['scb_company_name'];
            }
        }
        $join = [
            ["gjt_region r", "r.r_id = scb.scb_bank_province"],
            ["gjt_region rn", "rn.r_id = scb.scb_bank_city"],
        ];
        $alias = "scb";
        $field = "scb.*,r.r_name province,rn.r_name city";
        $list = $this->scb->joinGetAll($join, $alias, $where, $pageParam, [], 0, $field);
        $this->managerLog("浏览店铺开户行列表");
        return view(
            "shopBank",
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
     * 店铺类目审核
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-27
     */
    public function shopCategoryAudit()
    {

    }


    /**
     * [前台显示新的申请入驻信息]
     * @author 王牧田
     * @date 2018-05-26
     * @return \think\response\Json
     */
    public function registeredShowMessage(){
        $ss = $this->sr->getRow(["sr_ismsg"=>"1"]);

        if(empty($ss)){
            return json(format($ss, 201, ""));
        }else{
            return json(format($ss, 200, "success"));
        }
    }


    public function updateRegisteredShowMessage(){
        $condition = $this->request->only(["sr_id"], "post");
        $ss = $this->sr->save(["sr_ismsg"=>"0"],["sr_id"=>$condition["sr_id"]]);
        if(empty($ss)){
            return json(format($ss, 201, ""));
        }else{
            return json(format($ss, 200, "success"));
        }
    }

    /**
     * [前台显示审核提交]
     * @author 王牧田
     * @date 2018-05-26
     */
    public function shopShowMessage(){
        /*查看是否有申请入驻信息*/
        $sr = $this->sr->getRow(["sr_ismsg"=>"1"],"sr_id"); //["sr_ismsg"=>"1"]
        /*查看是否有入驻审核信息*/
        $ss = $this->ss->getRow(["ismsg"=>"1"],"ss_id");
        /*查看是否有品牌审核信息*/
        $sba = $this->sba->getRow(["sba_status"=>0,"sba_ismsg" => "1"],"sba_id");
        /*查看是否有商品审核信息*/
        $g = $this->g->getRow(["g_goods_verify"=>0,"g_ismsg" => "1"],"g_id");



        if(!empty($sr)){
            return json(format($sr, 200, "有新的申请入驻信息需要审核请注意查看"));
        }else if(!empty($ss)){
            return json(format($ss, 200, "有新的入驻信息需要审核请注意查看"));
        }else if(!empty($sba)){
            return json(format($sba, 200, "有新的品牌需要审核请注意查看"));
        }else if(!empty($g)){
            return json(format($g, 200, "有新的商品需要审核请注意查看"));
        }else{
            return json(format("", 201, ""));
        }
    }


    /**
     * [查看到有消息改变后不再提示]
     * @author 王牧田
     * @date 2018-05-26
     */
    public function updateShopShowMessage(){
        /*接收到的数据*/
        $condition = $this->request->only(["key_id","key_filed"], "post");
        $table = null;
        $ismsg = "";
        switch ($condition["key_filed"]){
            case 'sr_id':
                $table = $this->sr;
                $ismsg = "sr_ismsg";
                break;
            case 'ss_id':
                $table = $this->ss;
                $ismsg = "ismsg";
                break;
            case 'sba_id':
                $table = $this->sba;
                $ismsg = "sba_ismsg";
                break;
            case 'g_id':
                $table = $this->g;
                $ismsg = "g_ismsg";
                break;

        }

        if(empty($table)){
            return json(format("", 201, ""));
        }

        $data = $table->save([$ismsg=>0],[$condition["key_filed"]=>$condition["key_id"]]);
        if(empty($data)){
            return json(format($data, 201, ""));
        }else{
            return json(format($data, 200, "success"));
        }
    }


    /**
     * 店铺保障列表
     * @author 李鑫
     * @date   2018-04-24
     */
    public function guaranteeList()
    {
       
        $gu_value=$this->gu->getList();
        // dump($gu_value);die();


        return view(
            "guaranteeList",
            [
                "gu_value" => $gu_value, /*查询条件*/
              
            ]
        );
    }

    /**
     * 店铺保障添加
     * @author 李鑫
     * @date   2018-04-24
     */
    public function guaranteeAdd()
    {
       if ($this->request->isPost()) {
            $post_g_info = $this->request->only(['gu_name','content'], "post");
            if ($this->gu->save($post_g_info)) {
                $this->success("添加成功", url("manage/Shop/guaranteeList"));
            }else{
                 $this->success("添加失败");
            }
       }
        return view(
            "guaranteeAdd",
            [
            ]
        );
    }

    /**
     * 店铺保障修改
     * @author 李鑫
     * @date   2018-04-24
     */
    public function guaranteeEdit($id)
    {
        if ($this->request->isPost()) {
            $post_g_info = $this->request->only(['gu_name','content'], "post");
            $rule = array(
                "gu_name" => 'require|max:20',
            );
            $msg = array(
                "gu_name.require" => '请填写保障名称噢!~',
                "gu_name.max" => '保障名称不能超过20个字符噢!~',
            );
            $data = verify($post_g_info, $rule, $msg);
            $where['gu_id']=$id;

            if ($data['code'] === 1) {
                if ($this->gu->save($post_g_info,$where)) {
                    $this->success("修改成功", url("manage/Shop/guaranteeList"));
                }else{
                     $this->success("修改失败");
                }
            }
        }
        $gu_value=$this->gu->getRow(['gu_id'=>$id]);
        return view(
            "guaranteeEdit",
            [
             "gu_value" => $gu_value, /*查询条件*/
            ]
        );
    }

    /**
     * 店铺保障删除
     * @author 李鑫
     * @date   2018-04-24
     */
    public function guaranteeDel($id)
    {
        if (!empty($id)) {
            $where['gu_id'] = intval($id);
            if ($this->gu->del($where)) {
                $this->success("删除成功", url("manage/Shop/guaranteeList"));
            }else{
                $this->success("删除失败");
            }
        }else{
            $this->success("删除失败");
        }
        
    }

    /**
     * 店铺文章申请
     * @author 王牧田
     * @date   2018-04-24
     */
    public function articlereview(){
        if ($this->request->isPost()) {
            $post_g_info = $this->request->only(['id',"ss_is_areview"], "post");

            $ss = $this->ss->save(["ss_is_areview"=>$post_g_info["ss_is_areview"]],["ss_id"=>$post_g_info["id"]]);
            if(empty($ss)){
                return json(format($ss, 201, ""));
            }else{
                return json(format($ss, 200, "success"));
            }
        }
    }

}
