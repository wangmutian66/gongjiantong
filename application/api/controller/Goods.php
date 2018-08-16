<?php
namespace app\api\controller;

use model\Goods as g;
use model\ManageGoodsCategory as mgc;
use model\GoodsPicture as gp;
use model\Plugin;
use model\ShopGoodsSpecification as sgs;
use model\SellerManagers as sm;
use model\SellerShop as ss;
use model\UserBrowsingHistory as ubh;
use model\UserCollectionInformation as uci;
use model\ShopGoodsPrice as sgp;
use model\ShopGoodsType as sgt;
use model\ShopGoodsItem as sgi;
use model\Complain as co;
use model\Order as ord;
use model\OrderAction as orda;
use model\GoodsSpecifications as gsp;
use model\Specifications as sp;
use model\Activity as act;
use \model\Plugin as log;
use model\OrderGoods as og;
use model\OrderAction as oa;
use model\OrderReturn as _or;
use think\Config;
use think\Db;
use model\Guarantee as gu;
use model\UserShippingAddress as usa;
use model\Region as r;
use model\Invoice as i;
use model\PayLog as pl;
use model\ManageBackgroundConfig as mbc;
use model\Orderevaluation as oe;
use model\Shoppingcart as sca;
use model\Users as u;
/**
 * 商品的一些处理
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-10-30
 */
