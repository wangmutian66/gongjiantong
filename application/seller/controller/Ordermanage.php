<?php
namespace app\seller\controller;
/**
 * Created by PhpStorm.
 * User: 王牧田
 * Date: 2018/4/21
 * Time: 9:10
 */
use \model\Activity as act;
use \model\Order as o;
use \model\OrderAction as oa;
use \model\OrderDelivery as od;
use \model\OrderGoods as og;
use \model\Goods as g;
use \model\Users as u;
use \model\UserShippingAddress as usa;
use \model\Region as r;
use \model\GoodsSpecifications as gsp;
use \model\Specifications as sp;
use \model\SellerRegistered as sr;
use \model\Pluginconfig as logc;
use \model\OrderReturn as _or;
use \model\Plugin as log;
use model\Invoice as i;
use model\SellerShop as ss;
use model\Orderevaluation as oe;
use think\Config;
use think\Db;


class Ordermanage extends Base
{

    protected $act;
    protected $o;
    protected $oa;
    protected $od;
    protected $og;
    protected $g;
    protected $u;
    protected $usa;
    protected $r;
    protected $gsp;
    protected $sp;
    protected $sr;
    protected $logc;
    protected $or;
    protected $i;
    protected $log;
    protected $ss;
    protected $oe;
    public function __construct()
    {
        parent::__construct();
        $this->act = new act();
        $this->o = new o();
        $this->oa = new oa();
        $this->od = new od();
        $this->og = new og();
        $this->g = new g();
        $this->u = new u();
        $this->usa = new usa();
        $this->r = new r();
        $this->gsp = new gsp();
        $this->sp = new sp();
        $this->sr = new sr();
        $this->logc = new logc();
        $this->or=new _or();
        $this->log=new log();
        $this->i=new i();
        $this->ss=new ss();
        $this->oe=new oe();
    }


