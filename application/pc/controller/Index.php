<?php
namespace app\pc\controller;

use model\SellerRegistered as sr;
use model\SellerCompanyBank as scb;
use model\ManageGoodsCategory as mgc;
use model\SellerShop as ss;
use model\SellerManagers as sm;
use model\Artical as a;
use model\Users as u;
/**
 * 作者：袁中旭
 * 时间：2017-09-12
 * 主要功能：PC端商户入驻
 */
class Index extends Base
{
    protected $sr;
    protected $scb;
    protected $mgc;
    protected $ss;
    protected $sm;
    protected $a;
    protected $u;
    public function __construct()
    {
        parent::__construct();
        /* 商户注册信息 */
        $this->sr = new sr();
        /* 商户开户行信息 */
        $this->scb = new scb();
        /* 总后台商品分类信息 */
        $this->mgc = new mgc();
        /* 店铺信息 */
        $this->ss = new ss();
        /* 商户后台管理员 */
        $this->sm = new sm();
        /* 总后台文章表 */
        $this->a = new a();
        $this->u = new u();
    }
    
    /**
     * 作者：袁中旭
     * 时间：2017-09-12
     * 功能：PC端商户入驻--第一步--协议页面
     */
    public function index($flag=0)
    {   
        /* 通过查询总后台配置表查询出协议id */
        $protocol = getTableConfig('mbc_')['article']['check_in'];
        /* 获取入驻协议 */
        $article = $this->a->getRow(['a_id' => $protocol]);

        $session = $this->request->session();
        if (isset($session['u_id'])) {
            /* 查询这个用户是否是申请入驻过 */
            $sr_value = $this->sr->getRow(['u_id'=>$session['u_id']]);
            //session("sr_url_id", array($session['u_id'] => "index"));

            if(empty($sr_value)){
                $sr_value['sr_id'] = 0;

            }else{
                session("sr_id", $sr_value['sr_id']);
                session("sr_type", $sr_value['sr_type']);

                $sr_url_id = session("sr_url_id");
                /* 跳转 */

                if(!empty($sr_url_id) && $sr_url_id !="index"){
                    if(isset($sr_url_id[$session['u_id']]) && $flag == 0){

                        $ssisdata=$this->ss->getRow(["ismsg"=>1,"sr_id"=>$sr_value['sr_id']]);

                        if(empty($ssisdata)){
                            if($sr_url_id[$session['u_id']] == "settledInTwo"){
                                $this->redirect(url('pc/index/'.$sr_url_id[$session['u_id']],array("id"=>$sr_value["sr_id"])));
                            }else{
                                $this->redirect('pc/Index/'.$sr_url_id[$session['u_id']]);
                            }
                        }else{

                            $this->redirect('pc/Index/settledInFives');
                        }
                    }
                }

            }
            return view('index', [
                'title' => '工建通-PC入驻协议',
                'company' => '黑龙江特讯科技有限责任公司',
                'article' => $article,
                'sr_value' => $sr_value
            ]);
        } else {
            $this->error("请先登录噢!~", 'seller/Login/Index');
        }
    }
   
    /**
     * 作者：袁中旭
     * 时间：2017-09-12
     * 功能：PC端商户入驻--第二步--页
     */
    public function settledInTwo($id = 0)
    {

        $session = $this->request->session();
        if (isset($session['u_id'])) {
            session("sr_url_id", array($session['u_id'] => "settledInTwo"));
            $u_value = $this->u->getRow(['u_id'=>$session['u_id']]);
        } else {
            $this->error("请先登录噢!~", 'seller/Login/Index');
        }
        if(isset($id) && $id != ''  ){
            $settledInTwo = $this->sr->getRow(['sr_id' => $id]);
            $settledInTwo['settledInTwo'] = '2';
            session("sr_id", $settledInTwo['sr_id']);
            $scb_value = $this->scb->getRow(['sr_id' =>$id]);
        }else{ 
            $settledInTwo = '';
            $settledInTwo['settledInTwo'] = '0';
            $settledInTwo['sr_company_location_province'] = '0';
            $scb_value = '';
        }



        return view('settledInTwo', [
            'settledInTwo' => $settledInTwo,
            'list' => getRegion(),
            'scb_value' => $scb_value,
            'title' => '工建通-PC入驻信息',
            'company' => '黑龙江特讯科技有限责任公司',
            'mobile'  => isset($u_value['u_mobile'])?$u_value['u_mobile']:'',
        ]);
        
    }