class Goods extends Base
{
	protected $g;
	protected $mgc;
	protected $gp;
	protected $sgs;
	protected $sm;
	protected $ss;
	protected $uci;
	protected $ubh;
	protected $sgp;
	protected $sgt;
	protected $sgi;
	protected $co;
	protected $ord;
	protected $gsp;
    protected $sp;
    protected $act;
    protected $log;
    protected $og;
    protected $oa;
    protected $or;
    protected $gu;
    protected $usa;
    protected $r;
    protected $orda;
    protected $i;
    protected $pl;
    protected $mbc;
    protected $oe;
    protected $sca;
    protected $u;
	public function __construct()
	{
		parent::__construct();
		$this->g = new g();
		$this->mgc = new mgc();
		$this->gp = new gp();
		$this->sgs = new sgs();/*商家商品规格表*/
		$this->sm = new sm();
		$this->ss = new ss();
		$this->uci = new uci();
		$this->ubh = new ubh();
		$this->sgp = new sgp();/*商品规格设置钱数表*/
		$this->sgt = new sgt();/*商家商品类型*/
		$this->sgi = new sgi();/*规格内容*/
        $this->co = new co(); /*商品投诉*/
        $this->ord = new ord();
        $this->gsp = new gsp(); /*商品规格*/
        $this->sp = new sp(); /*规格名称*/
        $this->act=new act();/* 活动*/
        $this->log=new log();/*物流全部名称*/
        $this->og = new og(); /*订单商品*/
        $this->oa = new oa(); /*订单日志*/
        $this->or=new _or();/*退货*/
        $this->gu = new gu();
        $this->usa=new usa();/*收货地址*/
        $this->r = new r(); /*省市区*/
        $this->orda= new orda();/*订单日志*/
        $this->i=new i();
        $this->pl=new pl();/*支付记录表*/
        $this->mbc = new mbc();
        $this->oe=new oe();
        $this->sca=new sca();/*购物车*/
        $this->u=new u();/*购物车*/
	}
	/**
	 * 首页商品列表(建筑材料:1,工程机械:2)
	 * @author 户连超
	 * @e-mail zrkjhlc@gmail.com
	 * @date   2017-11-06
	 */
	public function GoodsList()
	{
        
		// $this->param['mgc_id']="";
  //      $this->param['page']=1;
  //      $this->param['composite']="desc";
  //      $this->param['type']="0";
  //      $this->param['keywords']="";
  //      $this->param['screen']="";
		//  $this->param['g_current_price']='';
		//   $this->param["keyword"]="小周牌多用模板";
		// $this->param["mLongitudeAndLatitude"]='126.854346,45.751438';
		if(isset($this->param["mgc_id"]) && ($this->param["mgc_id"] != '')){
		 	$this->param['mgc_id'];
		 	if (empty($this->param['mgc_id'])) {
        		return json(format('',223,'缺少必要参数!~'));
			}
		}

		if(isset($this->param["keyword"]) && ($this->param["keyword"] != '')){
		 	$this->param['keyword'];
		}
		// $this->param["screen"]='n1';
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
				$where['ss_id']=array('in',$fanwei);
			}else if ($srcarr[0]=='n2') {
				$fanwei = shaixuan('2','nature');
				foreach ($fanwei as $key => $value) {
					$fanwei[$key]=$value['ss_id'];
				}
				$fanwei=implode(",",$fanwei);
				$where['ss_id']=array('in',$fanwei);
			}else if ($srcarr[0]=='n3') {
				$fanwei = shaixuan('3','nature');
				foreach ($fanwei as $key => $value) {
					$fanwei[$key]=$value['ss_id'];
				}
				$fanwei=implode(",",$fanwei);
				$where['ss_id']=array('in',$fanwei);
			}else if ($srcarr[0]=='n4') {
				$fanwei = shaixuan('3','nature');
				foreach ($fanwei as $key => $value) {
					$fanwei[$key]=$value['ss_id'];
				}
				$fanwei=implode(",",$fanwei);
				$where['ss_id']=array('in',$fanwei);
			}
			if ($srcarr[0]!='n1' && $srcarr[0]!='n2' && $srcarr[0]!='n3' && $srcarr[0]!='n4' && !empty($srcarr[1])) {
				
				$fanwei = shaixuan($srcarr[1],'guarantee');
				foreach ($fanwei as $key => $value) {
					$fanwei[$key]=$value['ss_id'];
				}
				$fanwei=implode(",",$fanwei);
				$where['ss_id']=array('in',$fanwei);
			}
			if ($srcarr[0]!='n1' && $srcarr[0]!='n2' && $srcarr[0]!='n3' && $srcarr[0]!='n4' && empty($srcarr[1])) {
				$fanwei = shaixuan($srcarr[0],'guarantee');
				foreach ($fanwei as $key => $value) {
					$fanwei[$key]=$value['ss_id'];
				}
				$fanwei=implode(",",$fanwei);
				$where['ss_id']=array('in',$fanwei);
			}
		}
		// dump($srcarrs);
		if (isset($this->param["keyword"]) && ($this->param["keyword"] != '')) {
			// $where["g_name"] = ["like","%".$this->param['keyword'].'%'];
			$wherees["g_name"] = ["like","%".$this->param['keyword'].'%'];

	    	$gids= Db::name("goods")->where($wherees)->field('g_id')->select();
	    	if (!empty($gids)) {
		    	foreach ($gids as $key => $value) {
						$guanjian[$key]=$value['g_id'];
				}
			}
			$guanjians = shaixuangoods($this->param['keyword'],'g_keywords');
			foreach ($guanjians as $key => $value) {
				$guanjians[$key]=$value['g_id'];
			}
			if (!empty($guanjian)) {
				$guanjs= array_merge($guanjian,$guanjians);
			}else{
				$guanjs= $guanjians;
			}

			$guanjian=implode(",",$guanjs);
			if (!empty($guanjian)) {
				$where['g_id']=array('in',$guanjian);
			}else{
				$where["g_name"] = ["like","%".$this->param['keyword'].'%'];
			}

			unset($this->param['mgc_id']);

		}
		
		if(isset($this->param["mgc_id"]) && ($this->param["mgc_id"] != '')){
			if ($this->param['mgc_id'] == 1 || $this->param['mgc_id'] == 2 || $this->param['mgc_id'] == 3 || $this->param['mgc_id'] == 46) {
				$mgc_list = $this->mgc->getList(["mgc_parent_path" => ["like","%,".$this->param['mgc_id'].',%']],"mgc_id");
				foreach ($mgc_list as $key => $value) {
					$info[] = $value['mgc_id'];
				}
				$where["mgc_id"] = ['in',$this->param['mgc_id'].",".implode(",",$info)];
			} else {
				$where["mgc_id"] = $this->param['mgc_id'];
				
			}
		}
		$pageParam["query"]["g_goods_verify"] = $where['g_goods_verify'] = 1;/*审核通过*/
		$pageParam["query"]["is_show"] = $where['is_show'] = 1;/*上架*/
		$where['s_is_show'] = 0;
        $order["g_sort"] = "asc";
        $order["g_id"] = "desc";
		/*排序*/
        (isset($this->param["composite"]) && ($this->param["composite"] != '')) ? $order["g_shop_sort"] = $this->param["composite"] : false;/*综合排序*/
        (isset($this->param["g_current_price"]) && ($this->param["g_current_price"] != '')) ? $order["g_current_price"] = $this->param["g_current_price"] : false;/*价格*/
        (isset($this->param["g_sales"]) && ($this->param["g_sales"] != '')) ? $order["g_sales"] = $this->param["g_sales"] : false;
        (isset($this->param["is_new"]) && ($this->param["is_new"] != '')) ? $order["g_add_time"] = $this->param["is_new"] : false;

        if(isset($this->param["composite"]) && ($this->param["composite"] != '') ||
            isset($this->param["g_current_price"]) && ($this->param["g_current_price"] != '') ||
            isset($this->param["g_sales"]) && ($this->param["g_sales"] != '') ||
            isset($this->param["is_new"]) && ($this->param["is_new"] != '')){
            unset($order["g_sort"]);
            unset($order["g_id"]);
        }
        $resultcount = Db::name("goods")->where($where)->count();
        if (isset($this->param["page"]) && ($this->param["page"] != '')) {
        	$page=$this->param["page"];
        }else{
        	$page = "1";
        }
        


        $limit = 10; //限制一页显示的数量
        $field = "g_id,g_name,g_show_img_path,g_current_price,mgc_id,g_sales,ss_id,g_keywords,s_is_show";
        $list["data"] = $this->g->getList($where, $field,$order,($page-1)*$limit,$limit);

     	if (empty($list["data"])) {
     		unset($where['g_name']);
     		$mgc = $this->mgc->getOne(["mgc_name" => $this->param['keyword']],'mgc_id');
     		$where["mgc_id"] = $mgc;
     		$list["data"] = $this->g->getList($where, $field,$order,($page-1)*$limit,$limit);
     	}
     	foreach ($list["data"] as $k=>$row){
            $list["data"][$k]["g_current_price"] = $this->gsp->getOne(["g_id"=>$row["g_id"]],"gsp_price",["gsp_id"=>"asc"]);

            if ($list["data"][$k]["g_current_price"]=='') {
     			$list["data"][$k]["g_current_price"]='';
     		}
        }

     	$flag = array();  
  
		foreach($list["data"] as $v){  
		    $flag[] = $v['g_current_price'];  
		}  
		if (isset($this->param["g_current_price"]) && ($this->param["g_current_price"] != '') && $this->param["g_current_price"]=='desc'){
			array_multisort($flag, SORT_DESC, $list["data"]); 
		}else if (isset($this->param["g_current_price"]) && ($this->param["g_current_price"] != '') && $this->param["g_current_price"]=='asc') {
			array_multisort($flag, SORT_ASC, $list["data"]); 
		}
		 
     	foreach ($list["data"] as $key => $value) {
     		$lnglat=$this->ss->getRow(array("ss_id"=>$value['ss_id']),"ss_shop_location");
     		if (!empty($lnglat['ss_shop_location'])) {
	     		$lnglat=explode(',', $lnglat['ss_shop_location']);
	     		$lnglats['lng1']=$lnglat['0'];
	     		$lnglats['lat1']=$lnglat['1'];
	     		$list["data"][$key]['lnglat1']=$lnglats;
     		
	     		if (!empty($this->param["mLongitudeAndLatitude"])) {
		     		$lnglat=explode(',', $this->param['mLongitudeAndLatitude']);
		     		$lnglatse['lng2']=$lnglat['0'];
		     		$lnglatse['lat2']=$lnglat['1'];
		     		// $list["data"][$key]['lnglat2']=$lnglatse;
	     			$list["data"][$key]['lnglat']=round(getdistance($lnglats['lng1'], $lnglats['lat1'], $lnglatse['lng2'], $lnglatse['lat2'])/1000,2);
	     		}else{

	     			$list["data"][$key]['lnglat']="";
	     		}
     		}else{

	     			$list["data"][$key]['lnglat']="";
	     		}
     	
			$list["data"][$key]['g_show_img_path'] = IMG_URL . $value['g_show_img_path'];
			$wheres['ss_id'] = $list["data"][$key]['ss_id'];
        	$ss = $this->ss->getRow($wheres);

			if (!empty($ss['admin_nature'])) {
				$list["data"][$key]['admin_nature'] = $ss['admin_nature'];
				if ($list["data"][$key]['admin_nature']=='1') {
					$list["data"][$key]['nature'] = $ss['nature'];
				}else{
					$list["data"][$key]['nature'] = '';
				}
			}else{
					$list["data"][$key]['nature'] = '';
			}
			if (!empty($ss['guarantes'])) {
				$list["data"][$key]['guarantee'] = unserialize($ss['guarantes']);
				foreach ($list["data"][$key]['guarantee']  as $k => $v) {
					$contents= $this->gu->getRow(array("gu_id"=>$v['guids']),"content,g_img");
                    $list['data'][$key]['guarantee'][$k]['guname']=$contents['content'];
                    $list['data'][$key]['guarantee'][$k]['guarantess']=$contents['content'];
				}
			}
        }
		if (isset($this->param["g_sales"]) || isset($this->param["g_current_price"])) {
        }else{
        	$flags = array();  
  
			foreach($list["data"] as $vas){  
			    $flags[] = $vas['lnglat'];  
			}	
			if (isset($this->param["mLongitudeAndLatitude"])){
				array_multisort($flags, SORT_ASC, $list["data"]); 
			}
        }
        
        if(isset($this->param["mgc_id"]) && ($this->param["mgc_id"] != '')){
        	$list["goods_category"] =$this->getgoodsCategory($this->param['mgc_id']);
    	}else{
    		$list["goods_category"] =$this->getgoodsCategory();
    	}
    	$list["pageCount"] = intval(ceil($resultcount/$limit));
        $list["currentsPage"] = $page;
        // dump($where);
        // dump($list);die();
        return json(format($list));
	}

	/**
	 * 猜你喜欢(建筑材料:1,工程机械:2)
	 * @author 户连超
	 * @e-mail zrkjhlc@gmail.com
	 * @date   2017-11-06
	 */
	public function GoodsLists()
	{
		if(isset($this->param["mgc_id"]) && ($this->param["mgc_id"] != '')){
		 	$this->param['mgc_id'];
		 	if (empty($this->param['mgc_id'])) {
        		return json(format('',223,'缺少必要参数!~'));
			}
		}
		
		if(isset($this->param["keywords"]) && ($this->param["keywords"] != '')){
		 	$this->param['keywords'];
		}
		if (isset($this->param["keywords"]) && ($this->param["keywords"] != '')) {
			$where["g_name"] = ["like","%".$this->param['keywords'].'%'];
		}
		if(isset($this->param["mgc_id"]) && ($this->param["mgc_id"] != '')){
			if ($this->param['mgc_id'] == 1 || $this->param['mgc_id'] == 2 || $this->param['mgc_id'] == 3 || $this->param['mgc_id'] == 46) {
				$mgc_list = $this->mgc->getList(["mgc_parent_path" => ["like","%,".$this->param['mgc_id'].',%']],"mgc_id");
				foreach ($mgc_list as $key => $value) {
					$info[] = $value['mgc_id'];
				}
				$where["mgc_id"] = ['in',$this->param['mgc_id'].",".implode(",",$info)];
			} else {
				$where["mgc_id"] = $this->param['mgc_id'];
			}
		}
		$pageParam["query"]["g_goods_verify"] = $where['g_goods_verify'] = 1;/*审核通过*/
		$pageParam["query"]["is_show"] = $where['is_show'] = 1;/*上架*/
		$where['s_is_show'] = 0;
        $order["g_sort"] = "asc";
        $order["g_id"] = "desc";
		/*排序*/
        (isset($this->param["composite"]) && ($this->param["composite"] != '')) ? $order["g_shop_sort"] = $this->param["composite"] : false;/*综合排序*/
        (isset($this->param["g_current_price"]) && ($this->param["g_current_price"] != '')) ? $order["g_current_price"] = $this->param["g_current_price"] : false;/*价格*/
        (isset($this->param["g_sales"]) && ($this->param["g_sales"] != '')) ? $order["g_sales"] = $this->param["g_sales"] : false;
        (isset($this->param["is_new"]) && ($this->param["is_new"] != '')) ? $order["g_add_time"] = $this->param["is_new"] : false;

        if(isset($this->param["composite"]) && ($this->param["composite"] != '') ||
            isset($this->param["g_current_price"]) && ($this->param["g_current_price"] != '') ||
            isset($this->param["g_sales"]) && ($this->param["g_sales"] != '') ||
            isset($this->param["is_new"]) && ($this->param["is_new"] != '')){
            unset($order["g_sort"]);
            unset($order["g_id"]);
        }
        $resultcount = Db::name("goods")->where($where)->count();
        if (isset($this->param["page"]) && ($this->param["page"] != '')) {
        	$page=$this->param["page"];
        }else{
        	$page = "1";
        }
		
        // $page = "16";
        $limit = 10; //限制一页显示的数量
        $field = "g_id,g_name,g_show_img_path,g_current_price,mgc_id,g_sales,ss_id";
        $list["data"] = $this->g->getList($where, $field,$order,($page-1)*$limit,$limit);
       
       foreach ($list["data"] as $key => $value) {
			$list["data"][$key]['g_show_img_path'] = IMG_URL . $value['g_show_img_path'];
			$wheres['ss_id'] = $list["data"][$key]['ss_id'];
        	$ss = $this->ss->getRow($wheres);
        	if (!empty($ss['admin_nature'])) {
        	
				if (!empty($ss['admin_nature'])) {
					$list["data"][$key]['admin_nature'] = $ss['admin_nature'];
					if ($list["data"][$key]['admin_nature']=='1') {
						$list["data"][$key]['nature'] = $ss['nature'];
					}else{
						$list["data"][$key]['nature'] = '';
					}
				}else{
						$list["data"][$key]['nature'] = '';
				}
				if (!empty($ss['guarantes'])) {
					$list["data"][$key]['guarantee'] = unserialize($ss['guarantes']);
					foreach ($list["data"][$key]['guarantee']  as $k => $v) {
						$contents= $this->gu->getRow(array("gu_id"=>$v['guids']),"content,g_img");
	                    $list['data'][$key]['guarantee'][$k]['guname']=$contents['content'];
	                    $list['data'][$key]['guarantee'][$k]['guarantess']=$contents['content'];
					}
				}
			}
        }
      
        if(isset($this->param["mgc_id"]) && ($this->param["mgc_id"] != '')){
        	$list["goods_category"] =$this->getgoodsCategory($this->param['mgc_id']);
    	}else{
    		$list["goods_category"] =$this->getgoodsCategory();
    	}
    	$list["pageCount"] = intval(ceil($resultcount/$limit));
        $list["currentsPage"] = $page;

        return json(format($list));
	}
	/**
	 * 商品筛选列表
	 * @author 李鑫
	 * @date   2018-04-26
	 */
	public function GoodsScreen()
	{
		$list[0]['name']="卖家性质";
		$list[0]['saleVo']=array('0'=>array('gu_id'=>'n1','gu_name'=>'厂家'),  
              '1'=>array('gu_id'=>'n2','gu_name'=>'代理'),  
              '2'=>array('gu_id'=>'n3','gu_name'=>'零售'),
              '3'=>array('gu_id'=>'n4','gu_name'=>'其他'),
            );
		$list[1]['name']="卖家保障";
		$list[1]['saleVo']=$this->gu->getList();
		return json(format($list));

	}
	public function getgoodsCategory($mgc_id=0){
		$result = $this->mgc->getList(['mgc_parent_id' => $mgc_id, "mgc_is_show" => "1"],'mgc_id,mgc_parent_id,mgc_name,mgc_image',['mgc_sort' => "asc"]);
		foreach ($result as $key => $value) {
			$result[$key]["childcategory"]=  $this->getgoodsCategory($value["mgc_id"]);
			foreach ($result[$key]["childcategory"] as $k => $v) {
				$result[$key]["childcategory"][$k]['mgc_image']=IMG_URL.$v['mgc_image'];
			}
		}
		return $result;
	}
	/**
	 * 获取下级分类
	 * @author 户连超
	 * @e-mail zrkjhlc@gmail.com
	 * @date   2017-11-06
	 */
	public function getNextCategory()
	{
		if ($this->param['mgc_id'] == "") {
			return json(format('',223,'缺少必要参数!~'));
		}
		$list = $this->mgc->getList(['mgc_parent_id' => $this->param['mgc_id'], "mgc_is_show" => 1],'mgc_id,mgc_name',['mgc_sort' => "asc"]);

        return json(format($list));
	}
	/**
	 * 商品详细信息
	 * @author 户连超
	 * @e-mail zrkjhlc@gmail.com
	 * @date   2017-11-07
	 */
	public function goodsInfo()
	{
         // $this->param["g_id"] = 343;
		
		if (empty($this->param["g_id"])) {
			return json(format('',223,'缺少必要参数!~'));
		}

		$where['g_id'] = $this->param["g_id"];
		/*查询商品信息*/
		$data['goods_info'] = $this->g->getRow($where);

		if (!empty($data['goods_info'])) {
            /*获取商品规格*/
         
//            $goodsGuige = $this->sp->getList(['sgt_id'=>$data['goods_info']['sgt_id']]);
//            foreach ($goodsGuige as $k=>$row){
//            	$wheres['sp_id'] = $row['id'];
//                $wheres['g_id'] = $this->param["g_id"];
//            	$guige = $this->gsp->getRow($wheres,'gsp_id,sp_id,gsp_inventory,gsp_price');
//            	$goodsGuige[$k]["guige"]=$guige;
//            	$goodsGuige[$k]["activity"]=getActivityPrice($this->param["g_id"],$row['id']);
//            }
            $data['goods_info']['sgt_name']=sgtname($data['goods_info']['sgt_id']);
            $goodsGuige=array();
            $i=0;
            $wheres['g_id'] = $this->param["g_id"];
            $guige = $this->gsp->getList($wheres,'gsp_id,sp_id,gsp_inventory,gsp_price,gsp_weight,gsp_filepath');
            //IMG_URL
            foreach ($guige as $k=>$row){
                $goodsGuige[$i]["id"] = $row['sp_id'];
                $spRow = $this->sp->getRow(["id"=>$row['sp_id']],"sgt_id,sp_name");
               	if (!empty($spRow)) {
                    $goodsGuige[$i]["sp_name"] = $spRow['sp_name'];
                    $goodsGuige[$i]["sgt_id"] = $spRow['sgt_id'];
                }
                if (!empty($row["gsp_filepath"])) {
                	$row["gsp_filepath"] = IMG_URL.$row["gsp_filepath"];
                }else{
                	$row["gsp_filepath"] ="";
                }
                $goodsGuige[$i]["guige"]=$row;
                $goodsGuige[$i]["activity"]=getActivityPrice($this->param["g_id"],$row['sp_id']);
                $i++;
            }
            foreach ($goodsGuige as $k=>$row){
            	if (empty($row['guige'])) {
            		unset($goodsGuige[$k]);
            	}
            	if (empty($row['sp_name'])) {
            		unset($goodsGuige[$k]);
            	}
//            	if ($row['activity']['act_type'] =='0') {
//            		unset($goodsGuige[$k]['activity']);
//            	}
            }
            sort($goodsGuige);
			$data['goods_and_specifications']=$goodsGuige;

            // $data['goods_info']['guige'] = $goodResult;
			$data['goods_info']["g_show_img_path"] = IMG_URL . $data['goods_info']['g_show_img_path'];
	        $data['goods_info']['g_content'] = preg_replace('/(<img.+?src=")(.*?)/', '$1' . HTTP_HOST . '$2', $data['goods_info']['g_content']);
			/*商品轮播图*/
			$data["img_banner"] = $this->gp->getList($where,"gp_picture_path");
			
			foreach ($data["img_banner"] as $key => $value) {
				$data["img_banner"][$key]["gp_picture_path"] = IMG_URL . $value['gp_picture_path'];
			}

			/*商品规格类型*/
			// /*查询数商品的规格和价钱*/
			// $sgp_where['goods_id'] = $this->param['g_id'];
			// $sgp_list = $this->sgp->getList($sgp_where);/*商品规格设置钱数表*/
			// $array = [];
			// /*把查询出来的id全都放到一个数组里面*/
			// foreach ($sgp_list as $key => $value) {
			// 	$tmp_val = explode("_", $value['keys']);
			// 	array_push($array, $tmp_val);
			// }
			// /*讲上面的二维数组变成一维数组*/
			// $tmp_arr = [];
			// foreach ($array as $key => $value) {
			// 	foreach ($value as $k => $v) {
			// 		array_push($tmp_arr,$v);
			// 	}
			// }
			// /*去除重复*/
			// $sgi_ids = array_unique($tmp_arr);
			// $sgi_where['sgi_id'] = ['in', implode(",", $sgi_ids)];
			// /*查询出该商品添加的规格值*/
			// $sgi_list = $this->sgi->getList($sgi_where);
			// $sgi_tmp = [];
			// foreach ($sgi_list as $key => $value) {
			// 	array_push($sgi_tmp, $value['sgs_id']);
			// }

			// $sgs_ids = array_unique($sgi_tmp);
			// /*查询规格名称和类型*/
			// $sgs_where['sgs_id'] = ['in',implode(",", $sgs_ids)];
			// $sgs_list = $this->sgs->getList($sgs_where,'sgs_id,sgs_name,sgs_type');

			// $data['goods_and_specifications'] = [];
			// /*将其改为二维数组*/
			// foreach ($sgs_list as $key => $value) {
			// 	$data['goods_and_specifications'][$key] = $value;
			// 	foreach ($sgi_list as $k => $val) {
			// 		if ($value['sgs_id'] == $val['sgs_id']) {
			// 			unset($val['sgs_id']);
			// 			$data['goods_and_specifications'][$key]['sgi_info'][] = $val;
			// 		}
			// 	}
			// }

			$sm_where['ss_id'] = $data['goods_info']["ss_id"];
			/*店铺信息*/
			$data['shop_info'] = $this->ss->getRow($sm_where,"ss_name,ss_shop_province,ss_shop_city,ss_shop_area,ss_logo_img,nature,admin_nature,guarantee,guarantes,ss_invoice");
			$data['shop_info']['ss_logo_img'] = IMG_URL . $data['shop_info']['ss_logo_img'];
			if (empty($data['shop_info']['guarantes'])) {
				$data['shop_info']['guarantes'] = array();
			}else{
				$data['shop_info']['guarantes'] = unserialize($data['shop_info']['guarantes']);
				foreach ($data['shop_info']['guarantes'] as $key => $value) {
					$contents= $this->gu->getRow(array("gu_id"=>$value['guids']),"content,g_img");
					if (!empty($value['guarantess'])) {
						$data['shop_info']['guarantes'][$key]['content']=$value['guarantess'].$contents['content'];
					}else{
                        $data['shop_info']['guarantes'][$key]['content']=$contents['content'];
                        $data['shop_info']['guarantes'][$key]['guarantess']=$contents['content'];
                        $data['shop_info']['guarantes'][$key]['guname']=$contents['content'];
                    }
					$data['shop_info']['guarantes'][$key]['g_img']=IMG_URL.$contents['g_img'];

				}
			}
			if ($data['shop_info']['admin_nature']=='1') {
				$data['shop_info']['nature']=$data['shop_info']['nature'];
			}else{
				$data['shop_info']['nature']='';
			}
			
			$sm_where['sm_status'] = ["in","1,9"];
			/*店铺客服*/
			$data["sm_im_id"] = $this->sm->getOne($sm_where,"sm_im_id");

			/*判断此人是否收藏该商品*/
			if($this->u_id > 0){
				$uci_where['type'] = "0";
				$ubh_where['u_id'] = $save['u_id'] = $uci_where['u_id'] = $this->u_id;
				$ubh_where['ubh_browsing_id'] = $save['ubh_browsing_id'] = $uci_where['uci_collection_id'] = $this->param['g_id'];
				$data['is_collection'] = $this->uci->getCount($uci_where);
				$save['ubh_type'] = "1";
				$ubh_count = $this->ubh->getCount($save);
				$save['ubh_time'] = time();
				if ($ubh_count > 0) {
					$this->ubh->save($save,$ubh_where);
				} else {
					$this->ubh->save($save);
				}
			}else{
				$data['is_collection'] = 0;
			}

			
			// dump($this->oe->getLastSql());
           // dump($data);
           // exit();
	        return json(format($data));
		} else {
	        return json(format());
		}
	}


    /**
     * 新商品详细信息
     * @author wangmutian
     * @e-mail zrkjhlc@gmail.com
     * @date   2018-04-07
     */
    public function goodsShowInfo(){

//        $this->param["g_id"] = 38;
        if (empty($this->param["g_id"])) {
            return json(format('',223,'缺少必要参数!~'));
        }
        $where['g_id'] = $this->param["g_id"];
        /*查询商品信息*/

        //g_name,g_show_img_path,g_current_price
        $data['goods_info'] = $this->g->getRow($where);


        return json(format());

    }




	/**
	 * 推荐商品列表
	 * @author 户连超
	 * @e-mail zrkjhlc@gmail.com
	 * @date   2017-11-07
	 */
	public function recommendGoodsList()
	{

		$wheren['is_new'] = "1";
		$wheren['g_goods_verify'] = "1";
		$field = "g_id,g_name,ss_id,g_show_img_path,g_current_price,is_new,is_recommend,is_hot,g_sales";
		$list['is_new'] = $this->g->getList($wheren,$field,[],4);
		foreach ($list['is_new'] as $key => $value) {
			$list['is_new'][$key]["g_show_img_path"] = IMG_URL . $value['g_show_img_path'];
			if (unserialize(baozhang($value['ss_id']))) {
				$list['is_new'][$key]["guarantee"] = unserialize(baozhang($value['ss_id']));
			}else{
				$list['is_new'][$key]["guarantee"] =array();
			}
			
			if (zizhis($value['ss_id'])=='1') {
				$list['is_new'][$key]["nature"] = zizhi($value['ss_id']);
			}else{
				$list["is_new"][$key]['nature'] = '';
			}
			
			$list['is_new'][$key]["g_current_price"] = $this->gsp->getOne(["g_id"=>$value["g_id"]],"gsp_price");
		}
		$wherer['is_recommend'] = "1";
		$wherer['g_goods_verify'] = "1";
		$list['is_recommend'] = $this->g->getList($wherer,$field,[],4);
		foreach ($list['is_recommend'] as $key => $value) {
			$list['is_recommend'][$key]["g_show_img_path"] = IMG_URL . $value['g_show_img_path'];
			if (unserialize(baozhang($value['ss_id']))) {
				$list['is_recommend'][$key]["guarantee"] = unserialize(baozhang($value['ss_id']));
			}else{
				$list['is_recommend'][$key]["guarantee"] =array();
			}
			if (zizhis($value['ss_id'])=='1') {
				$list['is_recommend'][$key]["nature"] = zizhi($value['ss_id']);
			}else{
				$list["is_recommend"][$key]['nature'] = '';
			}
			$list['is_recommend'][$key]["g_current_price"] = $this->gsp->getOne(["g_id"=>$value["g_id"]],"gsp_price");
		}
		$whereh['is_hot'] = "1";
		$whereh['g_goods_verify'] = "1";
		$list['is_hot']  = $this->g->getList($whereh,$field,[],4);
		foreach ($list['is_hot'] as $key => $value) {
			$list['is_hot'][$key]["g_show_img_path"] = IMG_URL . $value['g_show_img_path'];
			if (unserialize(baozhang($value['ss_id']))) {
				$list['is_hot'][$key]["guarantee"] = unserialize(baozhang($value['ss_id']));
			}else{
				$list['is_hot'][$key]["guarantee"] =array();
			}
			if (zizhis($value['ss_id'])=='1') {
				$list['is_hot'][$key]["nature"] = zizhi($value['ss_id']);
			}else{
				$list["is_hot"][$key]['nature'] = '';
			}
			$list['is_hot'][$key]["g_current_price"] = $this->gsp->getOne(["g_id"=>$value["g_id"]],"gsp_price");
		}
		// dump($list);die();
        return json(format($list));
	}

	/**
	 * 选择属性后获取商品金额
	 * @author 户连超
	 * @e-mail zrkjhlc@gmail.com
	 * @date   2017-11-17
	 */
	public function getGoodsMoney()
	{
		/*拼装的值*/
		if (empty($this->param['keys'])) {
			return json(format('',223,'缺少必要参数!~'));
		}
		/*商品id*/
		if (empty($this->param['g_id'])) {
			return json(format('',223,'缺少必要参数!~'));
		}
		/*拆分字符串*/
		$keys = explode('_', $this->param['keys']);
		/*排序,获取查询条件*/
		sort($keys);
		$where['keys'] = implode('_', $keys);
		$where['goods_id'] = $this->param['g_id'];
		/*查询出当前选择属性的价格*/
		$info = $this->sgp->getRow($where,'price,store_count');
		if ($info) {
			return json(format($info));
		} else {
			return json(format('',223,'网路出错,请稍后再试!~'));
		}
	}

    /**
     * [商品投诉]
     * @author wangmutian
     * @date 2018-04-13
     */
	public function goodComplain(){

        $post['g_id'] = $this->param["g_id"];
        $post['co_tel'] = $this->param["co_tel"];
        $post['co_imgs'] = $this->param["co_imgs"];
        $post['co_content'] = $this->param["co_content"];

        $rule = [
            "g_id" => "require",
            "co_tel" => "require",
            "co_imgs" => "require",
            "co_tel" => "require",
        ];
        $msg = [
            "g_id.require" => "请选择商品!~",
            "co_tel.require" => "缺少必要参数!~",
            "co_imgs.require" => "缺少必要参数!~",
            "co_content.require" => "缺少必要参数!~",
        ];

        $data = verify($post, $rule, $msg);
        if ($data['code'] === 1) {
            $post['co_imgs'] = implode(",",$post['co_imgs']);
            $id = $this->co->save($post);
            if ($id > 0) {
                return json(format());
            } else {
                return json(format('', 253, "投诉失败!~"));
            }
        }else{
            return json(format('', 223, $data['msg']));
        }
    }


    /**
     * [商品规格]
     * @author wangmutian
     * @date 2018-04-20
     */
    public function spec(){
        $g_id = $this->param["g_id"];
        $join = [
            ["specifications sp", "gs.sp_id = sp.id"],
        ];
        $where = ["gs.g_id"=>$g_id];
        $gsplist = $this->gsp->joinGetList($join,"gs",$where,[],"sp.sp_name,gs.gsp_price");
        return json(format(rc4Encypt($gsplist),200,"success"));
    }

    /**
     * [订单详情]
     * @author wangmutian
     * @date 2018-04-20
     */
    public function orderinfo(){
//         $this->param["usa_id"] = "82";
//         $this->param["goods_info"] = '[{"g_id":"372","g_name":"小周牌多用模板","g_show_img_path":"http://192.168.2.190/public/upload/ShopGoods/18_06_11/5b1e0e0b56c23.jpg","g_unit_name":"块","goods_buy_num":"1","gsp_name":"多功能建筑拼块模板","money":"0.37","sca_id":"17","sp_id":"321"},{"g_id":"362","g_name":"小凤复合式模板","g_show_img_path":"http://192.168.2.190/public/upload/ShopGoods/18_06_08/5b19ea2f4bcf1.jpg","g_unit_name":"块","goods_buy_num":"1","gsp_name":"大型钢木(竹)组合模板","money":"0.77","sca_id":"18","sp_id":"280"}]';
        if (empty($this->param["usa_id"])) {
            return json(format('',223,'缺少必要参数!~'));
        }

        /**
         *  http://192.168.2.165/api/Goods/orderinfo;39;[{"g_id":"130","g_name":"奋斗","g_show_img_path":"http://192.168.2.165/public/upload/ShopGoods/17_11_04/59fd24b1d1cac.jpg","goods_buy_num":"1","gsp_name":"1","money":"63.0","sp_id":"80","ss_invoice":"3"}]
         */
//        $ss_id = 0;
        $usa_id = $this->param["usa_id"];
        $goods_info = $this->param["goods_info"];
        $goods_info=json_decode($goods_info,true);
        $order_goods = array();
        $goods_total=0;
        $goods_weight=0;
        $array=array();
        $ssarr = array();
        foreach ($goods_info as $k=>$row){

            $g_id = $row["g_id"];
            $sp_id = $row["sp_id"];

            $order_goods[$k]=$this->g->getRow(["g_id"=>$g_id],"g_sn,g_name,g_show_img_path,g_weight,ss_id,g_unit_value,g_unit_name");
            $order_goods[$k]["g_show_img_path"] = IMG_URL.$order_goods[$k]["g_show_img_path"];
            $order_goods[$k]["goods_buy_num"] = $row["goods_buy_num"];
            $order_goods[$k]['g_id'] = $g_id;
            $order_goods[$k]['sp_id'] = $sp_id;
            $gsprow=$this->gsp->getRow(["g_id"=>$g_id,"sp_id"=>$sp_id],"gsp_inventory,gsp_price");

            $sp_name = $this->sp->getRow(["id"=>$sp_id],"sp_name");

            $order_goods[$k]["sp_name"] = empty($sp_name)?'':$sp_name['sp_name'];

            if(!empty($gsprow)){
                $order_goods[$k] = array_merge($order_goods[$k],$gsprow);
            }else{
                $order_goods[$k]["gsp_price"] = 0;
            }

            $activy = getActivityPrice($g_id,$sp_id);


            switch ($activy["act_type"]){
                case 0:
                    $order_goods[$k]["act_msg"] = "暂无活动";
                    $order_goods[$k]["g_discount_price"] = $order_goods[$k]["gsp_price"];
                    break;
                case 1:
                    $order_goods[$k]["act_msg"] = "折扣";
                    $order_goods[$k]["g_discount_price"] = $activy["g_discount_price"];
                    break;
                case 2:
                    $order_goods[$k]["act_msg"] = "满{$activy["act_meet"]}减{$activy["act_reduction"]}";
                    $order_goods[$k]["g_discount_price"] = $activy["g_discount_price"];
                    break;
            }

            $order_goods[$k]["goods_one_total"] =$order_goods[$k]["goods_buy_num"] * $order_goods[$k]["g_discount_price"];
            $goods_total += $order_goods[$k]["goods_one_total"];

            $gspOne = $this->gsp->getOne(["sp_id"=>$row["sp_id"],"g_id"=>$row["g_id"]],"gsp_weight");

            $gspOne = empty($gspOne)?0:$gspOne;

            $goods_weight +=  $gspOne * $row["goods_buy_num"]; //$order_goods[$k]["g_weight"]
            $ss_id = $order_goods[$k]["ss_id"];
            //计算订单总价
            $order_goods[$k]["goods_weightmoney"] = $order_goods[$k]["g_discount_price"] * $row["goods_buy_num"];
            $order_goods[$k]["goods_weight"] = $gspOne;
            $array['totalprice'][$ss_id][] = $order_goods[$k]["g_discount_price"] * $row["goods_buy_num"];
            $array['goods_weight'][$ss_id][] = $gspOne;

            if(!in_array($ss_id,$ssarr)){
                array_push($ssarr,$ss_id);
                $array['goods_list'][$ss_id][0] = $order_goods[$k];
            }else{
                $array['goods_list'][$ss_id][] = $order_goods[$k];
            }
        }



        $u_id = $this->usa->getOne(["usa_id"=>$this->param["usa_id"]],"u_id");
        $iListArr = [];
        $iListArr[0]["i_id"]="";
        $iListArr[0]["u_id"]="";
        $iListArr[0]["i_name"]="";
        $iListArr[0]["i_num"]="";
        $iListArr[0]["i_email"]="";
        $iListArr[0]["i_invtype"]="0";
        $iListArr[0]["i_taitype"]="";

        $iListArr[1]["i_id"]="";
        $iListArr[1]["u_id"]="";
        $iListArr[1]["i_num"]="";
        $iListArr[1]["i_name"]="";
        $iListArr[1]["i_invtype"]="1";
        $iListArr[1]["i_taitype"]="";

        $iListArr[2]["i_id"]="";
        $iListArr[2]["u_id"]="";
        $iListArr[2]["i_name"]="";
        $iListArr[2]["i_num"]="";
        $iListArr[2]["i_email"]="";
        $iListArr[2]["i_regtel"]="";
        $iListArr[2]["i_regaddr"]="";
        $iListArr[2]["i_bank"]="";
        $iListArr[2]["i_banknum"]="";
        $iListArr[2]["i_conname"]="";
        $iListArr[2]["i_contel"]="";
        $iListArr[2]["i_conaddr"]="";
        $iListArr[2]["i_invtype"]="2";

        $iList = $this->i->getList(["u_id"=>$u_id]);
        foreach ($iList as $k=>$row){
            $iListArr[$row['i_invtype']] = $row;
            foreach ($row as $key=>$a){
                if($a == ""){
                    unset($iList[$k][$key]);
                    unset($iListArr[$row['i_invtype']][$key]);
                }
            }
        }


        foreach ($ssarr as $k=>$arr){
            $ssRow = $this->ss->getRow(array("ss_id"=>$arr),"ss_id,ss_name,ss_invoice");

            //店铺id
            $order_goods_array[$k]["ss_id"] = $arr;
            //店铺名称
            $order_goods_array[$k]["ss_name"] = $ssRow['ss_name'];
            $order_goods_array[$k]["totalprice"] =  array_sum($array['totalprice'][$arr]);
            //商品列表
            $order_goods_array[$k]["goods_list"] = $array['goods_list'][$arr];
            //物流列表
            $order_goods_array[$k]["log_list"] = $this->getloglist($arr,$usa_id,array_sum($array['goods_weight'][$arr]));//$loglist
            //发票类型
            $order_goods_array[$k]["invoice_array"] = $ssRow["ss_invoice"];
            //商品总价
            $order_goods_array[$k]["goods_total"] = $goods_total;
            //订单发票
            $order_goods_array[$k]["user_invoice"] = $iListArr;
        }

//        dump($order_goods_array);
//        exit();
        return json(format($order_goods_array,200,"success"));
    }



    public function getloglist($ss_id,$usa_id,$goods_weight){
        $loglist = $this->log->getList(["ss_id"=>$ss_id],"log_id,log_name");

        foreach ($loglist as $k1=>$row){

            $loglist[$k1]["wlmoney"] = getWeightMoney($row["log_id"],$usa_id,$goods_weight);

            if(empty($loglist[$k1]["wlmoney"])){
                unset($loglist[$k1]);
            }
        }

        //判断商家是否包邮
        $byou=$this->ss->getRow("ss_id={$ss_id} and find_in_set('1',guarantee)");

        if(!empty($byou)){

            $guarantess = in_array_findkey("guids",1,"guarantess",unserialize($byou["guarantes"]));
            $usaRow = $this->usa->getRow(["usa_id"=>$usa_id],"usa_province");
            $r_name = $this->r->getOne(["r_id"=>$usaRow['usa_province']],"r_name");
            $a_name = $this->r->getOne(["r_parent_id"=>0],"r_name");

            if(in_array($r_name,explode(",",$guarantess)) || in_array($a_name,explode(",",$guarantess))){
                unset($loglist);
                $loglist[0]["log_id"]=0;
                $loglist[0]["log_name"]="包邮";
                $loglist[0]["wlmoney"]=0;
            }
        }

        sort($loglist);
        return $loglist;
    }


    /**
     * [添加订单]
     * @author wangmutian
     * @date 2018-07-25
     */
    public function addOrder()
    {

//        file_put_contents("./public/addOrder.txt",json_encode($_POST));
        //sca
        $post['u_id'] = $this->param["u_id"]; /*用户id*/
        $post['usa_id'] = $this->param["usa_id"]; /*收货地址*/
        $post['o_pay_name'] = input("o_pay_name",""); /*支付宝/微信*/
        $addorderlist = $this->param["order_goods"];

        $addorderlist = json_decode($addorderlist,true);

        $r = array();
        $re = array();

        foreach ($addorderlist as $row){

            $row['o_logistics'] = $row['o_shipping_price'];
            $row['o_status'] = $row['o_pay_status'] = $row['o_shipping_status'] = 0;

            $row['o_payable_price']  = ($row['o_goods_price']+$row['o_shipping_price']); /*应付款金额*/

            $row['o_add_time'] = time(); /*订单总价*/
            if ($row['o_goods_price'] - $row['o_payable_price']>=0) {
                $row['o_prom_amount'] = abs(($row['o_goods_price'] - $row['o_payable_price'])); /*活动优惠金额*/
            }else{
                $row['o_prom_amount'] = 0;
            }

            //o_sn 订单号 后台生成的
            $ordercount =  $this->ord->getCount(["ss_id"=>$row['ss_id']]);
            $ordercount++;
            $num = "0000";
            $num = substr_replace($num,$ordercount,4 - strlen($ordercount),strlen($ordercount));
            $row['o_sn'] = $row['ss_id']."-".date("Ymd").$num;


            //物流code
            $logistics = Config::get("logistics");
            $row['o_shipping_code'] = "";

            if(array_search($row['o_shipping_name'],$logistics) !== false){
                $row['o_shipping_code'] = array_search($row['o_shipping_name'],$logistics);
            }

            //物流id
            $plugin = new \model\Plugin();
            $log_id = $plugin->getOne(["log_name"=>$row['o_shipping_name'],"ss_id"=>$row['ss_id']],"log_id");
            $row['log_id'] = empty($log_id)?0:$log_id;

            //收货人地址也添加到订单里
            $usaRow = $this->usa->getRow(["usa_id"=>$post['usa_id']]);

            $row["usa_user_name"]=$usaRow['usa_user_name'];
            $row["usa_mobile"]=$usaRow['usa_mobile'];
            $row["usa_province"]=$usaRow['usa_province'];
            $row["usa_city"]=$usaRow['usa_city'];
            $row["usa_district"]=$usaRow['usa_district'];
            $row["usa_address"]=$usaRow['usa_address'];


            $row['u_id'] = $post['u_id']; /*用户id*/
            $row['usa_id'] = $post['usa_id']; /*收货地址*/
            $row['o_pay_name'] =  $post['o_pay_name']; /*支付宝/微信*/

            $data = $row;
            unset($data['order_goods']);
            unset($data['totalprice']);

            $data['o_invoice'] = isset($data['o_invoice'])?json_encode($data['o_invoice']):[];


            $o_id = $this->ord->save($data);
            if($o_id < 0){
                return json(format('', 253, "订单提交失败!~"));
            }

            $r[] = ["o_id"=>$o_id,"on_sn"=>$row['o_sn']];

            $re[] = $o_id;

            //商品列表
            $orderGoods = $row['order_goods'];
            $record = array();

            foreach ($orderGoods as $ogrow){
                //去掉购物车商品
                $this->sca->del(["u_id"=>$post['u_id'],"g_id"=>$ogrow["g_id"],"sp_id"=>$ogrow["sp_id"]]);
                //去除库存
                $result = $this->gsp->setDataDec(["g_id"=>$ogrow["g_id"],"sp_id"=>$ogrow["sp_id"]],"gsp_inventory",$ogrow["goods_buy_num"]);

                if($result < 0){
                    $g_name = $this->g->getOne(["g_id"=>$ogrow["g_id"]],"g_name");
                    $sp_name = $this->sp->getOne(["id"=>$ogrow["sp_id"]],"sp_name");
                    return json(format('', 253, $g_name." ".$sp_name." 商品库存不足!~"));
                }
                $this->g->setDataInc(["g_id"=>$ogrow["g_id"]],"g_sales",$ogrow["goods_buy_num"]);
                $ogdata["o_id"] = $o_id;
                $ogdata["g_id"] = $ogrow["g_id"];
                $ogdata["sp_id"] = $ogrow["sp_id"];
                $gRow = $this->g->getRow(["g_id"=>$ogrow["g_id"]],"g_sn,g_name,g_current_price,ss_id,g_show_img_path");
                $ogdata["goods_sn"] = $gRow["g_sn"];
                $ogdata["goods_name"] = $gRow["g_name"];
                $ogdata["g_current_price"] = $this->gsp->getOne(["g_id"=>$ogrow["g_id"],"sp_id"=>$ogrow["sp_id"]],"gsp_price") ;//$gRow["g_current_price"]
                $ogdata["goods_buy_num"] = $ogrow["goods_buy_num"];
                $activy = getActivityPrice($ogrow["g_id"],$ogrow["sp_id"]);
                //活动id
                $ogdata["ga_id"] = $activy["act_id"];
                $ogdata["og_meet"] = $activy["act_meet"];
                $ogdata["og_reduction"] = $activy["act_reduction"];
                $ogdata['o_gnames'] = $gRow["g_name"]; /*商品名称*/
                $ogdata['o_imgpath'] = $gRow["g_show_img_path"]; /*商品图片*/
                $ogdata['o_spname'] =guige($ogrow["sp_id"]); /*商品规格*/
                //折扣价
                $ogdata["member_goods_price"] = $activy["g_discount_price"];
                $ogdata["shipping_status"] = 0;
                //订单商品表
                $this->og->save($ogdata);

                //订单记录表
                if(!in_array($gRow["ss_id"],$record)){
                    $record[] = $gRow["ss_id"];
                    $oadata["o_id"] = $o_id;
                    $oadata["ss_id"] = $gRow["ss_id"];
                    $oadata["oa_user"] = $post['u_id'];
                    $oadata["oa_order_status"] = $oadata["oa_pay_status"] = $oadata["oa_shipping_status"] = 0;
                    $oadata["oa_note"] = "您提交了订单，请等待系统确认";
                    $oadata["oa_time"]=time();
                    $oadata["oa_status_desc"] = "提交订单";
                    $oadata["oa_role"] = 0;
                    $this->oa->save($oadata);
                }
            }
        }

        return json(format(["o_id"=>json_encode($re)],200,'订单提交成功！'));
    }

    /**
     * [添加订单]
     * @author wangmutian
     * @date 2018-04-20
     */
    public function addOrderO(){

    	// 152;79;101;圆通速递;0.5;;0.39;[null];[{"g_id":"372","g_name":"小周牌多用模板","g_show_img_path":"http://192.168.2.190/public/upload/ShopGoods/18_06_11/5b1e0e0b56c23.jpg","g_unit_name":"块","goods_buy_num":"1","gsp_name":"大型钢木(竹)组合模板","money":"0.39","sp_id":"319","ss_invoice":"1,2"}]
        $post['u_id'] = $this->param["u_id"]; /*用户id*/
        $post['usa_id'] = $this->param["usa_id"]; /*收货地址*/
        $post['o_pay_name'] = input("o_pay_name"); /*支付宝/微信*/
        $post['ss_id'] = $this->param["ss_id"]; /*商户id*/
        $post['o_shipping_name'] = input("o_shipping_name"); /*物流名称*/
        $post['o_shipping_price'] = input("o_shipping_price"); /*邮费*/
        $post['o_logistics'] = input("o_shipping_price"); /*邮费*/
        $post['o_goods_price'] =input("o_goods_price"); /*商品总价*/
        $post['o_user_note'] = input("o_user_note"); /*用户备注*/
        $post['o_invoice'] = input('o_invoice'); /*订单发票*/
        $order_goods = $this->param['order_goods'];
        $order_goods = json_decode($order_goods,true);


        if(!empty($post['o_pay_name'])){
            $post['o_pay_time'] = time();
        }
        $post['o_status'] = $post['o_pay_status'] = $post['o_shipping_status'] = 0;
        $post['o_payable_price']  = ($post['o_goods_price']+$post['o_shipping_price']); /*应付款金额*/
        $post['o_add_time'] = time(); /*订单总价*/
        if ($post['o_goods_price'] - $post['o_payable_price']>=0) {
            $post['o_prom_amount'] = abs(($post['o_goods_price'] - $post['o_payable_price'])); /*活动优惠金额*/
        }else{
            $post['o_prom_amount'] = 0;
        }

        //o_sn 订单号 后台生成的
        $ordercount =  $this->ord->getCount(["ss_id"=>$post['ss_id']]);
        $ordercount++;
        $num = "0000";
        $num = substr_replace($num,$ordercount,4 - strlen($ordercount),strlen($ordercount));
        $post['o_sn'] = $post['ss_id']."-".date("Ymd").$num;
        //物流code
        $logistics = Config::get("logistics");
        $post['o_shipping_code'] = "";
        if(isset($logistics[$post['o_shipping_name']])){
            $post['o_shipping_code'] = $logistics[$post['o_shipping_name']];
        }

        $rule = [
            "u_id" => "require",
            "ss_id" => "require",
            "usa_id" => "require",
            "o_goods_price" => "require",
        ];

        $msg = [
            "usa_id.require" => "请选择收货地址!~",
            "o_goods_price.require" => "缺少商品总价!~",
        ];

        //物流id
        $plugin = new \model\Plugin();
        $log_id = $plugin->getOne(["log_name"=>$post['o_shipping_name']],"log_id");

        $post['log_id'] = empty($log_id)?0:$log_id;
        $data = verify($post, $rule, $msg);
        if ($data['code'] === 1) {
            //收货人地址也添加到订单里
            //收货人 usa_user_name 手机 usa_mobile 地址 省 usa_province  市 usa_city 区 usa_district  详细位置 usa_address
            //$post['usa_id']
            $usaRow = $this->usa->getRow(["usa_id"=>$post['usa_id']]);
            $post["usa_user_name"]=$usaRow['usa_user_name'];
            $post["usa_mobile"]=$usaRow['usa_mobile'];
            $post["usa_province"]=$usaRow['usa_province'];
            $post["usa_city"]=$usaRow['usa_city'];
            $post["usa_district"]=$usaRow['usa_district'];
            $post["usa_address"]=$usaRow['usa_address'];

            $id = $this->ord->save($post);
            if ($id > 0) {
                $record = array();
                foreach ($order_goods as $k=>$row){
                    //去除库存
                    $result = $this->gsp->setDataDec(["g_id"=>$row["g_id"],"sp_id"=>$row["sp_id"]],"gsp_inventory",$row["goods_buy_num"]);
                    $results=$this->g->setDataInc(["g_id"=>$row["g_id"]],"g_sales",$row["goods_buy_num"]);
                    if($result < 0){
                        return json(format('', 253, "有商品库存不足!~"));
                    }

                    $ogdata["o_id"] = $id;
                    $ogdata["g_id"] = $row["g_id"];
                    $ogdata["sp_id"] = $row["sp_id"];
                    $gRow = $this->g->getRow(["g_id"=>$row["g_id"]],"g_sn,g_name,g_current_price,ss_id,g_show_img_path");
                    $ogdata["goods_sn"] = $gRow["g_sn"];
                    $ogdata["goods_name"] = $gRow["g_name"];
                    $ogdata["g_current_price"] = $this->gsp->getOne(["g_id"=>$row["g_id"],"sp_id"=>$row["sp_id"]],"gsp_price") ;//$gRow["g_current_price"]
                    $ogdata["goods_buy_num"] = $row["goods_buy_num"];
                    $activy = getActivityPrice($row["g_id"],$row["sp_id"]);
                    //活动id
                    $ogdata["ga_id"] = $activy["act_id"];
                    $ogdata["og_meet"] = $activy["act_meet"];
                    $ogdata["og_reduction"] = $activy["act_reduction"];
                    $ogdata['o_gnames'] = $gRow["g_name"]; /*商品名称*/
        			$ogdata['o_imgpath'] = $gRow["g_show_img_path"]; /*商品图片*/
        			$ogdata['o_spname'] =guige($row["sp_id"]); /*商品规格*/
                    //折扣价
                    $ogdata["member_goods_price"] = $activy["g_discount_price"];
                    $ogdata["shipping_status"] = 0;
                    //订单商品表
                    $this->og->save($ogdata);

                    //订单记录表
                    if(!in_array($gRow["ss_id"],$record)){
                        $record[] = $gRow["ss_id"];
                        $oadata["o_id"] = $id;
                        $oadata["ss_id"] = $gRow["ss_id"];
                        $oadata["oa_user"] = $post['u_id'];
                        $oadata["oa_order_status"] = $oadata["oa_pay_status"] = $oadata["oa_shipping_status"] = 0;
                        $oadata["oa_note"] = "您提交了订单，请等待系统确认";
                        $oadata["oa_time"]=time();
                        $oadata["oa_status_desc"] = "提交订单";
                        $oadata["oa_role"] = 0;
                        $this->oa->save($oadata);
                    }
                }

                return json(format(["o_id"=>$id,"on_sn"=>$post['o_sn']],200,'订单提交成功！'));
            } else {
                return json(format('', 253, "订单提交失败!~"));
            }
        }else{
            return json(format('', 223, $data['msg']));
        }
    }

    /**
     * [退货订单]
     * @author 王牧田
     * @date 2018-04-26
     */
    public function returnOrder(){

        $post['u_id'] = $this->param["u_id"];
        $post['o_id'] = $this->param["o_id"];
        $post['g_id'] = $this->param["g_id"];
        $rule = [
            "u_id" => "require",
            "o_id" => "require",
            "g_id" => "require",
        ];

        $msg = [
            "u_id.require" => "缺少用户数据id!~",
            "o_id.require" => "缺少订单数据id!~",
            "g_id.require" => "缺少商品数据id!~",
        ];


        $data = verify($post, $rule, $msg);
        if ($data['code'] === 1) {
            $ord_ss_id = $this->ord->getOne(["o_id"=>$post['o_id']],"ss_id");
            $post['ss_id'] = $ord_ss_id;
            $post['or_describe'] = $this->param["or_describe"];
            $post['or_type'] = $this->param["or_type"];
            $post['or_payrealname'] = $this->param["or_payrealname"];
            $post['or_payaccount'] = $this->param["or_payaccount"];
            $post["or_time"] = time();
            $post['or_why'] = $this->param["or_why"];

            $path = "returngoods/".date("y_m_d", time());
            $info1 = uploadImage($path,'or_img_path1');
            $info2 = uploadImage($path,'or_img_path2');
            $info3 = uploadImage($path,'or_img_path3');
            if ($info1['code'] === 200) {
            	$post["or_img_path1"] = $info1['pic_cover'];
        	}
            if ($info2['code'] === 200) {
                $post["or_img_path2"] = $info2['pic_cover'];
            }
            if ($info3['code'] === 200) {
                $post["or_img_path3"] = $info3['pic_cover'];
            }
            $post["or_status"] = 0;

            $id = $this->or->save($post);

            if ($id > 0) {
                $oadata["o_id"] = $post['o_id'];
                $oadata["ss_id"] = $this->g->getOne(["g_id"=>$post['g_id']],"ss_id");
                $oadata["oa_user"] = $post['u_id'];
                $oadata["oa_order_status"] = $oadata["oa_pay_status"] = $oadata["oa_shipping_status"] = 0;
                $oadata["oa_note"] = "您提交了退货申请，请等待系统确认";
                $oadata["oa_time"]=time();
                $oadata["oa_status_desc"] = "提交订单";
                $oadata["oa_role"] = 0;
                $this->oa->save($oadata);

                $ortype = ($post['or_type']==1)?"换货":"退款";

                $ordList = $this->ord->getList(["o_id"=>$post['o_id']],"o_id,ss_id,o_payable_price,o_sn,o_add_time,usa_id,log_id,log_ss_id,o_shipping_code,o_shipping_name,o_status,o_pay_status,o_shipping_status");
                foreach ($ordList as $k=>$row){
                    $ordList[$k]["o_add_time"] = date("Y-m-d",$row["o_add_time"]);
                    $ordList[$k]["good_list"] = $this->og->getList(["o_id"=>$post['o_id'],"g_id"=>$post['g_id']],"g_id,sp_id,goods_buy_num,g_current_price,member_goods_price,o_gnames as g_name,o_imgpath as g_show_img_path,o_spname as sp_name");
                }

                //发送android推送
                separatePushAndroid("returnorder_".$id,$post['u_id'],"您的".$ortype."申请已发给售后","",json_encode($ordList));

                return json(format());
            } else {
                return json(format('', 253, "投诉失败!~"));
            }
        }else{
            return json(format('', 223, $data['msg']));
        }

    }


    /***
     * [订单列表]
     * @author 王牧田
     * @date 2018-05-07
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function orderlist(){
        //o_pay_status = 0待支付 o_shipping_status = 0待收货 o_status = 5已完成 o_status = 2已取消
       // $this->param["page"] = 1;
       // $this->param["u_id"] = 152;
       // $this->param["type"] = 3;

        $page=$this->param["page"];
        $u_id=$this->param["u_id"];
        $type = $this->param["type"];
        $limit = 10; //限制一页显示的数量
        $where["u_id"]=$u_id;
         //1,2,3,4
        switch ($type){
            case 1:
                $where["o_pay_status"] = 0;
                $where["o_shipping_status"] = ["neq",2];
                $where["o_status"] = ["not in","2,5"];
                break;
            case 2:
                // $where["o_shipping_status"] = 0;
                $where["o_pay_status"] = ["neq",0];
                $where["o_status"] = ["not in","2,5"];
                break;
            case 3:
                $where["o_status"] = 5;
                break;
            case 4:
                $where["o_status"] = 2;
                break;
        }
        $resultcount = Db::name("order")->where($where)->count();
        $result = Db::name("order")->where($where)->field("o_id,u_id,ss_id,o_payable_price,o_sn,o_add_time,usa_id,log_id,log_ss_id,o_shipping_code,o_shipping_name,o_status,o_pay_status,o_shipping_status,o_invoice,o_confirm_time")->limit(($page-1)*$limit,$limit)->order('o_id desc ')->select();
        foreach ($result as $k=>$row){
        	$result[$k]['order_type']=$type;
        	$address=$this->usa->getRow(["usa_id"=>$row["usa_id"]]);
        	if (!empty($address)) {
        		$result[$k]["usa_address"]  = dizhi($address['usa_province']).' '.dizhi($address['usa_city']).' '.dizhi($address['usa_district']).' '.$address['usa_address'];
        	}else{
        		$result[$k]["usa_address"]  ='';
        	}
        	
            /*新增加2.2倒计时*/
	        if ($row["o_pay_status"]=='0') {
	        	$times= $row["o_add_time"]+86400;
	        	$timec =$times-time();
	        	if ($timec > 0) {
	        		$timee=$timec;
	        	}else{
	        		$posts['o_id']=$row['o_id'];
		        	$poste['o_status'] = '2';
	        		$this->ord->save($poste,$posts);
	        		$timee='0';
	        	}
	        	$result[$k]["countdowntime"] = $timee;
	        }

	        // dump($row);
	        if ($row["o_status"]=='5') {
	        	$timess= $row["o_add_time"]+(86400*7);
	        	$timecc =$timess-time();
	        	if ($timecc > 0) {
	        		$timees=$timecc;
	        	}else{
	        		$oeevaluationdel = $this->oe->getRow(["o_id"=>$row["o_id"],"is_show"=>'1']);
	        		if (empty($oeevaluationdel)){
	        			$post['u_id'] = $row["u_id"];
				        $post['o_id'] = $row["o_id"];
				        $post['o_sn'] = $row["o_sn"];
		        		$post['oe_quality_star'] = '5';
			            $post['oe_logistics_star'] = '5';
			            $post['oe_service_star'] = '5';
			            $post['oe_content'] = '';
			            $post["oe_add_time"] = time();
			            $post["is_show"] = 1;
			            $post["is_type"] = 1;
		        		$this->oe->save($post);
	        		}
	        		
	        		$timees='0';
	        	}
	        	$result[$k]["countdowntimes"] = $timees;
	        }
	       
        	/*增加结束*/
            $result[$k]["o_add_time"]  = date("Y-m-d H:i",$row["o_add_time"]);

