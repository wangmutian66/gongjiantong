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
use \model\ShopBrandApplication as sba;
use \model\Goods as g;
use model\Region as r;
use model\Order as o;
use model\Users as u;
use model\OrderReturn as _or;
use model\OrderGoods as og;
use model\Specifications as sp;
use model\GoodsSpecifications as gsp;
use model\Activity as act;
/**
 * 审核
 * @author 王牧田
 * @e-mail zrkjhlc@gmail.com
 * @date   2018-05-07
 */
class Audit extends Base
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
    protected $r;
    protected $o;
    protected $u;
    protected $or;
    protected $og;
    protected $sp;
    protected $gsp;
    protected $act;
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
        $this->r = new r();
        $this->o = new o();
        $this->u = new u();
        $this->or = new _or();
        $this->og = new og();
        $this->sp = new sp();
        $this->gsp = new gsp();
        $this->act = new act();
    }

    /***
     * 入驻审核
     * @author 王牧田
     * @date 2018-05-07
     * @return \think\response\View
     */
    public function inaudit(){
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
            ["gjt_manage_goods_category mgc", "mgc.mgc_id = ss.mgc_id","left"],
            ["gjt_seller_registered sr", "sr.sr_id = ss.sr_id","left"],
        ];
        $alias = "ss";
        $field = "ss.*,mgc.mgc_name,sr.sr_reason";

        $list = $this->ss->joinGetAll($join, $alias, $where, $pageParam, [], 0, $field,"");

        $this->managerLog("浏览店铺列表");

        return view(
            "inaudit",
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


    public function apply(){
        $join = [
            ["gjt_seller_shop ss","ss.sr_id = sr.sr_id","left"]
        ];
        $alias = "sr";
        $field = "ss.*,sr.*";
        $list = $this->sr->joinGetAll($join, $alias, [], [], [], 0, $field,"");
//        $list = $this->sr->getAll([],[],["sr_id"=>"desc"]);

        return view(
            "apply",
            [
                "data" => "", /*查询条件*/
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
     * [入驻审核通过]
     * @author 王牧田
     * @date 2018-05-07
     */
    public function isapply(){
        $condition = $this->request->only(["sr_id","sr_isaudit"], "post");
        $sr_id = $condition["sr_id"];
        $srresult = $this->sr->save(["sr_isaudit"=>$condition["sr_isaudit"]],["sr_id"=>$sr_id]);

        if(empty($srresult)){
            return json(format($srresult, 201, "未做任何操作"));
        }else{
            $AppKey = config("plugin")['sms']['AppKey'];
            $AppSecret = config("plugin")['sms']['AppSecret'];

            $sms = new \sms\ServerAPI($AppKey, $AppSecret, 'curl');
            $srOne = $this->sr->getRow(["sr_id"=>$sr_id],"u_id,sr_email");
            $u_id = $srOne['u_id'];
            $u_mobile = $this->users->getOne(["u_id"=>$u_id],"u_mobile");

            if($condition["sr_isaudit"] == 1){
                sendEmail($srOne['sr_email'],"【黑龙江特讯科技】工建通用户,您的入驻申请资格已通过,请登录工建通商户后台管理系统http://www.gongjiantong.com完善商家信息，登录名".$u_mobile.",初始密码:123456(请勿告知他人,将导致账户泄密,请勿泄漏)");
                $auth_value = $sms->sendSMSTemplate("4092832", [$u_mobile], [$u_mobile]);
                if ($auth_value['code'] == 200) {
                    return json(format($srresult, 200, "审核通过，并发送短信，到：".$u_mobile));
                }
            }else if($condition["sr_isaudit"] == 2) {
                sendEmail($srOne['sr_email'],"【黑龙江特讯科技】工建通用户,您的入驻审核资格未通过,请登录工建通商户后台管理系统http://www.gongjiantong.com查看(请勿告知他人,将导致账户泄密,请勿泄漏)");
                $auth_value = $sms->sendSMSTemplate("4102827", [$u_mobile], []);
                if ($auth_value['code'] == 200) {
                    return json(format($srresult, 201, "操作成功，并发送短信，到：".$u_mobile));
                }
            }
        }
    }




    /**
     * [入驻商家]
     * @author 王牧田
     * @date 2018-05-07
     */
    public function merchants(){

        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where['ss_approval_status'] = 1;
        /*保存查询条件状态*/
        $pageParam['query']['ss_approval_status'] = 1;

        $join = [
            ["gjt_manage_goods_category mgc", "mgc.mgc_id = ss.mgc_id"],
            ["gjt_seller_registered sr", "sr.sr_id = ss.sr_id"],
            ["gjt_goods g","ss.ss_id = g.ss_id",'LEFT']/*修改左联查询*/
        ];
        $alias = "ss";
        $field = "ss.*,mgc.mgc_name,sr.sr_reason,count(g.g_id) as gcount";

        $list = $this->ss->joinGetAll($join, $alias, $where, $pageParam, [], 0, $field,"ss.ss_id");/*group查询g.ss_id改为ss.ss_id*/
       
        foreach ($list["data"] as $k=>$row){
            $list["data"][$k]["sbacount"] = $this->sba->getCount(["ss_id"=>$row["ss_id"],"sba_status"=>["in","1"]]);
            $list["data"][$k]["gcount"] = $this->g->getCount(["ss_id"=>$row["ss_id"],"g_goods_verify"=>["in","0,2"]]);
            $list["data"][$k]["gcounttotal"] = $this->g->getCount(["ss_id"=>$row["ss_id"]]);
            $list["data"][$k]["ocount"] = $this->o->getCount(["ss_id"=>$row["ss_id"]]);
            $orf = $this->or->getCount(["ss_id"=>$row["ss_id"],"or_refund"=>1]);
            $list["data"][$k]["orf"] = empty($orf)?0:1;
            $list["data"][$k]["orcount"] = $this->or->getCount(["ss_id"=>$row["ss_id"]]);
        }

        $this->managerLog("浏览店铺列表");

        return view(
            "merchants",
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
     * [店铺信息]
     * @author 王牧田
     * @date 2018-05-11
     * @param $ss_id
     */
    public function shopinfo($ss_id){
        if ($this->request->isPost()) {
            $condition = $this->request->only(["admin_nature"], "post");
            $value = $this->ss->save(["admin_nature"=>$condition["admin_nature"]],["ss_id"=>$ss_id]);
            if (false !== $value) {
                $this->success("设置成功", url("manage/Audit/merchants"));
            } else {
                $this->error("修改失败");
            }

        }else{
            $where = ["ss_id" => intval($ss_id)];
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

            /*上面是注册信息*/
            $ssRow = $this->ss->getRow(["ss_id"=>$ss_id]);
            $region = $this->r->getOnes(["r_id"=>["in",$ssRow["ss_shop_province"].",".$ssRow["ss_shop_city"].",".$ssRow["ss_shop_area"]]],"r_name");
            $gulist = $this->gu->getOnes(["gu_id"=>["in",$ssRow["guarantee"]]],"gu_name");

            $nature=["厂家","代理","零售","其他"];
            $invoice = ["电子发票","普通发票","专业发票","暂不发票"];
            $arr = explode(",",$ssRow["ss_invoice"]);
            $arr_invoice = [];
            array_walk($arr, function($value, $key) use (&$invoice,&$arr_invoice ){
                if($value!=0){
                    $arr_invoice[] = ($invoice[$value-1]);
                }
            });
            $guarantes = unserialize($ssRow["guarantes"]);
            if($guarantes != false){
                foreach ($guarantes  as $k => $v) {
                    $contents= $this->gu->getRow(array("gu_id"=>$v['guids']),"content,g_img");
                    $guarantes[$k]['guname']=$contents['content'];
                    $guarantes[$k]['guarantess']=$contents['content'];
                }
            }

            $ss_file = explode(",",$ssRow["ss_file"]);
            $this->assign("ss_file",$ss_file);
            $this->assign("arr_invoice",implode(",",$arr_invoice));
            $this->assign("nature",($ssRow["nature"]!=0)?$nature[$ssRow["nature"]-1]:""); /*店家性质*/
            $this->assign("guarantes",$guarantes);
            $this->assign("gulist",implode("、",$gulist));
            $this->assign("ssRow",$ssRow);
            $this->assign("region",$region);

             return view(
                "shopinfo",
                [
                    "info" => $info,
                    'mgc_id_array'=>explode(",",$settledIn["mgc_id"]),
                    "mgc_list" => $mgc_list,
                ]
            );
        }
    }

    /**
     * [总后台查看订单列表]
     * @author 王牧田
     * @date 2018-06-05
     * @param $ss_id
     */
    public function orderlist($ss_id){
        $mpg_id = $this->m_info['mpg_id'];
        if($mpg_id == 0){
            $mpg_ispay = 1;
        }else{
            $mpg_ispay = $this->mpg->getOne(['mpg_id'=>$mpg_id],"mpg_ispay");
        }


        $where = [];
        $o_status="";

        if(isset($_GET["o_status"]) && $_GET["o_status"]!=""){
            $where["o_status"]=$_GET["o_status"];
            $o_status=$_GET["o_status"];
        }


        $where["ss_id"]=$ss_id;
        $list = $this->o->getAll($where,[],["o_id"=>"desc"]);

        if(isset($list['data'])){
            foreach ($list['data'] as $k=>$row){
                $u_id = $row["u_id"];
                $uselist = $this->u->getRow(['u_id'=>$u_id],"u_name,u_mobile");


                if(!empty($uselist)){
                    $list['data'][$k]["userinfo"] = $uselist["u_name"].":".$uselist["u_mobile"];
                }else{
                    $list['data'][$k]["userinfo"] = "";
                }
                $list['data'][$k]['or_type'] = $this->or->getOne(['o_id'=>$row['o_id']],'or_type');
                $list['data'][$k]["o_add_time"] = date("Y-m-d H:i",$row["o_add_time"]);
            }
        }

        return view(
            "orderlist",
            [
                "mpg_ispay"=>$mpg_ispay,
                "list" => $list['data'], /*查询结果*/
                "page" => $list['page'], /*分页和html代码*/
                "lastPage" => $list['lastPage'], /*总页数*/
                "currentPage" => $list['currentPage'], /*当前页码*/
                "total" => $list['total'], /*总条数*/
                "listRows" => $list['listRows'], /*每页显示条数*/
                "o_status" => $o_status,
            ]
        );
    }

    /**
     * [总后台退换货]
     * @param $ss_id
     */
    public function returnlist($ss_id){

        $o_id = $this->o->getOnes(["ss_id"=>$ss_id],"o_id");
        //"o_id"=>["in",implode(",",$o_id)]
        $join = [
            ["gjt_order o", "og.o_id = o.o_id","left"],
        ];
        $g_value = $this->or->joinGetAll($join,"og",["o.ss_id"=>$ss_id]);

//        $g_value = $this->or->getAll([],[],["or_id"=>"desc"]); /*商户分类*/

        $g_valuedata = $g_value['data'];

        foreach ($g_valuedata as $k=>$row){
            $grow = $this->g->getRow(["g_id"=>$row["g_id"]],"g_name");
            $orow = $this->o->getRow(["o_id"=>$row["o_id"]],"o_sn,ss_id,o_add_time");

            if(empty($orow)){
                unset($g_valuedata[$k]);
            }else{
                $g_valuedata[$k] = array_merge($g_valuedata[$k],$grow,$orow);
            }

        }


        return view(
            'returnlist',
            [
                "g_value" => $g_valuedata, /*查询结果*/
                "page" => $g_value['page'], /*分页和html代码*/
                "lastPage" => $g_value['lastPage'], /*总页数*/
                "currentPage" => $g_value['currentPage'], /*当前页码*/
                "total" => $g_value['total'], /*总条数*/
                "listRows" => $g_value['listRows'], /*每页显示条数*/
            ]
        );
    }


    /**
     * [退货详情]
     * @author 王牧田
     * @date 2018-7-6
     *
     */
    public function return_info($or_id){
        $orRow = $this->or->getRow(["or_id"=>$or_id]);
        $oRow = $this->o->getRow(["o_id"=>$orRow['o_id']]);
        if(empty($oRow)){
            $this->error("当前订单已被删除");
        }
        $join = [
            ["goods g", "og.g_id = g.g_id"],
        ];
        $where = ["og.o_id"=>$orRow['o_id']];
        $gsplist = $this->og->joinGetList($join,"og",$where,[],"og.og_meet as act_meet,og.og_reduction as act_reduction,og.ga_id,g.g_weight,og.g_id,goods_sn,g_show_img_path,goods_name,goods_buy_num,og.sp_id,og.member_goods_price");

        /*总价*/
        $o_total_amount = 0;
        $totalweight = 0;

        foreach ($gsplist as $k=>$row){


            /*規格名字*/
            $gsplist[$k]["sp_name"] = $this->sp->getOne(["id"=>$row["sp_id"]],"sp_name");

            $gspRow = $this->gsp->getRow(["sp_id"=>$row["sp_id"],"g_id"=>$row["g_id"]]);

            $gsplist[$k]["gsp_price"] = isset($gspRow['gsp_price'])?$gspRow["gsp_price"]:0;
            /*重量*/
            $totalweight+=empty($gspRow["gsp_weight"])?0:$gspRow["gsp_weight"];
            /*检查是否有活动  select * from gjt_activity where find_in_set('25',g_ids);  find_in_set('".$row["g_id"]."',g_ids) and*/
            $gsplist[$k]["g_discount_price"] = $row["member_goods_price"];
            $gsplist[$k]["act_type"] = $this->act->getOne(["act_id"=>$row["ga_id"]],"act_type");
            $price = ($row["member_goods_price"]==0)?$gsplist[$k]["gsp_price"]:$row["member_goods_price"];
            $gsplist[$k]["xiaoji"] = $gsplist[$k]["goods_buy_num"]*$price;
            $o_total_amount +=$gsplist[$k]["xiaoji"];

        }


        $returnprice = $oRow["o_payable_price"] - $oRow["o_logistics"];
        $this->assign("returnprice",$returnprice);
        $orstatus = "";
        if($orRow["or_examine"]=="-1" && $orRow["or_status"] == 0){
            $orstatus = "or_status";
        }else if($orRow["or_examine"]=="-1" && $orRow["or_receiving"] == 0){
            $orstatus = "or_receiving";
        }

        $this->assign("orstatus",$orstatus);
        $this->assign("oRow",$oRow);
        $this->assign("orRow",$orRow);
        $this->assign("gsplist",$gsplist);
        return view();

    }

    /**
     * [退款]
     */
    public function returnor(){
        $or_id = input("or_id");

        $orrow = $this->or->getRow(["or_id"=>$or_id]);
        $oRow = $this->o->getRow(["o_id"=>$orrow["o_id"]]);

        $returnprice = $oRow["o_payable_price"] - $oRow["o_logistics"];
        $result = alipaygateway($orrow["or_payaccount"], $orrow["or_payrealname"], $returnprice);
//        $result = alipaygateway("18745016473", "王牧甜", "0.1");

        if (empty($result) || $result != 10000) {
            return json(format([],223,"退货失败，请与商家联系!"));
            exit();
        }

        $orsave = $this->or->save(["or_refund"=>2],["or_id"=>$or_id]);
        if(!empty($orsave)){
            return json(format());
        }

    }



}