    /**
     * 作者：袁中旭
     * 时间：2017-09-12
     * 功能：PC端商户入驻--第二步--处理
     */
    public function settledInTwoDealWith()
    {   

        $two_session = $this->request->session();
        $s_row = $this->request->only(['type_settled_in', 'province', 'city', 'area', 'sr_company_location_address', 'sr_real_name', 'sr_mobile', 'sr_company_phone', 'scb_bank_account', 'scb_bank_name'], 'post');


        /* 校验接收到值 */
        $two_rules = array(
            'type_settled_in' => 'require|in:0,1',
            'province' => 'require',
            'city' => 'require',
            'sr_company_location_address' => 'require|max:20',
            'sr_real_name' => 'require|max:20',
            'sr_mobile' => 'require|number|max:11',
            'sr_company_phone' => 'require',
            'scb_bank_account' => 'require',
            'scb_bank_name' => 'require',
        );
        $two_msgs = array(
            'type_settled_in.require' => '请选择入驻类型',
            'type_settled_in.in' => '入驻类型数据格式不正确',
            'province.require' => '请选择入驻的省份',
            'city.require' => '请选择入驻的市区',
            'sr_company_location_address.require' => '请填写详细地址',
            'sr_company_location_address.max' => '详细地址不能超过20个字符',
            'sr_real_name.require' => '请填写入驻人姓名',
            'sr_real_name.max' => '驻人姓名填写不能超过20个字符',
            'sr_mobile.require' => '请填写入驻人手机号',
            'sr_mobile.number' => '入驻人手机号有误请从新填写',
            'sr_mobile.max' => '入驻人手机号格式有误请从新填写',
            'sr_company_phone.require' => '请填写公司电话',
            'scb_bank_account.require' => '请填写公司银行账号',
            'scb_bank_name.require' => '请填写公司开户行名称',
        );
        /* 验证后接受的值 */
        $two_data = verify($s_row, $two_rules, $two_msgs);
        /* 获取session中的值 */
        
        if ($two_data['code']) {
            /* 集合数据 */
            $registered = [
                'u_id' => $two_session['u_id'],
                'sr_type' => $s_row['type_settled_in'],
                'sr_company_location_province' => $s_row['province'],
                'sr_company_location_city' => $s_row['city'],
                'sr_company_location_area' => $s_row['area'],
                'sr_company_location_address' => $s_row['sr_company_location_address'],
                'sr_real_name' => $s_row['sr_real_name'],
                'sr_mobile' => $s_row['sr_mobile'],
                'sr_company_phone' => $s_row['sr_company_phone'],
//                'sr_emergency_contact' => $s_row['sr_emergency_contact'],
//                'sr_emergency_contact_phone' => $s_row['sr_emergency_contact_phone'],
            ];


            /* 添加到数据库 */
            if(isset($two_session['sr_id']) && $two_session['sr_id'] != ''){
                $sellerRegistered_value = $this->sr->save($registered,['sr_id' => $two_session['sr_id']]);
            }else{
                $sellerRegistered_value = $this->sr->save($registered);
            }



            if ($sellerRegistered_value >= 0) {
                $scb_value=[
                    "scb_bank_account"=>$s_row['scb_bank_account'],
                    "scb_bank_name"=>$s_row['scb_bank_name'],
                ];

                if(isset($two_session['sr_id']) && $two_session['sr_id'] != ''){
                    $sr_id = $two_session['sr_id'];
                }else{
                    $sr_id = $sellerRegistered_value;
                }

                //将银行卡的事情交托给数据库
                $scbRow = $this->scb->getRow(['sr_id' => $sr_id]);
                if(!empty($scbRow)){
                    $this->scb->save($scb_value,['sr_id' => $sr_id]);
                }else{
                    $scb_value['sr_id'] = $sr_id;
                    $this->scb->save($scb_value);
                }




                /* 成功后把sr_id和sr_type存入session方便下面调用 */
                if(!isset($two_session['sr_id'])){
                    session("sr_id", $sellerRegistered_value);
                }
                session("sr_type", $s_row['type_settled_in']);
                if(isset($two_session['sr_id']) && $two_session['sr_id'] != '' && !empty($two_session['sr_id'])){
                    $ss_values = $this->ss->getRow(['sr_id' => $two_session['sr_id']]);
                    if(!empty($ss_values) && $ss_values != '' && isset($ss_values)){
                        $this->ss->save(['ss_approval_status' => 0],['sr_id' => $two_session['sr_id']]);
                    }
                }
                $this->success('上传信息成功',url('pc/index/settledInThree'),0);
            } else {
                $this->error("数据有误请重新修改");
            }
        } else {
            $this->error($two_data['msg']);
        }
    }