//            $result[$k]["ss_name"]=$this->ss->getOne(["ss_id"=>$row["ss_id"]],"ss_name");
            $goodlist=$this->og->getList(["o_id"=>$row["o_id"]],"g_id,sp_id,goods_buy_num,g_current_price,member_goods_price,o_gnames,o_imgpath,o_spname");
            $totalWeight = 0;
            foreach ($goodlist as $g=>$gl){
                // $gRow=$this->g->getRow(["g_id"=>$gl["g_id"]],"g_name,g_show_img_path,g_weight");
                // $spRow = $this->sp->getRow(["id"=>$gl["sp_id"]]);
                // //dump($spRow);
                // $sp_name = $this->sp->getOne(["id"=>$gl["sp_id"]],"sp_name");
                // //商品名称
                // $goodlist[$g]["g_name"] = $gRow["g_name"];
                // //商品图片
                // $goodlist[$g]["g_show_img_path"] = IMG_URL.$gRow["g_show_img_path"];
                // //规格名称
                // $goodlist[$g]["sp_name"] = $sp_name;

                $gspOne = $this->gsp->getOne(["sp_id"=>$gl["sp_id"],"g_id"=>$gl["g_id"]],"gsp_weight");
                $gspOne = empty($gspOne)?0:$gspOne;
                $totalWeight+=$gspOne;

            }

            
            if (!empty($goodlist)) {
            	foreach ($goodlist as $key => $value) {
            		if (!empty($value['o_gnames'])) {
            			$goodlist[$key]['g_name']=$value['o_gnames'];
            			$goodlist[$key]["g_show_img_path"] = IMG_URL.$value["o_imgpath"];
            			$goodlist[$key]["sp_name"] = $value['o_spname'];
            		}else{
            			$gRow=$this->g->getRow(["g_id"=>$gl["g_id"]],"g_name,g_show_img_path,g_weight");
		                $spRow = $this->sp->getRow(["id"=>$gl["sp_id"]]);
		                //dump($spRow);
		                $sp_name = $this->sp->getOne(["id"=>$gl["sp_id"]],"sp_name");
		                //商品名称
		                $goodlist[$g]["g_name"] = $gRow["g_name"];
		                //商品图片
		                $goodlist[$g]["g_show_img_path"] = IMG_URL.$gRow["g_show_img_path"];
		                //规格名称
		                $goodlist[$g]["sp_name"] = $sp_name;

            		}
            		
            	}

            	$result[$k]["good_list"]=$goodlist;
            }

            //物流费用
            $result[$k]["wlmoney"]=getWeightMoney($row["log_id"],$row["usa_id"],$totalWeight);

            //物流code
            $result[$k]["log_code"] = $this->log->getOne(["log_id"=>$row["log_ss_id"]],"log_code");

            //物流编号
            $result[$k]["ss_invoice"] = $this->ss->getOne(["ss_id"=>$row["ss_id"]],"ss_invoice");
            $result[$k]["o_invoice"] = stripslashes($row['o_invoice']);
            // dump();
           
            $retu = $this->or->getRow(["o_id"=>$row["o_id"]],"or_id,or_examine,or_over");
            if ($retu) {
        		$result[$k]["isreturn"] = '1';
        		if(!empty($retu['or_examine']) && $retu['or_examine']=='-1'){
	        		$result[$k]["isreturn"] ='0';
	        	}else if(!empty($retu['or_over']) && $retu['or_over']=='1'){
	        		$result[$k]["isreturn"] ='0';
	        	}
        	}else{
	            $result[$k]["isreturn"] ='0';
	        }
	        $oeevaluation = $this->oe->getCount(["o_id"=>$row["o_id"]],"oe_id");
	        $oeevaluationdel = $this->oe->getRow(["o_id"=>$row["o_id"],"is_show"=>'0'],"is_show");
	        if ($oeevaluation =='1') {
        		$result[$k]["isevaluation"] = '1';
	        }else if ($oeevaluation =='2') {
        		$result[$k]["isevaluation"] = '2';
	        }else if ($oeevaluation >='2') {
        		$result[$k]["isevaluation"] = '2';
	        }else{
		        $result[$k]["isevaluation"] ='0';
		    }
            if ($oeevaluationdel) {
            	$result[$k]["oeevaluationdel"]='1';
            }else{
            	$result[$k]["oeevaluationdel"]='0';
            }
	         
          
            if ($result[$k]["o_pay_status"]=='0') {
	            if (isset($result[$k]["countdowntime"]) && $result[$k]["countdowntime"] == '0') {
		        	unset($result[$k]);
		        }
		    }
			if (empty($goodlist)) {
            	unset($result[$k]);
            }

        }
          sort($result);
		$flag = array();  
  
		foreach($result as $v){  
		    $flag[] = $v['o_id'];  
		}  
		array_multisort($flag, SORT_DESC, $result); 
        $result = array_values($result);
        $resulte["pageCount"] = ceil($resultcount/$limit);
        $resulte["currentPage"] = $page;
        $results['goodss']=$result;
        $results['page']=$resulte;
        return json(format($results,200,"success"));
    }

