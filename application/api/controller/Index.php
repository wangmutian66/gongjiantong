<?php
namespace app\api\controller;

use model\ManageAd as ma;
use model\Goods as g;
use model\SellerShop as ss;
use model\SellerRegistered as sr;
use model\Artical as a;
use model\UserReportInfo as uri;
use model\Feedback as fb;
use think\Config;
/**
 * 首页及常用接口
 */
class Index extends Base
{
    protected $ma;
    protected $g;
	protected $ss;
    protected $a;
	protected $sr;
    protected $uri;
    protected $fb;
    public function __construct()
    {
        parent::__construct();
        $this->ma = new ma();
        $this->g = new g();
		$this->ss = new ss();
        $this->a = new a();
		$this->sr = new sr();
        $this->uri = new uri();
        $this->fb = new fb();
    }
    /**
     * 首页猜你喜欢
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-11-07
     */
    public function index()
    {
        $keyword = (isset($this->param['keyword']) && !empty($this->param['keyword'])) ? $this->param['keyword'] : false ;
        $where['is_show'] = '1';
        $where['g_goods_verify'] = "1";
        if (!empty($keyword)) {
            $where["g_keywords|g_name|g_desc"] = ["like", "%" . $keyword . "%"];
        }
        $list = $this->g->getAll($where, [], [], 10, "g_id,g_name,g_show_img_path,g_current_price");
        if (empty($list["data"]) || count($list["data"]) < 11) {
            unset($where["g_keywords|g_name|g_desc"]);
            $list = $this->g->getAll($where, [], [], 10, "g_id,g_name,g_show_img_path,g_current_price");
        }
        foreach ($list["data"] as $key => $value) {
			$list['data'][$key]['g_show_img_path'] = IMG_URL . $value['g_show_img_path'];
        }
        return json(format($list["data"]));
    }
    /**
     * 搜索商品或店铺
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-11-07
     */
    public function serchGoodsAndShop()
    {
    	$post['type'] = $this->param["type"];
    	$post['keyword'] = $this->param["keyword"];
    	$rule = [
            "type" => "require|in:0,1",
            "keyword" => "require",
        ];
        $msg = [
            "type.require" => "缺少收藏类型!~",
            "type.in" => "收藏类型传输有误!~",
            "u_id.require" => "缺少必要参数!~",
        ];
        $data = verify($post, $rule, $msg);
        if ($data['code'] === 1) {
	    	switch ($post['type']) {
	    		case '0':/*商品*/

                    $order["g_sort"] = "asc";
                    $order["g_id"] = "desc";
                    /*排序*/
                    (isset($this->param["composite"]) && ($this->param["composite"] != '')) ? $order["g_shop_sort"] = $this->param["composite"] : false;/*综合排序*/
                    (isset($this->param["g_current_price"]) && ($this->param["g_current_price"] != '')) ? $order["g_current_price"] = $this->param["g_current_price"] : false;/*价格*/
                    (isset($this->param["g_sales"]) && ($this->param["g_sales"] != '')) ? $order["g_sales"] = $this->param["g_sales"] : false;

                    (isset($this->param["is_new"]) && ($this->param["is_new"] != '')) ? $order["g_add_time"] = $this->param["is_new"] : false;

	    			$pageParam["query"]["g_name"] = $pageParam["query"]["g_keywords"] = $where["g_keywords|g_name"] = ["like", "%" . $post["keyword"] . "%"];
					$pageParam['query']["g_goods_verify"] = $where["g_goods_verify"] = '1';
					$pageParam['query']["is_show"] = $where["is_show"] = '1';
					$list = $this->g->getAll($where, $pageParam, $order, 0, "g_id,g_name,g_show_img_path,g_current_price");
					foreach ($list['data'] as $key => $value) {
						$list['data'][$key]['g_show_img_path'] = IMG_URL . $value['g_show_img_path'];
					}
	    			break;
	    		case '1':/*店铺*/
                /*展示的省份*/
                    (isset($this->param["province_id"]) && ($this->param["province_id"] != '')) ? $condition["ss_shop_province"] = $this->param["province_id"] : false;
                    (isset($this->param["city_id"]) && ($this->param["city_id"] != '')) ? $condition["ss_shop_city"] = $this->param["city_id"] : false;
                    /*地区(省)*/
                    if (isset($condition['ss_shop_province']) && ('' != $condition['ss_shop_province']) && $condition['ss_shop_province'] > 1) {
                        $where['ss.ss_shop_province'] = $condition['ss_shop_province'];
                        $pageParam['query']['ss.ss_shop_province'] = $condition['ss_shop_province'];
                    }
                    /*地区(省)*/
                    if (isset($condition['ss_shop_city']) && ('' != $condition['ss_shop_city']) && $condition['ss_shop_city'] > 1) {
                        $where['ss.ss_shop_city'] = $condition['ss_shop_city'];
                        $pageParam['query']['ss.ss_shop_city'] = $condition['ss_shop_city'];
                    }

	    			$pageParam["query"]["ss.ss_name"] = $where["ss.ss_name"] = ["like", "%" . $post["keyword"] . "%"];

					$order["ss.ss_sort"] = "desc";
					$order["ss.ss_id"] = "desc";
					$join = [
						["gjt_goods g", "g.ss_id = ss.ss_id"],
						["gjt_region p", "p.r_id = ss.ss_shop_province"],
						["gjt_region c", "c.r_id = ss.ss_shop_city"],
						["gjt_region a", "a.r_id = ss.ss_shop_area"],
					];
					$alias = "ss";
					$field = "ss.ss_id, ss.ss_name, ss.ss_logo_img, ss.ss_shop_location, ss.ss_shop_address, count(g.g_id) as g_num, count(g.g_sales) as g_sales, p.r_name as ss_shop_province, c.r_name as ss_shop_city, a.r_name as ss_shop_area";
					$group = "g.ss_id";
					$list = $this->ss->joinGetAll($join, $alias, $where, $pageParam, $order, 0, $field,$group);
					foreach ($list['data'] as $key => $value) {
						$list['data'][$key]['ss_logo_img'] = IMG_URL . $value['ss_logo_img'];
						$g_where = ["ss_id" => $value['ss_id'], "g_goods_verify" => '1', "s_is_show" => '0'];
						$g_field = "g_id, g_name, g_current_price, g_show_img_path";
						/*查找三个商品*/
						$list['data'][$key]['goods_info'] = $this->g->getList($g_where, $g_field, ["g_add_time"=>"desc"], 0, 3);
						foreach ($list['data'][$key]['goods_info'] as $k => $val) {
							$list['data'][$key]['goods_info'][$k]['g_show_img_path'] = IMG_URL . $val['g_show_img_path'];
						}
					}
	    			break;
	    		
	    		default:
            		return json(format('', 223, "缺少必要参数!~"));
	    			break;
	    	}
	    	return json(format($list['data']));
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 广告列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-11-04
     */
    public function adList()
    {
//        $this->param["lat_lgt"] = "45.751438,126.854346";
        $where["ma.ma_status"] = '1';
//        $this->param["map_value"] = "recommend_ad";
        /*展示的省份*/
        (isset($this->param["province_id"]) && ($this->param["province_id"] != '')) ? $condition["r_id"] = $this->param["province_id"] : false;
        /*地区(省)*/
        if (isset($condition['r_id']) && ('' != $condition['r_id']) && $condition['r_id'] > 1) {
            $where['ma.r_id'] = ['in', '1,' . $condition['r_id']];
            $pageParam['query']['ma.r_id'] = $condition['r_id'];
        }
        (isset($this->param["map_value"]) && ($this->param["map_value"] != '')) ? $condition["map_value"] = $this->param["map_value"] : false;
        /*定位值*/
        if (isset($condition['map_value']) && ('' != $condition['map_value'])) {
            $where['map.map_value'] = $condition['map_value'];
            $pageParam['query']['map.map_value'] = $condition['map_value'];
        }

        $join = [
            ["gjt_manage_ad_position map", "map.map_id= ma.map_id"],
        ];

        $field = "ma.ma_image,ma.ma_start_time,ma.ma_end_time,ma.ma_link_type,ma.ma_link,ma.ma_content,ma.ma_ss_id";
        $list = $this->ma->joinGetList($join, "ma", $where, [], $field);
        $result=array();
        $i=0;
        foreach ($list as $key => $value) {
            if ($value['ma_start_time'] > time() && $value['ma_start_time'] != 0 && $value['ma_start_time'] > time() && $value['ma_start_time'] != 0 && $value['ma_end_time'] < time() && $value['ma_end_time'] != 0) {
                unset($list[$key]);
            } else if ($value['ma_end_time'] < time() && $value['ma_end_time'] != 0) {
                unset($list[$key]);
            } else {
                $result[$i] = $value;
                $result[$i]['ma_image'] = IMG_URL . $value['ma_image'];
                //$list[$key]['ma_image'] = IMG_URL . $value['ma_image'];
                $i++;
            }

        }

        return json(format($result));
    }
    /**
     * [获取热搜关键字]
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-28
     */
    public function getSearchKeyword()
    {
        /*type==1首页热搜,2招聘信息热搜*/
        $this->param['type']=1;
        $info["type"] = $this->param['type'];
        $rule = [
            "type" => 'require|in:1,2',
        ];
        $msg = [
            "type.require" => '缺少必要参数!~',
            "type.in" => '必要参数传输有误,请重试!~',
        ];
        $data = verify($info, $rule, $msg);
        if ($data['code'] === 1) {
            $mbc_data = getTableConfig("mbc_");
            if ($info['type'] == 1) {
                $ret_info = $mbc_data['other']['search'];
            } else if ($info['type'] == 2) {
                $ret_info = $mbc_data['other']['recruitment_search'];
            }
             $ret_infos=explode(',', $ret_info);
            foreach ($ret_infos as $key => $value) {
                  $ret_infose[]=Array('ids'=>$key,'name'=>$value);
            }
                $ret_infoss['data']= $ret_infose;
                // dump($ret_infoss);
           
             // return json_encode($ret_infoss);
            // dump($ret_infos);die();
            // die();
            return json(format($ret_infoss));
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 用户举报
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-11-08
     */
    public function report()
    {   

        $this->param["uri_type"] = "0";

        //  $this->u_id='58';
        //  $this->param["param"] = "68e224";
        //  $this->param["sign"] = "769b37e875af294237ce6b2b8de5eaad";
        //  $this->param["uri_report_info_id"] = "1";
        // $this->param["uri_mobile"] = "18745016473";
        //  $this->param["uri_contents"] = "啦啦啦啦";

        $post["u_id"] = $this->u_id;
        $post["uri_report_info_id"] = $this->param["uri_report_info_id"];
        $post["uri_type"] = $this->param["uri_type"];
        $post["uri_contents"] = $this->param["uri_contents"];
        $post["uri_mobile"] = $this->param["uri_mobile"];
        $rule = [
            "u_id" => 'require|number',
            "uri_report_info_id" => 'require|number',
            "uri_type" => 'require|in:0,1',
            "uri_contents" => 'require',
            "uri_mobile" => 'require|number',
        ];
        $msg = [
            "u_id.require" => '缺少必要参数!~',
            "u_id.number" => '必要参数传输有误!~',
            "uri_report_info_id.require" => '缺少信息参数!~',
            "uri_report_info_id.number" => '信息参数传输有误!~',
            "uri_type.require" => '缺少传输类型!~',
            "uri_type.in" => '传输类型有误!~',
            "uri_contents.require" => '内容必须填写!~',
            "uri_mobile.require" => '缺少必要参数!~',
            "uri_mobile.number" => '必要参数传输有误!~',
        ];
        
        $data = verify($post, $rule, $msg);
        if ($data['code'] == "1") {
            $path = "report/".date("y_m_d", time());
            $info1 = uploadImage($path,'uri_img_path1');
            $info2 = uploadImage($path,'uri_img_path2');
            $info3 = uploadImage($path,'uri_img_path3');
            $info4 = uploadImage($path,'uri_img_path4');

            // if ($info1['code'] === 200) {
                $post["uri_img_path"] = $info1['pic_cover'];
                if ($info2['code'] === 200) {
                    $post["uri_img_path2"] = $info2['pic_cover'];
                }
                if ($info3['code'] === 200) {
                    $post["uri_img_path3"] = $info3['pic_cover'];
                }
                if ($info4['code'] === 200) {
                    $post["uri_img_path4"] = $info4['pic_cover'];
                }
            // } else {
            //     return json(format('', 223, $info1['msg']));
            // }
                $post["uri_add_time"] = time();
                $post["uri_verify_status"] = "0";
                $id = $this->uri->save($post);

                if ($id > 0) {
                    return json(format('', 200, "举报成功!~"));
                } else {
                    return json(format('', 253, "举报失败!~"));
                }
           
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    public function getArticles()
    {
        $type = $this->param['type'] = 'aboutAs';
        $verify = ["update","help","licensing_and_service","privacy","aboutAs","announcement"];
        if (!empty($type)) {
            if (!in_array($type, $verify)) {
                return json(format('', 223, "参数传输有误!~"));
            } else {
                if (array_key_exists($type, getTableConfig("mbc_")['article'])) {
                    $a_id = getTableConfig("mbc_")['article'][$type];
                    if ($a_id > 0) {
                        $info = $this->a->getRow(['a_id' => $a_id],"a_title, a_content");
                        if ($info['a_content']) {
                            $info['a_content'] = preg_replace('/(<img.+?src=")(.*?)/', '$1' . HTTP_HOST . '$2', $info['a_content']);
                            return json(format($info));
                        } else {
                            return json(format());
                        }
                    } else {
                        return json(format());
                    }
                } else {
                    return json(format());
                }
            }
        } else {
            return json(format('', 223, "缺少必要参数!~"));
        }
    }

    /**
     * [首页广告一和首页广告二]
     * @return \think\response\Json
     */
    public function indexad(){
        //广告一
        $ad1_map_id = Config::get("ad1_map_id");
        $list["indexad1"] = $this->ma->getRow(["map_id"=>$ad1_map_id],"ma_id,ma_image,ma_link_type,ma_link,ma_content,ma_ss_id");
        if(!empty($list["indexad1"])){
            $list["indexad1"]['ma_image'] = IMG_URL.$list["indexad1"]['ma_image'];
        }

        //广告二
        $ad2_map_id = Config::get("ad2_map_id");
        $list["indexad2"] = $this->ma->getRow(["map_id"=>$ad2_map_id],"ma_id,ma_image,ma_link_type,ma_link,ma_content,ma_ss_id");
        if(!empty($list["indexad2"])) {
            $list["indexad2"]['ma_image'] = IMG_URL . $list["indexad2"]['ma_image'];
        }

        return json(format($list));
    }

    /**
     * [公告]
     * @author 王牧田
     * @date 2018-05-17
     */
    public function adGongGao(){
        $gonggao_ac_id = Config::get("gonggao_ac_id");
        $aList = $this->a->getList(["ac_id"=>$gonggao_ac_id,"a_is_open"=>1],"a_id,a_title,a_description,a_add_time");
        foreach ($aList as $k=>$row){
            $aList[$k]["a_add_time"] = date("Y-m-d H:i",$row["a_add_time"]);
        }

        return json(format($aList));

    }

    /**
     * [公告详情]
     * @author 王牧田
     * @date 2018-05-18
     * @return \think\response\Json
     */
    public function adGongGaoinfo(){
        $a_id = $this->param['a_id'];
        $aRow = $this->a->getRow(["a_id"=>$a_id],"a_title,a_author,a_content,FROM_UNIXTIME(a_add_time,'%Y-%m-%d %H:%i') as addtime");
        return json(format($aRow));
    }


    /**
     * 意见反馈
     * @author 李鑫
     * @date   2018-06-28
     */
    public function feedbacks()
    {   

        $post["u_id"] = $this->param["u_id"];
        $post["f_type"] = $this->param["f_type"];
        $post["f_content"] = $this->param["f_content"];
        $rule = [
            "u_id" => 'require|number',
            "f_type" => 'require|in:0,1,2,3',
            "f_content" => 'require',
        ];
        $msg = [
            "u_id.require" => '缺少必要参数!~',
            "u_id.number" => '必要参数传输有误!~',
            "uri_type.require" => '缺少传输类型!~',
            "uri_type.in" => '传输类型有误!~',
            "uri_content.require" => '内容必须填写!~',
        ];
        
        $data = verify($post, $rule, $msg);
        if ($data['code'] == "1") {
            $path = "feedback/".date("y_m_d", time());
            $info1 = uploadImage($path,'f_img_path1');
            $info2 = uploadImage($path,'f_img_path2');
            $info3 = uploadImage($path,'f_img_path3');

                $post["f_img_path1"] = $info1['pic_cover'];
                if ($info2['code'] === 200) {
                    $post["f_img_path2"] = $info2['pic_cover'];
                }
                if ($info3['code'] === 200) {
                    $post["f_img_path3"] = $info3['pic_cover'];
                }
               
           
                $post["f_add_time"] = time();
                $id = $this->fb->save($post);

                if ($id > 0) {
                    return json(format('', 200, "反馈成功!~"));
                } else {
                    return json(format('', 253, "反馈失败!~"));
                }
           
        } else {
            return json(format('', 223, $data['msg']));
        }
    }

}