    /**
     * 作者：袁中旭
     * 时间：2017-09-26
     * 功能：PC端商户入驻--第三步--页
     */
    public function settledInThree()
    {

        $session = $this->request->session();

        if (isset($session['u_id'])) {
            session("sr_url_id", array($session['u_id'] => "settledInThree"));
            $u_value = $this->u->getRow(['u_id'=>$session['u_id']]);
        } else {
            $this->error("请先登录噢!~", 'seller/Login/Index');
        }
        $q_session = $this->request->session();
        if(isset($q_session['sr_id']) && $q_session['sr_id'] != ''){
            $settledInThrees = $this->sr->getRow(['sr_id' => $q_session['sr_id']]);
//            if(isset($settledInThrees['sr_type']) && $settledInThrees['sr_type'] == '1' && $settledInThrees['sr_operating_period'] != 0){
//                $times = explode(',',$settledInThrees['sr_operating_period']);
//                $settledInThrees['sr_operating_period0'] = $times['0'];
//                $settledInThrees['sr_operating_period1'] = $times['1'];
//            }
            $scb_value = $this->scb->getRow(['sr_id' => $q_session['sr_id']]);
        }else{
            $settledInThrees = '';
            $scb_value = '';
        }

        if(isset($settledInThrees['sr_type'])){
            if($settledInThrees['sr_type'] != $q_session['sr_type']){
                $settledInThrees = '';
                $scb_value = '';
            }
        }


        $settledInThrees['sr_otherarray'] = explode(",",$settledInThrees['sr_other']);


        $settledInThree = [
            'id' => $q_session['sr_id'],
            'type' => $q_session['sr_type'],
        ];

        return view('settledInThree', [
            "sr_id"=>$q_session['sr_id'],
            'settledInThree' => $settledInThrees,
            'scb_value' => $scb_value,
            'value' => getRegion(),             /* 获取省份 */
            'list' => $settledInThree,          /* 获取session中的值 */
            'title' => '工建通-PC入驻信息',
            'company' => '黑龙江特讯科技有限责任公司',
            'mobile'  => $u_value['u_mobile'],
        ]);
    }