/***
     * [已完成订单列表]
     * @author 李鑫
     * @date 2018-08-11
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function evaluationcenter(){
        //o_pay_status = 0待支付 o_shipping_status = 0待收货 o_status = 5已完成 o_status = 2已取消
       // $this->param["page"] = 1;
       // $this->param["u_id"] = 152;
       // $this->param["type"] = 3;

        $page=$this->param["page"];
        $u_id=$this->param["u_id"];
        $type = $this->param["type"];
        $limit = 10; //限制一页显示的数量
        $where["u_id"]=$u_id;
         //1,2,3,4
        switch ($type){
            case 1:
                $where["o_pay_status"] = 0;
                $where["o_shipping_status"] = ["neq",2];
                $where["o_status"] = ["not in","2,5"];
                break;
            case 2:
                // $where["o_shipping_status"] = 0;
                $where["o_pay_status"] = ["neq",0];
                $where["o_status"] = ["not in","2,5"];
                break;
            case 3:
                $where["o_status"] = 5;
                break;
            case 4:
                $where["o_status"] = 2;
                break;
        }
        $resultcount = Db::name("order")->where($where)->count();
        $result = Db::name("order")->where($where)->field("o_id,u_id,ss_id,o_payable_price,o_sn,o_add_time,usa_id,log_id,log_ss_id,o_shipping_code,o_shipping_name,o_status,o_pay_status,o_shipping_status,o_invoice,o_confirm_time")->limit(($page-1)*$limit,$limit)->order('o_id desc ')->select();
        foreach ($result as $k=>$row){
        	$result[$k]['order_type']=$type;
        	$address=$this->usa->getRow(["usa_id"=>$row["usa_id"]]);
        	if (!empty($address)) {
        		$result[$k]["usa_address"]  = dizhi($address['usa_province']).' '.dizhi($address['usa_city']).' '.dizhi($address['usa_district']).' '.$address['usa_address'];
        	}else{
        		$result[$k]["usa_address"]  ='';
        	}
        	
            /*新增加2.2倒计时*/
	        

	        if ($row["o_status"]=='5') {
	        	$timess= $row["o_add_time"]+(86400*7);
	        	$timecc =$timess-time();
	        	if ($timecc > 0) {
	        		$timees=$timecc;
	        	}else{
	        		$oeevaluationdel = $this->oe->getRow(["o_id"=>$row["o_id"],"is_show"=>'1']);
	        		if (empty($oeevaluationdel)){
	        			$post['u_id'] = $row["u_id"];
				        $post['o_id'] = $row["o_id"];
				        $post['o_sn'] = $row["o_sn"];
		        		$post['oe_quality_star'] = '5';
			            $post['oe_logistics_star'] = '5';
			            $post['oe_service_star'] = '5';
			            $post['oe_content'] = '';
			            $post["oe_add_time"] = time();
			            $post["is_show"] = 1;
			            $post["is_type"] = 1;
		        		$this->oe->save($post);
	        		}
	        		
	        		$timees='0';
	        	}
	        	$result[$k]["countdowntimes"] = $timees;
	        }
	       
        	/*增加结束*/
            $result[$k]["o_add_time"]  = date("Y-m-d H:i",$row["o_add_time"]);

