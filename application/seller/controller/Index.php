<?php
namespace app\seller\controller;

use model\SellerShop as ss;
use model\Goods as g;
use model\ShopArtical as sa;
use model\SellerManagers as sm;
use model\Order as o;
use model\OrderGoods as og;
use model\UserCollectionInformation as uci;
use model\ManageGoodsCategory as mgc;
/**
 * 作者：袁中旭
 * 时间：2017-09-12
 * 主要功能：商户端后台首页
 */
class Index extends Base
{
    protected $ss;
    protected $g;
    protected $sa;
    protected $sm;
    protected $o;
    protected $og;
    protected $uci;
    protected $mgc;
    public function __construct()
    {
        parent::__construct();
        /* 店铺信息 */
        $this->ss = new ss();
        /* 商品信息 */
        $this->g = new g();
        /* 文章信息 */
        $this->sa = new sa();
        /* 用户账号  */
        $this->sm = new sm();
        /* 订单 */
        $this->o = new o();
        /* 订单商品 */
        $this->og = new og();
        /*收藏*/
        $this->uci = new uci();
        $this->mgc = new mgc();
    }
    public function index()
    {
        if (isset($this->sm_id)) {



            $sm_im = $this->sm->getRow(['sm_id' => $this->sm_id]);
            $complain = $this->ss->getOne(["ss_id"=>$this->sm_info['ss_id']],"ss_complain_msg");

            $settledInFour = $this->ss->getRow(["ss_id"=>$this->sm_info['ss_id']]);
            $first_cier_category = $this->mgc->getList(['mgc_parent_id'=>0]);
            $this->assign("complain",$complain);
            return view('index', [
                // 'title' => $this->sm_name.'-商户后台',
                'title' => '工建通商户后台管理系统',
                "sm_im" => $sm_im,
                'company' => '黑龙江特讯科技有限责任公司',

                'settledInFour' => $settledInFour,
                'value' => getRegion(),
                'mgc_id_array'=>explode(",",$settledInFour["mgc_id"]),
                'list' => $first_cier_category,


            ]);
        } else {
            $this->error("请先登录噢!~", 'Login/Index');
        }
    }
    public function sellerWelcome()
    {
        /*访问量*/
        $numberClicks = $this->g->getSum(['ss_id' => $this->sm_info['ss_id']],"g_number_of_clicks");
        /*商品总数*/
        $gcount = $this->g->getCount(['ss_id' => $this->sm_info['ss_id']]);
        /*销售总数*/
        $oids= $this->o->getOnes(['ss_id' => $this->sm_info['ss_id']],"o_id");
//        $goods_buy_countnum = $this->og->getSum(["o_id"=>["in",implode(",",$oids)]],"goods_buy_num");

        $goods_buy_countnum = $this->g->getSum(['ss_id' => $this->sm_info['ss_id']],"g_sales");


        $goods_buy_countprice = $this->og->getSum(["o_id"=>["in",implode(",",$oids)]],"member_goods_price");
        /* 收藏 */
        $gids = $this->g->getOnes(['ss_id' => $this->sm_info['ss_id']],"g_id");
        $where_ss = $this->sm_info['ss_id'];

        if(!empty($gids)){
            $where_ss.=",".implode(",",$gids);
        }
        $totalColl = $this->uci->getCount(["uci_collection_id"=>["in",$where_ss]]);


        /*未处理订单*/
        $wclorder = $this->o->getCount(['ss_id' => $this->sm_info['ss_id'],"o_status"=>0,"o_pay_status"=>0,"o_shipping_status"=>0]);
        /*管理员姓名*/
        $sm_name = $this->sm_name;
        /*管理员权限*/
        $sm_status = $this->sm_info['sm_status'];
        /*店铺信息*/
        $ss_value = $this->ss->getRow(['ss_id' => $this->sm_info['ss_id']]);

        return view(
            'sellerWelcome',
            [
                "sm_name" => $sm_name,
                "sm_status" => $sm_status,
                "ss_value" => $ss_value,
                "numberClicks" =>$numberClicks,
                "gcount" =>$gcount,
                "goods_buy_countnum"=>$goods_buy_countnum,
                "goods_buy_countprice"=>$goods_buy_countnum*$goods_buy_countprice,
                "wclorder" =>$wclorder,
                "totalColl" =>$totalColl,
            ]
        );  
    }

    public function welcome(){
        $this->redirect(url('seller/index/sellerWelcome'));
    }

    /**
     * [修改密码]
     * @author 王牧田
     * @date 2018-04-28
     */
    public function editPwd(){
        if ($this->request->isPost()) {
            $info = $this->request->only(["oldpwd", "newpwd", "newspwd"], "post");
            $result = $this->sm->save(["sm_seller_passwd"=>md5($info["newpwd"])],["sm_id"=>$this->sm_info["sm_id"],"sm_seller_passwd"=>md5($info["oldpwd"])]);
            if($result){
                return json(format());
            }else{
                return json(format([],203,"网络错误，请稍后重试"));
            }
        }
    }



