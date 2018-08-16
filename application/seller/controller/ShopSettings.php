<?php
namespace app\seller\controller;

use model\SellerShop as ss;
use model\Region as r;
use model\SellerRegistered as sr;
use model\Artical as a;
use model\SellerManagers as sm;
use model\InstantMessaging as im;
use \model\ShopGoodsType as sgt;
use \model\Goods as g;
use \model\Activity as act;
use model\Complain as co;
use \model\Specifications as sp;
use \model\GoodsSpecifications as gsp;
use model\Guarantee as gu;
use model\Users as us;
use model\ManageGoodsCategory as mgc;
use think\Config;
use model\OrderReturn as _or;

/**
 * 作者：袁中旭
 * 时间：2017-10-30
 * 功能：店铺设置
 */

class ShopSettings extends Base
{
    /**
     * 作者：袁中旭
     * 时间：2017-10-30
     * 功能：继承父类构造函数
     */
    protected $ss;
    protected $r;
    protected $sr;
    protected $a;
    protected $sm;
    protected $im;
    protected $sgt;
    protected $g;
    protected $act;
    protected $co;
    protected $sp;
    protected $gsp;
    protected $gu;
    protected $us;
    protected $mgc;
    protected $or;

    public function __construct()
    {
        parent::__construct();
        /*商户店铺信息*/
        $this->ss = new ss();
        /*地区*/
        $this->r = new r();
        /*商户注册信息*/
        $this->sr = new sr();
        /* 总后台文章表 */
        $this->a = new a();
        /* 商户后台管理员 */
        $this->sm = new sm();
        /* 环信聊天朋友 */
        $this->im = new im();
        /* 商户后台商品系列 */
        $this->sgt = new sgt();
        /* 总后台商品信息 */
        $this->g = new g();
        /* 商铺活动 */
        $this->act = new act();
        /* 投诉 */
        $this->co = new co();
        /* 规格 */
        $this->sp = new sp();
        /*商品规格*/
        $this->gsp = new gsp();
        $this->gu = new gu();
        $this->us = new us();
        $this->mgc = new mgc();
        /*退货*/
        $this->or = new _or();
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-30
     * 功能：商户店铺设置显示
     */
    
    public function shopSettings($type = 1)
    {


        /* 通过查询总后台配置表查询出协议id */
        $protocol = getTableConfig('mbc_')['article']['check_in'];
        /* 获取入驻协议 */
        $article = $this->a->getRow(['a_id' => $protocol]);

        $ss_value = $this->ss->getRow(['ss_id' => $this->sm_info['ss_id']]);

        $sr_value = $this->sr->getRow(['sr_id' => $this->sm_info['sr_id']]);
        $r_value = $this->r->getList(['r_parent_id' => 1]);

        $act_value_all = $this->act->getAll(['ss_id' => $this->sm_info['ss_id']],[],["act_id"=>"desc"]);

        $act_value = $act_value_all["data"];

        foreach ($act_value as $k=>$val){

            $act_value[$k]["act_start_time"] = date("Y-m-d",$val["act_start_time"]);
            $act_value[$k]["act_end_time"] = date("Y-m-d",$val["act_end_time"]);

            if(time() < $val["act_start_time"] ){
                $act_value[$k]["status"] = 2;
            }

            if(time() > $val["act_start_time"]  && time() < $val["act_end_time"] ){
                $act_value[$k]["status"] = 1;
            }

            if(time() > $val["act_end_time"]){
                $act_value[$k]["status"] = 3;
            }

            if($val["act_isstop"] == 1){
                $act_value[$k]["status"] = 4;
            }


        }
        $gu_value = $this->gu->getList();
        $gu_valuelist= $gu_value;
        unset($gu_valuelist[count($gu_value)-1]);
        unset($gu_valuelist[0]);

        $guarantes=unserialize($ss_value['guarantes']);
        $s_guarantee1 = '';
        $guarantess ="";
        // dump(unserialize(unserialize($ss_value['guarantes'])));die();
        if($guarantes){
            foreach ($guarantes as $key => $value) {
                if ($value['guids']=='1') {
                    if (!empty($value["guarantess"])) {
                       $s_guarantee1 = explode(",",$value["guarantess"]);
                    }
                }
                if ($value['guids']!='1') {
                    $guarantess[$value['guids']] = $value;
                }
            }
        }
        $s_keywords = explode(",",$ss_value["s_keywords"]);
        $s_guarantee = explode(",",$ss_value["guarantee"]);


        $invoice = Config::get("ss_invoice");

        $s_guarantee = explode(",",$ss_value["guarantee"]);

        if (isset($ss_value['ss_file'])) {
            $ss_file = explode(",",$ss_value["ss_file"]);
        }
        $mgc_name = "";
        if(isset($ss_value["mgc_id"])){
            $mgc_name = $this->mgc->getOnes(["mgc_id"=>["in",$ss_value["mgc_id"]]],"mgc_name");
            $mgc_name = implode(",",$mgc_name);
        }

        $gcount = $this->g->getCount(["ss_id"=>$this->sm_info['ss_id'],"g_goods_verify"=>1,"s_is_show"=>0]);
        $sr_value['sr_otherarray'] = explode(",",$sr_value['sr_other']);


        return view(
            "shopsettings",
            [
                "gcount"=>$gcount,
                "mgc_name"=>$mgc_name,
                "type"=>$type,
                "shop_settings" => $ss_value,
                "ss_file" => $ss_file,
                "gu_valuelist" => $gu_valuelist,
                "gu_value" => $gu_value,
                "r_value" => $r_value,
                "s_keywords"=>$s_keywords,
                "s_guarantee"=>$s_guarantee,
                "s_guarantee1"=>$s_guarantee1,
                "guarantess"=>$guarantess,
                "sr_value" => $sr_value,
                "article" => $article,
                "act_value" => $act_value,
                "act_page"=>$act_value_all["page"],
                "invoice"=>$invoice,
                "invoiceArr"=>explode(",",$ss_value["ss_invoice"])
            ]
        );
    }



    /**
     * 作者：袁中旭
     * 时间：2017-10-30
     * 功能：商户店铺设置修改
     */
    public function shopSettingsEdit()
    {
        if ($this->request->isPost()) {
            /*只获取post以下参数*/
            $post_ss_info = $this->request->only(['ss_name', 'ss_logo_img1','ss_is_shipping','ss_shop_province','ss_shop_city','ss_shop_area','ss_shop_address','ss_payer','ss_desc','nature','qualifications1','s_content','s_keywords','guarantee','invoice','ss_file','payee_real_name','payee_account'], "post");
            //---

            $rule = array(
                "payee_real_name"=>"require",
                "payee_account"=>"require",
                "invoice" => 'require',
            );
            $msg = array(
                "invoice.require" => '请选择发票设置!~',
                "payee_real_name.require" => '请输入支付真实姓名!~',
                "payee_account.require" => '请输入在支付账号!~',
            );
            $data = verify($post_ss_info, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {

                if(isset($post_ss_info['s_keywords'])){
                        $post_ss_info['s_keywords'] = implode(",",$post_ss_info['s_keywords']);
                }
                if(isset($_POST['guarantee'])){
                    foreach ($_POST['guarantee'] as $key => $value) {
                        $post_ss_info['guarantes'][$key]['guids']=$value;
                        
                        if (!empty($_POST['gunames'.$value])) {
                           $post_ss_info['guarantes'][$key]['guname']=$_POST['gunames'.$value];
                        }
                        
                        if ($value=='1') {
                            $addrs = '';
                            if (isset($_POST['guaranteadds']) && isset($_POST['guarantees1'])) {

                                    $addrs = array_merge($_POST['guaranteadds'],$_POST['guarantees1']);

                            }else if(isset($_POST['guaranteadds'])){
                                if(!empty($_POST['guaranteadds'])){
                                    $addrs = array_merge($_POST['guaranteadds']);
                                }
                            }else if (isset($_POST['guarantees1'])) {
                                if(!empty($_POST['guarantees1'])){
                                    $addrs = array_merge($_POST['guarantees1']);
                                }
                            }
                            if (!empty($addrs)) {
                                $post_ss_info['guarantes'][$key]['guarantess']=implode(',', $addrs);
                            }
                        }else{
                            if (!empty($_POST['guarantees'.$value])) {
                                $post_ss_info['guarantes'][$key]['guarantess']=$_POST['guarantees'.$value];
                            }
                        }

                    }
                    $post_ss_info['guarantes']=serialize($post_ss_info['guarantes']);
                }

                if(isset($post_ss_info['guarantee'])){
                    $post_ss_info['guarantee'] = implode(",",$post_ss_info['guarantee']);
                }else{
                    $post_ss_info['guarantee'] = "";
                }
         
                if(!isset($post_ss_info['guarantee'])){
                    $post_ss_info['guarantee'] = implode(",",$post_ss_info['guarantee']);
                }else{
                    $post_ss_info['guarantee'] = $post_ss_info['guarantee'];
                }


//                if(isset($_FILES) && $_FILES['ss_logo_img']['error'] == 0){
//                    $path = "shopAd/" . date("y_m_d", time());
//                    $ss_logo_img = uploadImage($path,'ss_logo_img');
//                    $post_ss_info['ss_logo_img'] = $ss_logo_img['pic_cover'];
//                }
//                if(isset($_FILES) && $_FILES['qualifications']['error'] == 0){
//                    $path = "shopAd/" . date("y_m_d", time());
//                    $qualifications = uploadImage($path,'qualifications');
//                    $post_ss_info['qualifications'] = $qualifications['pic_cover'];
//                }

                $post_ss_info['ss_logo_img'] = $post_ss_info['ss_logo_img1'];
                unset($post_ss_info['ss_logo_img1']);

                $post_ss_info['qualifications'] = $post_ss_info['qualifications1'];
                unset($post_ss_info['qualifications1']);

//                if(isset($_FILES) && $_FILES['ss_file']['error']['0'] == 0){
//                    $path = "shopAd/" . date("y_m_d", time());
//                    $ss_file = uploadImages($path,'ss_file');
//                    $post_ss_info['ss_file'] = $ss_file['pic_cover'];
//                    $post_ss_info['ss_file'] =implode(',', $post_ss_info['ss_file']);
//                }



                $post_ss_info["ss_file"] = implode(",",$post_ss_info["ss_file"]);

                /*添加轮播图*/

              
                if(!empty($post_ss_info["invoice"])){

                    $post_ss_info["ss_invoice"] = implode(",",$post_ss_info["invoice"]);
                    unset($post_ss_info["invoice"]);
                }
                $value = $this->ss->save($post_ss_info, ['ss_id' => $this->sm_info['ss_id']]);

                if (false !== $value) {
                    $this->sellerManagerLog("修改店铺设置");
                    $this->success("修改成功", url("seller/ShopSettings/shopSettings"));
                } else {
                    $this->error("修改失败");
                }
            }else{
                $this->error($data['msg']);
            }
        }
    }

     public function ShopImgupdate()
    {
        $path = "ShopImg/" . date("y_m_d", time());
        $goods_img = uploadImage($path);
        return json_encode($goods_img);
    }
    /**
     * 作者：袁中旭
     * 时间：2017-11-08
     * 功能：商户后台客服
     */
    public function customerService()
    {
        $sm_im = $this->sm->getRow(['sm_id' => $this->sm_id]);
        $im_val = $this->im->getList(['sm_im_id' => $sm_im['sm_im_id'],'ss_id' => $this->sm_info['ss_id']],'*',['end_time' => "desc"]);
//        dump($im_val["uid"]);
//        exit();
        foreach ($im_val as $key => $value) {
            $usRow = $this->us->getRow(['u_id' => $value['u_id']],'u_name,u_headimg');

            $im_val[$key]['u_name']=$usRow['u_name'];
            $im_val[$key]['u_headimg']=$usRow['u_headimg'];
            if (empty($im_val[$key]['u_name'])) {
                unset($im_val[$key]);
            }
        }
        $ss_goods=$this->g->getList(['ss_id'=>$this->sm_info['ss_id'],'g_goods_verify'=>1]);
        foreach ($ss_goods as $key => $value) {
            $mgcs = $this->mgc->getOne(['mgc_id' => $value['sgc_id']], 'mgc_parent_id');
            if ($mgcs!='0') {
                $ss_goods[$key]['mgc_nameone'] = $this->mgc->getOne(['mgc_id' => $mgcs], 'mgc_name'); //一级分类
                $ss_goods[$key]['sgc_name'] = $this->mgc->getOne(['mgc_id' => $value['sgc_id']], 'mgc_name'); //二级分类
                $ss_goods[$key]['mgc_name'] = $this->mgc->getOne(['mgc_id' => $value['mgc_id']], 'mgc_name'); //三级分类
                $ss_goods[$key]['sgt_name'] = $this->sgt->getOne(['sgt_id' => $value['sgt_id']], 'sgt_name');
                $gsp_price = $this->gsp->getOne(["g_id"=>$value["g_id"]],"gsp_price");

                $ss_goods[$key]['guiges'] = empty($gsp_price)?0:$gsp_price;
            }else{
                $ss_goods[$key]['mgc_nameone'] = ''; //一级分类
                $ss_goods[$key]['sgc_name'] = ''; //二级分类
                $ss_goods[$key]['mgc_name'] =  ''; //三级分类
                $ss_goods[$key]['sgt_name'] =  '';
                $ss_goods[$key]['guiges'] =  '';
            }
        }
        // $ss_goods=$this->g->getList(['ss_id'=>$this->sm_info['ss_id']]);
        // dump($ss_goods);
        // die();
        return view(
            "customerService",
            [
                "sm_im" => $sm_im,
                "im_val" => $im_val,
                "ss_goods" => $ss_goods,
            ]
        );
    }

     /**
     * 作者：李鑫
     * 时间：2017-11-08
     * 功能：AJAX商户后台客服用户头像
     */
    public function ajaximg()
    {
        if ($this->request->isAjax()) {
            // $info['u_im_id']='93599B4F0E3F7624';
            $info = $this->request->only(['u_im_id','sm_im_id'], 'get');
            $im_val = $this->im->getRow(['u_im_id' => $info['u_im_id']]);
            $u_img = $this->us->getRow(['u_id' => $im_val['u_id']]);
            $save['im_state']='1';
            $wheres['u_im_id']=$info['u_im_id'];
            $wheres['sm_im_id']=$info['sm_im_id'];
            $this->im->save($save, $wheres);
            // dump($u_img['u_headimg']);die();
            return json(format($u_img['u_headimg']));
           
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }

     /**
     * 作者：李鑫
     * 时间：2017-11-08
     * 功能：AJAX商户后台客服用户头像
     */
    public function ajaximgs()
    {
        if ($this->request->isAjax()) {
            // $info['u_im_id']='93599B4F0E3F7624';
            $info = $this->request->only(['u_im_id'], 'get');
            $im_val = $this->im->getRow(['u_im_id' => $info['u_im_id']]);
            $u_img = $this->us->getRow(['u_id' => $im_val['u_id']]);
            // dump($u_img['u_headimg']);die();
            return json(format($u_img));
           
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-11-08
     * 功能：AJAX商户后台客服用户列表
     */
    public function customerServiceUserList()
    {
        if ($this->request->isAjax()) {
            // $info['sm_im_id']='15eeb1267fc1cc64';
            $info = $this->request->only(['sm_im_id'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "sm_im_id" => 'require',
            );
            $msg = array(
                "sm_im_id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                $im_val = $this->im->getList(['sm_im_id' => $info['sm_im_id'],'ss_id' => $this->sm_info['ss_id']]);
                // dump($im_val);die();
                return json(format($im_val));
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    public function ajaxChatRecord1()
    {
        if ($this->request->isAjax()) {

            $info = $this->request->only(['sm_im_id','u_im_ids','msgs','type'], 'post');
            $uname = $this->im->getRow(["sm_im_id"=>$info['sm_im_id'],'u_im_id'=>$info['u_im_ids']]);
            $info['u_name']=$uname['u_name'];
            $info['s_name']=$uname['ss_name'];
            $info['time']=date("Y-m-d H:i:s");
            $save['im_state']='0';
            $save['end_time']=time();
            $wheres['im_id']=$uname['im_id'];
            $this->im->save($save, $wheres);
            // dump($uname);
            // die();
            /*验证接到的值有没有问题*/
            $rule = array(
                "sm_im_id" => 'require',
                "u_im_ids" => 'require',
                "msgs" => 'require',
                "type" => 'require',
            );
            $msg = array(
                "sm_im_id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
                "u_im_ids.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
                "msgs.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
                "type.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                logs($info,$_REQUEST['sm_im_id'].'.js');
                return json(format('', '1'));
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }


    public function shopActivityAdd(){


        if ($this->request->isPost()) {
            $info = $this->request->only(["act_type","g_ids","goodschange","act_start_time","act_end_time","act_discount","act_meet","act_reduction","act_describe","g_discount_price"], "post");

            /*验证接到的值有没有问题*/
            $rule = array(
                "act_type" => 'require',
                "act_start_time" => 'require',
                "act_end_time" => 'require',
                "act_describe" => 'require',
                "g_ids" => 'require',
            );
            $msg = array(
                "act_type.require" => '请选择活动类型噢!~',
                "act_start_time.require" => '活动开始日期不能为空噢!~',
                "act_end_time.require" => '活动结束日期不能为空噢!~',
                "act_describe.require" => '请写活动描述噢!~',
                "g_ids.require" => '请选择活动商品噢!~',
            );
            $data = verify($info, $rule, $msg);

            if ($data['code'] === 1) {
                $sm_id = $this->sm_info["sm_id"];
                $ss_id = $this->sm_info["ss_id"];
                $g_ids = "";
                if($info["goodschange"] == "1"){
                    $g_ids = "0";
                }else if(isset($info["g_ids"])){
                    $g_ids = implode(",",$info["g_ids"]);
                }


                $discount = array();
                /*判断 是满减 还是 折扣*/
                if($info["act_type"] == 1){

                    foreach ($info["g_ids"] as $k=>$row){

                        $r = explode("_",$row);
                        $discount[$k]["g_id"] = $r[0];
                        $discount[$k]["sp_id"] = $r[1];
                        $discount[$k]["g_discount_price"] = $info["g_discount_price"][$k];

                    }

                }


                $act_value=[
                    "act_type" => $info["act_type"],
                    "g_ids" =>$g_ids,
                    "act_start_time" => strtotime($info["act_start_time"]),
                    "act_end_time" => strtotime($info["act_end_time"]),
                    "act_discount" => serialize($discount),
                    "act_meet" => $info["act_meet"],
                    "act_reduction" => $info["act_reduction"],
                    "act_describe" => $info["act_describe"],
                    "sm_id" => $sm_id,
                    "ss_id" => $ss_id
                ];

                $act_id = $this->act->save($act_value);
                if (intval($act_id) > 0) {
                    $this->sellerManagerLog("添加活动,添加的活动id为:" . $act_id);
                    $this->success("添加成功",  url("seller/ShopSettings/shopSettings",array('type'=>4)));
                } else {
                    $this->success("添加失败");
                }
            } else {
                $this->error($data['msg']);
            }
        }else{
            /*查询套件*/
            $where = ['ss_id' => $this->sm_info['ss_id']];

            $list = $this->sgt->getList($where);
            foreach ($list as $v=>$key){
                 // 此处查询应该已上架 和 通过审核的商品
                 $list[$v]["goods"] = $goods = $this->g->getList(['s_is_show'=>0,'g_goods_verify'=>1,'sgt_id'=>$key["sgt_id"]],"g_id,g_name,g_inventory,g_current_price,g_unit_name",["g_id"=>"desc"]);
                 foreach ($goods as $k=>$row){
                     $list[$v]["goods"][$k]["guigecount"] = 0;
                     $list[$v]["goods"][$k]["guige"] = array();
                     $gsplist = $this->gsp->getList(["g_id"=>$row["g_id"]]);
                     if(!empty($gsplist)){
                         foreach ($gsplist as $gk=>$g){
                             $gsplist[$gk]["sp_name"] =$this->sp->getOne(["id"=>$g["sp_id"]],"sp_name");
                         }
                         $list[$v]["goods"][$k]["guige"] = $gsplist;
                         $list[$v]["goods"][$k]["guigecount"] = count($gsplist);
                     }
                 }

            }

            $this->assign("list",$list);

            return view();
        }

    }


    public function shopActivityShow($act_id){


        if ($this->request->isPost()) {
            $act_isstop=$this->act->getOne(["act_id"=>$act_id],"act_isstop");
            $rule = $msg = [];

            if($act_isstop == 1){
                $info_act = $this->request->only(["act_start_time","act_end_time"], "post");
                /*验证接到的值有没有问题*/
                $rule = array(
                    "act_start_time" => 'require',
                    "act_end_time" => 'require',
                );
                $msg = array(
                    "act_start_time.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
                    "act_end_time.require" => '程序员都累吐血了也没有接到传输的数据噢!~'
                );
            }else{
                $info_act = $this->request->only(["act_stop_reason"], "post");

                /*验证接到的值有没有问题*/
                $rule = array(
                    "act_stop_reason" => 'require',
                );
                $msg = array(
                    "act_stop_reason.require" => '程序员都累吐血了也没有接到传输的数据噢!~'
                );

            }

            $data = verify($info_act, $rule, $msg);

            if ($data['code'] === 1) {

                $success = "";
                if($act_isstop == 1){
                    $act_id = $this->act->save(["act_isstop"=>0,"act_start_time"=>strtotime($info_act["act_start_time"]),"act_end_time"=>strtotime($info_act["act_end_time"])],["act_id"=>$act_id]);
                    $success = "恢复活动";
                }else{
                    $act_id = $this->act->save(["act_isstop"=>1,"act_stop_reason"=>$info_act["act_stop_reason"]],["act_id"=>$act_id]);
                    $success = "终止";
                }

                if (intval($act_id) > 0) {
                    $this->success($success."成功", url("seller/ShopSettings/shopSettings",array('type'=>4)));
                }else{
                    $this->error("未做任何修改");
                }
            }else{
                $this->error($data['msg']);
            }

        }else{

            $info=$this->act->getRow(["act_id"=>$act_id]);

            if($info["act_end_time"]<time()){
                $info["act_isstop"] = 2;
            }
            $info["act_start_time"] = date("Y-m-d",$info["act_start_time"]);
            $info["act_end_time"] = date("Y-m-d",$info["act_end_time"]);
            $goodsname= $this->g->getOnes(["g_id"=>["in",$info["g_ids"]]],"g_name");
            $actDiscount = array();
            if(!empty($info["act_discount"])){
                $actDiscount = unserialize($info["act_discount"]);
            }


            foreach ($actDiscount as $k=>$row){
                $goods= $this->g->getRow(["g_id"=>["in",$row["g_id"]]],"g_name,g_unit_name");
                $goodsSpecifications = $this->gsp->getRow(["g_id"=>$row["g_id"],"sp_id"=>$row["sp_id"]]);
                $specifications = array();
                $specifications["sp_name"] = "<span style='color:#ccc; font-size:12px;'>暂无规格</span>";
                if(isset($row["sp_id"])){

                    $specifications = $this->sp->getRow(["id"=>["in",$row["sp_id"]]],"sp_name");
                    if(empty($specifications)){
                        $specifications["sp_name"] = "<span style='color:#ccc; font-size:12px;'>暂无规格</span>";
                    }
                }

                if(empty($goodsSpecifications)){
                    unset($actDiscount[$k]);
                }else{
                    $actDiscount[$k] = array_merge($goods,$goodsSpecifications,$row,$specifications);
                }
            }


            $this->assign("goodsname",implode(",",$goodsname));
            $this->assign("info",$info);
            $this->assign("actDiscount",$actDiscount);
            return view();
        }
    }



    public function ajaxBrandSearch()
    {
        if ($this->request->isAjax()) {
            /*只接收id的值*/
            $info = $this->request->only(['brand_search'], 'post');

            /*验证接到的值有没有问题*/
            $rule = array(
                "brand_search" => 'require',
            );
            $msg = array(
                "brand_search.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );

            $data = verify($info, $rule, $msg);

            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                $ajax_mgb = $this->r->getList(['r_name' => ["like", "%" . $info['brand_search'] . "%"],'r_parent_id'=>['in','0,1']]);

                if(empty($ajax_mgb)){
                    return json(format('', '-1', "没有这个地址噢!~"));
                }else{
                   return json(format($ajax_mgb));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }

    public function complain(){
        $where = ['ss_id' => $this->sm_info['ss_id']];

        $ss_complain_msg = $this->ss->getOne($where,"ss_complain_msg");


        $complainAll = $this->co->getAll($where,[],["co_id"=>"desc"]);
        $complain = $complainAll["data"];


        foreach ($complain as $k=>$row){
            $complain[$k]["co_imgs"] = explode(",",$row["co_imgs"]);
            $complain[$k]["g_name"] = $this->g->getOne(["g_id"=>$row["g_id"]],"g_name");
        }

        $this->assign("ss_complain_msg",$ss_complain_msg);
        $this->assign("page",$complainAll["page"]);
        $this->assign("complain",$complain);
        return view();
    }

    public function ShopComplain(){
        if ($this->request->isAjax()) {
            /*只接收id的值*/
            $ss_complain_msg = $this->request->only(['ss_complain_msg'], 'post');
            $where = ['ss_id' => $this->sm_info['ss_id']];
            $ss_id = $this->ss->save(["ss_complain_msg"=>$ss_complain_msg["ss_complain_msg"]],$where);
            if(intval($ss_id) > 0){
                return json(format());
            }else{
                return json(format('', '-1', "未做任何修改"));
            }

        }else{
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }


    /**
     * [前台显示投诉]
     * @author wangmutian
     * @date 2018-04-14
     */
    public function showComplain(){
        $where = ['ss_id' => $this->sm_info['ss_id']];
        $ss_complain = $this->ss->getOne($where,"ss_complain_msg");
        $ss = $this->co->getRow(["ismsg"=>"1",'ss_id' => $this->sm_info['ss_id']]);

        if(empty($ss) || $ss_complain == 0){
            return json(format($ss, 201, ""));
        }else{
            return json(format($ss, 200, "success"));
        }

    }

    /**
     *  [前台显示投诉提示后修改]
     * @author wangmutian
     * @date 2018-04-14
     */
    public function updateComplain(){
        /*接收到的数据*/
        $condition = $this->request->only(["co_id"], "post");
        $ss = $this->co->save(["ismsg"=>0],["co_id"=>$condition["co_id"]]);

        if(empty($ss)){
            return json(format($ss, 201, ""));
        }else{
            return json(format($ss, 200, "success"));
        }
    }

    /**
     * [前台显示退货订单]
     * @author wangmutian
     * @date 2018-04-14
     */
    public function showReturnOrder(){


        $or = $this->or->getRow(["or_is_show"=>"0",'ss_id' => $this->sm_info['ss_id']],"or_id");

        if(empty($or)){
            return json(format($or, 201, ""));
        }else{
            return json(format($or, 200, "您有新的退货订单请注意查收"));
        }
    }


    public function updateReturnOrder(){
        $condition = $this->request->only(["key_id","key_filed"], "post");
        $or = $this->or->save(['or_is_show'=>'1'],["or_id"=>$condition["key_id"]]);
        if(empty($or)){
            return json(format($or, 201, ""));
        }else{
            return json(format($or, 200, "success"));
        }
    }



}