    /**
     * 作者：袁中旭
     * 时间：2017-09-26
     * 功能：PC端商户入驻--第三步--处理
     */
    public function settledInThreeDealWith()
    {
        $file_info = '';
        /* 商户入驻上传图片路径 */
        $path = "IDcard/" . date("y_m_d", time());

        foreach ($_FILES as $key => $value) {
            if($value['name'] != '' && $value['type'] != ''){
                $file_info[$key] = uploadImage($path,$key);
            }
        }


        $s_session = $this->request->session();

        if($s_session['sr_type'] == 0){
            /* 个人入驻 */
            $s_sellerRegistered = $this->request->only(['sr_latitude_and_longitude','sr_legal_person_idcard','sr_idcard_positive','sr_idcard_background','sr_id','sr_legal_person'], 'post');
            if($file_info != ''){
                $s_sellerRegistered['sr_idcard_positive'] = $file_info['sr_idcard_positive']['pic_cover'];
                $s_sellerRegistered['sr_idcard_background'] = $file_info['sr_idcard_background']['pic_cover'];
            }else{
                if(isset($_REQUEST['sr_idcard_positives']) && isset($_REQUEST['sr_idcard_backgrounds'])){
                    $s_sellerRegistered['sr_idcard_positive'] = $_REQUEST['sr_idcard_positives'];
                    $s_sellerRegistered['sr_idcard_background'] = $_REQUEST['sr_idcard_backgrounds'];
                }
            }

            /* 校验接收到值 */
            $three_s_rules = array(
                'sr_latitude_and_longitude' => 'require|max:40',
                'sr_legal_person_idcard' => 'require|max:18',
                'sr_idcard_positive' => 'require',
                'sr_idcard_background' => 'require',
                'sr_legal_person' => 'require|chs|max:20',
            );

            $three_s_msgs = array(
                'sr_latitude_and_longitude.require' => '请选择经纬度！',
                'sr_latitude_and_longitude.max' => '经纬度格式不正确！',
                'sr_legal_person_idcard.require' => '请填写身份证号！',
                'sr_legal_person_idcard.require' => '身份证号最大只能输入18个字符！',
                'sr_idcard_positive.require' => '请上传身份证正面！',
                'sr_idcard_background.require' => '请上传身份证反面！',
                'sr_legal_person.require' => '请填写身份证姓名！',
                'sr_legal_person.chs' => '身份证姓名只能输入中文！',
                'sr_legal_person.chs' => '身份证姓名最大只能输入20个字符！',
            );

            $three_s_data = verify($s_sellerRegistered, $three_s_rules, $three_s_msgs);
            if ($three_s_data['code']) {
                /* 商户注册修改数据整合 */

                $save_sellerRegistered = [
                    'sr_latitude_and_longitude' => $s_sellerRegistered['sr_latitude_and_longitude'],
                    'sr_legal_person' => $s_sellerRegistered['sr_legal_person'],
                    'sr_legal_person_idcard' => $s_sellerRegistered['sr_legal_person_idcard'],
                    'sr_idcard_positive' => $s_sellerRegistered['sr_idcard_positive'],
                    'sr_idcard_background' => $s_sellerRegistered['sr_idcard_background'],
                ];

            }else{
                $this->error($three_s_data['msg']);
            }
        }else{
            /*公司入驻*/
            /* 选择POST接受的值 */

            $w_sellerRegistered = $this->request->only(['sr_company_name','sr_business_path','sr_business_license_number','sr_company_location_province','sr_company_location_city','sr_company_location_area','sr_company_location_address','sr_latitude_and_longitude','sr_company_phone','sr_legal_person','sr_legal_person_idcard','sr_idcard_positive','sr_idcard_background','sr_agency_text','sr_environ_text','sr_related_text','sr_honor_text','sr_other_text'], 'post');
            /*判断修改信息的时候是否上传图片*/

            if(isset($file_info['sr_idcard_positive'])){
                $w_sellerRegistered['sr_idcard_positive'] = $file_info['sr_idcard_positive']['pic_cover'];
            }else{
                if(isset($_REQUEST['sr_idcard_positives'])){
                    $w_sellerRegistered['sr_idcard_positive'] = $_REQUEST['sr_idcard_positives'];
                }
            }

            if(isset($file_info['sr_idcard_background'])){
                $w_sellerRegistered['sr_idcard_background'] = $file_info['sr_idcard_background']['pic_cover'];
            }else{
                if(isset($_REQUEST['sr_idcard_backgrounds'])){
                    $w_sellerRegistered['sr_idcard_background'] = $_REQUEST['sr_idcard_backgrounds'];
                }
            }

            if(isset($file_info['sr_business_path'])){
                $w_sellerRegistered['sr_business_path'] = $file_info['sr_business_path']['pic_cover'];
            }else{
                if(isset($_REQUEST['sr_business_paths'])){
                    $w_sellerRegistered['sr_business_path'] = $_REQUEST['sr_business_paths'];
                }
            }


            /* 校验接收到值 */
            $three_w_rules = array(
                'sr_company_name' => 'require|max:20|chsAlphaNum',
                'sr_business_path' => 'require',
                'sr_business_license_number' => 'require|max:18',
                'sr_company_location_province'=> 'require',
                'sr_company_location_city'=> 'require',
                'sr_company_location_area' => 'require',
                'sr_company_location_address' => 'require',
                'sr_company_phone' => 'require', //|max:12|number
                'sr_latitude_and_longitude' => 'require|max:40',
                'sr_legal_person' => 'require|chs',
                'sr_legal_person_idcard' => 'require',
                'sr_idcard_positive' => 'require',
                'sr_idcard_background' => 'require',
            );
            $three_w_msgs = array(
                'sr_company_name.require' => '请填写公司名称！',
                'sr_company_name.max' => '公司名称最大只能输入20个字符！',
                'sr_company_name.chsAlphaNum' => '公司名称只能输入汉字、字母和数字！',
                'sr_business_path.require' => '请上传营业执照图片！',
                'sr_business_license_number.require' => '请填写营业执照注册号！',
                'sr_business_license_number.max' => '营业执照注册号最大只能输入18个字符！',
                'sr_company_location_province.require'=> '请选择公司所在省！',
                'sr_company_location_city.require'=> '请选择公司所在市！',
                'sr_company_location_area.require' => '请选择公司所在区！',
                'sr_company_location_address.require' => '请填写公司所在详细地址！',
                'sr_company_phone.require' => '请填写公司电话！',
                'sr_latitude_and_longitude.require' => '请选择公司所在地！',
                'sr_latitude_and_longitude.max' => '公司所在地格式不正确！',
                'sr_legal_person.require' => '请填写身份证姓名！',
                'sr_legal_person.chs' => '身份证姓名只能输入中文！',
                'sr_legal_person_idcard.require' => '请填写身份证号！',
                'sr_idcard_positive.require' => '请上传身份证正面！',
                'sr_idcard_background.require' => '请上传身份证反面！',
            );

            $three_w_data = verify($w_sellerRegistered, $three_w_rules, $three_w_msgs);
            if ($three_w_data['code']) {
                /* 要修改的商户注册信息数组 */
                $save_sellerRegistered = [
                    'sr_company_name' => $w_sellerRegistered['sr_company_name'],
                    // 'sr_need_money' => $w_sellerRegistered['sr_need_money'],
                    'sr_business_path' => $w_sellerRegistered['sr_business_path'],
                    'sr_business_license_number' => $w_sellerRegistered['sr_business_license_number'],
                    'sr_company_location_province'=> $w_sellerRegistered['sr_company_location_province'],
                    'sr_company_location_city'=> $w_sellerRegistered['sr_company_location_city'],
                    'sr_company_location_area' => $w_sellerRegistered['sr_company_location_area'],
                    'sr_company_location_address' => $w_sellerRegistered['sr_company_location_address'],
                    'sr_company_phone' => $w_sellerRegistered['sr_company_phone'],
                    'sr_latitude_and_longitude' => $w_sellerRegistered['sr_latitude_and_longitude'],
                    'sr_legal_person' => $w_sellerRegistered['sr_legal_person'],
                    'sr_legal_person_idcard' => $w_sellerRegistered['sr_legal_person_idcard'],
                    'sr_idcard_positive' => $w_sellerRegistered['sr_idcard_positive'],
                    'sr_idcard_background' => $w_sellerRegistered['sr_idcard_background'],
                    'sr_agency' =>  $w_sellerRegistered['sr_agency_text'],
                    'sr_environ' =>  $w_sellerRegistered['sr_environ_text'],
                    'sr_related' =>  $w_sellerRegistered['sr_related_text'],
                    'sr_honor' =>  $w_sellerRegistered['sr_honor_text'],
                    'sr_other' =>  $w_sellerRegistered['sr_other_text'],
                ];
            }else{
                $this->error($three_w_data['msg']);
            }
        }

        /* 修改商户注册信息数据库 */
        $success_sellerRegistered = $this->sr->save($save_sellerRegistered,['sr_id'=>$s_session['sr_id']]);
        /* 商户开户行接受信息 */
        $q_sellerRegistered = $this->request->only(['sr_legal_person','scb_bank_account','scb_bank_name'], 'post');


        $save_sellerCompanyBank = [
            'sr_id' => $s_session['sr_id'],
            'scb_company_name' => $q_sellerRegistered['sr_legal_person'],
            'scb_bank_account' => $q_sellerRegistered['scb_bank_account'],
            'scb_bank_name' => $q_sellerRegistered['scb_bank_name'],
        ];
        /* 查询开户行表是否有这个用户提交的数据 */
        $type_sellerCompanyBank = $this->scb->getRow(['sr_id'=>$s_session['sr_id']]);
        /* 判断开户行是否有这个用户提交的数据 */
        if(empty($type_sellerCompanyBank)){
            $success_sellerCompanyBank = $this->scb->save($save_sellerCompanyBank);
        }else{
            $success_sellerCompanyBank = $this->scb->save($save_sellerCompanyBank,['scb_id'=>$type_sellerCompanyBank['scb_id']]);
        }


        //此处略去一万字

        $sellerShop_session = $this->request->session();

        /* 店铺数据整合 */
        $save_sellerShop = [
            'sr_id' => $sellerShop_session['sr_id'],
            'ismsg' => 1
        ];
        $count = $this->ss->getCount(['sr_id' => $sellerShop_session['sr_id']]);
        if($count > 0){
            $this->ss->save($save_sellerShop,['sr_id' => $sellerShop_session['sr_id']]);
        }else{
            $this->ss->save($save_sellerShop);
        }

        if($success_sellerRegistered >= 0 && $success_sellerCompanyBank >= 0){
//            $this->success('上传信息成功',url('pc/index/settledInFour'));
            $this->success('上传信息成功',url('pc/index/settledInFives'));
        }
    }