//            $result[$k]["ss_name"]=$this->ss->getOne(["ss_id"=>$row["ss_id"]],"ss_name");
            $goodlist=$this->og->getList(["o_id"=>$row["o_id"]],"g_id,sp_id,goods_buy_num,g_current_price,member_goods_price,o_gnames,o_imgpath,o_spname");
            $totalWeight = 0;
            foreach ($goodlist as $g=>$gl){
           
                $gspOne = $this->gsp->getOne(["sp_id"=>$gl["sp_id"],"g_id"=>$gl["g_id"]],"gsp_weight");
                $gspOne = empty($gspOne)?0:$gspOne;
                $totalWeight+=$gspOne;

            }

            
            if (!empty($goodlist)) {
            	foreach ($goodlist as $key => $value) {
            		if (!empty($value['o_gnames'])) {
            			$goodlist[$key]['g_name']=$value['o_gnames'];
            			$goodlist[$key]["g_show_img_path"] = IMG_URL.$value["o_imgpath"];
            			$goodlist[$key]["sp_name"] = $value['o_spname'];
            		}else{
            			$gRow=$this->g->getRow(["g_id"=>$gl["g_id"]],"g_name,g_show_img_path,g_weight");
		                $spRow = $this->sp->getRow(["id"=>$gl["sp_id"]]);
		                //dump($spRow);
		                $sp_name = $this->sp->getOne(["id"=>$gl["sp_id"]],"sp_name");
		                //商品名称
		                $goodlist[$g]["g_name"] = $gRow["g_name"];
		                //商品图片
		                $goodlist[$g]["g_show_img_path"] = IMG_URL.$gRow["g_show_img_path"];
		                //规格名称
		                $goodlist[$g]["sp_name"] = $sp_name;

            		}
            		
            	}

            	$result[$k]["good_list"]=$goodlist;
            }

            //物流费用
            $result[$k]["wlmoney"]=getWeightMoney($row["log_id"],$row["usa_id"],$totalWeight);

            //物流code
            $result[$k]["log_code"] = $this->log->getOne(["log_id"=>$row["log_ss_id"]],"log_code");

            //物流编号
            $result[$k]["ss_invoice"] = $this->ss->getOne(["ss_id"=>$row["ss_id"]],"ss_invoice");
            $result[$k]["o_invoice"] = stripslashes($row['o_invoice']);
            // dump();
           
            $retu = $this->or->getRow(["o_id"=>$row["o_id"]],"or_id,or_examine,or_over");
            if ($retu) {
        		$result[$k]["isreturn"] = '1';
        		if(!empty($retu['or_examine']) && $retu['or_examine']=='-1'){
	        		$result[$k]["isreturn"] ='0';
	        	}else if(!empty($retu['or_over']) && $retu['or_over']=='1'){
	        		$result[$k]["isreturn"] ='0';
	        	}
        	}else{
	            $result[$k]["isreturn"] ='0';
	        }
	        $oeevaluation = $this->oe->getCount(["o_id"=>$row["o_id"]],"oe_id");
	        $oeevaluationdel = $this->oe->getRow(["o_id"=>$row["o_id"],"is_show"=>'0'],"is_show");
	        if ($oeevaluation =='1') {
        		$result[$k]["isevaluation"] = '1';
	        }else if ($oeevaluation =='2') {
        		$result[$k]["isevaluation"] = '2';
	        }else if ($oeevaluation >='2') {
        		$result[$k]["isevaluation"] = '2';
	        }else{
		        $result[$k]["isevaluation"] ='0';
		    }
            if ($oeevaluationdel) {
            	$result[$k]["oeevaluationdel"]='1';
            }else{
            	$result[$k]["oeevaluationdel"]='0';
            }
	         
          
            if ($result[$k]["o_pay_status"]=='0') {
	            if (isset($result[$k]["countdowntime"]) && $result[$k]["countdowntime"] == '0') {
		        	unset($result[$k]);
		        }
		    }
			if (empty($goodlist)) {
            	unset($result[$k]);
            }

        }
          sort($result);
		$flag = array();  
  
		foreach($result as $v){  
		    $flag[] = $v['o_confirm_time'];  
		}  
		array_multisort($flag, SORT_DESC, $result); 
        $result = array_values($result);
        $resulte["pageCount"] = ceil($resultcount/$limit);
        $resulte["currentPage"] = $page;
        $results['goodss']=$result;
        $results['page']=$resulte;
        // dump($results);die();
        return json(format($results,200,"success"));
    }

    /**
     * [通过订单号查询订单详情]
     * @author 王牧田
     * @date 2018-05-05
     */

    public function snOderinfo(){
    	$o_sn = $this->param["o_sn"];
        // $o_sn = input("o_sn","80-201807070022");
        $ordgetRow = $this->ord->getRow(["o_sn"=>$o_sn],"o_id,usa_user_name,usa_mobile,usa_province,usa_city,usa_district,usa_address,ss_id,o_sn,o_add_time,o_invoice_title,o_pay_name,o_payable_price,o_shipping_price,o_goods_price,o_prom_amount,o_invoice,log_ss_id,o_shipping_code,o_status,o_pay_status,o_shipping_status");
        $rRow = $this->r->getOnes(["r_id"=>["in",$ordgetRow["usa_province"].",".$ordgetRow["usa_city"].",".$ordgetRow["usa_district"]]],"r_name");
        $ordgetRow["usa_province"]=dizhi($ordgetRow["usa_province"]);
        $ordgetRow["usa_city"]=dizhi($ordgetRow["usa_city"]);
        $ordgetRow["usa_district"]=dizhi($ordgetRow["usa_district"]);

        // $ordgetRow["usa_province"]=$rRow[0];
        // $ordgetRow["usa_city"]=$rRow[1];
        // if (isset($rRow[2])) {
        // 	$ordgetRow["usa_district"]=$rRow[2];
        // }
        //店铺名称 店铺图标
        $ssRow = $this->ss->getRow(["ss_id"=>$ordgetRow["ss_id"]],"ss_name,ss_logo_img,ss_invoice");
        $ordgetRow["ss_name"] = $ssRow["ss_name"];
        $ordgetRow["ss_logo_img"] = IMG_URL.$ssRow["ss_logo_img"];
        /*新增加2.2倒计时*/
        if ($ordgetRow["o_pay_status"]=='0') {
        	$times= $ordgetRow["o_add_time"]+86400;
        	$timec =$times-time();
        	if ($timec > 0) {
        		$timee=$timec;
        	}else{
        		$this->ord->save(["o_status"=>2],["o_sn"=>$o_sn]);
        		$timee='0';
        	}
        	$ordgetRow["countdowntime"] = $timee;
        }
        /*增加结束*/
        $ordgetRow["o_add_time"] = date("Y-m-d H:i",$ordgetRow["o_add_time"]);

        $ordgetRow["ss_invoice"] = $ssRow["ss_invoice"];

        $join=[
            ["goods g","g.g_id = og.g_id"]
        ];
        $oglist=$this->og->joinGetList($join,"og",["og.o_id"=>$ordgetRow["o_id"]],[],"sp_id,og.g_id,g_name,g_show_img_path,goods_buy_num,og.g_current_price,og.member_goods_price,g_unit_name");

        foreach ($oglist as $k=>$row){
            $oglist[$k]["sp_name"] = $this->sp->getOne(["id"=>$row["sp_id"]],"sp_name");
            $oglist[$k]["g_current_price"] = number_format($row["g_current_price"],2);
            $oglist[$k]["member_goods_price"] = number_format($row["member_goods_price"],2);
            $oglist[$k]["g_show_img_path"] = IMG_URL.$row["g_show_img_path"];
        }


        $ordgetRow["log_code"] = $this->log->getOne(["log_id"=>$ordgetRow["log_ss_id"]],"log_code");

        $ordgetRow["goodslist"]=$oglist;

        return json(format($ordgetRow,200,"success"));
    }


    /**
     * [取消订单(支持多)]
     * @author 王牧田
     * @date 2018-05-07
     */
    public function cancelOrder(){

        $o_id=$this->param["o_id"];
        $o_id=json_decode($o_id);
        $where = "`o_id` IN (".implode(",",$o_id).") AND (`o_pay_status` <> '0' OR `o_shipping_status` <> '0')";
        $ordOne = Db::name("order")->where($where)->find();

        if(!empty($ordOne)){
            return json(format('', 253, "订单已被处理，不可以取消!~"));
        }else{
           $result = $this->ord->save(["o_status"=>2],["o_id"=>["in",implode(",",$o_id)]]);
            // $result = 1;
            if ($result > 0) {
                //日志
                foreach ($o_id as $o){
                	$wheres['o_id']=$o;
        			$ordOne = Db::name("order")->where($wheres)->find();
                    $oa['o_id']=$o;
                    $oa['ss_id']=$ordOne["ss_id"];
                    $oa['oa_user']=$ordOne["u_id"];
                    $oa['oa_order_status'] = 2;
                    $oa['oa_pay_status'] = $ordOne['o_pay_status'];
                    $oa['oa_shipping_status'] = $ordOne['o_shipping_status'];
                    $oa['oa_time'] = time();
                    $oa['oa_status_desc'] = '订单被取消';
                    $oa['oa_role'] = 0;
                    $this->orda->save($oa);
                    //返回库存
                    $oglist = $this->og->getList(["o_id"=>$o],"g_id,goods_buy_num,sp_id");
                    foreach ($oglist as $og){
                        $this->gsp->setDataInc(["g_id"=>$og["g_id"],"sp_id"=>$og["sp_id"]],"gsp_inventory",$og["goods_buy_num"]);
                    }
                }
                return json(format("",200,"订单取消成功"));
            }else{
                return json(format('', 253, "未做任何操作!~"));
            }
        }
    }


    /**
     * [确认收货]
     * @author 王牧田
     * @date 2018-05-07
     */
    public function okOrder(){

        $o_id=$this->param["o_id"];
        //开始支付 o_shipping_price
        $ordRow = $this->ord->getRow(["o_id"=>$o_id],"ss_id,o_payable_price,o_goods_price,o_diffvalue,o_status,o_shipping_status,o_shipping_price");
        $ssRow = $this->ss->getRow(["ss_id"=>$ordRow["ss_id"]],"payee_real_name,payee_account");
        $mbc_value = $this->mbc->getOne(["mbc_name"=>"transfer"],"mbc_value");
        if($ordRow['o_shipping_status'] == 0){
            return json(format("",203,"您的订单未发货，不能确认收货！"));
        }
        if($ordRow['o_status'] == 5){
            return json(format("",203,"订单已完成，不能重复确认！"));
        }
        if($mbc_value){
            //(商品小计 + 价格调整) * $mbc_value/100 + 物流费用
            $amount = (($ordRow["o_goods_price"]+$ordRow["o_diffvalue"])*$mbc_value/100) + $ordRow['o_shipping_price'];
            if($amount < 0.1){
                return json(format("",203,"提现金额不能少于0.1元"));
            }
            $result = alipaygateway($ssRow["payee_account"],$ssRow["payee_real_name"],$amount);

            if(empty($result) || $result != 10000){
                return json(format("",203,"确认收货失败，请与商家联系"));
            }
        }
       

        $ordresult = $this->ord->save(["o_status"=>5,"o_confirm_time"=>time()],["o_id"=>$o_id]);
        
        if ($ordresult > 0) {
            $ordRow = $this->ord->getRow(["o_id" => $o_id]);
            $oa['o_id'] = $o_id;
            $oa['ss_id'] = $ordRow["ss_id"];
            $oa['oa_user'] = $ordRow["u_id"];
            $oa['oa_order_status'] = $ordRow["o_status"];
            $oa['oa_pay_status'] = $ordRow['o_pay_status'];
            $oa['oa_shipping_status'] = $ordRow['o_shipping_status'];
            $oa['oa_time'] = time();
            $oa['oa_status_desc'] = '确认收货';
            $oa['oa_role'] = 0;
            $this->orda->save($oa);
            return json(format("",200,"确认收货成功"));
        }else{
            return json(format('', 253, "未做任何操作!~"));
        }
    }


    /**
     * 订单支付页面
     * @author 王牧田
     * @e-mail zrkjhlc@gmail.com
     * @date   2018-05-10
     */
    public function orderGoodsPay()
    {
       
        $pay_action = $this->param["type"]; /*alipay,wechatPay*/
        $o_add['o_id'] = $pl_save['pay_goods_id'] = $this->param['o_id']; /*订单id*/
        $o_add['u_id'] = $pl_save['u_id'] = $this->param['u_id'];

        //$o_add['o_pay_status'] = $pl_save['is_paid'] = 0; /*是否支付*/
        $pl_save['order_type'] = '2'; /*类型2代表正常商品订单*/
        $rule = [
            "o_id" => "require",
            "u_id" => "require|number",
        ];
        $msg = [
            "o_id.require" => "o_id缺少必要参数!~",
            "u_id.require" => "u_id缺少必要参数!~",
            "u_id.number" => "u_id必要参数传输有误!~",
        ];

        $data = verify($o_add, $rule, $msg);
        if ($data['code'] === 1) {
            if ($pay_action != "wechatPay" && $pay_action != "alipay") {
                return json(format('', 223, "支付方式选择有误!~"));
            } else {
                /*查询是否有该订单*/

                $tso_count = $this->ord->getCount(['o_id' => $o_add['o_id'],'o_pay_status'=>1]);
                /*如果已支付订单大于0*/
                if ($tso_count > 0) {
                    return json(format());
                } else {
                    /*查询该订单是否存在,如果存在的情况下订单号为原来的订单号, 否则为新生成的订单号*/
                    $row = $this->ord->getRow(['o_id' => $o_add['o_id']]);
                    $order_sn = $row['o_id'];
                    $ret = $pay_action($order_sn, $row['o_payable_price'], 1);
                    if (false !== $ret) {
                        return json(format($ret));
                    } else {
                        return json(format('', 250, "支付信息请求失败,请稍后再试!~"));
                    }
                }

            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }

    /**
     * [多条订单一起支付]
     * @param
     * @author 王牧田
     * @date 2018-05-17
     */
    public function ordersGoodsPay(){


        $o_id=$this->param['o_id']; //["1","2","3"]
        $o_id = json_decode($o_id,true);
        $o_add['u_id'] = $pl_save['u_id'] = $this->param['u_id'];
        $pay_action = $this->param["type"]; /*alipay,wechatPay*/

        $pl_save['order_type'] = '2'; /*类型2代表正常商品订单*/
        $rule = [
            "u_id" => "require|number",
        ];
        $msg = [
            "u_id.require" => "u_id缺少必要参数!~",
            "u_id.number" => "u_id必要参数传输有误!~",
        ];

        $data = verify($o_add, $rule, $msg);
        if ($data['code'] === 1) {
            if ($pay_action != "wechatPay" && $pay_action != "alipay") {
                return json(format('', 223, "支付方式选择有误!~"));
            } else {
                $o_payable_price = 0;
                $order_snstr="";
                foreach ($o_id as $o){
                    /*查询该订单是否存在,如果存在的情况下订单号为原来的订单号, 否则为新生成的订单号*/
                    $row = $this->ord->getRow(['o_id' => $o]);
                    $o_payable_price+=$row['o_payable_price'];
                    $order_snstr=time();
                }

                $ret = $pay_action($order_snstr, $o_payable_price);
                if (false !== $ret) {
                    return json(format($ret));
                } else {
                    return json(format('', 250, "支付信息请求失败,请稍后再试!~"));
                }

            }
        } else {
            return json(format('', 223, $data['msg']));
        }



    }

    /**
     * [支付成功回调]
     * @author 王牧田
     * @date 2018-05-17
     * @return \think\response\Json
     *
     */
    public function defrayok(){


        $o_id=$this->param['o_id']; //["1","2","3"]
        $pay_action = $this->param["type"];
        $o_id = json_decode($o_id,true);

        foreach ($o_id as $o) {
            $update_info['o_pay_name'] = $pay_action;
            $update_info['o_pay_status'] = 1;
            $update_info['o_pay_time'] = time();
            $update_where['o_id'] = $o;
            $this->ord->save($update_info, $update_where);
            $pl_save['order_id'] = $o;
            $this->pl->save($pl_save);

            $ordRow = $this->ord->getRow(["o_id"=>$o],"ss_id,u_id,o_status,o_pay_status,o_shipping_status");
            $oadata["o_id"] = $o;
            $oadata["ss_id"] = $ordRow["ss_id"];
            $oadata["oa_user"] = $ordRow['u_id'];
            $oadata["oa_order_status"] = $ordRow["o_status"];
            $oadata["oa_pay_status"] = $ordRow["o_pay_status"];
            $oadata["oa_shipping_status"] = $ordRow["o_shipping_status"];
            $oadata["oa_note"] = "";
            $oadata["oa_time"]=time();
            $oadata["oa_status_desc"] = "确认支付";
            $oadata["oa_role"] = 0;
            $this->oa->save($oadata);
        }


        return json(format());
    }

    /***
     * [申请订单列表]
     * @author 李鑫
     * @date 2018-06-05
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function severorderlist(){
        //o_pay_status = 0待支付 o_shipping_status = 0待收货 o_status = 5已完成 o_status = 2已取消


        $page=$this->param["page"];
        $u_id=$this->param["u_id"];
        // $type = $this->param["type"];
        $limit = 20; //限制一页显示的数量
        $where = "`u_id` = ".$u_id."  AND `o_pay_status` <> '0'  AND ( `o_shipping_status` = '0' OR `o_status` = '5')";
        $resultcount = Db::name("order")->where($where)->count();
        $result = Db::name("order")->where($where)->field("o_id,ss_id,u_id,o_payable_price,o_sn,o_add_time,usa_id,log_id,log_ss_id,o_shipping_code,o_shipping_name,o_status,o_pay_status,o_shipping_status")->limit(($page-1)*$limit,$limit)->order('o_id desc ')->select();
        // dump(Db::getLastSql());die();
        foreach ($result as $k=>$row){
        	$address=$this->usa->getRow(["usa_id"=>$row["usa_id"]]);
        	if (!empty($address)) {
        		$result[$k]["usa_address"]  = dizhi($address['usa_province']).' '.dizhi($address['usa_city']).' '.dizhi($address['usa_district']).' '.$address['usa_address'];
        	}else{
        		$result[$k]["usa_address"]  ='';
        	}
        	
            $result[$k]["o_add_time"]  = date("Y-m-d H:i",$row["o_add_time"]);
            $goodlist=$this->og->getList(["o_id"=>$row["o_id"]],"g_id,sp_id,goods_buy_num,g_current_price,member_goods_price,o_gnames,o_imgpath,o_spname");
            $totalWeight = 0;
            foreach ($goodlist as $g=>$gl){
                //商品名称
                if (!empty($gRow)) {
	               	$gspOne = $this->gsp->getOne(["sp_id"=>$gl["sp_id"],"g_id"=>$gl["g_id"]],"gsp_weight");
               		$gspOne = empty($gspOne)?0:$gspOne;
                	$totalWeight+=$gspOne;
                }
            }
            
            if (!empty($goodlist)) {
            	$result[$k]["good_list"]=$goodlist;
            }

            //物流费用
            $result[$k]["wlmoney"]=getWeightMoney($row["log_id"],$row["usa_id"],$totalWeight);

            //物流code
            $result[$k]["log_code"] = $this->log->getOne(["log_id"=>$row["log_ss_id"]],"log_code");

            //物流编号
            $result[$k]["ss_invoice"] = $this->ss->getOne(["ss_id"=>$row["ss_id"]],"ss_invoice");
			if (empty($goodlist)) {
            	unset($result[$k]);
            }
            //是否退换货Return of the goods
        	if ($this->or->getOne(["o_id"=>$row["o_id"]],"or_id")) {
        		$result[$k]["Returngoods"] = '1';
        	}else{
            	$result[$k]["Returngoods"] ='0';
            }
        }
        $result = array_values($result);
        $resulte["pageCount"] = ceil($resultcount/$limit);
        $resulte["currentPage"] = $page;
        $results['goodss']=$result;
        $results['page']=$resulte;

        return json(format($results,200,"success"));
    }

    /**
     * [检查订单是否过期]
     * @author 王牧田
     * @date 2018-06-07
     * @return \think\response\Json
     */
    public function checkOrderTime(){

        $mbc_value = $this->mbc->getOne(["mbc_name"=>"arrivaltime"],"mbc_value");
        //$ordlist = $this->ord->getList(" datediff(now(),FROM_UNIXTIME(o_add_time,'%Y-%m-%d')) >= ".$mbc_value);
        $ordsave = $this->ord->save(["o_status"=>1],"datediff(now(),FROM_UNIXTIME(o_add_time,'%Y-%m-%d')) >= ".$mbc_value);
        file_put_contents("e:/checkOrderTime.txt",db()->getLastSql()."\n\r",FILE_APPEND);
        return json(format($ordsave,200,db()->getLastSql()));

    }

    /***
     * [客服商户订单列表]
     * @author 李鑫
     * @date 2018-06-05
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function shoporderlist(){
        //o_pay_status = 0待支付 o_shipping_status = 0待收货 o_status = 5已完成 o_status = 2已取消


        $ss_id=$this->param["ss_id"];
        $page=$this->param["page"];
        $u_id=$this->param["u_id"];
        $type = $this->param["type"];
        $limit = 10; //限制一页显示的数量
        $where["u_id"]=$u_id;
         //1,2,3,4
        switch ($type){
            case 1:
                $where["o_pay_status"] = 0;
                $where["o_shipping_status"] = ["neq",2];
                $where["o_status"] = ["not in","2,5"];
                break;
            case 2:
                // $where["o_shipping_status"] = 0;
                $where["o_pay_status"] = ["neq",0];
                $where["o_status"] = ["not in","2,5"];
                break;
            case 3:
                $where["o_status"] = 5;
                break;
            case 4:
                $where["o_status"] = 2;
                break;
        }
        if ($ss_id!='75') {
         	$where["ss_id"] = $ss_id;
        }
        $resultcount = Db::name("order")->where($where)->count();
        $result = Db::name("order")->where($where)->field("o_id,ss_id,o_payable_price,o_sn,o_add_time,usa_id,log_id,log_ss_id,o_shipping_code,o_shipping_name,o_status,o_pay_status,o_shipping_status")->limit(($page-1)*$limit,$limit)->order('o_id desc ')->select();

        foreach ($result as $k=>$row){
        	$result[$k]['order_type']=$type;
        	$address=$this->usa->getRow(["usa_id"=>$row["usa_id"]]);
        	if (!empty($address)) {
        		$result[$k]["usa_address"]  = dizhi($address['usa_province']).' '.dizhi($address['usa_city']).' '.dizhi($address['usa_district']).' '.$address['usa_address'];
        	}else{
        		$result[$k]["usa_address"]  ='';
        	}
        	
            $result[$k]["o_add_time"]  = date("Y-m-d H:i",$row["o_add_time"]);
//            $result[$k]["ss_name"]=$this->ss->getOne(["ss_id"=>$row["ss_id"]],"ss_name");
            $goodlist=$this->og->getList(["o_id"=>$row["o_id"]],"g_id,sp_id,goods_buy_num,g_current_price,member_goods_price,o_gnames,o_imgpath,o_spname");
            $totalWeight = 0;
            foreach ($goodlist as $g=>$gl){
                //商品名称
                if (!empty($gRow)) {
	               	$gspOne = $this->gsp->getOne(["sp_id"=>$gl["sp_id"],"g_id"=>$gl["g_id"]],"gsp_weight");
               		$gspOne = empty($gspOne)?0:$gspOne;
                	$totalWeight+=$gspOne;
                }
            }
            
            if (!empty($goodlist)) {
            	$result[$k]["good_list"]=$goodlist;
            }

            //物流费用
            $result[$k]["wlmoney"]=getWeightMoney($row["log_id"],$row["usa_id"],$totalWeight);

            //物流code
            $result[$k]["log_code"] = $this->log->getOne(["log_id"=>$row["log_ss_id"]],"log_code");

            //物流编号
            $result[$k]["ss_invoice"] = $this->ss->getOne(["ss_id"=>$row["ss_id"]],"ss_invoice");
            if (empty($goodlist)) {
            	unset($result[$k]);
            }
        }
        $result = array_values($result);
        $resulte["pageCount"] = ceil($resultcount/$limit);
        $resulte["currentPage"] = $page;
        $results['goodss']=$result;
        $results['page']=$resulte;
        $results['page']=$resulte;

        return json(format($results,200,"success"));
    }

    /**
     * [退货记录]
     * @author 李鑫
     * @date 2018-04-26
     */
    public function rturnrecord(){

    	$post['u_id'] = $this->param["u_id"];
        $rule = [
            "u_id" => "require",
        ];

        $msg = [
            "u_id.require" => "缺少用户数据id!~",
        ];
        $data = verify($post, $rule, $msg);
        if ($data['code'] === 1) {
           
        	$pageParam['page'] = $this->param['page']=1;
	        $aList = $this->or->getAll($post, $pageParam, [], 10,'or_id,or_time,o_id,g_id,or_status,or_examine,or_over,or_satisfaction,or_content');
	       	foreach ($aList['data'] as $key => $value) {
	            $aList['data'][$key]["or_time"] = date("Y-m-d",$value["or_time"]);
	            $goodss = $this->ord->getRow(["o_id"=>$value['o_id']],"o_id,ss_id,o_payable_price,o_sn,o_add_time,usa_id,log_id,log_ss_id,o_shipping_code,o_shipping_name,o_status,o_pay_status,o_shipping_status");
	           	// $aList['data'][$key]["o_sn"] = $goodss["o_sn"];
	           	// $aList['data'][$key]["o_add_time"] = date("Y-m-d",$goodss["o_add_time"]);

	           	$aList['data'][$key]["goodss"][0] = $goodss;
	           	$aList['data'][$key]["goodss"][0]['o_add_time'] = date('Y-m-s', $goodss['o_add_time']);
	           	$goodlist=$this->og->getList(["o_id"=>$value["o_id"]],"g_id,sp_id,goods_buy_num,g_current_price,member_goods_price");
	            $totalWeight = 0;
	            
	            foreach ($goodlist as $g=>$gl){
	                $gRow=$this->g->getRow(["g_id"=>$gl["g_id"]],"g_name,g_show_img_path,g_weight");
	                $sp_name = $this->sp->getOne(["id"=>$gl["sp_id"]],"sp_name");

		               if (!empty($gRow)) {
		                $goodlist[$g]["g_name"] = $gRow["g_name"];
		                //商品图片
		                $goodlist[$g]["g_show_img_path"] = IMG_URL.$gRow["g_show_img_path"];
		                //规格名称
		                $goodlist[$g]["sp_name"] = $sp_name;


	                    $gspOne = $this->gsp->getOne(["sp_id"=>$gl["sp_id"],"g_id"=>$gl["g_id"]],"gsp_weight");
	                    $gspOne = empty($gspOne)?0:$gspOne;
	                    $totalWeight += $gspOne;
	//	                $totalWeight+=$gRow["g_weight"];
	                }
	            }
	            
	            if (empty($goodlist)) {
	            	unset($aList['data'][$key]);
	            }
	            if (!empty($goodlist)) {
	            	$aList['data'][$key]["goodss"][0]["good_list"]=$goodlist;
	            }
	           	if ($value['or_status']=='0') {
					$aList['data'][$key]["return_note"]="退换申请审理中";
		        }
		        if($value['or_status']=='1'){
		        	$aList['data'][$key]["return_note"]="退换申请已接受";
		        }
		        if(!empty($value['or_examine'])){
			        if($value['or_examine']=='-1'){
			        	$aList['data'][$key]["return_note"]="退换申请已拒绝";
			        }
			    }
			    if(!empty($value['or_over'])){
			        if($value['or_over']=='1'){
			        	$aList['data'][$key]["return_note"]="退换申请已完成";
			        }
		        }
	        }

            return json(format($aList));
        }
    }

    /**
     * [退货记录单条]
     * @author 李鑫
     * @date 2018-04-26
     */
    public function rturnrecordshow(){

    	$post['o_id'] = $this->param["o_id"];
        $rule = [
            "o_id" => "require",
        ];

        $msg = [
            "o_id.require" => "缺少退货记录数据id!~",
        ];
        $data = verify($post, $rule, $msg);
        if ($data['code'] === 1) {
           
            $aList = $this->or->getRow($post);
            $goodss = $this->ord->getRow(["o_id"=>$post['o_id']],'o_sn,o_add_time,o_payable_price,o_logistics');
            $aList["o_sn"] = $goodss["o_sn"];
            if (!empty($goodss["o_payable_price"])) {
                $aList["return_money"] =$goodss["o_payable_price"]- $goodss["o_logistics"];
            }
            $aList["o_add_time"] = date("Y-m-d",$goodss["o_add_time"]);
            if (!empty($aList["or_img_path1"])) {
                $aList["or_img_path1"] =IMG_URL.$aList["or_img_path1"];
            }
            if (!empty($aList["or_img_path2"])) {
                $aList["or_img_path2"] =IMG_URL.$aList["or_img_path2"];
            }
            if (!empty($aList["or_img_path3"])) {
                $aList["or_img_path3"] =IMG_URL.$aList["or_img_path3"];
            }
            if ($aList['or_status']=='0') {
                if($aList['or_examine']=='-1'){
                    $aList["return_examine"]="1";
                    $aList["return_state"]="2";
                }else{
                    $aList["return_state"]="1";
                    $aList["return_examine"]="0";
                }
            }
            if ($aList['or_status']=='1') {
                if($aList['or_examine']=='-1'){
                    $aList["return_examine"]="1";
                    $aList["return_state"]="3";
                }else{
                    $aList["return_state"]="2";
                    $aList["return_examine"]="0";
                }
            }
            if ($aList['or_receiving']=='1') {
                $aList["return_state"]="3";
                $aList["return_examine"]="0";
            }

            if ($aList['or_refund']=='1' || $aList['or_refund']=='2' ) {
                $aList["return_state"]="4";
                $aList["return_examine"]="0";
            }
            if ($aList['or_over']=='1') {
                $aList["return_state"]="5";
                $aList["return_examine"]="0";
            }
            if ($aList['or_satisfaction']=='2') {
                $aList["isping"]='0';
            }else{
                $aList["isping"]='1';
            }

            return json(format($aList));
        }
    }

     /**
     * [退货完成]
     * @author 李鑫
     * @date 2018-04-26
     */
    public function rturnover(){
    	$or_where['or_id'] = $this->param["or_id"];
        $rule = [
            "or_id" => "require",
        ];
        $msg = [
            "or_id.require" => "缺少退货记录数据id!~",
        ];
        $data = verify($or_where, $rule, $msg);
        if ($data['code'] === 1) {
	        $orgid = $this->or->getRow($or_where);
            $save['or_over'] = "1";
	        $or_ids = $this->or->save($save,$or_where);
	        if ($or_ids > 0) {
	       		$this->ord->save(["o_status"=>5],["o_id"=>$orgid['o_id']]);
            	return json(format("",200,"确认退货成功"));
	        }else{
	            return json(format('', 253, "确认退货失败"));
	        }
            return json(format());
        }
    }


     /**
     * [退货完成]
     * @author 李鑫
     * @date 2018-04-26
     */
    public function rturnovers(){
    	$or_where['or_id'] = $this->param["or_id"];
        $rule = [
            "or_id" => "require",
        ];
        $msg = [
            "or_id.require" => "缺少退货记录数据id!~",
        ];
        $data = verify($or_where, $rule, $msg);
        if ($data['code'] === 1) {
	        $orgid = $this->or->getRow($or_where);
            $save['or_satisfaction'] = $this->param["or_satisfaction"];
            $save['or_content'] = $this->param["or_content"];
	        $or_ids = $this->or->save($save,$or_where);
	        if ($or_ids > 0) {
	       		$this->ord->save(["o_status"=>5],["o_id"=>$orgid['o_id']]);
            	return json(format("",200,"确认退货成功"));
	        }else{
	            return json(format('', 253, "确认退货失败"));
	        }
            return json(format());
        }
    }

    /***
     * [退货订单列表]
     * @author 李鑫
     * @date 2018-07-03
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function returnorderlist(){
        //o_pay_status = 0待支付 o_shipping_status = 0待收货 o_status = 5已完成 o_status = 2已取消

//        $this->param["page"] =1 ;
//        $this->param["u_id"] = 152;
        $page=$this->param["page"];
        $u_id=$this->param["u_id"];
        // $type = $this->param["type"];
        $limit = 20; //限制一页显示的数量
        $where = "`u_id` = ".$u_id."  AND `o_pay_status` <> '0'";
        $resultcount = Db::name("order")->where($where)->count();
        $result = Db::name("order")->where($where)->field("o_id,ss_id,u_id,o_payable_price,o_sn,o_add_time,usa_id,log_id,log_ss_id,o_shipping_code,o_shipping_name,o_status,o_pay_status,o_shipping_status")->limit(($page-1)*$limit,$limit)->order('o_id desc ')->select();

        foreach ($result as $k=>$row){
        	$address=$this->usa->getRow(["usa_id"=>$row["usa_id"]]);
        	if (!empty($address)) {
        		$result[$k]["usa_address"]  = dizhi($address['usa_province']).' '.dizhi($address['usa_city']).' '.dizhi($address['usa_district']).' '.$address['usa_address'];
        	}else{
        		$result[$k]["usa_address"]  ='';
        	}
        	
            $result[$k]["o_add_time"]  = date("Y-m-d H:i",$row["o_add_time"]);
            $goodlist=$this->og->getList(["o_id"=>$row["o_id"]],"g_id,sp_id,goods_buy_num,g_current_price,member_goods_price,o_gnames,o_imgpath,o_spname");
            $totalWeight = 0;
            foreach ($goodlist as $g=>$gl){
                //商品名称
                if (!empty($gRow)) {
	               	$gspOne = $this->gsp->getOne(["sp_id"=>$gl["sp_id"],"g_id"=>$gl["g_id"]],"gsp_weight");
               		$gspOne = empty($gspOne)?0:$gspOne;
                	$totalWeight+=$gspOne;
                }
            }
           
            if (!empty($goodlist)) {
            	foreach ($goodlist as $key => $value) {
            		if (!empty($value['o_gnames'])) {
            			$goodlist[$key]['g_name']=$value['o_gnames'];
            			$goodlist[$key]["g_show_img_path"] = IMG_URL.$value["o_imgpath"];
            			$goodlist[$key]["sp_name"] = $value['o_spname'];
            		}else{
            			$gRow=$this->g->getRow(["g_id"=>$gl["g_id"]],"g_name,g_show_img_path,g_weight");
		                $spRow = $this->sp->getRow(["id"=>$gl["sp_id"]]);
		                //dump($spRow);
		                $sp_name = $this->sp->getOne(["id"=>$gl["sp_id"]],"sp_name");
		                //商品名称
		                $goodlist[$g]["g_name"] = $gRow["g_name"];
		                //商品图片
		                $goodlist[$g]["g_show_img_path"] = IMG_URL.$gRow["g_show_img_path"];
		                //规格名称
		                $goodlist[$g]["sp_name"] = $sp_name;

            		}
            		
            	}
            	$result[$k]["good_list"]=$goodlist;
            }

            //物流费用
            $result[$k]["wlmoney"]=getWeightMoney($row["log_id"],$row["usa_id"],$totalWeight);

            //物流code
            $result[$k]["log_code"] = $this->log->getOne(["log_id"=>$row["log_ss_id"]],"log_code");

            //物流编号
            $result[$k]["ss_invoice"] = $this->ss->getOne(["ss_id"=>$row["ss_id"]],"ss_invoice");
 			if (empty($goodlist)) {
            	unset($result[$k]);
            }
            //是否退换货Return of the goods
            $goods = $this->or->getRow(["o_id"=>$row["o_id"]]);
        	if ($goods) {
        		$result[$k]["Returngoods"] = '1';

	           	if ($goods['or_status']=='0') {
					$result[$k]["return_note"]="退换申请审理中";
		        }
		        if($goods['or_status']=='1'){
		        	$result[$k]["return_note"]="退换申请已接受";
		        }
		        if(!empty($goods['or_examine'])){
			        if($goods['or_examine']=='-1'){
			        	$result[$k]["return_note"]="退换申请已拒绝";
			        }
			    }
			    if(!empty($goods['or_over'])){
			        if($goods['or_over']=='1'){
			        	$result[$k]["return_note"]="退换申请已完成";
			        }
		        }
        	}else{
            	$result[$k]["Returngoods"] ='0';
				$result[$k]["return_note"]="";
            }
        }
        $result = array_values($result);
        $resulte["pageCount"] = ceil($resultcount/$limit);
        $resulte["currentPage"] = $page;
        $results['goodss']=$result;
        $results['page']=$resulte;

        return json(format($results,200,"success"));
    }


    /**
     * [订单评论]
     * @author 李鑫
     * @date 2018-07-18
     */
    public function orderevaluation(){
            // file_put_contents('./public/1.txt', '1');
    	
       // $this->param["u_id"] = "152";
       // $this->param["o_id"] = "408";
       // $this->param["o_sn"] = "343";
       // $this->param["oe_content"]= "xxx";
       // $this->param["oe_quality_star"] = $this->param["oe_logistics_star"] = $this->param["oe_service_star"]= 1;
        $post['u_id'] = $this->param["u_id"];
        $post['o_id'] = $this->param["o_id"];
        $post['o_sn'] = $this->param["o_sn"];
        $rule = [
            "u_id" => "require",
            "o_id" => "require",
            "o_sn" => "require",
        ];

        $msg = [
            "u_id.require" => "缺少用户数据id!~",
            "o_id.require" => "缺少订单数据id!~",
            "o_sn.require" => "缺少订单号!~",
        ];


        $data = verify($post, $rule, $msg);
        if ($data['code'] === 1) {

            $post['oe_quality_star'] = $this->param["oe_quality_star"];
            $post['oe_logistics_star'] = $this->param["oe_logistics_star"];
            $post['oe_service_star'] = $this->param["oe_service_star"];
            $post['oe_content'] = $this->param["oe_content"];
            $post["oe_add_time"] = time();
            $post["is_show"] = 1;
            $post["is_type"] = 1;
            
            $path = "evaluation/".date("y_m_d", time());
            $info1 = uploadImage($path,'oe_img1');
            $info2 = uploadImage($path,'oe_img2');
            $info3 = uploadImage($path,'oe_img3');
            $info4 = uploadImage($path,'oe_img4');
            $info5 = uploadImage($path,'oe_img5');
            $info6 = uploadImage($path,'oe_img6');
            if ($info1['code'] === 200) {
            	$post["oe_img1"] = $info1['pic_cover'];
        	}
            if ($info2['code'] === 200) {
                $post["oe_img2"] = $info2['pic_cover'];
            }
            if ($info3['code'] === 200) {
                $post["oe_img3"] = $info3['pic_cover'];
            }
            if ($info4['code'] === 200) {
                $post["oe_img4"] = $info4['pic_cover'];
            }
            if ($info5['code'] === 200) {
                $post["oe_img5"] = $info5['pic_cover'];
            }
            if ($info6['code'] === 200) {
                $post["oe_img6"] = $info6['pic_cover'];
            }

            $id = $this->oe->save($post);

            if ($id > 0) {
                return json(format());
            } else {
                return json(format('', 253, "评论失败!~"));
            }
        }else{
            return json(format('', 223, $data['msg']));
        }

    }


    /**
     * [订单追评论]
     * @author 李鑫
     * @date 2018-07-18
     */
    public function orderevaluationzhui(){
    	$post['u_id'] = $this->param["u_id"];
        $post['o_id'] = $this->param["o_id"];
        $post['o_sn'] = $this->param["o_sn"];
        $rule = [
            "u_id" => "require",
            "o_id" => "require",
            "o_sn" => "require",
        ];

        $msg = [
            "u_id.require" => "缺少用户数据id!~",
            "o_id.require" => "缺少订单数据id!~",
            "o_sn.require" => "缺少订单号!~",
        ];

        $data = verify($post, $rule, $msg);
        if ($data['code'] === 1) {
            $post['oe_content'] = $this->param["oe_content"];
            $post["oe_add_time"] = time();
            $post["is_show"] = 1;
            $post["is_type"] = 1;
            $post["is_zhui"] =1;

            $path = "evaluation/".date("y_m_d", time());
            $info1 = uploadImage($path,'oe_img1');
            $info2 = uploadImage($path,'oe_img2');
            $info3 = uploadImage($path,'oe_img3');
            $info4 = uploadImage($path,'oe_img4');
            $info5 = uploadImage($path,'oe_img5');
            $info6 = uploadImage($path,'oe_img6');
            if ($info1['code'] === 200) {
            	$post["oe_img1"] = $info1['pic_cover'];
        	}
            if ($info2['code'] === 200) {
                $post["oe_img2"] = $info2['pic_cover'];
            }
            if ($info3['code'] === 200) {
                $post["oe_img3"] = $info3['pic_cover'];
            }
            if ($info4['code'] === 200) {
                $post["oe_img4"] = $info4['pic_cover'];
            }
            if ($info5['code'] === 200) {
                $post["oe_img5"] = $info5['pic_cover'];
            }
            if ($info6['code'] === 200) {
                $post["oe_img6"] = $info6['pic_cover'];
            }

            $id = $this->oe->save($post);

            if ($id > 0) {
                return json(format());
            } else {
                return json(format('', 253, "评论失败!~"));
            }
        }else{
            return json(format('', 223, $data['msg']));
        }

    }
     /**
     * [订单评论详情]
     * @author 李鑫
     * @date 2018-07-24
     */
    public function orderevaluationshow(){
    	$post['o_id'] = $this->param["o_id"];
    	$post['u_id'] = $this->param["u_id"];
        $rule = [
            "o_id" => "require",
            "u_id" => "require",
        ];
        $msg = [
            "o_id.require" => "缺少退货记录数据id!~",
            "u_id.require" => "缺少用户数据id!~",
        ];
        $data = verify($post, $rule, $msg);
        if ($data['code'] === 1) {
           
	        	$aList = $this->oe->getList($post,'oe_id,o_id,o_sn,u_id,oe_quality_star,oe_logistics_star,oe_service_star,oe_content,oe_add_time,oe_img1,oe_img2,oe_img3,oe_img4,oe_img5,oe_img6,is_zhui');
	          	foreach ($aList as $key => $value) {
	          		$aList[$key]["oe_add_time"]=date("Y-m-d",$value["oe_add_time"]);

	          		if (!empty($value["oe_img1"])) {
		            	$aList[$key]["oe_img1"] =IMG_URL.$value["oe_img1"];
		            }
		            if (!empty($value["oe_img2"])) {
		            	$aList[$key]["oe_img2"] =IMG_URL.$value["oe_img2"];
		            }
		            if (!empty($value["oe_img3"])) {
		            	$aList[$key]["oe_img3"] =IMG_URL.$value["oe_img3"];
		            }
		            if (!empty($value["oe_img4"])) {
		            	$aList[$key]["oe_img4"] =IMG_URL.$value["oe_img4"];
		            }
		            if (!empty($value["oe_img5"])) {
		            	$aList[$key]["oe_img5"] =IMG_URL.$value["oe_img5"];
		            }
		            if (!empty($value["oe_img6"])) {
		            	$aList[$key]["oe_img6"] =IMG_URL.$value["oe_img6"];
		            }
	          	}
			     // dump($aList);die();
            return json(format($aList));
   		}
    }

    /**
     * [删除评论]
     * @author 李鑫
     * @date 2018-07-24
     */
    public function orderevaluationdel(){
        $post['o_id'] = $this->param["o_id"];
        $rule = [
            "o_id" => "require",
        ];
        $msg = [
            "o_id.require" => "缺少订单数据id!~",
        ];
        $data = verify($post, $rule, $msg);
        if ($data['code'] === 1) {
            $posts["is_show"] = 0;
            $id = $this->oe->save($posts,$post);
            if ($id > 0) {
                return json(format());
            } else {
                return json(format('', 253, "删除失败!~"));
            }
        }else{
            return json(format('', 223, $data['msg']));
        }

    }

     /**
     * [加入购物车]
     * @author 李鑫
     * @date 2018-07-25
     */

     public function shoppingcartadd(){
       	// $this->param["u_id"] = "134";
        // $this->param["ss_id"] = "79";
        // $this->param["g_id"] = "371";
        // $this->param["g_num"]= "54";
        // $this->param["g_name"] = '小周牌多用模板';
        // $this->param["g_img"] = 'http://192.168.2.190/public/upload/ShopGoods/18_06_11/5b1e0e0b56c23.jpg';
        // $this->param["sgc_id"]='318';
        // $this->param["sgc_name"]= '大型钢木(竹)组合模板';
        // $this->param["g_price"]= '0.39';
        $post['u_id'] = $this->param["u_id"];
        $post['ss_id'] = $this->param["ss_id"];
        $post['g_id'] = $this->param["g_id"];
        $rule = [
            "u_id" => "require",
            "ss_id" => "require",
            "g_id" => "require",
        ];

        $msg = [
            "u_id.require" => "缺少用户数据id!~",
            "ss_id.require" => "缺少商家数据id!~",
            "g_id.require" => "缺少商品数据id!~",
        ];


        $data = verify($post, $rule, $msg);

        if ($data['code'] === 1) {

            $post['g_name'] = $this->param["g_name"];
            $post['g_img'] = $this->param["g_img"];
            $post['sp_id'] = $this->param["sgc_id"];
            $post['sp_name'] = $this->param["sgc_name"];
            $post['g_num'] = $this->param["g_num"];
            $post['g_price'] = $this->param["g_price"];
            $post["sca_add_time"] = time();

            $issets = $this->sca->getRow(array("u_id"=>$this->param["u_id"],"g_id"=>$this->param["g_id"],"sp_id"=>$this->param["sgc_id"]));
            $inventory = $this->gsp->getRow(array("sp_id"=>$this->param["sgc_id"],"g_id"=>$this->param["g_id"]));
            // if (!empty($issets['g_num'])) {
            // 	$nums = $issets["g_num"]+$this->param["g_num"];
            // 	if ($nums > $inventory['gsp_inventory']) {
            // 		return json(format('', 253, "不能大于库存!~"));
            // 	}
            // }
            if (!empty($issets)) {
       			return json(format('', 253, "购物车已存在该商品!~"));
            }
        	if ($this->param["g_num"] > $inventory['gsp_inventory']) {
        		return json(format('', 253, "不能大于库存!~"));
        	}
            if ($issets) {
            	$posts['sca_id']=$issets['sca_id'];
            	$poste['g_num'] = $issets['g_num']+$this->param["g_num"];
            	$id = $this->sca->save($poste,$posts);
            }else{
            	$id = $this->sca->save($post);
            }

            if ($id > 0) {
                return json(format());
            } else {
                return json(format('', 253, "购物车添加失败!~"));
            }
        }else{
            return json(format('', 223, $data['msg']));
        }

    }
    /**
     * [修改购物车]
     * @author 李鑫
     * @date 2018-07-25
     */

     public function shoppingcartnumedit(){
       	// $this->param["sca_id"] = "71";
        // $this->param["g_id"] = "341";
        // $this->param["num"]= '3';
        $post['sca_id'] = $this->param["sca_id"];
        $post['num'] = $this->param["num"];
        $rule = [
            "sca_id" => "require",
        ];
        $msg = [
            "u_id.require" => "缺少用户数据id!~",
        ];
        $data = verify($post, $rule, $msg);
        if ($data['code'] === 1) {
        	$posts['sca_id']=$post['sca_id'];
        	$poste['g_num'] = $this->param["num"];
        	$id = $this->sca->save($poste,$posts);
            if ($id > 0) {
                return json(format());
            } else {
                return json(format('', 253, "购物车操作失败!~"));
            }
        }else{
            return json(format('', 223, $data['msg']));
        }

    }

    /**
     * [购物车列表]
     * @author 李鑫
     * @date 2018-07-25
     */
    public function shoppingcartshow(){
    	$post['u_id'] = $this->param["u_id"];
        $rule = [
            "u_id" => "require",
        ];
        $msg = [
            "u_id.require" => "缺少用户数据id!~",
        ];
        $data = verify($post, $rule, $msg);
        if ($data['code'] === 1) {
           
	        	$aList = $this->sca->getList($post);
	        	if (!empty($aList)) {
					foreach($aList as $k=>$item){
					    $order_id = $item['ss_id'];
					    $sp_id = $item['sp_id'];
					    $g_id = $item['g_id'];
					    $ss_name = $this->ss->getRow(array("ss_id"=>$order_id),"ss_name");
					    // unset($item['ss_id']);
					    if(!isset($items['data'][$order_id])) {
					        $items['data'][$order_id] = array('ss_id'=>$ss_name['ss_name'], 'items'=>array());
					    }
					    $kucun = $this->gsp->getRow(array("sp_id"=>$sp_id,"g_id"=>$g_id),"gsp_inventory,gsp_filepath");
					    if (!empty($kucun['gsp_inventory'])) {
					    	$item['kucun'] = $kucun['gsp_inventory'];
					    }else{
					    	$item['kucun'] = '0';
					    }
					    if (!empty($kucun['gsp_filepath'])) {
					    	$item['g_img'] = IMG_URL.$kucun['gsp_filepath'];
					    }
					    $isshow = $this->g->getRow(array("g_id"=>$g_id),"s_is_show,g_unit_name");
					    $item['g_unit_name'] = $isshow['g_unit_name'];
					    $item['isshow'] = $isshow['s_is_show'];
					    $items['data'][$order_id]['items'][] = $item;

					}
					sort($items['data']);
				}else{
					 $items['data'] = array();
				}
			    // dump($items);
			    // die();
            return json(format($items));
   		}
    }

     /**
     * [删除购物车]
     * @author 李鑫
     * @date 2018-07-25
     */
    public function shoppingcartdel(){
       	$sca_ids  = $info["sca_id"] = $this->param['sca_id'];//[1,2,3]
        $sca_ids = json_decode($sca_ids,true);
        $rule = [
            "sca_id" => "require",
        ];
        $msg = [
            "sca_id.require" => "购物车id不能为空噢!~",
        ];
        $data = verify($info, $rule, $msg);

        if ($data['code'] === 1) {

            $ret = $this->sca->del(["sca_id" => ["in", implode(",", $sca_ids)]]);
            if ($ret > 0) {
                return json(format('', 200, "购物车删除成功!~"));
            } else {
                return json(format('', 227, "购物车删除失败!~"));
            }
        }else{
            return json(format('', 223, $data['msg']));
        }

    }
    /**
     * [删除订单]
     * @author 李鑫
     * @date 2018-07-26
     */
    public function orderdel(){
       	$o_ids  = $info["o_id"] = $this->param['o_id'];
        $rule = [
            "o_id" => "require",
        ];
        $msg = [
            "o_id.require" => "订单id不能为空噢!~",
        ];
        $data = verify($info, $rule, $msg);

        if ($data['code'] === 1) {

            $ret = $this->ord->del(["o_id" => $o_ids]);
            if ($ret > 0) {
                return json(format('', 200, "订单删除成功!~"));
            } else {
                return json(format('', 227, "订单删除失败!~"));
            }
        }else{
            return json(format('', 223, $data['msg']));
        }

    }
/**
     * [商品评论]
     * @author 李鑫
     * @date 2018-07-26
     */
    public function goodscomments(){
    	// $this->param["g_id"] = 362;
    	// $this->param["comments"] = 5;
    	$gids = $this->param["g_id"];
    	$type = $this->param["comments"];
    	$oegoods= $this->og->getList(["g_id"=>$gids],"o_id");
			if (!empty($oegoods)) {
				foreach ($oegoods as $key => $val) {
					 $oegoodse[$key] = $val['o_id'];
				}
				$oe_where['o_id'] = ['in', $oegoodse];
				$oe_where['is_show'] = '1';
				switch ($type){
		            case 1:
		                $oe_where['oe_quality_star'] = array('egt',4);
						$oe_where['is_zhui'] = 0;
		                break;
		            case 2:
		                $oe_where['oe_quality_star'] = 3;
						$oe_where['is_zhui'] = 0;
		                break;
		            case 3:
		                $oe_where['oe_quality_star'] = array('elt',2);
						$oe_where['is_zhui'] = 0;
		                break;
		            case 4:
		            	$oe_where['is_zhui'] = 0;
		                break;
	                case 5:
	                	$oe_where['is_zhui'] = 0;
						// $oe_where['oe_img1'] = ['neq', "NULL"];
		                break;
		            default:
						$oe_where['is_zhui'] = '0';
		        }
				

				/*********************************************/

				$oeevaluationsc = $this->oe->getList($oe_where,'oe_id,o_id,u_id,oe_quality_star,oe_logistics_star,oe_service_star,oe_content,oe_img1,oe_img2,oe_img3,oe_img4,oe_img5,oe_img6,s_content,is_zhui,oe_add_time');
				
				if (!empty($oeevaluationsc)) {
					foreach ($oeevaluationsc as $key => $val) {
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
							$val['oe_img'][$keys]['dat']=IMG_URL.$values['dat'];
						}
						$oeevaluationsce[$val['o_id']] = $val;
						
						if ($val['is_zhui']=='0') {
							$oes_where['o_id'] = $val['o_id'];
							$oes_where['is_show'] = '1';
							$oes_where['is_zhui'] = '1';
							$oeevaluationscs = $this->oe->getRow($oes_where,'oe_id,o_id,u_id,oe_content,oe_img1,oe_img2,oe_img3,oe_img4,oe_img5,oe_img6,s_content,is_zhui,oe_add_time');
							if (!empty($oeevaluationscs)) {
								$oeevaluationscs['oe_add_time']= date("Y-m-d",$oeevaluationscs["oe_add_time"]);
								$oeevaluationscs['oe_img']=array('0'=>array('dat'=>$oeevaluationscs['oe_img1']),  
						              '1'=>array('dat'=>$oeevaluationscs['oe_img2']),  
						              '2'=>array('dat'=>$oeevaluationscs['oe_img3']),
						              '3'=>array('dat'=>$oeevaluationscs['oe_img4']),
						              '4'=>array('dat'=>$oeevaluationscs['oe_img5']),
						              '5'=>array('dat'=>$oeevaluationscs['oe_img6']),
								);
								for ($i=0; $i < 6; $i++) { 
									if (empty($oeevaluationscs['oe_img'][$i]['dat'])) {
										unset($oeevaluationscs['oe_img'][$i]);
									}
								}
								if (empty($oeevaluationscs['oe_img']['0']['dat'])) {
									$oeevaluationscs['oe_img']=array();
								}
								unset($oeevaluationscs['oe_img1']);
								unset($oeevaluationscs['oe_img2']);
								unset($oeevaluationscs['oe_img3']);
								unset($oeevaluationscs['oe_img4']);
								unset($oeevaluationscs['oe_img5']);
								unset($oeevaluationscs['oe_img6']);
								sort($oeevaluationscs['oe_img']);
								foreach ($oeevaluationscs['oe_img'] as $keyse => $valuese) {
									$oeevaluationscs['oe_img'][$keyse]['dat']=IMG_URL.$valuese['dat'];
								}
								$oeevaluationsce[$val['o_id']]['zhuijia'] = $oeevaluationscs;
								
							}else{
								$oeevaluationsce[$val['o_id']]['zhuijia']['oe_id'] ='';
								$oeevaluationsce[$val['o_id']]['zhuijia']['o_id'] ='';
								$oeevaluationsce[$val['o_id']]['zhuijia']['u_id'] ='';
								$oeevaluationsce[$val['o_id']]['zhuijia']['oe_content'] ='';
								$oeevaluationsce[$val['o_id']]['zhuijia']['s_content'] ='';
								$oeevaluationsce[$val['o_id']]['zhuijia']['is_zhui'] ='';
								$oeevaluationsce[$val['o_id']]['zhuijia']['oe_add_time'] ='';
								$oeevaluationsce[$val['o_id']]['zhuijia']['oe_img']=array();
							}
							
						}
						
					}
					if ($type=='4') {
						foreach ($oeevaluationsce as $keyc => $valuec) {
							if (empty($valuec['zhuijia']['oe_id'])) {
								unset($oeevaluationsce[$keyc]);
							}
						}
					}
					if ($type=='5') {
						foreach ($oeevaluationsce as $keyc => $valuec) {
							if (empty($valuec['oe_img']) && empty($valuec['zhuijia']['oe_img'])) {
								unset($oeevaluationsce[$keyc]);
							}
						}
					}
					if (!empty($oeevaluationsce)) {
						sort($oeevaluationsce);
					}
					// $data['oeevaluations'] = $oeevaluationsce;
				}else{
					$oeevaluationsce='';
				}


				// $resultcount = Db::name("order_evaluation")->where($oe_where)->count();

					$resultcount = count($oeevaluationsce);
				/*********************************************/
				// dump($resultcount);die();
		        if (isset($this->param["page"]) && ($this->param["page"] != '')) {
		        	$page=$this->param["page"];
		        }else{
		        	$page = "1";
		        }
		        $limit = 10; //限制一页显示的数量
				$oeevaluations = $this->oe->getList($oe_where,'oe_id,o_id,u_id,oe_quality_star,oe_logistics_star,oe_service_star,oe_content,oe_img1,oe_img2,oe_img3,oe_img4,oe_img5,oe_img6,s_content,is_zhui,oe_add_time',[],($page-1)*$limit,$limit);
				if (!empty($oeevaluations)) {
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
							$val['oe_img'][$keys]['dat']=IMG_URL.$values['dat'];
						}
						$oeevaluationse[$val['o_id']] = $val;
						
						if ($val['is_zhui']=='0') {
							$oes_where['o_id'] = $val['o_id'];
							$oes_where['is_show'] = '1';
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
									$oeevaluationss['oe_img'][$keyse]['dat']=IMG_URL.$valuese['dat'];
								}
								$oeevaluationse[$val['o_id']]['zhuijia'] = $oeevaluationss;
								
							}else{
								$oeevaluationse[$val['o_id']]['zhuijia']['oe_id'] ='';
								$oeevaluationse[$val['o_id']]['zhuijia']['o_id'] ='';
								$oeevaluationse[$val['o_id']]['zhuijia']['u_id'] ='';
								$oeevaluationse[$val['o_id']]['zhuijia']['oe_content'] ='';
								$oeevaluationse[$val['o_id']]['zhuijia']['s_content'] ='';
								$oeevaluationse[$val['o_id']]['zhuijia']['is_zhui'] ='';
								$oeevaluationse[$val['o_id']]['zhuijia']['oe_add_time'] ='';
								$oeevaluationse[$val['o_id']]['zhuijia']['oe_img']=array();
							}
							
						}
						
					}
					if ($type=='4') {
						foreach ($oeevaluationse as $keyc => $valuec) {
							if (empty($valuec['zhuijia']['oe_id'])) {
								unset($oeevaluationse[$keyc]);
							}
						}
					}
					if ($type=='5') {
						foreach ($oeevaluationse as $keyc => $valuec) {
							if (empty($valuec['oe_img']) && empty($valuec['zhuijia']['oe_img'])) {
								unset($oeevaluationse[$keyc]);
							}
						}
					}
					if (!empty($oeevaluationse)) {
						sort($oeevaluationse);
					}
					$data['oeevaluations'] = $oeevaluationse;
				}else{
					$data['oeevaluations'] = array();
				}
				$goodswhere['oe_quality_star'] = array('egt',4);
				$goodswhere['is_zhui'] = 0;
				$goodswhere['o_id'] = ['in', $oegoodse];
				$goodswhere['is_show'] = '1';
				$data["goodCount"] = Db::name("order_evaluation")->where($goodswhere)->count();
				$inthewhere['oe_quality_star'] = 3;
				$inthewhere['is_zhui'] = 0;
				$inthewhere['o_id'] = ['in', $oegoodse];
				$inthewhere['is_show'] = '1';
				$data["intheCount"] = Db::name("order_evaluation")->where($inthewhere)->count();
				$poorwhere['oe_quality_star'] = array('elt',2);
				$poorwhere['is_zhui'] = 0;
				$poorwhere['o_id'] = ['in', $oegoodse];
				$poorwhere['is_show'] = '1';
				$data["poorCount"] = Db::name("order_evaluation")->where($poorwhere)->count();
				$zhuiwhere['is_zhui'] = 1;
				$zhuiwhere['o_id'] = ['in', $oegoodse];
				$zhuiwhere['is_show'] = '1';
				$data["zhuiCount"] = Db::name("order_evaluation")->where($zhuiwhere)->count();
				$imgwhere['o_id'] = ['in', $oegoodse];
				$imgwhere['oe_img1'] = ['neq', "NULL"];
				$imgwhere['is_show'] = '1';
				$data["imgCount"] = Db::name("order_evaluation")->where($imgwhere)->count();
				$totalwhere['is_zhui'] = 0;
				$totalwhere['o_id'] = ['in', $oegoodse];
				$totalwhere['is_show'] = '1';
				$totalcount=Db::name("order_evaluation")->where($totalwhere)->count();
				$data["totalCount"] = $totalcount;
				if ($totalcount=='0') {
					$totalcount='1';
				}
				$data["favorablerate"] = ($data["goodCount"]/$totalcount)*100;
				$data["pageCount"] = intval(ceil($resultcount/$limit));
	        	$data["currentsPage"] = $page;
			}else{
				$data['oeevaluations'] = array();
				$data["goodCount"] = 0;
				$data["intheCount"] = 0;
				$data["poorCount"] = 0;
				$data["zhuiCount"] = 0;
				$data["imgCount"] = 0;
				$data["totalCount"] = 0;
				$data["favorablerate"] = 0;
				$data["pageCount"] = 0;
				$data["currentsPage"] = 0;
			}
			// dump($this->oe->getLastSql());
			
           	
	        return json(format($data));
		
    }
}

