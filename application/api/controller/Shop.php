<?php
namespace app\api\controller;

use model\SellerShop as ss;
use model\SellerRegistered as sr;
use model\Goods as g;
use model\ShopGoodsCategory as sgc;
use model\UserCollectionInformation as uci;
use model\UserBrowsingHistory as ubh;
use model\ShopAd as sa;
use think\Db;
use model\ShopGoodsType as sgt;
use model\SellerManagers as sm;
use model\GoodsSpecifications as gsp;
use model\UserIndustry as ui;
/**
 * 
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-10-25
 */
class Shop extends Base
{
	protected $ss;
	protected $g;
	protected $sgc;
	protected $ui;
	protected $uci;
	protected $sr;
    protected $ubh;
    protected $sgt;
    protected $sa;
    protected $sm;
    protected $gsp;
	public function __construct()
	{
		parent::__construct();
		$this->ss = new ss();
		$this->g = new g();
		$this->sgc = new sgc();
		$this->uci= new uci();
		$this->sr = new sr();
        $this->ubh = new ubh(); /*历史浏览记录表*/
        $this->sgt = new sgt();/*商家商品类型*/
        $this->sa = new sa();
        $this->sm = new sm();
        $this->gsp = new gsp(); /*商品规格*/
        $this->ui = new ui();
	}
	/**
	 * [shopList description]
	 * @author 户连超
	 * @e-mail zrkjhlc@gmail.com
	 * @date   2017-10-25
	 * @return [type]            [description]
	 */
	public function shopList()
	{
	     //入参：
	     //  $this->param['province_id']= '';
	     //  $this->param['city_id']= '';
	     //  $this->param['district_id']= '';
	     //  $this->param['page']= 1;
	     //  $this->param['type']= 1;
	     //  // $this->param['mgc_id']= 3;
	     //  // $this->param['zh_sum']='asc';

	     // // // $this->param['screen']='n1,厂家';
	     //  $this->param['keyword']='五金标准件';

//	     $this->param['g_sum']= 'asc';
//         $this->param['z_sum'] = 'desc';

	    // 前三个参数是省市区  修改之后返回的数据都是一样的
		// $this->param["mLongitudeAndLatitude"]='126.854346,45.751438';
		if (empty($this->param['mgc_id'])) {
        	// return json(format('',223,'缺少必要参数!~'));
        	$this->param['mgc_id']="";
		}
		$where['ss.ss_approval_status'] = '1';
		// $fanwei = shaixuan($this->param["mgc_id"],'mgc_id');
		// foreach ($fanwei as $key => $value) {
		// 	$fanwei[$key]=$value['ss_id'];
		// }
		// $fanwei=implode(",",$fanwei);
		// $where['ss.ss_id']=array('in',$fanwei);
		// $where['ss.mgc_id'] = $this->param["mgc_id"];//46装修装饰/3建筑公司
		/*展示的省份*/
        (isset($this->param["province_id"]) && ($this->param["province_id"] != '')) ? $condition["ss_shop_province"] = $this->param["province_id"] : false;
        (isset($this->param["city_id"]) && ($this->param["city_id"] != '')) ? $condition["ss_shop_city"] = $this->param["city_id"] : false;
        (isset($this->param["district_id"]) && ($this->param["district_id"] != '')) ? $condition["ss_shop_area"] = $this->param["district_id"] : false;
     	/*地区(省)*/
        if (isset($condition['ss_shop_province']) && ('' != $condition['ss_shop_province']) && $condition['ss_shop_province'] > 1) {
            $where['ss.ss_shop_province'] = $condition['ss_shop_province'];
            $pageParam['query']['ss.ss_shop_province'] = $condition['ss_shop_province'];
        }
        /*地区(市)*/
        if (isset($condition['ss_shop_city']) && ('' != $condition['ss_shop_city']) && $condition['ss_shop_city'] > 1) {
            $where['ss.ss_shop_city'] = $condition['ss_shop_city'];
            $pageParam['query']['ss.ss_shop_city'] = $condition['ss_shop_city'];
        }
        /*地区(区)*/
        if (isset($condition['ss_shop_area']) && ('' != $condition['ss_shop_area']) && $condition['ss_shop_area'] > 1) {
            $where['ss.ss_shop_area'] = $condition['ss_shop_area'];
            $pageParam['query']['ss.ss_shop_area'] = $condition['ss_shop_area'];
        }


		$pageParam['query']['ss.mgc_id'] = $this->param["mgc_id"];
        if(isset($this->param['z_sum']) && '' != $this->param['z_sum']){
            $order["ucicount"] = "desc";
        }
		if(isset($this->param['keyword']) && '' != $this->param['keyword']){
            $where["ss.ss_name"] =["like","%".$this->param['keyword'].'%'];
        }
		if (isset($this->param['g_sum']) && '' != $this->param['g_sum']) {
			$order["g.g_sales1"] = 'desc';
			$order["ss.ss_id"] = "asc";
		}
		if (isset($this->param['zh_sum']) && '' != $this->param['zh_sum']) {
			$order["ss.ss_sort"] = "asc";
			$order["ss.nature"] = "asc";
			$order["ss.ss_id"] = "asc";
		}else{
			$order["ss.ss_sort"] = "asc";
			$order["ss.nature"] = "asc";
			$order["ss.ss_id"] = "asc";
		}
		if(isset($this->param["screen"]) && ($this->param["screen"] != '')){
		 	$this->param['screen'];
			$srcarrs=explode(",",$this->param['screen']);
			$srcarr =$srcarrs;
			// $srcarr['name'] =$srcarrs[1];
			if ($srcarr[0]=='n1') {
				$fanwei = shaixuan('1','nature');
				foreach ($fanwei as $key => $value) {
					$fanwei[$key]=$value['ss_id'];
				}
				$fanwei=implode(",",$fanwei);
				$where['ss.ss_id']=array('in',$fanwei);
			}else if ($srcarr[0]=='n2') {
				$fanwei = shaixuan('2','nature');
				foreach ($fanwei as $key => $value) {
					$fanwei[$key]=$value['ss_id'];
				}
				$fanwei=implode(",",$fanwei);
				$where['ss.ss_id']=array('in',$fanwei);
			}else if ($srcarr[0]=='n3') {
				$fanwei = shaixuan('3','nature');
				foreach ($fanwei as $key => $value) {
					$fanwei[$key]=$value['ss_id'];
				}
				$fanwei=implode(",",$fanwei);
				$where['ss.ss_id']=array('in',$fanwei);
			}else if ($srcarr[0]=='n4') {
				$fanwei = shaixuan('3','nature');
				foreach ($fanwei as $key => $value) {
					$fanwei[$key]=$value['ss_id'];
				}
				$fanwei=implode(",",$fanwei);
				$where['ss.ss_id']=array('in',$fanwei);
			}
			if ($srcarr[0]!='n1' && $srcarr[0]!='n2' && $srcarr[0]!='n3' && $srcarr[0]!='n4' && !empty($srcarr[1])) {
				
				$fanwei = shaixuan($srcarr[1],'guarantee');
				foreach ($fanwei as $key => $value) {
					$fanwei[$key]=$value['ss_id'];
				}
				$fanwei=implode(",",$fanwei);
				$where['ss.ss_id']=array('in',$fanwei);
			}
			if ($srcarr[0]!='n1' && $srcarr[0]!='n2' && $srcarr[0]!='n3' && $srcarr[0]!='n4' && empty($srcarr[1])) {
				$fanwei = shaixuan($srcarr[0],'guarantee');
				foreach ($fanwei as $key => $value) {
					$fanwei[$key]=$value['ss_id'];
				}
				$fanwei=implode(",",$fanwei);
				$where['ss.ss_id']=array('in',$fanwei);
			}
				
		}
		$join = [
			["(select count(g1.g_id) as g_num1,sum(g1.g_sales) as g_sales1,g1.ss_id as g_num,g1.ss_id from gjt_goods as g1 group by g1.ss_id ) g", "g.ss_id = ss.ss_id","left"], //之前没有left
			["gjt_region p", "p.r_id = ss.ss_shop_province"],
			["gjt_region c", "c.r_id = ss.ss_shop_city"],
			["gjt_region a", "a.r_id = ss.ss_shop_area"],
			["gjt_user_collection_information uci", "uci.uci_collection_id = ss.ss_id","left"],
		];
		$alias = "ss";
		$field = "ss.ss_id, ss.ss_name, ss.ss_logo_img, ss.ss_shop_location, ss.ss_shop_address,g.g_num1,g.g_sales1, p.r_name as ss_shop_province, c.r_name as ss_shop_city, a.r_name as ss_shop_area,ss.nature,ss.admin_nature,count(uci_collection_id) as ucicount";
		$group = "g.ss_id"; //
		$pageParam['page'] = $this->param['page']=1;
		$list = $this->ss->joinGetAll($join, $alias, $where, $pageParam, $order, 10, $field,$group);
		// dump($this->ss->getlastsql());
		// dump($where);
		if (empty($list["data"])) {
			unset($where['ss.ss_name']);
			$guanjian = shaixuan($this->param['keyword'],'s_keywords');
			foreach ($guanjian as $key => $value) {
				$guanjian[$key]=$value['ss_id'];
			}
			$guanjian=implode(",",$guanjian);
			$where['ss.ss_id']=array('in',$guanjian);
			$list = $this->ss->joinGetAll($join, $alias, $where, $pageParam, $order, 10, $field,$group);
		}
		

        foreach ($list['data'] as $key => $value) {
	     		$lnglat=explode(',', $value['ss_shop_location']);
	     		$lnglats['lng1']=$lnglat['0'];
	     		$lnglats['lat1']=$lnglat['1'];
	     		// $list["data"][$key]['lnglat1']=$lnglats;
     		if (!empty($this->param["mLongitudeAndLatitude"])) {
	     		$lnglat=explode(',', $this->param['mLongitudeAndLatitude']);
	     		$lnglatse['lng2']=$lnglat['0'];
	     		$lnglatse['lat2']=$lnglat['1'];
	     		// $list["data"][$key]['lnglat2']=$lnglatse;
     			$list["data"][$key]['lnglat']=round(getdistance($lnglats['lng1'], $lnglats['lat1'], $lnglatse['lng2'], $lnglatse['lat2'])/1000,2);
     		}
			$list['data'][$key]['ss_logo_img'] = IMG_URL . $value['ss_logo_img'];
			if ($list['data'][$key]['admin_nature']=='1') {
				if ($list['data'][$key]['nature']=='1') {
					$list['data'][$key]['nature'] = '厂家';
				}else if($list['data'][$key]['nature']=='2'){
					$list['data'][$key]['nature'] = '代理';
				}else if($list['data'][$key]['nature']=='3'){
					$list['data'][$key]['nature'] = '零售';
				}else if($list['data'][$key]['nature']=='4'){
					$list['data'][$key]['nature'] = '其他';
				}
			}else{
				$list['data'][$key]['nature']='';
			}
			$list['data'][$key]['natures'] =$value['nature'];
			$g_where = ["ss_id" => $value['ss_id'], "g_goods_verify" => '1', "s_is_show" => '0'];
			$g_wheres = ["uci_collection_id" => $value['ss_id']];
			$g_field = "g_id, g_name, g_current_price, g_show_img_path";
			$list['data'][$key]['goods_count'] = count($this->g->getList($g_where, $g_field, ["g_add_time"=>"desc"]));
			// $list['data'][$key]['uci_num'] = Db::name("user_collection_information")->where($g_wheres)->count();

			/*查找三个商品*/
			$list['data'][$key]['goods_info'] = $this->g->getList($g_where, $g_field, ["g_add_time"=>"desc"], 0, 3);
			foreach ($list['data'][$key]['goods_info'] as $k => $val) {
				$list['data'][$key]['goods_info'][$k]['g_show_img_path'] = IMG_URL . $val['g_show_img_path'];
			}

			/**商品关注量**/
            $list['data'][$key]["uci_collection_count"] = $this->uci->getCount(["uci_collection_id"=>$value["ss_id"],"type"=>1]);

		}
		// $list['data'][count($list["data"])]['lastPage']=$list['lastPage'];

		// return json_encode($list);
		// dump($list['data']);die();
        return json(format($list));
	}
	/**
	 * 店铺商品列表(46:装修装饰,3:建筑公司)
	 * @author 户连超
	 * @e-mail zrkjhlc@gmail.com
	 * @date   2017-11-06
	 */
	public function shopGoodsList()
	{
		 // $this->param['ss_id']=76;
   //      $this->param['page'] = 1;
		// $this->param['sgt_id']=6;
		// $this->u_id="134";
		if (empty($this->param['ss_id'])) {
        	return json(format('',223,'缺少必要参数!~'));
		}
		$order["g_shop_sort"] = "desc";
		$pageParam = [];
		$g_where = [];
        (isset($this->param['ss_id']) && ($this->param['ss_id'] != '')) ? $pageParam['query']['ss_id'] = $g_where['ss_id'] = $this->param['ss_id'] : false;

        (isset($this->param["composite"]) && ($this->param["composite"] != '')) ? $order["g_shop_sort"] = $this->param["composite"] : false;/*综合排序*/
        (isset($this->param["g_current_price"]) && ($this->param["g_current_price"] != '')) ? $order["g_current_price"] = $this->param["g_current_price"] : false;/*价格*/
        (isset($this->param["g_sales"]) && ($this->param["g_sales"] != '')) ? $order["g_sales"] = $this->param["g_sales"] : false;/*价格*/

        (isset($this->param["is_new"]) && ($this->param["is_new"] != '')) ? $order["g_add_time"] = $this->param["is_new"] : false;/*价格*/

        /*店铺信息*/
		$shop_info = $this->ss->getRow($g_where,"ss_logo_img,ss_id,sr_id,ss_name,ss_desc,ss_shop_province,ss_shop_city,ss_shop_area,ss_shop_address,ss_shop_location");
		$shop_info['ss_logo_img'] = IMG_URL . $shop_info['ss_logo_img'];

        /*如果只传顶级分类id,二级分类,三级分类的情况下应该怎么处理*/
        (isset($this->param['sgc_id']) && ($this->param['sgc_id'] != '')) ? $pageParam['query']['sgc_id'] = $g_where['sgc_id'] = $this->param['sgc_id'] : false;

		/*商品列表*/
		$pageParam['query']["g_goods_verify"] = $g_where["g_goods_verify"] = '1';
		$pageParam['query']["s_is_show"] = $g_where["s_is_show"] = '0';
		/*商品系列筛选*/
		if (isset($this->param["sgt_id"]) && ($this->param["sgt_id"] != '')) {
			$g_where["sgt_id"]=$this->param["sgt_id"];
		}
		$pageParam['page'] = $this->param['page'];
		$list = $this->g->getAll($g_where, $pageParam, $order, 10, "g_id,g_name,g_show_img_path,g_current_price");

		/*用户收藏列表*/
		$user_collection_list = $this->uci->getList(['type' => '0',"u_id" => $this->u_id],"uci_collection_id");
		foreach ($list['data'] as $key => $value) {
			$list['data'][$key]['g_show_img_path'] = IMG_URL . $value['g_show_img_path'];
			$list['data'][$key]['is_collection'] = 0;/*没收藏过*/
			$list['data'][$key]['g_current_price'] = $this->gsp->getOne(["g_id"=>$value["g_id"]],"gsp_price");
            if ($list["data"][$key]["g_current_price"]=='') {
     			$list["data"][$key]["g_current_price"]='';
     		}
			if (!empty($user_collection_list)) {
				foreach ($user_collection_list as $k => $v) {
					if ($value['g_id'] == $v["uci_collection_id"]) {
						$list['data'][$key]['is_collection'] = 1;/*收藏过*/
					}
				}
			}
		}
		/*用户店铺收藏列表*/
		$user_collection_lists = $this->uci->getList(['type' => '1',"u_id" => $this->u_id,"uci_collection_id" => $this->param['ss_id']],"uci_collection_id");
		$sm_where['ss_id'] = $this->param['ss_id'];
		$sm_where['sm_status'] = ["in","1,9"];
			/*店铺客服*/
		$list["sm_im_id"] = $this->sm->getOne($sm_where,"sm_im_id");
		if ($list["sm_im_id"]=='0') {
			$list["sm_im_id"] ='';
		}else{
			$list["sm_im_id"] =$list["sm_im_id"];
		}

		$list['shop_info'] = $shop_info;
		$list['shop_info']['ss_shop_province']=Db::name("region")->where('r_id='.$shop_info['ss_shop_province'])->field("r_name")->find()['r_name'];
		$list['shop_info']['ss_shop_city']=Db::name("region")->where('r_id='.$shop_info['ss_shop_city'])->field("r_name")->find()['r_name'];
		$list['shop_info']['ss_shop_area']=Db::name("region")->where('r_id='.$shop_info['ss_shop_area'])->field("r_name")->find()['r_name'];
		$list['shop_info']['addrs']=$list['shop_info']['ss_shop_province'].'-'.$list['shop_info']['ss_shop_city'].'-'.$list['shop_info']['ss_shop_area'].$shop_info['ss_shop_address'];
		if (!empty($user_collection_lists)) {
			$list['shop_info']['is_collections'] = 1;
		}else{
			$list['shop_info']['is_collections'] = 0;
		}
		$sgtwhere = array('ss_id'=>$this->param['ss_id']);
		$sgt = $this->sgt->getList($sgtwhere,'sgt_id,sgt_name');
		$list['shop_sgt'] = $sgt;
		if (!empty($this->u_id)) {
			$uhb_where['ubh_browsing_id'] = $this->param['ss_id'];
			$uhb_where['ubh_type'] = '0';
			$uhb_where['u_id'] = $this->u_id;
			$count = $this->ubh->getCount($uhb_where);
			if ($count < 1) {
				$this->ubh->save($uhb_where);
			}

		}
		// dump($list);die();
        return json(format($list));
	}