    /**
     * 作者：王牧田
     * 时间：2018-07-31
     * 功能：上传图片
     */
    public function goodsImgupdate()
    {
        $path = "ShopGoods/" . date("y_m_d", time());
        $goods_img = uploadImage($path);
        return json_encode($goods_img);
    }

    /**
     * 作者：袁中旭
     * 时间：2017-10-09
     * 功能：PC端商户入驻--第四步--页
     */
    public function settledInFour()
    {
        $session = $this->request->session();
        if (isset($session['u_id'])) {
            session("sr_url_id", array($session['u_id'] => "settledInFour"));
            $u_value = $this->u->getRow(['u_id'=>$session['u_id']]);
        } else {
            $this->error("请先登录噢!~", 'seller/Login/Index');
        }
        $m_session = $this->request->session();
        if(isset($m_session['sr_id']) && $m_session['sr_id'] > 0){
            $settledInFour = $this->ss->getRow(['sr_id' => $m_session['sr_id']]);
        }else{
            $settledInFour = '';
        }
        $first_cier_category = $this->mgc->getList(['mgc_parent_id'=>0]);

        if(!isset($settledInFour["mgc_id"])){
            $settledInFour["mgc_id"]="";
        }

        return view('settledInFour', [
            'settledInFour' => $settledInFour,
            'value' => getRegion(),
            'mgc_id_array'=>explode(",",$settledInFour["mgc_id"]),
            'list' => $first_cier_category,
            'title' => '工建通-PC入驻信息',
            'company' => '黑龙江特讯科技有限责任公司',
            'mobile'  => $u_value['u_mobile'],
        ]);
    }