    /**
     * 作者：王牧田
     * 时间：2018-04-21
     * 功能：订单列表
     */
    public function index(){
        $where = [];
        $o_status="";

        if(isset($_GET["o_status"]) && $_GET["o_status"]!=""){
            $where["o_status"]=$_GET["o_status"];
            $o_status=$_GET["o_status"];
        }

        $ss_id = $this->sm_info['ss_id'];
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
            "index",
            [
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
     * [订单查看]
     * @author 王牧田
     * @param $order_id
     * @return \think\response\View
     * @date 2018-04-24
     */
    public function detail($order_id){

        $oRow=$this->o->getRow(["o_id"=>$order_id]);

        /**操作记录**/
        $oalist = $this->oa->getList(["o_id"=>$order_id,"ss_id"=>$this->sm_info['ss_id']]);
        foreach ($oalist as $k=>$row){
            $ulist = $this->u->getRow(["u_id"=>$row["oa_user"]],"u_name,u_mobile");
            if(empty($ulist)){
                $ulist["u_name"] = $ulist["u_mobile"] = "";
            }
            $oalist[$k] = array_merge($row,$ulist);
        }

        $userinfo = $this->u->getRow(["u_id"=>$oRow["u_id"]]);
//        $usainfo = $this->usa->getRow(["usa_id"=>$oRow["usa_id"]]);
        $reginone = $this->r->getOnes(["r_id"=>["in",$oRow["usa_province"].",".$oRow["usa_city"].",".$oRow["usa_district"]]],"r_name");

        $join = [
            ["goods g", "og.g_id = g.g_id"],
        ];
        $where = ["og.o_id"=>$order_id];
        $gsplist = $this->og->joinGetList($join,"og",$where,[],"og.og_meet as act_meet,og.og_reduction as act_reduction,og.ga_id,g.g_weight,og.g_id,goods_sn,g_show_img_path,goods_name,goods_buy_num,og.sp_id,og.member_goods_price");

        /*总价*/
        $o_total_amount = 0;
        $totalweight = 0;

        foreach ($gsplist as $k=>$row){

            /*規格名字*/
            $gsplist[$k]["sp_name"] = $this->sp->getOne(["id"=>$row["sp_id"]],"sp_name");

            $gspRow = $this->gsp->getRow(["sp_id"=>$row["sp_id"],"g_id"=>$row["g_id"]]);
//            $gsplist[$k]["gsp_price"] = $this->gsp->getOne(["sp_id"=>$row["sp_id"],"g_id"=>$row["g_id"]],"gsp_price");
            $gsplist[$k]["gsp_price"] = $gspRow["gsp_price"];
            /*重量*/
            $totalweight+=empty($gspRow["gsp_weight"])?0:$gspRow["gsp_weight"];
            /*检查是否有活动  select * from gjt_activity where find_in_set('25',g_ids);  find_in_set('".$row["g_id"]."',g_ids) and*/
            $gsplist[$k]["g_discount_price"] = $row["member_goods_price"];
            $gsplist[$k]["act_type"] = $this->act->getOne(["act_id"=>$row["ga_id"]],"act_type");
            $price = ($row["member_goods_price"]==0)?$gsplist[$k]["gsp_price"]:$row["member_goods_price"];
            $gsplist[$k]["xiaoji"] = $gsplist[$k]["goods_buy_num"]*$price;
            $o_total_amount +=$gsplist[$k]["xiaoji"];
        }


        ini_set('display_errors', 'on');
        $logistics = getWeightMoney($oRow["log_id"],$oRow["usa_id"],$totalweight);
        $oRow["o_goods_price"] = number_format($oRow["o_goods_price"],2);
        $oRow["o_logistics"] = number_format($oRow["o_logistics"],2);
        $oRow["o_payable_price"] = number_format($oRow["o_payable_price"],2);
        $oRow["o_diffvalue"] = number_format($oRow["o_diffvalue"],2);
        $oRow["o_payable_price"] = $oRow["o_payable_price"];
        $oRow["o_shipping_price"] = number_format($oRow["o_shipping_price"],2);

        $log_name= $this->log->getOne(["log_id"=>$oRow["log_ss_id"]],"log_name");
        if(!isset($userinfo["u_name"])){
            $userinfo["u_name"] = $userinfo['u_mobile'] = "";
        }
        $this->assign("log_name",$log_name);
        $this->assign("logistics",$logistics);
        $this->assign("o_total_amount",number_format($o_total_amount,2));
        $this->assign("order_id",$order_id);
        $this->assign("oalist",$oalist);
        $this->assign("gsplist",$gsplist);
        $this->assign("reginone",$reginone);
        $this->assign("usainfo",$oRow);
        $this->assign("userinfo",$userinfo);
        $this->assign("oRow",$oRow);
        return view();
    }

    /**
     * [删除订单]
     * @date 2018-04-24
     * @author wangmutian
     */
    public function delorder(){

        $sm_info = $this->request->only(["oid"], "post");
        $result = $this->o->del(["o_id"=>$sm_info["oid"]]);
        if(intval($result)>0){
            return json(format());
        }else{
            return json(format('', '-1', "网络请求失败，请稍后重试"));
        }

    }


    /**
     * [计算物流费用]
     * @param $oRow
     * @date 2018-04-24
     * @author wangmutian
     */
    public function getWeightMoney($oRow,$weight){
        $log_id = $oRow["log_id"];
        $logclist = $this->logc->getOnes(["log_id"=>$log_id],"logc_area");

        $address =  $this->usa->getRow(["usa_id"=>$oRow["usa_id"]],"usa_province,usa_city,usa_district");
        foreach ($logclist as $a){
            $unionarray=array_intersect(explode(",",$a),$address);
            if(!empty($unionarray)){

                $logcRow = $this->logc->getRow(["logc_area"=>$a],"logc_heavy,logc_cmoney,logc_contheavys,logc_contcmoneys");

                if($weight < $logcRow["logc_heavy"]){
                    return $logcRow["logc_cmoney"];
                }

                return ceil(($weight - $logcRow["logc_heavy"])/$logcRow["logc_contheavys"])*$logcRow["logc_contcmoneys"]+$logcRow["logc_cmoney"];
            }
        }
    }


    /**
     * [确认订单/取消订单]
     * @param $order_id
     * @param $type
     * @return \think\response\Json
     * @date 2018-04-24
     */
    public function orderaction($order_id,$type){

        /*接收到的数据*/
        $note = $this->request->only(["note"], "post");
        $o_status  = 0;
        $oa_status_desc = "";
        $orow=$this->o->getRow(["o_id"=>$order_id],"o_status,o_pay_status");
        switch ($type){
            case "pay":
                $o_status  = 1;
                //将订单表修改成确认
                $this->o->save(["o_status"=>$o_status,"o_status"=>$o_status,"o_admin_note"=>$note["note"]],["o_id"=>$order_id]);
                $oa_status_desc="确认订单";
                break;
            case "invalid":
                $o_status = 2;
                $oa_status_desc="取消订单";
                break;

        }


        //添加操作记录
        $sr_id = $this->sm_info["sr_id"];
        $u_id = $this->sr->getOne(["sr_id"=>$sr_id],"u_id");

        $result = [
            "o_id" =>$order_id,
            "ss_id" =>$this->sm_info['ss_id'],
            "oa_user" =>$u_id,
            "oa_order_status"=>$o_status,
            "oa_pay_status"=>$orow["o_pay_status"],
            "oa_note"=>$note["note"],
            "oa_time"=>time(),
            "oa_status_desc"=>$oa_status_desc,
            "oa_role" => 1,

        ];

        $id=$this->oa->save($result);
        if(intval($id)>0){
            return json(format());
        }else{
            return json(format('', '-1', "网络请求失败，请稍后重试"));
        }


    }


    /**
     * [订单价格修改]
     * @param order_id
     * @author 王牧田
     * @date 2018-04-24
     */
    public function editprice($order_id){
        $oRow=$this->o->getRow(["o_id"=>$order_id],"o_logistics,o_goods_price,o_payable_price,o_payable_price,o_diffvalue,o_shipping_price,o_status,o_pay_status");

        if ($this->request->isPost()) {
            $sm_info = $this->request->only(["o_shipping_price","o_diffvalue"], "post");

            $o_payable_price = $oRow["o_goods_price"]+$sm_info["o_shipping_price"]+$sm_info["o_diffvalue"];
            $ret = $this->o->save(["o_payable_price"=>$o_payable_price,
                "o_diffvalue"=>$sm_info["o_diffvalue"],
                "o_shipping_price"=>$sm_info["o_shipping_price"],'o_logistics'=>$sm_info["o_shipping_price"]],["o_id"=>$order_id]);

            if ($ret > 0) {
                //-

                $sr_id =$this->sm_info["sr_id"];
                $u_id = $this->sr->getOne(["sr_id"=>$sr_id],"u_id");
                $res = [
                    "o_id" =>$order_id,
                    "ss_id" =>$this->sm_info['ss_id'],
                    "oa_user" =>$u_id,
                    "oa_order_status"=>$oRow["o_status"],
                    "oa_pay_status"=>$oRow["o_pay_status"],
                    "oa_note"=>"",
                    "oa_time"=>time(),
                    "oa_status_desc"=>"修改金额",
                    "oa_role" => 1,
                ];
                $this->oa->save($res);
                $this->success("添加成功!",url('seller/Ordermanage/detail',array("order_id"=>$order_id)));
            } else {
                $this->error("添加失败!");
            }
        }else{

            $oRow["o_logistics"] = number_format($oRow["o_logistics"],2);
            $oRow["o_goods_price"] = number_format($oRow["o_goods_price"],2);
            $oRow["o_payable_price"] = number_format($oRow["o_payable_price"],2);
            $oRow["o_payable_price"] = number_format($oRow["o_payable_price"],2);
            $oRow["o_shipping_price"] = number_format($oRow["o_shipping_price"],2);

            $this->assign("oRow",$oRow);
            return view();
        }

    }






	/**
     * 作者：李鑫
     * 时间：2018-04-21
     * 功能：订单发货列表
     */
    public function deliverylist()
    {

        $where = [];
        $o_shipping_status="";

        if(isset($_GET["o_shipping_status"]) && $_GET["o_shipping_status"]!=""){
            $where["o_shipping_status"]=$_GET["o_shipping_status"];
            $o_shipping_status=$_GET["o_shipping_status"];
        }
        $where["ss_id"] = $this->sm_info['ss_id'];
        $where["o_status"] = ["in","1,5"];
        $where["o_pay_status"] = 1;
        $g_value = $this->o->getAll($where,[],["o_id"=>"desc"]); /*商户分类*/
       
        $g_valuedata = $g_value['data'];
        foreach ($g_valuedata as $k=>$row){

            $usarow=$this->usa->getRow(["usa_id"=>$row["usa_id"]],"usa_user_name,usa_mobile");
            if(empty($usarow)){
                $usarow["usa_user_name"] = $usarow["usa_mobile"] = "";
            }
            $g_valuedata[$k]["usa_user_name"] = $usarow["usa_user_name"];
            $g_valuedata[$k]["usa_mobile"] = $usarow["usa_mobile"];

        }

        return view(
            'deliverylist',
            [
                "g_value" => $g_valuedata, /*查询结果*/
                "page" => $g_value['page'], /*分页和html代码*/
                "lastPage" => $g_value['lastPage'], /*总页数*/
                "currentPage" => $g_value['currentPage'], /*当前页码*/
                "total" => $g_value['total'], /*总条数*/
                "listRows" => $g_value['listRows'], /*每页显示条数*/
                "o_shipping_status" => $o_shipping_status,
            ]
        );
    }

    /**
     * 作者：李鑫
     * 时间：2018-04-21
     * 功能：订单发货表
     */
    public function deliverygoods($id)
    {


//        $uid = 134;
//        $result = separatePushAndroid("order_".$id,$uid,"您的订单已发货","");
//        dump($result['ret']=='SUCCESS');exit();

        $o_value = $this->o->getRow(['o_id' => $id]); /*单条订单*/
//        $usa_value = $this->usa->getRow(['usa_id' => $o_value['usa_id']]); /*订单收货地址*/
        if ($this->request->isPost()) {
            $info = $this->request->only(["invoice_no","manager_note","log_ss_id"], "post");

            $sr_id =$this->sm_info["sr_id"];
            $u_id = $this->sr->getOne(["sr_id"=>$sr_id],"u_id");


            $rule = array(
                "invoice_no" => 'require',

            );
            $msg = array(
                "invoice_no.require" => '配送单号不能为空!~',

            );
            $data = verify($info, $rule, $msg);
            if (intval($data['code']) === 1) {
                $usaRow=$this->usa->getRow(["usa_id"=>$o_value["usa_id"]]) ;

                $infodata["o_id"] = $oadata["o_id"] = $o_value["o_id"];
                $infodata["o_sn"] = $o_value["o_sn"];
                $infodata["u_id"] = $o_value["u_id"];
                $infodata["ss_id"] = $o_value["ss_id"];
                $infodata["manager_id"]  = $oadata["oa_user"] = $u_id;
                $infodata["consignee"] = $usaRow["usa_user_name"];
                $infodata["mobile"] = $usaRow["usa_mobile"];
                $infodata["province_id"] = $usaRow["usa_province"];
                $infodata["city_id"] = $usaRow["usa_city"];
                $infodata["area_id"] = $usaRow["usa_district"];
                $infodata["address"] = $usaRow["usa_address"];

                $infodata["shipping_name"] =$o_value["o_shipping_name"];
                $infodata["shipping_price"] =$o_value["o_shipping_price"];
                $infodata["invoice_no"] = $oadata["oa_note"] = $info["manager_note"];
                $infodata["manager_note"]=$info["manager_note"];
                $infodata["create_time"] = $oadata["oa_time"] = time();

                //添加订单发货表
                $this->od->save($infodata);
                //添加订单记录表
                $oadata["oa_order_status"] = 1;
                $oadata["oa_pay_status"] = 1;
//                $oadata["oa_shipping_status"] = 1;
                $oadata["oa_status_desc"] = "订单已发货";
                $oadata["oa_role"]=1;
                $oadata["ss_id"]=$this->sm_info['ss_id'];
                $this->oa->save($oadata);
                //修改订单发货状态
                $ret = $this->o->save(["o_shipping_code"=>$info["invoice_no"],"o_shipping_status"=>2,"log_ss_id"=>$info["log_ss_id"]],["o_id"=>$id]);
                if ($ret > 0) {
                    //发送android推送
                    $ordList = $this->o->getList(["o_id"=>$id],"o_id,ss_id,o_payable_price,o_sn,o_add_time,usa_id,log_id,log_ss_id,o_shipping_code,o_shipping_name,o_status,o_pay_status,o_shipping_status");
                    foreach ($ordList as $k=>$row){
                        $ordList[$k]["o_add_time"] = date("Y-m-d",$row["o_add_time"]);
                        $ordList[$k]["good_list"] = $this->og->getList(["o_id"=>$id],"g_id,sp_id,goods_buy_num,g_current_price,member_goods_price,o_gnames as g_name,o_imgpath as g_show_img_path,o_spname as sp_name");
                    }
                    separatePushAndroid("sendorder_".$id,$o_value["u_id"],"您的订单已发货","",json_encode($ordList));

                    $this->success("操作成功!",url('seller/Ordermanage/deliverylist'));
                } else {
                    $this->error("网络请求失败，请稍后重试!");
                }
            }else{
                $this->error($data['msg']);
                exit();
            }
        }else{
            $join = [
                ["goods g", "og.g_id = g.g_id"],
            ];
            $where = ["og.o_id"=>$id];
            $gsplist = $this->og->joinGetList($join,"og",$where,[],"og.og_meet as act_meet,og.og_reduction as act_reduction,og.ga_id,g.g_weight,og.g_id,goods_sn,g_show_img_path,goods_name,goods_buy_num,og.sp_id,og.member_goods_price");

            /*总价*/
            $o_total_amount = 0;
            $totalweight = 0;

            foreach ($gsplist as $k=>$row){
                /*重量*/
                $totalweight+=$row["g_weight"];
                /*規格名字*/
                $gsplist[$k]["sp_name"] = $this->sp->getOne(["id"=>$row["sp_id"]],"sp_name");
                $gsplist[$k]["gsp_price"] = $this->gsp->getOne(["sp_id"=>$row["sp_id"],"g_id"=>$row["g_id"]],"gsp_price");

                /*检查是否有活动  select * from gjt_activity where find_in_set('25',g_ids);  find_in_set('".$row["g_id"]."',g_ids) and*/
                //$actfind = $this->act->getRow("find_in_set('".$row["g_id"]."_".$row["sp_id"]."',g_ids) and UNIX_TIMESTAMP(now()) > act_start_time and UNIX_TIMESTAMP(now()) < act_end_time and act_isstop = 0");


                $gsplist[$k]["g_discount_price"] = $row["member_goods_price"];
                $gsplist[$k]["act_type"] = $this->act->getOne(["act_id"=>$row["ga_id"]],"act_type");
                $price = ($gsplist[$k]["g_discount_price"]==0)?$gsplist[$k]["gsp_price"]:$gsplist[$k]["g_discount_price"];
                $gsplist[$k]["xiaoji"] = $gsplist[$k]["goods_buy_num"]*$price;
                $o_total_amount +=$gsplist[$k]["xiaoji"];
            }

            $loglist = $this->log->getList(["ss_id"=>$o_value["ss_id"]]);

            return view(
                'deliverygoods',
                [
                    "o_value" => $o_value, /*查询结果*/
                    "gsplist" => $gsplist, /*查询结果*/
                    "loglist"=>$loglist
                ]
            );
        }
    }


    public function recruitmentEdit(){

    }

    /**
     * [退货列表]
     * @date 2018-04-25
     * @author 王牧田
     */
    public function returnlist(){
        $ss_id = $this->sm_info['ss_id'];
        $o_id = $this->o->getOnes(["ss_id"=>$ss_id],"o_id");
        //"o_id"=>["in",implode(",",$o_id)]
        $join = [
            ["gjt_order o", "og.o_id = o.o_id","left"],
        ];
        $g_value = $this->or->joinGetAll($join,"og",["og.ss_id"=>$ss_id]);

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
        $returnprice = $oRow["o_payable_price"] - $oRow["o_logistics"];

        if ($this->request->isPost()) {
            //退款/换货完成
            $info = $this->request->only(["returnprice","or_remarks","or_address","file","or_examine","or_examine_content"], "post");

            $oa_status_desc = "";
            switch ($info["file"]){
                case "or_status":
                    if($info["or_examine"] != -1) {
                        $oa_status_desc = "商家确认完成";
                        //退款完成的状态
                        $this->or->save(["or_remarks" => $info["or_remarks"], "or_address" => $info["or_address"], "or_status" => 1], ["or_id" => $or_id]);
                    }else{
                        $oa_status_desc = "商家未确认完成";
                    }
                    break;
                case "or_receiving":
                    if($info["or_examine"] != -1){
                        $oa_status_desc="商家已确认收货";
                        $this->or->save(["or_receiving"=>1],["or_id"=>$or_id]);
                    }else{

                        $oa_status_desc="商家未确认收货";
                    }
                    break;
                case "or_refund":
                    $oa_status_desc = "商家退款";
                        
                    

                    if($orRow["or_type"] == 0){
                        if (empty($orRow["or_payaccount"]) || empty($orRow["or_payrealname"])) {
                        $this->error("缺少支付宝账号或者支付宝真实姓名!");
                        exit();
                        }
                        $this->or->save(["or_refund" => 1], ["or_id" => $or_id]);
                    }else{
                        $this->or->save(["or_refund" => 2], ["or_id" => $or_id]);
                    }

                    //修改订单退款状态 最后退换货成功后
                    $this->o->save(["o_status" => 6], ["o_id" => $orRow["o_id"]]);
                    break;
            }
            $data = $info;
            unset($data['file']);
            $data['or_examine'] = (isset($info['or_examine'])?$info['or_examine']:'');
            $data['or_examine_content'] = (isset($info['or_examine_content'])?$info['or_examine_content']:'');
            $this->or->save($data,["or_id"=>$or_id]);


            //订单日志
            $ogetRow = $this->o->getRow(["o_id"=>$orRow["o_id"]]);
            $sr_id =$this->sm_info["sr_id"];
            $u_id = $this->sr->getOne(["sr_id"=>$sr_id],"u_id");
            $oadata["o_id"]=$orRow["o_id"];
            $oadata["oa_user"] = $u_id;
            $oadata["oa_order_status"] = $ogetRow["o_status"];
            $oadata["oa_pay_status"] = $ogetRow["o_pay_status"];
            $oadata["oa_shipping_status"] = $ogetRow["o_shipping_status"];
            $oadata["oa_note"] =$info["or_remarks"];
            $oadata["oa_status_desc"] = $oa_status_desc;
            $oadata["oa_time"] = time();
            $oadata["oa_role"] = 1;
            $oadata["ss_id"]=$this->sm_info['ss_id'];
            $result = $this->oa->save($oadata);


            if ($result > 0) {
                if($info["file"] == "or_status"){
                    //发送android推送
                    $ordList = $this->o->getList(["o_id"=>$orRow['o_id']],"o_id,ss_id,o_payable_price,o_sn,o_add_time,usa_id,log_id,log_ss_id,o_shipping_code,o_shipping_name,o_status,o_pay_status,o_shipping_status");
                    foreach ($ordList as $k=>$row){
                        $ordList[$k]["o_add_time"] = date("Y-m-d",$row["o_add_time"]);
                        $ordList[$k]["good_list"] = $this->og->getList(["o_id"=>$orRow['o_id']],"g_id,sp_id,goods_buy_num,g_current_price,member_goods_price,o_gnames as g_name,o_imgpath as g_show_img_path,o_spname as sp_name");
                    }
                    if ($data['or_examine']=='-1') {
                        separatePushAndroid("returnorderok_".$or_id,$u_id,"您的订单已被拒绝","",json_encode($ordList));
                    }else{
                        separatePushAndroid("returnorderok_".$or_id,$u_id,"您的订单已确认","",json_encode($ordList));
                    }
                }
                $this->success("操作成功!",url('seller/Ordermanage/returnlist'));
            } else {
                $this->error("网络请求失败，请稍后重试!");
            }
        }else {
            $join = [
                ["goods g", "og.g_id = g.g_id"],
            ];
            $where = ["og.o_id" => $orRow['o_id']];
            $gsplist = $this->og->joinGetList($join, "og", $where, [], "og.og_meet as act_meet,og.og_reduction as act_reduction,og.ga_id,g.g_weight,og.g_id,goods_sn,g_show_img_path,goods_name,goods_buy_num,og.sp_id,og.member_goods_price");

            /*总价*/
            $o_total_amount = 0;
            $totalweight = 0;

            foreach ($gsplist as $k => $row) {


                /*規格名字*/
                $gsplist[$k]["sp_name"] = $this->sp->getOne(["id" => $row["sp_id"]], "sp_name");

                $gspRow = $this->gsp->getRow(["sp_id" => $row["sp_id"], "g_id" => $row["g_id"]]);

                $gsplist[$k]["gsp_price"] = isset($gspRow['gsp_price']) ? $gspRow["gsp_price"] : 0;
                /*重量*/
                $totalweight += empty($gspRow["gsp_weight"]) ? 0 : $gspRow["gsp_weight"];
                /*检查是否有活动  select * from gjt_activity where find_in_set('25',g_ids);  find_in_set('".$row["g_id"]."',g_ids) and*/
                $gsplist[$k]["g_discount_price"] = $row["member_goods_price"];
                $gsplist[$k]["act_type"] = $this->act->getOne(["act_id" => $row["ga_id"]], "act_type");
                $price = ($row["member_goods_price"] == 0) ? $gsplist[$k]["gsp_price"] : $row["member_goods_price"];
                $gsplist[$k]["xiaoji"] = $gsplist[$k]["goods_buy_num"] * $price;
                $o_total_amount += $gsplist[$k]["xiaoji"];

            }


            $this->assign("returnprice", $returnprice);
            $orstatus = "";
            if ($orRow["or_examine"] == "-1" && $orRow["or_status"] == 0) {
                $orstatus = "or_status";
            } else if ($orRow["or_examine"] == "-1" && $orRow["or_receiving"] == 0) {
                $orstatus = "or_receiving";
            }

            $this->assign("orstatus", $orstatus);
            $this->assign("oRow", $oRow);
            $this->assign("orRow", $orRow);
            $this->assign("gsplist", $gsplist);
            return view();
        }
    }



    /**
     * [删除退款记录]
     * @author 王牧田
     * @date 2018-04-25
     */
    public function delorderReturn(){
        $sm_info = $this->request->only(["or_id"], "post");

        $result = $this->or->del(["or_id"=>$sm_info["or_id"]]);
        if(intval($result)>0){
            return json(format());
        }else{
            return json(format('', '-1', "网络请求失败，请稍后重试"));
        }
    }

    /**
     * [订单日志]
     * @author 王牧田
     * @date 2018-04-25
     */
    public function orderlog(){
        $ss_id = $this->sm_info['ss_id'];

        $g_value = $this->oa->getAll(["ss_id"=>$ss_id],[],["oa_id"=>"desc"],0,"*","o_id");

        $gvaluedata = $g_value["data"];
        foreach ($gvaluedata as $k=>$row){
            $gvaluedata[$k]["u_name"]=$this->u->getOne(["u_id"=>$row["oa_user"]],"u_name");
        }

        return view(
            'orderlog',
            [
                "g_value" => $gvaluedata, /*查询结果*/
                "page" => $g_value['page'], /*分页和html代码*/
                "lastPage" => $g_value['lastPage'], /*总页数*/
                "currentPage" => $g_value['currentPage'], /*当前页码*/
                "total" => $g_value['total'], /*总条数*/
                "listRows" => $g_value['listRows'], /*每页显示条数*/
            ]
        );
    }


    /**
     * [修改订单]
     * @author 王牧田
     * @date 2018-05-04
     */
    public function editorder($order_id){
        if ($this->request->isPost()) {
            $info = $this->request->only(["usa_user_name", "usa_mobile","usa_province","usa_city","usa_district","usa_address","log_id","o_invoice_title","o_admin_note","old_goods"], "post");
            $rule = array(
                "usa_user_name" => 'require',
                "usa_mobile" => 'require',
                "usa_province" => 'require',
                "usa_city" => 'require',
                "usa_district" => 'require',
                "usa_address" => 'require',
                "log_id" => 'require',
                "o_invoice_title" => 'require',
                "old_goods" => 'require',
            );
            $msg = array(
                "usa_user_name.require" => '收货人不能为空!~',
                "usa_mobile.require" => '手机不能为空!~',
                "usa_province.require" => '收货省不能为空!~',
                "usa_city.require" => '收货市不能为空!~',
                "usa_district.require" => '收货区不能为空!~',
                "usa_address.require" => '收货地址不能为空!~',
                "log_id.require" => '收货物流不能为空!~',
                "o_invoice_title.require" => '发票抬头不能为空!~',
                "old_goods.require" => '商品列表不能为空!~',
            );
            $data = verify($info, $rule, $msg);

            if (intval($data['code']) === 1) {
                $old_good= $info["old_goods"];
                unset($info["old_goods"]);
                $this->og->del(["o_id"=>$order_id]);
                foreach ($old_good as $k=>$row){
                    $kexp = explode("_",$k);
                    $g_id = $kexp[0];
                    $sp_id = $kexp[1];
                    $actprice = getActivityPrice($g_id,$sp_id);

                    $gRow = $this->g->getRow(["g_id"=>$g_id],"g_name,g_sn"); //goods_name goods_sn
                    $ogdata=[
                        "o_id"=>$order_id,
                        "g_id"=>$g_id,
                        "sp_id"=>$sp_id,
                        "goods_name"=>$gRow["g_name"],
                        "goods_sn"=>$gRow["g_sn"],
                        "goods_buy_num"=>$row,
                        "ga_id"=>$actprice["act_id"],
                        "g_current_price"=>$actprice["gsp_price"],
                        "member_goods_price"=>$actprice["g_discount_price"],
                        "og_meet" => $actprice["act_meet"],
                        "og_reduction" => $actprice["act_reduction"]
                    ];
                    $this->og->save($ogdata);
                }

                //添加操作记录
                $sr_id = $this->sm_info["sr_id"];
                $u_id = $this->sr->getOne(["sr_id"=>$sr_id],"u_id");
                $result = $this->o->save($info,["o_id"=>$order_id]);

                $orow = $this->o->getRow(["o_id"=>$order_id]);



                if(intval($result)>0){
                    $res = [
                        "o_id" =>$order_id,
                        "ss_id" =>$this->sm_info['ss_id'],
                        "oa_user" =>$u_id,
                        "oa_order_status"=>$orow["o_status"],
                        "oa_pay_status"=>$orow["o_pay_status"],
                        "oa_note"=>$info['o_admin_note'],
                        "oa_time"=>time(),
                        "oa_status_desc"=>"修改订单",
                        "oa_role" => 1,
                    ];
                    $this->oa->save($res);
                    return json(format());
                }else{
                    return json(format('', '-1', "网络请求失败，请稍后重试"));
                }
            }else{
                return json(format('', '-1', $data['msg']));

            }
        }else{
            $oRow = $this->o->getRow(["o_id"=>$order_id]);
            $oRow["o_goods_price"] = number_format($oRow["o_goods_price"],2);
            $oRow["o_shipping_price"] = number_format($oRow["o_shipping_price"],2);
            $oRow["o_goods_total"] = number_format(($oRow["o_goods_price"] + $oRow["o_shipping_price"]),2);
            $rlist = $this->r->getList(["r_parent_id"=>1]);
            $plist = $this->r->getList(["r_parent_id"=>$oRow["usa_province"]]);
            $clist = $this->r->getList(["r_parent_id"=>$oRow["usa_city"]]);
            $ss_id = $this->sm_info['ss_id'];
            $loglist = $this->log->getList(["ss_id"=>$ss_id]);


            $join = [
                ["goods g", "og.g_id = g.g_id"],
            ];
            $where = ["og.o_id"=>$order_id];
            $gsplist = $this->og->joinGetList($join,"og",$where,[],"og.og_meet as act_meet,og.og_reduction as act_reduction,og.ga_id,g.g_weight,og.g_id,goods_sn,g_show_img_path,goods_name,goods_buy_num,og.sp_id,og.member_goods_price");

            foreach ($gsplist as $k=>$row){
                /*規格名字*/
                $gsplist[$k]["sp_name"] = $this->sp->getOne(["id"=>$row["sp_id"]],"sp_name");
                $gsplist[$k]["gsp_price"] = $this->gsp->getOne(["sp_id"=>$row["sp_id"],"g_id"=>$row["g_id"]],"gsp_price");
                $gsplist[$k]["g_discount_price"] = $row["member_goods_price"];
                $gsplist[$k]["act_type"] = $this->act->getOne(["act_id"=>$row["ga_id"]],"act_type");
                $gsplist[$k]["xiaoji"] = $gsplist[$k]["goods_buy_num"]*$row["member_goods_price"];

            }

            $this->assign("gsplist",$gsplist);

            $ssRowby = $this->ss->getRow("ss_id = $ss_id and FIND_IN_SET(1,guarantee)");

            if(!empty($ssRowby)){

                array_unshift($loglist, ["log_id"=>0,"log_name"=>"包邮"]);
            }

            $this->assign("loglist",$loglist);
            $this->assign("rlist",$rlist);
            $this->assign("plist",$plist);
            $this->assign("clist",$clist);
            $this->assign("oRow",$oRow);
            return view();
        }
    }

    /**
     * 选择商品
     * @author 王牧田
     * @date 2018-05-04
     */
    public function selectGoods(){
        $page=input("page",1);
        $limit = 10;
        $join1 = [
            ["goods g", "gsp.g_id = g.g_id"],
        ];
        $join2 = [
            ["specifications sp", "gsp.sp_id = sp.id"],
        ];

        $ss_id = $this->sm_info['ss_id'];
        $where["g.ss_id"]=$ss_id;
        $list = Db::name("goods_specifications")
            ->alias("gsp")
            ->join($join1)
            ->join($join2)
            ->where($where)
            ->field("g.g_id,gsp.sp_id,g.g_name,gsp.gsp_price,gsp.gsp_inventory,sp.sp_name")
            ->limit(($page-1)*$limit,$limit)
            ->select();

        $listcount = Db::name("goods_specifications")
            ->alias("gsp")
            ->join($join1)
            ->join($join2)
            ->where($where)
            ->count();

        foreach ($list as $k=>$row){
            $price = getActivityPrice($row["g_id"],$row["sp_id"]);
            $list[$k] = array_merge($row,$price);
        }


        return json(array("list"=>$list,"pageCount"=>ceil($listcount/$limit)));
    }

     /**
     * [发票查看]
     * @author 李鑫
     * @param $u_id
     * @return \think\response\View
     * @date 2018-05-10
     */
    public function fapiao($order_id){

        $ilist = $this->o->getRow(["o_id"=>$order_id],"o_invoice");
        $ilist['o_invoice'] = json_decode($ilist['o_invoice'],true);

        // if(!isset($ilist['o_invoice'][0]['i_invtype'])){
        //     $ilist['o_invoice'][0]['i_invtype'] = "2";
        // }
        // dump($ilist['o_invoice']);die();

        return view(
            "fapiao",
            [
               "list" => $ilist["o_invoice"], /*查询结果*/
            ]
        );
    }


    /**
     * [订单查看]
     * @author 王牧田
     * @param $order_id
     * @return \think\response\View
     * @date 2018-04-24
     */
    public function saverdetail($order_id){

        $oRow=$this->o->getRow(["o_sn"=>$order_id]);

        if ($oRow['ss_id'] != $this->sm_info['ss_id']) {
           $this->error("不是该商户订单无法查看！~");
        }
        if (!empty($oRow)) {
        
        /**操作记录**/
        $oalist = $this->oa->getList(["o_id"=>$order_id,"ss_id"=>$this->sm_info['ss_id']]);
        foreach ($oalist as $k=>$row){
            $ulist = $this->u->getRow(["u_id"=>$row["oa_user"]],"u_name,u_mobile");
            if(empty($ulist)){
                $ulist["u_name"] = $ulist["u_mobile"] = "";
            }
            $oalist[$k] = array_merge($row,$ulist);
        }

        $userinfo = $this->u->getRow(["u_id"=>$oRow["u_id"]]);
//        $usainfo = $this->usa->getRow(["usa_id"=>$oRow["usa_id"]]);
        $reginone = $this->r->getOnes(["r_id"=>["in",$oRow["usa_province"].",".$oRow["usa_city"].",".$oRow["usa_district"]]],"r_name");

        $join = [
            ["goods g", "og.g_id = g.g_id"],
        ];
        $where = ["og.o_id"=>$oRow["o_id"]];
        $gsplist = $this->og->joinGetList($join,"og",$where,[],"og.og_meet as act_meet,og.og_reduction as act_reduction,og.ga_id,g.g_weight,og.g_id,goods_sn,g_show_img_path,goods_name,goods_buy_num,og.sp_id,og.member_goods_price");

        /*总价*/
        $o_total_amount = 0;
        $totalweight = 0;

        foreach ($gsplist as $k=>$row){



            /*規格名字*/
            $gsplist[$k]["sp_name"] = $this->sp->getOne(["id"=>$row["sp_id"]],"sp_name");

            $gspRow = $this->gsp->getRow(["sp_id"=>$row["sp_id"],"g_id"=>$row["g_id"]]);
            //$gsplist[$k]["gsp_price"] = $this->gsp->getOne(["sp_id"=>$row["sp_id"],"g_id"=>$row["g_id"]],"gsp_price");
            $gsplist[$k]["gsp_price"] = $gspRow['gsp_price'];
            /*重量*/
            $totalweight+=empty($gspRow["gsp_weight"])?0:$gspRow["gsp_weight"];


            /*检查是否有活动  select * from gjt_activity where find_in_set('25',g_ids);  find_in_set('".$row["g_id"]."',g_ids) and*/

            $gsplist[$k]["g_discount_price"] = $row["member_goods_price"];
            $gsplist[$k]["act_type"] = $this->act->getOne(["act_id"=>$row["ga_id"]],"act_type");
            $price = ($row["member_goods_price"]==0)?$gsplist[$k]["gsp_price"]:$row["member_goods_price"];
            $gsplist[$k]["xiaoji"] = $gsplist[$k]["goods_buy_num"]*$price;
            $o_total_amount +=$gsplist[$k]["xiaoji"];
        }


        ini_set('display_errors', 'on');
        $logistics = getWeightMoney($oRow["log_id"],$oRow["usa_id"],$totalweight);

        $oRow["o_goods_price"] = number_format($oRow["o_goods_price"],2);
        $oRow["o_logistics"] = number_format($oRow["o_logistics"],2);
        $oRow["o_payable_price"] = number_format($oRow["o_payable_price"],2);
        $oRow["o_diffvalue"] = number_format($oRow["o_diffvalue"],2);
        $oRow["o_payable_price"] = $oRow["o_payable_price"];
        $oRow["o_shipping_price"] = number_format($oRow["o_shipping_price"],2);

        $log_name= $this->log->getOne(["log_id"=>$oRow["log_ss_id"]],"log_name");
        if(!isset($userinfo["u_name"])){
            $userinfo["u_name"] = $userinfo['u_mobile'] = "";
        }
        $this->assign("log_name",$log_name);
        $this->assign("logistics",$logistics);
        $this->assign("o_total_amount",number_format($o_total_amount,2));
        $this->assign("order_id",$order_id);
        $this->assign("oalist",$oalist);
        $this->assign("gsplist",$gsplist);
        $this->assign("reginone",$reginone);
        $this->assign("usainfo",$oRow);
        $this->assign("userinfo",$userinfo);
        $this->assign("oRow",$oRow);
        return view();
        }else{
            $this->error("暂无订单！");
        }
    }

     /**
     * [订单查看]
     * @author 王牧田
     * @param $order_id
     * @return \think\response\View
     * @date 2018-04-24
     */
    public function saverdetails($order_id){

        $oRow=$this->o->getRow(["o_sn"=>$order_id]);
      
        if (!empty($oRow)) {
        
        /**操作记录**/
        $oalist = $this->oa->getList(["o_id"=>$order_id,"ss_id"=>$this->sm_info['ss_id']]);
        foreach ($oalist as $k=>$row){
            $ulist = $this->u->getRow(["u_id"=>$row["oa_user"]],"u_name,u_mobile");
            if(empty($ulist)){
                $ulist["u_name"] = $ulist["u_mobile"] = "";
            }
            $oalist[$k] = array_merge($row,$ulist);
        }

        $userinfo = $this->u->getRow(["u_id"=>$oRow["u_id"]]);
//        $usainfo = $this->usa->getRow(["usa_id"=>$oRow["usa_id"]]);
        $reginone = $this->r->getOnes(["r_id"=>["in",$oRow["usa_province"].",".$oRow["usa_city"].",".$oRow["usa_district"]]],"r_name");

        $join = [
            ["goods g", "og.g_id = g.g_id"],
        ];
        $where = ["og.o_id"=>$oRow["o_id"]];
        $gsplist = $this->og->joinGetList($join,"og",$where,[],"og.og_meet as act_meet,og.og_reduction as act_reduction,og.ga_id,g.g_weight,og.g_id,goods_sn,g_show_img_path,goods_name,goods_buy_num,og.sp_id,og.member_goods_price");
        /*总价*/
        $o_total_amount = 0;
        $totalweight = 0;

        foreach ($gsplist as $k=>$row){
            /*重量*/
            $totalweight+=$row["g_weight"];
            /*規格名字*/
            $gsplist[$k]["sp_name"] = $this->sp->getOne(["id"=>$row["sp_id"]],"sp_name");
            $gsplist[$k]["gsp_price"] = $this->gsp->getOne(["sp_id"=>$row["sp_id"],"g_id"=>$row["g_id"]],"gsp_price");

            /*检查是否有活动  select * from gjt_activity where find_in_set('25',g_ids);  find_in_set('".$row["g_id"]."',g_ids) and*/

            $gsplist[$k]["g_discount_price"] = $row["member_goods_price"];
            $gsplist[$k]["act_type"] = $this->act->getOne(["act_id"=>$row["ga_id"]],"act_type");
            $price = ($row["member_goods_price"]==0)?$gsplist[$k]["gsp_price"]:$row["member_goods_price"];
            $gsplist[$k]["xiaoji"] = $gsplist[$k]["goods_buy_num"]*$price;
            $o_total_amount +=$gsplist[$k]["xiaoji"];
        }


        ini_set('display_errors', 'on');
        $logistics = getWeightMoney($oRow["log_id"],$oRow["usa_id"],$totalweight);
        $oRow["o_goods_price"] = number_format($oRow["o_goods_price"],2);
        $oRow["o_logistics"] = number_format($oRow["o_logistics"],2);
        $oRow["o_payable_price"] = number_format($oRow["o_payable_price"],2);
        $oRow["o_diffvalue"] = number_format($oRow["o_diffvalue"],2);
        $oRow["o_payable_price"] = $oRow["o_payable_price"];
        $oRow["o_shipping_price"] = number_format($oRow["o_shipping_price"],2);

        $log_name= $this->log->getOne(["log_id"=>$oRow["log_ss_id"]],"log_name");
        if(!isset($userinfo["u_name"])){
            $userinfo["u_name"] = $userinfo['u_mobile'] = "";
        }
        $this->assign("log_name",$log_name);
        $this->assign("logistics",$logistics);
        $this->assign("o_total_amount",number_format($o_total_amount,2));
        $this->assign("order_id",$order_id);
        $this->assign("oalist",$oalist);
        $this->assign("gsplist",$gsplist);
        $this->assign("reginone",$reginone);
        $this->assign("usainfo",$oRow);
        $this->assign("userinfo",$userinfo);
        $this->assign("oRow",$oRow);
        return view();
        }else{
            $this->error("暂无订单！");
        }
    }


   


   


    


    /**
     * 获取物流信息
     * @author jim
     * @param String $com 物流公司编号
     * @param String $nu  物流单号，快递单号
     * @param String $show 0表示返回json,1表示返回xml
     * @param String $muti 0表示多行完整信息,1表示一行信息
     * @param String $order desc asc 按时间降序，升序
     * @return Array
     * status
     * 0:物流单号暂无结果；
     * 3:在途，快递处于运输过程中；
     * 4:揽件，快递已被快递公司揽收并产生了第一条信息；
     * 5:疑难，快递邮寄过程中出现问题；
     * 6:签收，收件人已签收；
     * 7:退签，快递因用户拒签、超区等原因退回，而且发件人已经签收；
     * 8:派件，快递员正在同城派件；
     * 9:退回，货物处于退回发件人途中；
     *
     */
    protected function getExpress($com,$nu,$show = '0',$muti = '0',$order = 'desc') { //子类以及子类的子类可以访问


        $id         = trim(Config::get('kuaidi_key'));
        $kuaidi_api = trim(Config::get('kuaidi_api'));

        $url = $kuaidi_api.'?id='.$id.'&com='.$com.'&nu='.$nu.'&show='.$show.'&muti='.$muti.'&order='.$order;
        $result = json_decode(file_get_contents($url),true);
        return $result;
    }


    /**
     * 作者：李鑫
     * 时间：2018-07-18
     * 功能：用户评论
     */
    public function goodsEvaluationlist()
    {

        $pageParam = [];
        $where["o.o_status"]='5';
        $join = [
            ["order_evaluation oe", "o.o_id = oe.o_id"],
        ];
//        $where["o.ss_id"]=$this->sm_info['ss_id'];
        $group = "o.o_id";
        $list = $this->o->joinGetAll($join,"o",$where,$pageParam,[],[],"o.*,oe.*",$group);

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
                $list['data'][$k]["oe_add_time"] = date("Y-m-d H:i",$row["oe_add_time"]);
                $list['data'][$k]["oe_count"] = $this->oe->getCount(['o_id'=>$row['o_id'],'u_id'=>$row["u_id"]]);
            }
        }

        return view(
            "goodsEvaluationlist",
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
     * 作者：李鑫
     * 时间：2018-07-18
     * 功能：用户评论详情
     */
    public function goodsevaluationshow($order_id)
    {
        $oRow=$this->o->getRow(["o_id"=>$order_id]);

        $oeRow = $this->oe->getRow(["o_id"=>$order_id]);


        $userinfo = $this->u->getRow(["u_id"=>$oRow["u_id"]]);
//        $usainfo = $this->usa->getRow(["usa_id"=>$oRow["usa_id"]]);
        $reginone = $this->r->getOnes(["r_id"=>["in",$oRow["usa_province"].",".$oRow["usa_city"].",".$oRow["usa_district"]]],"r_name");

        $join = [
            ["goods g", "og.g_id = g.g_id"],
        ];
        $where = ["og.o_id"=>$order_id];
        $gsplist = $this->og->joinGetList($join,"og",$where,[],"og.og_meet as act_meet,og.og_reduction as act_reduction,og.ga_id,g.g_weight,og.g_id,goods_sn,g_show_img_path,goods_name,goods_buy_num,og.sp_id,og.member_goods_price");

        /*总价*/
        $o_total_amount = 0;
        $totalweight = 0;

        foreach ($gsplist as $k=>$row){

            /*規格名字*/
            $gsplist[$k]["sp_name"] = $this->sp->getOne(["id"=>$row["sp_id"]],"sp_name");

            $gspRow = $this->gsp->getRow(["sp_id"=>$row["sp_id"],"g_id"=>$row["g_id"]]);
//            $gsplist[$k]["gsp_price"] = $this->gsp->getOne(["sp_id"=>$row["sp_id"],"g_id"=>$row["g_id"]],"gsp_price");
            $gsplist[$k]["gsp_price"] = $gspRow["gsp_price"];
            /*重量*/
            $totalweight+=empty($gspRow["gsp_weight"])?0:$gspRow["gsp_weight"];
            /*检查是否有活动  select * from gjt_activity where find_in_set('25',g_ids);  find_in_set('".$row["g_id"]."',g_ids) and*/
            $gsplist[$k]["g_discount_price"] = $row["member_goods_price"];
            $gsplist[$k]["act_type"] = $this->act->getOne(["act_id"=>$row["ga_id"]],"act_type");
            $price = ($row["member_goods_price"]==0)?$gsplist[$k]["gsp_price"]:$row["member_goods_price"];
            $gsplist[$k]["xiaoji"] = $gsplist[$k]["goods_buy_num"]*$price;
            $o_total_amount +=$gsplist[$k]["xiaoji"];
        }


        ini_set('display_errors', 'on');
        $logistics = getWeightMoney($oRow["log_id"],$oRow["usa_id"],$totalweight);
        $oRow["o_goods_price"] = number_format($oRow["o_goods_price"],2);
        $oRow["o_logistics"] = number_format($oRow["o_logistics"],2);
        $oRow["o_payable_price"] = number_format($oRow["o_payable_price"],2);
        $oRow["o_diffvalue"] = number_format($oRow["o_diffvalue"],2);
        $oRow["o_payable_price"] = $oRow["o_payable_price"];
        $oRow["o_shipping_price"] = number_format($oRow["o_shipping_price"],2);

        $log_name= $this->log->getOne(["log_id"=>$oRow["log_ss_id"]],"log_name");
        if(!isset($userinfo["u_name"])){
            $userinfo["u_name"] = $userinfo['u_mobile'] = "";
        }
        $oe_where['o_id'] = $order_id;
        //$oe_where['is_show'] = '1';
        $oe_where['is_zhui'] = '0';
        $oeevaluations = $this->oe->getList($oe_where,'oe_id,o_id,u_id,oe_quality_star,oe_logistics_star,oe_service_star,oe_content,oe_img1,oe_img2,oe_img3,oe_img4,oe_img5,oe_img6,s_content,is_zhui,oe_add_time');

        $oeevaluationse = array();
        foreach ($oeevaluations as $key => $val) {
            $val['oe_add_time']= date("Y-m-d",$val["oe_add_time"]);
            $userinfo =$this->u->getRow(["u_id"=>$val["u_id"]],"u_name,u_headimg");
            $val['u_name']= $userinfo['u_name'];
            $val['u_headimg']= IMG_URL.$userinfo['u_headimg'];
            $val['o_spname']=$this->og->getOne(["o_id"=>$val["o_id"]],"o_spname");
            $val['oe_img']=array('0'=>array('dat'=>$val['oe_img1']),  
                '1'=>array('dat'=>$val['oe_img2']),  
                '2'=>array('dat'=>$val['oe_img3']),
                '3'=>array('dat'=>$val['oe_img4']),
                '4'=>array('dat'=>$val['oe_img5']),
                '5'=>array('dat'=>$val['oe_img6']),
            );
            for ($i=0; $i < 6; $i++) { 
                if (empty($val['oe_img'][$i]['dat'])) {
                    unset($val['oe_img'][$i]);
                }
            }
            if (empty($val['oe_img']['0']['dat'])) {
                $val['oe_img']=array();
            }
            unset($val['oe_img1']);
            unset($val['oe_img2']);
            unset($val['oe_img3']);
            unset($val['oe_img4']);
            unset($val['oe_img5']);
            unset($val['oe_img6']);
            sort($val['oe_img']);
            foreach ($val['oe_img'] as $keys => $values) {
                $val['oe_img'][$keys]['dat']=$values['dat'];
            }

            $oeevaluationse[$val['o_id']] = $val;
            

            if ($val['is_zhui']=='0') {
                $oes_where['o_id'] = $val['o_id'];
                //$oes_where['is_show'] = '1';
                $oes_where['is_zhui'] = '1';
                $oeevaluationss = $this->oe->getRow($oes_where,'oe_id,o_id,u_id,oe_content,oe_img1,oe_img2,oe_img3,oe_img4,oe_img5,oe_img6,s_content,is_zhui,oe_add_time');

                if (!empty($oeevaluationss)) {
                    $oeevaluationss['oe_add_time']= date("Y-m-d",$oeevaluationss["oe_add_time"]);
                    $oeevaluationss['oe_img']=array('0'=>array('dat'=>$oeevaluationss['oe_img1']),  
                          '1'=>array('dat'=>$oeevaluationss['oe_img2']),  
                          '2'=>array('dat'=>$oeevaluationss['oe_img3']),
                          '3'=>array('dat'=>$oeevaluationss['oe_img4']),
                          '4'=>array('dat'=>$oeevaluationss['oe_img5']),
                          '5'=>array('dat'=>$oeevaluationss['oe_img6']),
                    );
                    for ($i=0; $i < 6; $i++) { 
                        if (empty($oeevaluationss['oe_img'][$i]['dat'])) {
                            unset($oeevaluationss['oe_img'][$i]);
                        }
                    }
                    if (empty($oeevaluationss['oe_img']['0']['dat'])) {
                        $oeevaluationss['oe_img']=array();
                    }
                    unset($oeevaluationss['oe_img1']);
                    unset($oeevaluationss['oe_img2']);
                    unset($oeevaluationss['oe_img3']);
                    unset($oeevaluationss['oe_img4']);
                    unset($oeevaluationss['oe_img5']);
                    unset($oeevaluationss['oe_img6']);
                    sort($oeevaluationss['oe_img']);
                    foreach ($oeevaluationss['oe_img'] as $keyse => $valuese) {
                        $oeevaluationss['oe_img'][$keyse]['dat']=$valuese['dat'];
                    }
                    $oeevaluationse[$val['o_id']]['zhuijia'] = $oeevaluationss;
                }else{
                    $oeevaluationse[$val['o_id']]['zhuijia'] =array();
                }
            }
        }

        sort($oeevaluationse);

        $this->assign("oeevaluationse",$oeevaluationse);
        $this->assign("log_name",$log_name);
        $this->assign("logistics",$logistics);
        $this->assign("o_total_amount",number_format($o_total_amount,2));
        $this->assign("order_id",$order_id);
        $this->assign("gsplist",$gsplist);
        $this->assign("reginone",$reginone);
        $this->assign("usainfo",$oRow);
        $this->assign("userinfo",$userinfo);
        $this->assign("oRow",$oRow);
        $this->assign("oeRow",$oeRow);
        return view();
            
    }

    /**
     * 评论回复
     * @author 李鑫
     * @date 2018-07-30
     */
    public function commentback(){
        $post['oe_id'] = $_POST["oe_id"];
        $posts["s_content"] = $_POST["content"];
        $result = $this->oe->save($posts,$post);
        if(intval($result)>0){
            return json(format('', '200', "回复成功！"));
        }else{
            return json(format('', '-1', "回复失败，请稍后重试!"));
        }
    }
}