	/**
	 * 店铺商品首页图片(46:装修装饰,3:建筑公司)
	 * @author 李鑫
	 * @date   2018-05-05
	 */
	public function shopGoodsimg()
	{
		// $this->param['ss_id']=10;
		if (empty($this->param['ss_id'])) {
        	return json(format('',223,'缺少必要参数!~'));
		}
		$g_where = array('ss_id' => $this->param['ss_id']);
		$list=$this->ss->getRow($g_where,"ss_file");
		$lists=explode(',', $list['ss_file']);
		foreach ($lists as $key => $value) {
			$listse['data'][$key]['img_name']=IMG_URL.$value;
		}
		// dump($listse);die();
		// return json_encode($listse);
        return json(format($listse));
	}
	/**
	 * 获取店铺分类
	 * @author 户连超
	 * @e-mail zrkjhlc@gmail.com
	 * @date   2017-11-06
	 * @return [type]            [description]
	 */
	public function getShopGoodsCategory()
	{

		if ($this->param['ss_id'] == "") {
        	return json(format('',223,'缺少必要参数!~'));
		}
		$where['sgc_is_show'] = '1';
		$where["ss_id"] = $this->param['ss_id'];
		$where["sgc_parent_id"] = (isset($this->param['sgc_parent_id']) && ($this->param['sgc_parent_id'] != '')) ? $this->param['sgc_parent_id'] : 0;

        $list = $this->sgc->getList($where,"sgc_id,sgc_name",["sgc_sort" => "desc"]);
        return json(format($list));
	}
	/**
	 * 店铺详细信息
	 * @author 户连超
	 * @e-mail zrkjhlc@gmail.com
	 * @date   2017-11-06
	 */
	public function getShopInfo()
	{
		// $this->param['ss_id'] = 76;
		if (empty($this->param['ss_id'])) {
        	return json(format('',223,'缺少必要参数!~'));
		}
		$c_where['uci_collection_id'] = $where['ss_id'] = $this->param['ss_id'];
		$data = $this->ss->getRow($where,"ss_id,sr_id,ss_name,ss_confirm_time,mgc_id,qualifications,s_content,nature,admin_nature");
		// $data['ss_logo_img'] = IMG_URL . $data['ss_logo_img'];ss_logo_img,
		/*商铺资质图片*/
		$data['ss_zizhi_img'] = IMG_URL . $data['qualifications'];
		$c_where['type'] = 1;
		/*店铺营业执照*/
		$data['sr_business_path'] = IMG_URL . $this->sr->getOne(["sr_id" => $data['sr_id']],"sr_business_path");
		/*所有关注量*/
		// $data['collection_count'] = $this->uci->getCount($c_where);
		/*当前用户是否关注*/
		$c_where['u_id'] = $this->u_id;
		// $data['is_collection'] = $this->uci->getCount($c_where);
		/*商品数量*/
		$n_where["g_goods_verify"] = $where['g_goods_verify'] = 1;
		$data['g_count'] = $this->g->getCount($where);
		/*商品种类*/
		$mgc=explode(',', $data['mgc_id']);
		foreach ($mgc as $key => $value) {
			$mgcs[]=fenlei($value);
		}
		$data['mgc_id'] = implode(',', $mgcs);
		if ($data['admin_nature']=='1') {
			$data['nature']=$data['nature'];
		}else{
			$data['nature']='';
		}
		// $today = date2time(date("Y-m-d"));/*今天凌晨*/
		// $tomorrow = date2time(date("Y-m-d",time()+86400));/*明天凌晨*/
		// $n_where["g_add_time"] = ["between","$today,$tomorrow"];
		// $data['new_goods'] = $this->g->getCount($n_where);/*新增商品*/
		// $data['promotions'] = 0;/*促销商品,现在没写活动所以是0*/
		// dump($data);die();
		return json(format($data));
	}
	/**
	 * 添加用户收藏
	 * @author 户连超
	 * @e-mail zrkjhlc@gmail.com
	 * @date   2017-11-06
	 * @return [type]            [description]
	 */
	public function collectionAdd()
	{
		$post['type'] = $this->param['type'];/*类型:0:商品,1店铺,2规范,3招投信息,4简历*/
		$post['uci_collection_id'] = $this->param['uci_collection_id'];/*商品/店铺id/规范id/招投id/简历id*/
		$post["u_id"] = $this->u_id;/*用户id*/
		$post["uci_add_time"]=time();
		$rule = [
            "type" => "require|in:0,1,2,3,4",
            "uci_collection_id" => "require|number",
            "u_id" => "require|number",
        ];
        $msg = [
            "type.require" => "缺少收藏类型!~",
            "type.in" => "收藏类型传输有误!~",
            "u_id.require" => "缺少必要参数!~",
            "u_id.number" => "必要参数传输有误!~",
            "uci_collection_id.require" => "缺少商品活店铺信息!~",
            "uci_collection_id.number" => "商品活店铺信息传输有误!~",
        ];
        $data = verify($post, $rule, $msg);
        if ($data['code'] === 1) {
        	$count = $this->uci->getCount($post);
        	if ($count > 0) {
        		return json(format('', 251, '收藏失败,您已经收藏过了!~'));
        	} else {
	        	$id = $this->uci->save($post);
	        	if ($id > 0) {
	        		return json(format());
	        	} else {
	        		return json(format('', 252, '收藏失败!~'));
	        	}
        	}
        } else {
            return json(format('', 223, $data['msg']));
        }
	}
	/**
	 * 删除用户收藏
	 * @author 户连超
	 * @e-mail zrkjhlc@gmail.com
	 * @date   2017-11-06
	 */
	public function collectionDel()
	{
		$post['type'] = $this->param['type'];/*类型:0:商品,1店铺*/
		$post['uci_collection_id'] = $this->param['uci_collection_id'];/*商品或者店铺id*/
		$post["u_id"] = $this->u_id;/*用户id*/
		$rule = [
            "type" => "require|in:0,1,2,3,4",
            "uci_collection_id" => "require|number",
            "u_id" => "require|number",
        ];
        $msg = [
            "type.require" => "缺少收藏类型!~",
            "type.in" => "收藏类型传输有误!~",
            "u_id.require" => "缺少必要参数!~",
            "u_id.number" => "必要参数传输有误!~",
            "uci_collection_id.require" => "缺少商品活店铺信息!~",
            "uci_collection_id.number" => "商品活店铺信息传输有误!~",
        ];
        $data = verify($post, $rule, $msg);
        if ($data['code'] === 1) {
        	$count = $this->uci->getCount($post);
        	if ($count < 0) {
        		return json(format('', 251, '取消收藏失败,您已经取消收藏过了!~'));
        	} else {
	        	$id = $this->uci->del($post);
	        	if ($id > 0) {
	        		return json(format());
	        	} else {
	        		return json(format('', 252, '取消收藏失败!~'));
	        	}
        	}
        } else {
            return json(format('', 223, $data['msg']));
        }
	}
	/**
	 * 删除商品收藏
	 * @author 李鑫
	 * @date   2018-05-05
	 */
	public function goodscollectionDel()
	{
		// $post['type'] = 0;/*类型:0:商品,1店铺*/
		// $post['uci_collection_id'] = $this->param['uci_collection_id'];/*商品或者店铺id*/
		// $post["u_id"] = $this->u_id;/*用户id*/
		$usa_ids  = $info["uci_collection_id"] = $this->param['uci_collection_id'];//[1,2,3]

        $usa_ids = json_decode($usa_ids,true);
        $rule = [
            "uci_collection_id" => "require",
        ];
        $msg = [
            "uci_collection_id.require" => "商品id不能为空噢!~",
        ];
        $data = verify($info, $rule, $msg);
        if ($data['code'] === 1) {

            $ret = $this->uci->del(["uci_collection_id" => ["in", implode(",",$usa_ids)]]);
            
            if (false !== $ret) {
                return json(format('', 200, "商品收藏删除成功!~"));
            } else {
                return json(format('', 227, "商品收藏删除失败!~"));
            }
        }else{
            return json(format('', 223, $data['msg']));
        }
		

	}