    /**
     * 作者：袁中旭
     * 时间：2017-10-09
     * 功能：PC端商户入驻--第四步--处理
     */
    public function settledInFourDealWith()
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
            // 'ss_shop_area' => 'require',
            'ss_shop_address' => 'require',
            'ss_shop_location' => 'require',
//            'ss_is_shipping' => 'require',
//            'ss_payer' => 'require',
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
            // 'ss_shop_area.require' => '请选择店铺所在区',
            'ss_shop_address.require' => '请输入店铺详细地址',
            'ss_shop_location.require' => '请选择店铺定位',
            // 'ss_is_shipping.require' => '请选择书否配送',
            // 'ss_payer.require' => '请选择承担配送费用方',
            // 'recommended_users.max' => '推广员不能超过10个字符',
            // 'recommended_users.number' => '推广员格式不正确',
        );

        $four_data = verify($sellerShop, $four_rules, $four_msgs);

        if ($four_data['code']) {
            $ss_mgc_ids = '';
            foreach($sellerShop['ss_mgc_ids'] as $k=>$v){
                $ss_mgc_ids .= $v.',';
            }



            $sellerShop_session = $this->request->session();

            /* 店铺数据整合 */            
            $save_sellerShop = [
                'sr_id' => $sellerShop_session['sr_id'],
                'ss_name' => $sellerShop['ss_name'],
                'ss_desc' => $sellerShop['ss_desc'],
                'mgc_id' => implode(",",$sellerShop['mgc_id']),
                'ss_mgc_ids' => rtrim($ss_mgc_ids, ','),
                'ss_shop_province' => $sellerShop['ss_shop_province'],
                'ss_shop_city' => $sellerShop['ss_shop_city'],
                'ss_shop_area' => $sellerShop['ss_shop_area'],
                'ss_shop_address' => $sellerShop['ss_shop_address'],
                'ss_shop_location' => $sellerShop['ss_shop_location'],
                'ismsg' => 1
//                'ss_is_shipping' => $sellerShop['ss_is_shipping'],
//                'ss_delivery_province' => $sellerShop['ss_delivery_province'],
//                'ss_delivery_city' => $sellerShop['ss_delivery_city'],
//                'ss_delivery_area' => $sellerShop['ss_delivery_area'],
//                'ss_delivery_address' => $sellerShop['ss_delivery_address'],
//                'ss_payer' => $sellerShop['ss_payer'],
//                'recommended_users' => $sellerShop['recommended_users']
            ];
            $count = $this->ss->getCount(['sr_id' => $sellerShop_session['sr_id']]);
            if($count > 0){
                $success_sellerShop = $this->ss->save($save_sellerShop,['sr_id' => $sellerShop_session['sr_id']]);
            }else{
                $success_sellerShop = $this->ss->save($save_sellerShop);
            }
            if($success_sellerShop >= 0 ){
                $this->success('上传信息成功',url('pc/index/settledInFives'));
            }else{
                $this->error('error',url('pc/index/settledInFour'));
            }
        }else{
            $this->error($four_data['msg']);
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-09
     * 功能：PC端商户入驻--AJAX--获取二级类目
     */
    public function settledInFourAjax()
    {
        $n_session = $this->request->session();
        if ($this->request->isAjax()) {
            $ajax_mgc_id = $this->request->only(['mgc_id','mgc_id'], 'post');
            $ajax_value = $this->mgc->getList(['mgc_parent_id'=> $ajax_mgc_id['mgc_id']]);
            if(isset($n_session['sr_id']) && $n_session['sr_id'] > 0){
                $settledInFour = $this->ss->getOne(['sr_id' => $n_session['sr_id']],'ss_mgc_ids');
                if(isset($settledInFour) && $settledInFour != '' && count($settledInFour) > 0){
                    $ajax_value['ss_mgc_ids'] = explode(',',$settledInFour);
                }
            }
            return ['type' => 0,'content' => json_encode($ajax_value)];
        } else {
            return ['type' => 1, 'content' => '出现错误请联系客服噢!~'];
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-09
     * 功能：PC端商户入驻--第五步--页
     */
    public function settledInFives()
    {

        $session = $this->request->session();

        if (isset($session['u_id'])) {
            session("sr_url_id", array($session['u_id'] => "settledInFives"));
            $u_value = $this->u->getRow(['u_id'=>$session['u_id']]);
        } else {
            $this->error("请先登录噢!~", 'seller/Login/Index');
        }
        $settledInFives_session = $this->request->session();

        if(!empty($settledInFives_session) &&  $settledInFives_session != ''){

            /* 获取店铺信息 */
            $settledInFives = $this->ss->getRow(['sr_id'=>$settledInFives_session['sr_id']]);

            if(isset($settledInFives) && !empty($settledInFives) && $settledInFives != '')
            {
                /* 未通过原因 */
                $sr_reason = $this->sr->getOne(['sr_id' => $settledInFives['sr_id']],'sr_reason');
                $settledInFives['sr_reason'] = $sr_reason;
            }else{

                $settledInFives['sr_reason'] = '';
                $settledInFives["ss_approval_status"] = "-1";
                $settledInFives['sr_id'] = $settledInFives_session['sr_id'];
            }

            return view('settledInFives', [
                'settledInFives' => $settledInFives,
                'title' => '工建通-PC入驻信息',
                'company' => '黑龙江特讯科技有限责任公司',
                'mobile'  => $u_value['u_mobile'],
            ]);
            
        }else{
            $this->error('error',url('pc/index/index'));
        }
        
    }


    /***
     * [高德地图获取经纬度相关信息]
     * @author 王牧田
     * @date 2018-07-31
     * @param $address
     * @return string|\think\response\Json
     */
    public function getLoaction(){
        $address = input('address','哈尔滨香坊区格兰云天');
        $url = "http://restapi.amap.com/v3/geocode/geo?key=389880a06e3f893ea46036f030c94700&s=rsv3&city=35&address=".$address;
        $result = file_get_contents($url);
        $res = json_decode($result,true);
        return json($res["geocodes"]);
    }





}