    public function  delsql(){
        $mgb_id = $this->g->getOnes(['g_id'=>['neq','0']],"mgb_id");
        $mgb = new \model\ManageGoodsBrand();

        $mgb->del(['mgb_id'=>['not in',implode(",",$mgb_id)]]);
        echo db()->getLastSql();
        die('1');
    }

    /**
     *
     * 作者：王牧田
     * 时间：2018-08-04
     * 功能：PC端商户入驻--AJAX--获取二级类目
     */
    public function settledInAjax()
    {
        $ajax_mgc_id = $this->request->only(['mgc_id','mgc_id'], 'post');
        $ajax_value = $this->mgc->getList(['mgc_parent_id'=> $ajax_mgc_id['mgc_id']]);
        //if(isset($n_session['sr_id']) && $n_session['sr_id'] > 0){
            $settledInFour = $this->ss->getOne(["ss_id"=>$this->sm_info['ss_id']],'ss_mgc_ids');
            if(isset($settledInFour) && $settledInFour != '' && count($settledInFour) > 0){
                $ajax_value['ss_mgc_ids'] = explode(',',$settledInFour);
            }
        //}
        return ['type' => 0,'content' => json_encode($ajax_value)];
    }



    /**
     * 作者：王牧田
     * 时间：2018-08-06
     * 功能：SELLER端商户入驻
     */
    public function settledInDealWith()
    {

        $sellerShop = $this->request->only(['ss_name','ss_desc','mgc_id','ss_mgc_ids','ss_shop_province','ss_shop_city','ss_shop_area','ss_shop_address','ss_shop_location'], 'post');

        $unique_arr = array_unique ($sellerShop["mgc_id"]);

        if(count($unique_arr) != count($sellerShop["mgc_id"])){
            $this->error("一级分类有重复数据");
            exit();
        }

        /* 校验接收到值 */
        $four_rules = array(
            'ss_name' => 'require|max:20|chsAlphaNum',
            'ss_desc' => 'require|max:255',
            'mgc_id' => 'require',
            'ss_mgc_ids' => 'require',
            'ss_shop_province' => 'require',
            'ss_shop_city' => 'require',
            'ss_shop_address' => 'require',
            'ss_shop_location' => 'require',
            'recommended_users' => 'max:10|number',
        );

        $four_msgs = array(
            'ss_name.require' => '请输入店铺名称',
            'ss_name.max' => '店铺名称不能超过20个字符',
            'ss_name.chsAlphaNum' => '店铺名称只能是汉字、字母和数字',
            'ss_desc.require' => '请输入店铺简介',
            'ss_desc.max' => '店铺简介不能超过255个字符',
            'mgc_id.require' => '请选择经营一级类目',
            'ss_mgc_ids.require' => '请选择经营二级类目',
            'ss_shop_province.require' => '请选择店铺所在省',
            'ss_shop_city.require' => '请选择店铺所在市',
            'ss_shop_address.require' => '请输入店铺详细地址',
            'ss_shop_location.require' => '请选择店铺定位',
        );

        $four_data = verify($sellerShop, $four_rules, $four_msgs);

        if ($four_data['code']) {
            $ss_mgc_ids = '';
            foreach($sellerShop['ss_mgc_ids'] as $k=>$v){
                $ss_mgc_ids .= $v.',';
            }

            $sr_id = $this->ss->getOne(["ss_id"=>$this->sm_info['ss_id']],'sr_id');

            /* 店铺数据整合 */
            $save_sellerShop = [
                'sr_id' => $sr_id,
                'ss_name' => $sellerShop['ss_name'],
                'ss_desc' => $sellerShop['ss_desc'],
                'mgc_id' => implode(",",$sellerShop['mgc_id']),
                'ss_mgc_ids' => rtrim($ss_mgc_ids, ','),
                'ss_shop_province' => $sellerShop['ss_shop_province'],
                'ss_shop_city' => $sellerShop['ss_shop_city'],
                'ss_shop_area' => $sellerShop['ss_shop_area'],
                'ss_shop_address' => $sellerShop['ss_shop_address'],
                'ss_shop_location' => $sellerShop['ss_shop_location'],
                'ss_ispop' => 1
            ];

            $success_sellerShop = $this->ss->save($save_sellerShop,['sr_id' => $sr_id]);

            if($success_sellerShop >= 0 ){
                $this->success('上传信息成功');
            }else{
                $this->error('error');
            }
        }else{
            $this->error($four_data['msg']);
        }
    }



   
}