	/**
	 * 删除其他收藏
	 * @author 李鑫
	 * @date   2018-05-05
	 */
	public function othercollectionDel()
	{
		$info['type'] = $this->param['type'];
		$info["u_id"] = $this->param['u_id'];
		$usa_ids  = $info["uci_collection_id"] = $this->param['uci_collection_id'];//[1,2,3]

        $usa_ids = json_decode($usa_ids,true);
        $rule = [
            "uci_collection_id" => "require",
        ];
        $msg = [
            "uci_collection_id.require" => "商品id不能为空噢!~",
        ];
        $data = verify($info, $rule, $msg);
        if ($data['code'] === 1) {

            $ret = $this->uci->del(["uci_collection_id" => ["in", implode(",",$usa_ids)],'type'=>$info['type'],'u_id'=>$info['u_id']]);
            
            if (false !== $ret) {
                return json(format('', 200, "商品收藏删除成功!~"));
            } else {
                return json(format('', 227, "商品收藏删除失败!~"));
            }
        }else{
            return json(format('', 223, $data['msg']));
        }
		

	}
	/**
	 * 获取收藏列表
	 * @author 户连超
	 * @e-mail zrkjhlc@gmail.com
	 * @date   2017-11-06
	 * @return [type]            [description]
	 */
	public function collectionList()
	{
		 // $this->param['type']="4";
		 // $this->u_id="152";
		$pageParam["query"]["type"] = $where['uci.type'] = $this->param['type'];/*类型:0:商品,1店铺*/
		$pageParam["query"]["u_id"] = $where["uci.u_id"] = $this->u_id;/*用户id*/
		$rule = [
            "type" => "require|in:0,1,2,3,4",
            "u_id" => "require|number",
        ];
        $msg = [
            "type.require" => "缺少收藏类型!~",
            "type.in" => "收藏类型传输有误!~",
            "u_id.require" => "缺少必要参数!~",
            "u_id.number" => "必要参数传输有误!~",
        ];
        $data = verify($pageParam["query"], $rule, $msg);
        if ($data['code'] === 1) {
        	$alias = "uci";
        	/*查询收藏的商品*/
        	if ($where['uci.type'] == 0) {
        		$join= [
        			["gjt_goods g", "g.g_id = uci.uci_collection_id"],
        		];
        		$field = "g.g_id,g.g_name,g.g_show_img_path,g.g_current_price,uci_id";
        		$order["g_sort"] = "desc";
        		$order["g_id"] = "desc";
        		// $where['uci.type'] = '0';
        		$list = $this->uci->joinGetAll($join, $alias, $where, $pageParam, $order, 0, $field);
        		if (!empty($list['data'])) {
        			foreach ($list['data'] as $key => $value) {
        				$list['data'][$key]['g_show_img_path'] = IMG_URL . $value['g_show_img_path'];

                        $gsp_price = $this->gsp->getOne(["g_id"=>$value["g_id"]],"gsp_price");
                        $list['data'][$key]['g_current_price'] = empty($gsp_price)?0:$gsp_price;
        			}
        		}


        	} else if ($where['uci.type'] == 1) {
        		/*查询收藏的店铺*/
        		$join= [
        			["gjt_seller_shop ss", "ss.ss_id = uci.uci_collection_id"],
					// ["gjt_goods g", "g.ss_id = uci.uci_collection_id"],
        		];
        		$field = "ss.ss_id, ss.ss_name, ss.ss_logo_img,ss.nature,ss.admin_nature";
        		$order["ss.ss_sort"] = "desc";
        		$order["ss.ss_id"] = "desc";
        		$group = "ss.ss_id";
        		$list = $this->uci->joinGetAll($join, $alias, $where, $pageParam, $order, 0, $field,$group);

        		if (!empty($list['data'])) {
        			foreach ($list['data'] as $key => $value) {
        				$list['data'][$key]['ss_logo_img'] = IMG_URL . $value['ss_logo_img'];
        				$list['data'][$key]['ss_num'] = $this->uci->getCount(['uci_collection_id'=>$value['ss_id'],'type'=>'1']);
        			}
        		}
        	}else if ($where['uci.type'] == 2) {
        		/*查询收藏的规范*/
        		$join= [
        			["gjt_engineering_specifications ges", "ges.es_id = uci.uci_collection_id"],
        		];
        		$field = "uci_id,ges.*";
        		$order["ges.es_id"] = "desc";
        		$group = "ges.es_id";
        		$list = $this->uci->joinGetAll($join, $alias, $where, $pageParam, $order, 0, $field,$group);
        		

        		
        	}else if ($where['uci.type'] == 3) {
        		/*查询收藏的招投信息*/
        		$join= [
        			["gjt_bidding_information gbi", "gbi.bi_id = uci.uci_collection_id"],
        		];
        		$field = "uci_id,gbi.*";
        		$order["gbi.bi_id"] = "desc";
        		$group = "gbi.bi_id";
        		$list = $this->uci->joinGetAll($join, $alias, $where, $pageParam, $order, 0, $field,$group);
        		if (!empty($list['data'])) {
        			foreach ($list['data'] as $key => $value) {
        				$list['data'][$key]['r_id'] = dizhi($value['r_id']);
        			}
        		}
        		
        	}else if ($where['uci.type'] == 4) {
        		/*查询收藏的简历*/
        		$join= [
        			["gjt_recruitment_info gri", "gri.ri_id = uci.uci_collection_id"],
        		];
        		$field = "uci_id,gri.*";
        		$order["gri.ri_id"] = "desc";
        		$group = "gri.ri_id";
        		$list = $this->uci->joinGetAll($join, $alias, $where, $pageParam, $order, 0, $field,$group);
        		if (!empty($list['data'])) {
        			foreach ($list['data'] as $key => $value) {
        				$list['data'][$key]['ui_id'] = $this->ui->getOne(["ui_id" => $value['ui_id']],"ui_name");
        				$list['data'][$key]['ri_work_province'] = dizhi($value['ri_work_province']);
        				$list['data'][$key]['ri_work_city'] = dizhi($value['ri_work_city']);
        				$list['data'][$key]['ri_work_area'] = dizhi($value['ri_work_area']);
        			}
        		}
        	}
        	// dump($list);die();
    		return json(format($list['data']));
        } else {
            return json(format('', 223, $data['msg']));
        }
	}
	/**
	 * 店铺广告列表
	 * @author 户连超
	 * @e-mail zrkjhlc@gmail.com
	 * @date   2017-11-10
	 * @return [type]            [description]
	 */
	public function shopAdList()
    {
        if (empty($this->param['ss_id'])) {
            return json(format("",223,"缺少必要参数!~"));
        }
        $where['ss_id'] = $this->param['ss_id'];
        $where["sa_position_type"] = '0';
        $where['sa_status'] = "1";
        $list = $this->sa->getList($where,"sa_title,sa_link,sa_img_path,sa_start_time,sa_end_time");
        $data = [];
        if (!empty($list)) {
	        foreach ($list as $key => $value) {
	            if ($value['sa_start_time'] > time() && $value['sa_start_time'] != 0 && $value['sa_end_time'] < time() && $value['sa_end_time'] != 0) {
	                unset($list[$key]);
	            } else if ($value['sa_end_time'] < time() && $value['sa_end_time'] != 0) {
	                unset($list[$key]);
	            } else {
	                $list[$key]['sa_img_path'] = IMG_URL . $value['sa_img_path'];
	            }
	        }
	        foreach ($list as $key => $value) {
	            $data[] = $value;
	        }
        }
        return json(format($data));
    }

