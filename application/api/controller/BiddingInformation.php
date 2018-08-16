<?php
namespace app\api\controller;

use model\BiddingInformation as bi;
use model\SellerShop as ss;
use model\EngineeringSpecifications as es;
use model\PayLog as pl;
use model\RecruitmentInfo as ri;
use model\TenderSpecificationOrder as tso;
use model\UserBrowsingHistory as ubh;
use model\UserJobSearch as ujs;
use model\UserSkills as us; /*地区表*/
use model\SellerManagers as sm;
use model\ManageAd as ma;
use model\UserIndustry as ui;
use model\UserCollectionInformation as uci;
/**
 * 招投标信息
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-10-09
 */
class BiddingInformation extends Base
{
    protected $bi;
    protected $ss;
    protected $tso;
    protected $pl;
    protected $ri;
    protected $uci;
    protected $ujs;
    protected $ubh;
    protected $es;
    protected $us;
    protected $sm;
    protected $ma;
    protected $ui;
    public function __construct()
    {   
        parent::__construct();
        $this->bi = new bi(); /*招投标信息*/
        $this->tso = new tso(); /*招标和规范订单表*/
        $this->pl = new pl(); /*支付日志*/
        $this->ri = new ri(); /*招聘信息*/
        $this->ujs = new ujs(); /*简历投递表*/
        $this->ubh = new ubh(); /*历史浏览记录表*/
        $this->es = new es(); /*规范*/
        $this->us = new us(); /*技能*/
        $this->sm = new sm(); /*技能*/
        $this->ss = new ss();
        $this->ma = new ma();
        $this->ui = new ui();
        $this->uci= new uci();
    }
    /**
     * 获取招投标信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-21
     */
    public function biddingInformationList()
    {
        /*招投标区分 招标 公告 中标*/
        // $this->param['province_id']="12";
        // $this->param["expire"]='1';
//        $this->param['page']=1;
        (isset($this->param["bi_type"]) && ($this->param["bi_type"] != '')) ? $condition["bi_type"] = $this->param["bi_type"] : false;
        /*展示的省份*/
        (isset($this->param["province_id"]) && ($this->param["province_id"] != '')) ? $condition["r_id"] = $this->param["province_id"] : false;
        /*搜索的标题*/
        (isset($this->param["bi_title"]) && ($this->param["bi_title"] != '')) ? $condition["bi_title"] = $this->param["bi_title"] : false;
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where["bi_is_show"] = '1';
        if($this->param['province_id']==''){
            $condition["r_id"]='1';
        }
          (isset($this->param["expire"]) && ($this->param["expire"] != '')) ? $where["expire"] = $this->param["expire"] : false;
        if (isset($condition) && is_array($condition)) {
            if (isset($condition['bi_title']) && ('' != $condition['bi_title'])) {
                /*模糊查询*/
                $where['bi_title'] = ['like', "%" . $condition['bi_title'] . "%"];
                $pageParam['query']['bi_title'] = $condition['bi_title'];
            }
            /*地区(省)*/
            if (isset($condition['r_id']) && ('' != $condition['r_id']) && $condition['r_id'] > 1) {
                    $where['r_id'] = ['in', $condition['r_id']];
                // $where['r_id'] = ['in', '1,' . $condition['r_id']];
                $pageParam['query']['r_id'] = $condition['r_id'];
            }
            /*类型*/
            if (isset($condition['bi_type']) && ('' != $condition['bi_type'])) {
                $where['bi_type'] = $condition['bi_type'];
                $pageParam['query']['bi_type'] = $condition['bi_type'];
            }
        }

        $field = "bi_id,bi_title,bi_add_time,bi_winning_bid,bi_end_time,bi_desc,expire,r_id,bi_status";
        $pageParam['page'] = $this->param['page'];
        $order = "bi_id desc";
        $list = $this->bi->getAll($where, $pageParam, $order, 10, $field);
        foreach ($list["data"] as $key => $value) {
            $list["data"][$key]['r_name'] =dizhi($value['r_id']);
        }
        // dump($where);
        // dump($list);die();



        return json(format($list));
    }
    /**
     * 招投标信息展示
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-24
     */
    public function biddingInformationInfo()
    {
        /*获取单条信息的id*/
        $bi_where = ["bi_id" => $this->param['bi_id']];
        $rule = [
            "bi_id" => 'require|number',
        ];
        $msg = [
            "bi_id.require" => '缺少必要参数!~',
            "bi_id.number" => '必要参数传输有误,请重试!~',
        ];
        $data = verify($bi_where, $rule, $msg);
        if ($data['code'] === 1) {
            /*如果有用户id,添加用户历史浏览记录*/
            if ($this->u_id > 0) {
                $ubh_where['u_id'] = $save['u_id'] = $this->u_id;
                $ubh_where['ubh_type'] = $save['ubh_type'] = "2";
                $ubh_where['ubh_browsing_id'] = $save['ubh_browsing_id'] = $this->param['bi_id'];
                $count = $this->ubh->getCount($save);
                if($count < 1) {
                    $save['ubh_time'] = time();
                    $this->ubh->save($save);
                }else {
                    $save['ubh_time'] = time();
                    $this->ubh->save($save,$ubh_where);
                }
            }
            /*获取招投标信息*/
            $bi_info = $this->bi->getRow($bi_where);
            if ($bi_info != '') {
                /*如果要是招标信息,需要做处理*/
                // if ($bi_info['bi_type'] == '0') {
                // /*如果需要支付需要进一步处理,查询该用户的支付信息*/
                // if ($bi_info['bi_is_pay'] == '1') {
                // $pl_where['pay_goods_id'] = $this->param['bi_id'];
                // $pl_where['order_type'] = '0';
                // $pl_where['u_id'] = $this->u_id;
                // $count = $this->pl->getCount($pl_where);
                // if ($count > 0) {
                // return json(format($bi_info));
                // }else{
                // return json(format('', 244, "该招标信息还没支付,请先支付!~"));
                // }
                // }else{
                // return json(format($bi_info));
                // }
                // }else{
                $bi_info['bi_contents'] = preg_replace('/(<img.+?src=")(.*?)/', '$1' . HTTP_HOST . '$2', $bi_info['bi_contents']);
                $user_collection_list = $this->uci->getRow(['type' => '3',"u_id" => $this->u_id,"uci_collection_id" => $this->param['bi_id']]);
                if ($user_collection_list) {
                    $bi_info['is_collection'] = 1;/*收藏过*/
                }else{
                    $bi_info['is_collection'] = 0;/*收藏过*/
                }
                return json(format($bi_info));
                // }
            } else {
                return json(format('', 245, "该信息已经不存在!~"));
            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 招聘信息列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-28
     */
    public function getRecruitmentList()
    {
        /*投放地区*/
        // $this->param["ui_id"]= 8;
        // $this->param["province_id"]="黑龙江";
        // $this->param["city_id"]="哈尔滨";
        // file_put_contents('./public/2.txt', $this->param["province_id"]);
        (isset($this->param["ri_puton_city"]) && ($this->param["ri_puton_city"] != '')) ? $condition["ri_puton_city"] = dizhis($this->param['ri_puton_city']) : false;
        (isset($this->param["ri_puton_province"]) && ($this->param["ri_puton_province"] != '')) ? $condition["ri_puton_province"] = dizhis($this->param['ri_puton_province']) : false;
        /*行业和技能*/
        (isset($this->param["ui_id"]) && ($this->param["ui_id"] != '')) ? $condition["ui_id"] = $this->param["ui_id"] : false;
        
        // (isset($this->param["us_id"]) && ($this->param["us_id"] != '')) ? $condition["us_id"] = $this->param["us_id"] : false;
        /*按照公司名或者关键字搜索*/
        // if (isset($this->param["type"]) && ($this->param["type"] != '')) {
        //     if ($this->param["type"] == 1) {
        //         /*公司名*/
        //         (isset($this->param["keyword"]) && ($this->param["keyword"] != '')) ? $condition["ri_company"] = $this->param["keyword"] : false;
        //     } else {
        //         /*关键字*/
        //         (isset($this->param["keyword"]) && ($this->param["keyword"] != '')) ? $condition["ri_title"] = $this->param["keyword"] : false;
        //     }
        // }

        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (isset($condition) && is_array($condition)) {
           
            //地区(市)
            if (isset($condition['ri_puton_city']) && ('' != $condition['ri_puton_city']) && $condition['ri_puton_city'] > 1) {
                $where['ri_work_city'] = $condition['ri_puton_city'];
                $pageParam['query']['ri_work_city'] = $condition['ri_puton_city'];
            }
            /*地区(省)*/
            if (isset($condition['ri_puton_province']) && ('' != $condition['ri_puton_province'])) {
                $where['ri_work_province'] = $condition['ri_puton_province'];
                $pageParam['query']['ri_work_province'] = $condition['ri_puton_province'];
            }
            /*类型*/
            if (isset($condition['ui_id']) && ('' != $condition['ui_id'])) {
                $where['ui_id'] = $condition['ui_id'];
                $pageParam['query']['ui_id'] = $condition['ui_id'];
            }
            /*类型*/
            // if (isset($condition['us_id']) && ('' != $condition['us_id'])) {
            //     // $where['us_id'] = ['in', $condition['us_id']];
            //     $where['us_id'] = $condition['us_id'];
            //     $pageParam['query']['us_id'] = $condition['us_id'];
            // }
        }
        $where['ri_is_release'] = '1';
        $field = "ri_id,ri_title,ri_releasetime,ri_startwork_time,ri_work_province,ri_work_city,ui_id";
        $order = "ri_refresh_time desc,ri_add_time desc";
        $pageParam['page'] = $this->param['page'];
        $list = $this->ri->getAll($where, $pageParam, $order, 10, $field);
        // dump($this->ri->getlastsql());
        // file_put_contents('./public/1.txt', $this->ri->getlastsql());

        foreach ($list['data'] as $key => $value) {
            $list['data'][$key]['ri_puton_province']=dizhi($value['ri_work_province']);
            $list['data'][$key]['ri_puton_city']=dizhi($value['ri_work_city']);
            $list['data'][$key]['ui_name']=hangye($value['ui_id']);
        }
        // dump($list);die();
        return json(format($list));
    }
    /**
     * 招聘信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-28
     */
    public function getRecruitmentInfo()
    {
       // $this->param['ri_id'] = 12;
       // $this->u_id='152';
        /*简历id*/
        $ri_where["ri.ri_id"] = $this->param['ri_id'];
        $rule = [
            "ri_id" => 'require|number',
        ];
        $msg = [
            "ri_id.require" => '缺少必要参数!~',
            "ri_id.number" => '必要参数传输有误,请重试!~',
        ];
        $data = verify($this->param, $rule, $msg);
        if ($data['code'] === 1) {
            $join = [
                // ["gjt_shop_artical sa", "sa.sa_id = ri.ri_company_profile"],
                ["gjt_user_industry ui", 'ui.ui_id = ri.ui_id'],
                // ["gjt_user_skills us", 'us.us_id = ri.us_id'],
            ];
            $field = "ri.*,  ui.ui_name";
            /*联合查询简历信息*/
            $ri_info = $this->ri->joinGetRow($join, "ri", $ri_where, $field);
         
            // $ri_info['ri_puton_province']=dizhi($ri_info['ri_puton_province']);
            // $ri_info['ri_puton_city']=dizhi($ri_info['ri_puton_city']);

            $ri_info['ri_work_province']=dizhi($ri_info['ri_work_province']);
            $ri_info['ri_work_city']=dizhi($ri_info['ri_work_city']);
            $ri_info['ri_work_area']=dizhi($ri_info['ri_work_area']);
                // dump($ri_info);die();

            /*如果用户id存在,查看当前用户是否投递过该简历*/
            // if ($this->u_id > 0) {
            //     $count = $this->ujs->getCount(["u_id" => $this->u_id, "ri_id" => $this->param['ri_id']]);
            //     if ($count > 0) {
            //         /*已经投递过*/
            //         $ri_info['is_browse'] = 1;
            //     } else {
            //         $ri_info['is_browse'] = 0;
            //     }
            // } else {
            //     $ri_info['is_browse'] = 0;
            // }
            /*增加浏览次数*/
            // $ri_browse_num = $ri_info['ri_browse_num'] + 1;
            // $this->ri->save(['ri_browse_num' => $ri_browse_num], ['ri_id' => $ri_info['ri_id']]);
            $sm_where['ss_id'] = $ri_info['ss_id'];
            $sm_where['sm_status'] = ["in","1,9"];
                /*店铺客服*/
            $list["sm_im_id"] = $this->sm->getOne($sm_where,"sm_im_id");
            if ($list["sm_im_id"]=='0') {
                $ri_info["sm_im_id"] ='';
            }else{
                $ri_info["sm_im_id"] =$list["sm_im_id"];
            }
            $ss_where['ss_id'] = $ri_info['ss_id'];
                /*店铺客服*/
            $ri_info["ss_logo_img"] = IMG_URL.$this->ss->getOne($ss_where,"ss_logo_img");
            if ($ri_info != '') {
                /*转换文章内容*/
                $ri_info['ri_contents'] = preg_replace('/(<img.+?src=")(.*?)/', '$1' . HTTP_HOST . '$2', $ri_info['ri_contents']);
                // $ri_info['sa_content'] = preg_replace('/(<img.+?src=")(.*?)/', '$1' . HTTP_HOST . '$2', $ri_info['sa_content']);
                $ri_info['ss_name'] = $this->ss->getOne($ss_where,"ss_name");
                $user_collection_list = $this->uci->getRow(['type' => '4',"u_id" => $this->u_id,"uci_collection_id" => $this->param['ri_id']]);
                if ($user_collection_list) {
                    $ri_info['is_collection'] = 1;/*收藏过*/
                }else{
                    $ri_info['is_collection'] = 0;/*收藏过*/
                }
                return json(format($ri_info));
            } else {
                return json(format('', 245, "该信息已经不存在!~"));
            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 投递简历
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-29
     */
    public function sendJobInformation()
    {
        $save['u_id'] = $this->u_id;
        $save['ur_id'] = $this->param['ur_id'];
        $save['ss_id'] = $this->param['ss_id'];
        $save['ri_id'] = $this->param['ri_id'];
        $rule = [
            "u_id" => 'require|number',
            "ur_id" => 'require|number',
            "ss_id" => 'require|number',
            "ri_id" => 'require|number',
        ];
        $msg = [
            "u_id.require" => '缺少用户信息!~',
            "u_id.number" => '用户信息传输有误,请重试!~',
            "ur_id.require" => '缺少简历信息!~',
            "ur_id.number" => '简历信息传输有误,请重试!~',
            "ss_id.require" => '缺少店铺信息!~',
            "ss_id.number" => '店铺信息传输有误,请重试!~',
            "ri_id.require" => '缺少招聘信息!~',
            "ri_id.number" => '招聘信息传输有误,请重试!~',
        ];
        $data = verify($save, $rule, $msg);
        if ($data['code'] === 1) {
            /*检查该条招聘信息用户是否投递过*/
            $count = $this->ujs->getCount(['u_id' => $save['u_id'], "ri_id" => $save['ri_id']]);
            if ($count > 0) {
                return json(format('', 247, "该简历已经投递过了!~"));
            }
            /*添加投递信息*/
            $id = $this->ujs->save($save);
            if ($id > 0) {
                return json(format());
            } else {
                return json(format('', 246, "简历投递失败!~"));
            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 规范列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-31
     */
    public function engineeringSpecificationsList()
    {   
        /*要查询的省份*/
        (isset($this->param["province_id"]) && ($this->param["province_id"] != '')) ? $condition["r_id"] = $this->param['province_id'] : false;
        /*标题*/
        (isset($this->param["es_title"]) && ($this->param["es_title"] != '')) ? $condition["es_title"] = $this->param['es_title'] : false;

        /*保存查询信息*/
        $pageParam = [];
        /*查询条件*/
        if (isset($this->param['es_type']) && $this->param['es_type'] != '') {
            $where['es_type'] = $this->param['es_type'];
        } else {
            $where['es_type'] = "0";
        }
        /*查询展示的*/
        $where['es_is_show'] = 1;
        if (isset($condition) && is_array($condition)) {
            /*关键字*/
            if (isset($condition['es_title']) && ('' != $condition['es_title'])) {
                /*模糊查询*/
                $where['es_title|es_file_sn'] = ['like', "%" . $condition['es_title'] . "%"];
                $pageParam['query']['es_title'] = $condition['es_title'];
            }
            /*地区(省)*/
            if (isset($condition['r_id']) && ('' != $condition['r_id'])) {
                $where['r_id'] = ['in', '1,' . $condition['r_id']];
                $pageParam['query']['r_id'] = $condition['r_id'];
            }
        }
        $field = "es_id,es_title,es_file_sn,es_thumb,es_implementation_time,es_price";
        $order = "es_add_time desc";
        $pageParam['page'] = $this->param['page']=1;
        $list = $this->es->getAll($where, $pageParam, $order, 0, $field);
        /*循环遍历添加host*/
        foreach ($list["data"] as $key => $value) {
            foreach ($value as $k => $val) {
                if ($k == "es_thumb") {
                    $list["data"][$key][$k] = IMG_URL . $val;
                }
            }
            $list["data"][$key]['is_pay'] = 0; /*不能看  没有支付*/
        }
        /*如果后台设置金额为0 或者为空 不需要支付 都能看*/
        if (getTableConfig("mbc_")['other']['specification_pay_money'] != 0 || getTableConfig("mbc_")['other']['specification_pay_money'] != '') {
            /*查询支付的订单次数*/
            if (isset($this->u_id) && $this->u_id > 0) {
                /*订单支付日志*/
                $pl_where['u_id'] = $this->u_id;
                $pl_where['order_type'] = 1; /*1代表规范*/
                $pl_where['is_paid'] = 1; /*1代表已支付*/
                $pl_list = $this->pl->getList($pl_where);
                foreach ($list["data"] as $key => $value) {
                    foreach ($pl_list as $ke => $val) {
                        if ($val['pay_goods_id'] == $value['es_id']) {
                            $list["data"][$key]['is_pay'] = 1; /*可以看  已经支付*/
                        }
                    }
                }
            }
        }
       
        return json(format($list));
    }
    /**
     * 获取规范信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-31
     */
    public function getEngineeringSpecificationsInfo()
    {
//        $this->param['es_id']='28';
        // $this->u_id='152';
        $rule = [
            "es_id" => 'require|number',
        ];
        $msg = [
            "es_id.require" => '缺少必要参数!~',
            "es_id.number" => '必要参数传输有误,请重试!~',
        ];
        $data = verify($this->param, $rule, $msg);
        if ($data['code'] === 1) {
            if (empty($this->u_id) || $this->u_id < 0) {
                return json(format('', 249, "请求失败,请重试!~"));
            }
            $pl_where['u_id'] = $this->u_id; /*用户id*/
            $pl_where['order_type'] = 1; /*规范*/
            $pl_where['is_paid'] = 1; /*已支付*/
            /*查询支付的所有信息*/
            $list = $this->pl->getList($pl_where);
            /*定义一个变量,为了下面循环使用 0代表数据库中没有该id的字段*/
            $is_save = 0;

            /*遍历支付信息中是否包含此条规范信息*/
            foreach ($list as $key => $value) {
                if ($value['pay_goods_id'] == $this->param['es_id']) {
                    $is_save = 1;
                    break;
                }
            }

            // $free_browsse_num = (int) getTableConfig("mbc_")['other']['specification_free_browsse_num'];
            /*如果浏览次数超出免费浏览次数 需要支付*/
            // if (count($list) >= $free_browsse_num) {
            //     /*数据库中有该条数据添加记录 可以展示*/
            //     if ($is_save == 1) {
            //         /*获取规范信息*/
            //         $ret_info = $this->es->getRow(['es_id' => $this->param['es_id']], "es_id,es_title,es_path");
            //         $ret_info['is_need_pay'] = 0; /*不需要支付*/
            //         $ret_info['es_path'] = IMG_URL . $ret_info['es_path'];

            //         $count = $this->ubh->getCount(["u_id" => $this->u_id, "ubh_type" => "3", "ubh_browsing_id" => $this->param['es_id']]);
            //         if ($count < 1) {
            //             $save = [
            //                 "u_id" => $this->u_id, 
            //                 "ubh_type" => "3", 
            //                 "ubh_browsing_id" => $this->param['es_id'],
            //                 "ubh_time" => time(),
            //             ];
            //             $this->ubh->save($save);
            //         } else {
            //             $where = [
            //                 "u_id" => $this->u_id, 
            //                 "ubh_type" => "3", 
            //                 "ubh_browsing_id" => $this->param['es_id'],
            //             ];

            //             $save = ["ubh_time" => time()];
            //             $this->ubh->save($save,$where);
            //         }

            //         return json(format($ret_info));
            //     } else {
            //         /*否则需要支付*/
            //         $ret_info['is_need_pay'] = 1; /*需要支付*/
            //         $ret_info['es_id'] = $this->param['es_id']; /*需要支付的规范id*/
            //         $ret_info['pay_money'] = getTableConfig("mbc_")['other']['specification_pay_money']; /*支付金额*/
            //         return json(format($ret_info));
            //     }

            // } else {
                /*获取规范信息*/
                $ret_info = $this->es->getRow(['es_id' => $this->param['es_id']]);
                /*添加规范支付信息(这个时候不需要支付,还在免费条数范围内*/
                // $pl_where['ss_id'] = 0;
                // $pl_where['order_id'] = 0;
                // $pl_where['pay_goods_id'] = $this->param['es_id']; /*规范id*/
                if ($is_save == 0) {
                    // $this->pl->save($pl_where);
                     $ret_info['is_need_pay'] = 1;
                }else{
                     $ret_info['is_need_pay'] = 0; /*不需要支付*/
                }
                $count = $this->ubh->getCount(["u_id" => $this->u_id, "ubh_type" => "3", "ubh_browsing_id" => $this->param['es_id']]);
                if ($count < 1) {
                    $save = [
                        "u_id" => $this->u_id, 
                        "ubh_type" => "3", 
                        "ubh_browsing_id" => $this->param['es_id'],
                        "ubh_time" => time(),
                    ];
                    $this->ubh->save($save);
                } else {
                    $where = [
                        "u_id" => $this->u_id, 
                        "ubh_type" => "3", 
                        "ubh_browsing_id" => $this->param['es_id'],
                    ];

                    $save = ["ubh_time" => time()];
                    $this->ubh->save($save,$where);
                }
                if (!file_exists(UPLOAD . $ret_info['es_path'])) {
                    $ret_info['es_path'] = IMG_URL . $ret_info['es_path'];
                    $ret_info['es_thumb'] = IMG_URL . $ret_info['es_thumb'];
                    $user_collection_list = $this->uci->getRow(['type' => '2',"u_id" => $this->u_id,"uci_collection_id" => $this->param['es_id']]);
                    if ($user_collection_list) {
                        $ret_info['is_collection'] = 1;/*收藏过*/
                    }else{
                        $ret_info['is_collection'] = 0;/*收藏过*/
                    }
                    return json(format($ret_info));
                } else {
                    return json(format('', 223, "文件不存在!~"));
                }
            // }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 规范信息支付页面
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-31
     */
    public function engineeringSpecificationsPay()
    {
        $pay_action = $this->param["type"]; /*alipay,wechatPay*/
        $tso_add['tender_spec_id'] = $pl_save['pay_goods_id'] = $this->param['es_id']; /*规范id*/
        $tso_add['u_id'] = $pl_save['u_id'] = $this->u_id;
        $tso_add['tso_pay_status'] = $pl_save['is_paid'] = 0; /*是否支付*/
        $tso_add['tso_type'] = $pl_save['order_type'] = '1'; /*类型1代表规范订单*/
        $tso_add['tso_add_time'] = time();
        $tso_add['p_code'] = $pay_action;
        $pl_save['order_amount'] = $tso_add['tso_pay_money'] = getTableConfig("mbc_")['other']['specification_pay_money']; /*支付金额*/
        $pl_save['order_sn'] = $tso_add['tso_sn'] = getOrderSn($this->param['es_id'], $this->u_id); /*订单号*/
        $rule = [
            "tender_spec_id" => "require|number",
            "u_id" => "require|number",
            "p_code" => "require",
            "tso_pay_money" => "require",
            "tso_sn" => "require",
        ];
        $msg = [
            "tender_spec_id.require" => "缺少必要参数!~",
            "tender_spec_id.number" => "必要参数传输有误!~",
            "u_id.require" => "缺少必要参数!~",
            "u_id.number" => "必要参数传输有误!~",
            "p_code.require" => "缺少支付方式!~",
            "tso_pay_money.require" => "缺少支付金额!~",
            "tso_sn.require" => "订单号生成失败!~",
        ];
        $data = verify($tso_add, $rule, $msg);
        if ($data['code'] === 1) {
            if ($tso_add['p_code'] != "wechatPay" && $tso_add['p_code'] != "alipay") {
                return json(format('', 223, "支付方式选择有误!~"));
            } else {
                /*查询是否有该订单*/
                $tso_count = $this->tso->getCount(['u_id' => $this->u_id, 'tender_spec_id' => $this->param['es_id'], 'tso_pay_status' => 1, 'tso_type' => '1']);
                $pl_count = $this->pl->getCount(['u_id' => $this->u_id, 'pay_goods_id' => $this->param['es_id'], 'is_paid' => 1, 'order_type' => '1']);
                /*如果已支付订单大于0*/
                if ($tso_count > 0 && $pl_count > 0) {
                    return json(format());
                } else {
                    /*如果支付金额为0 或者空的话 不需要支付 直接添加订单*/
                    if ($tso_add['tso_pay_money'] == 0 || empty($tso_add['tso_pay_money'])) {
                        $tso_add['tso_pay_status'] = $pl_save['is_paid'] = 1; /*是否支付*/
                        $this->tso->save($tso_add);
                        $this->pl->save($pl_save);
                        return json(format());
                    } else {
                        /*查询该订单是否存在,如果存在的情况下订单号为原来的订单号, 否则为新生成的订单号*/
                        $row = $this->tso->getRow(['u_id' => $this->u_id, 'tender_spec_id' => $this->param['es_id'], 'tso_pay_status' => 0, 'tso_type' => '1']);
                        if (!empty($row)) {
                            $order_sn = $row['tso_sn'] . time();
                            $update_info['p_code'] = $pay_action;
                            $update_where['tso_id'] = $row['tso_id'];
                            $this->tso->save($update_info, $update_where);
                        } else {
                            $order_sn = $tso_add['tso_sn'] . time();
                            $id = $this->tso->save($tso_add);
                            $pl_save['order_id'] = $id;
                            $this->pl->save($pl_save);
                        }
                        $ret = $pay_action($order_sn, $tso_add['tso_pay_money'], 1);
                        if (false !== $ret) {
                            return json(format($ret));
                        } else {
                            return json(format('', 250, "支付信息请求失败,请稍后再试!~"));
                        }
                    }
                }
            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }

    /**
     * [技能]
     * @author 李鑫
     * @date 2018-05-12
     */
    public function jineng(){
        $iRow = $this->ui->getList(["ui_status"=>'1'],'ui_id,ui_name');
        return json(format($iRow));
    }
}