    /**
     * 店铺入驻
     * @author 王牧田
     * @e-mail zrkjhlc@gmail.com
     * @date   2018-04-20
     * @return [type]            [description]
     */

    public function shopEntry(){

//        $this->param['u_id'] = 121;
//        $this->param['sr_real_name'] = "王牧田";
//        $this->param['sr_mobile'] = "18745016473";
//        $this->param['sr_email'] = "824495596@qq.com";
//        $this->param['sr_business_license_number'] = "1234567";
//        $this->param['sr_company_name'] = "那个公司";

        if (!isset($this->param['u_id'])) {
            return json(format('',223,'缺少必要参数!~'));
        }

        $post['u_id'] = $this->param['u_id'];
        $post['sr_real_name'] = $this->param['sr_real_name'];
        $post['sr_mobile'] = $this->param['sr_mobile'];
        $post['sr_email'] = $this->param['sr_email'];
        $post['sr_business_license_number'] = $this->param['sr_business_license_number'];
        $post['sr_company_name'] = $this->param['sr_company_name'];
        $post['sr_apply_time'] = time();
        $post['sr_ismsg'] = 1;
        $post['sr_company_location_address'] = "";
        $post['sr_emergency_contact'] = "";
        $post['sr_emergency_contact_phone'] = "";
        $rule = [
            "u_id" => "require|number",
            "sr_real_name" => "require",
            "sr_mobile" => "require",
            "sr_email" => "require",
            "sr_business_license_number" => "require",
            "sr_company_name" => "require",
        ];
        $msg = [
            "sr_company_name.require"=>"请填写公司名称!~",
            "u_id.require" => "缺少必要参数!~",
            "u_id.number" => "必要参数传输有误!~",
            "sr_real_name.require" => "请填写真实姓名!~",
            "sr_mobile.require" => "请填写手机号!~",
            "sr_email.require" => "请填写邮箱!~",
            "sr_business_license_number.require" => "请填写纳税人编号!~",
        ];



        $data = verify($post, $rule, $msg);
        if ($data['code'] === 1) {
            $srRow = $this->sr->getRow(["u_id"=>$this->param['u_id']]);
            if(empty($srRow)){
                $rs_id =  $this->sr->save($post);
                if ($rs_id < 0) {
                    return json(format('', 251, '申请入驻失败,请稍后重试!~'));
                } else {
                    return json(format('',200,'提交成功，三日内将以短信形式告知申请结果!~'));
                }
            }else{
                return json(format('', 251, '已申请入驻，不能重复申请!~'));
            }

        }else{
            return json(format('', 223, $data['msg']));
        }
    }



}

