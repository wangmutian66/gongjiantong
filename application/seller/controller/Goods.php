<?php
namespace app\seller\controller;

/**
 * 作者：袁中旭
 * 时间：2017-09-12
 * 主要功能：商户后台商品管理
 */
use \model\GoodsAndSpecifications as gas;
use \model\Goods as g;
use \model\GoodsPicture as gp;
use \model\ManageGoodsBrand as mgb;
use \model\ManageGoodsCategory as mgc;
use \model\Region as r;
use \model\SellerShop as ss;
use \model\ShopBrandApplication as sba;
use \model\ShopGoodsCategory as sgc;
use \model\ShopGoodsItem as sgi;
use \model\ShopGoodsPrice as sgp;
use \model\ShopGoodsSpecification as sgs;
use \model\ShopGoodsType as sgt;
use \model\Specifications as sp;
use \model\GoodsSpecifications as gsp;
use model\Guarantee as gu;
use model\Plugin as log;
class Goods extends Base
{
    protected $sgc;
    protected $g;
    protected $sgs;
    protected $sgt;
    protected $sba;
    protected $mgc;
    protected $mgb;
    protected $gas;
    protected $gp;
    protected $r;
    protected $sgi;
    protected $sgp;
    protected $sp;
    protected $gsp;
    protected $gu;
    protected $log;
    public function __construct()
    {
        parent::__construct();
        /* 商户商品分类信息 */
        $this->sgc = new sgc();
        /* 总后台商品信息 */
        $this->g = new g();
        /* 商户店铺信息 */
        $this->ss = new ss();
        /* 商户后台商品系列 */
        $this->sgt = new sgt();
        /* 商户后台商品规格 */
        $this->sgs = new sgs();
        /* 商户后台品牌申请 */
        $this->sba = new sba();
        /* 总后台商品分类 */
        $this->mgc = new mgc();
        /* 总后台品牌 */
        $this->mgb = new mgb();
        /* 商品和规格关联表 */
        $this->gas = new gas();
        /* 商品轮播图表 */
        $this->gp = new gp();
        /* 地区表 */
        $this->r = new r();
        /* 商户后台规格内容 */
        $this->sgi = new sgi();
        /* 商品规格线数设置表 */
        $this->sgp = new sgp();
        $this->sp = new sp();
        /* 商品规格表 */
        $this->gsp = new gsp();
        /* 规格 */
        $this->gu = new gu();
        /* 物流公司 */
        $this->log = new log();
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-14
     * 功能：商户后台商品列表
     */
    public function goodsList()
    {

        /*接收到的数据*/
        $condition = $this->request->only(["g_name", "g_sn"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*where条件*/
        $where = [];
        $where['ss_id'] = $this->sm_info['ss_id'];

        if (is_array($condition)) {
            /*是否接收到名称信息*/
            if (isset($condition['g_name']) && ('' != $condition['g_name'])) {
                /*模糊查询*/
                $where['g_name'] = ['like', "%" . $condition['g_name'] . "%"];
                $pageParam['query']['g_name'] = $condition['g_name'];
            }
            /*是否接收到商品号*/
            if (isset($condition['g_sn']) && ('' != $condition['g_sn'])) {
                /*模糊查询*/
                $where['g_sn'] = $condition['g_sn'];
                $pageParam['query']['g_sn'] = $condition['g_sn'];
            }
        }
        $this->sellerManagerLog("查看商品列表");
        $goods_list = $this->g->getAll($where, $pageParam,["g_id"=>"desc"]);

        foreach ($goods_list['data'] as $key => $value) {

            $mgcs = $this->mgc->getOne(['mgc_id' => $value['sgc_id']], 'mgc_parent_id');
            $goods_list['data'][$key]['mgc_nameone'] = $this->mgc->getOne(['mgc_id' => $mgcs], 'mgc_name'); //一级分类
            $goods_list['data'][$key]['sgc_name'] = $this->mgc->getOne(['mgc_id' => $value['sgc_id']], 'mgc_name'); //二级分类
            $goods_list['data'][$key]['mgc_name'] = $this->mgc->getOne(['mgc_id' => $value['mgc_id']], 'mgc_name'); //三级分类
            $goods_list['data'][$key]['sgt_name'] = $this->sgt->getOne(['sgt_id' => $value['sgt_id']], 'sgt_name');
            $gsp_price = $this->gsp->getOne(["g_id"=>$value["g_id"]],"gsp_price");

            $goods_list['data'][$key]['guiges'] = empty($gsp_price)?0:$gsp_price;
        }


        $mgc_valuess = $this->ss->getRow(['ss_id' => $this->sm_info['ss_id']]);/*商家分类权限*/
        $mgc_valuese=$mgc_valuess['mgc_id'].','.$mgc_valuess['ss_mgc_ids'];


        $mgc_value = $this->mgc->getList(['mgc_is_show' => '1','mgc_id'=>array("in",$mgc_valuese)], "*", ['concat(mgc_parent_path,mgc_id)' => 'mgc_parent_path']);
            // dump($mgc_value);die();
        $mgc_values1 = array();
        $mgc_values2 = array();
        $mgc_values3 = array();
        /* 总平台分类 */
        if (!empty($mgc_value)) {
            /* 添加区分类别大小的样式 */
            foreach ($mgc_value as $mgck => $mgcv) {
                $mgc_value = explode(',', $mgcv['mgc_parent_path']);
                $mgc_count = count($mgc_value) - 1;
                 if ($mgc_count=='1') {
                    $mgcv['mgc_name'] = $mgcv['mgc_name'];
                    $mgc_values[] = $mgcv;
                 }
            }
        } else {
            $mgc_values = '';

        }

        $mgb_value = $this->mgb->getList(); /* 总平台品牌 */

        $sgc_value = $this->sgc->getList(['ss_id' => $this->sm_info['ss_id']]); /*商户分类*/
        // dump($this->sm_info);exit;

        if (!empty($sgc_value)) {
            /* 添加区分类别大小的样式 */
            foreach ($sgc_value as $sgck => $sgcv) {
                $sgc_value = explode(',', $sgcv['sgc_parent_path']);
                $sgc_count = count($sgc_value) - 1;
                /* 添加的时候不显示三级分类 */
                $style = "├";
                for ($i = 1; $i <= $sgc_count; $i++) {
                    $style .= "─";
                }
                $sgcv['sgc_name'] = $style . $sgcv['sgc_name'];
                $sgc_values[] = $sgcv;
            }
        } else {
            $mgc_values1 = array();
            $mgc_values2 = array();
            $mgc_values3 = array();
        }


        $logCount = $this->log->getCount(["ss_id"=>$this->sm_info['ss_id']]);

        return view(
            "goodsList",
            [
                "logCount" => $logCount,
                "mgc_value" => $mgc_values,
                "mgb_value" => $mgb_value,
                "data" => $condition, /*查询条件*/
                "list" => $goods_list['data'], /*查询结果*/
                "page" => $goods_list['page'], /*分页和html代码*/
                "lastPage" => $goods_list['lastPage'], /*总页数*/
                "currentPage" => $goods_list['currentPage'], /*当前页码*/
                "total" => $goods_list['total'], /*总条数*/
                "listRows" => $goods_list['listRows'], /*每页显示条数*/
            ]
        );
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-14
     * 功能：商户后台商品显示
     */
    public function goodsShow()
    {
        /*是不是ajax*/
        if ($this->request->isAjax()) {
            $info = $this->request->only(['id'], 'post');

            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);

            /*验证返回如果code == 1 说明成功*/
            if (intval($data['code']) === 1) {
                $where["g_id"] = intval($info['id']);
                $info = $this->g->getRow($where);

                /* 品牌名字 */
                $info['mgb_name'] = $this->mgb->getOne(['mgb_id' => $info['mgb_id']], 'mgb_name');
                /* 商品一级分类 */
                $mgcs = $this->mgc->getOne(['mgc_id' => $info['sgc_id']], 'mgc_parent_id');
                $info['mgc_nameone'] = $this->mgc->getOne(['mgc_id' => $mgcs], 'mgc_name');

                /* 商品二级分类名 */
                $info['sgc_name'] = $this->mgc->getOne(['mgc_id' => $info['sgc_id']], 'mgc_name');
                /* 商品三级分类名 */
                $info['mgc_name'] = $this->mgc->getOne(['mgc_id' => $info['mgc_id']], 'mgc_name');

                /* 店铺分类名 */
                $info['sgt_name'] = $this->sgt->getOne(['sgt_id' => $info['sgt_id']], 'sgt_name');
                $info['gzutu'] = $this->gp->getList(['g_id' => $info['g_id']], 'gp_picture_path');


                $info['g_add_time'] = date('Y-m-d H:i:s', $info['g_add_time']);
                $info['g_edit_time'] = date('Y-m-d H:i:s', $info['g_edit_time']);

                /* 商品属性值 */
                $gas = $this->gas->getList(['g_id' => $info['g_id']]);
                $gas_name = '';
                $s = 0;
                for ($i = 0; $i < count($gas); $i++) {
                    $s++;
                    $gas_name .= "{$s}:" . $gas[$i]['gas_value'] . ',';
                }
                $info['gas_name'] = $gas_name;
                /* 是否上架 */
                if ($info['s_is_show'] == 0) {
                    $info['s_is_show'] = '上架';
                } else {
                    $info['s_is_show'] = '下架';
                }
                /* 是否包邮 */
//                if ($info['is_free_shipping'] == '0') {
//                    $info['is_free_shipping'] = '包邮';
//                } else {
//                    $info['is_free_shipping'] = '不包邮';
//                }

                /*店铺保证*/


                /*金额*/
                $gsp_price = $this->gsp->getOne(["g_id"=>$info['g_id']],"gsp_price");
                $info['gsp_price'] = empty($gsp_price)?0:$gsp_price;


                $this->sellerManagerLog("查看商品详情,查看的商品id为:" . $info['g_id']);

                return json(format($info, '1', $data['msg']));
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }


    /**
     * 保存64位编码图片
     *  前端参考http://www.cn3wm.com/3wmkj/yzmsfa/index.php?s=/Home/Index/applay.html
    */
    //$base64_image_content
    public function saveBase64Image($base64_image_content){
        //保存位置--图片名
        preg_match('(,\/9j\/)', $base64_image_content, $result);
        if(!empty($result)){
            $image_name=date('His').str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT).".jpg";
            $image_url = ".".getUploadPath().'/ShopGoods/'.date('y-m-d').'/'.$image_name;

            $tmp = base64_decode($base64_image_content);

            if(!is_dir(pathinfo($image_url,PATHINFO_DIRNAME))){
                mkdir(pathinfo($image_url,PATHINFO_DIRNAME),0777,true);
            }
            if (file_put_contents($image_url, $tmp)){
                $data['code']='0';
                $data['image_url']='ShopGoods/'.date('y-m-d').'/'.$image_name;
                $data['msg']='保存成功！';
            }else{
                $data['code']='1';
                $data['image_url']='';
                $data['msg']='图片保存失败！';
            }

        }else{
            $data['code']='0';
            $data['image_url']=$base64_image_content;
        }

        return $data;
    }


    /**
     * 作者：袁中旭
     * 时间：2017-10-14
     * 功能：商户后台商品添加
     */
    public function goodsAdd()
    {

        if (empty($_POST['sba_name'])) {
            
        }else{
            $count = $this->sba->getCount(["sba_name" => $_POST['sba_name']]);
            if ($count > 0) {
                $this->error("您输入的名称重复了噢!~,请重新输入!~");
                exit();
            }
            /*先上传文件*/
            $path = "goodsBrand/" . date("y_m_d", time());
            $file_info = uploadImage($path, 'sba_logo_path');
            if ($file_info['code'] == 200) {
                $post['sba_logo_path'] = $file_info['pic_cover'];
                $post['sba_add_time'] = time();
                $post['ss_id'] = $this->sm_info['ss_id'];
                $post['sba_name'] = $_POST['sba_name'];
                $post['sba_english_name'] = $_POST['sba_english_name'];
                $post['sba_desc'] = $_POST['sba_desc'];
                $post['sba_ismsg'] = 1;
                /*存入数据库*/
                $id = $this->sba->save($post);
                if ($id > 0) {
                    // $this->sellerManagerLog("添加品牌,id为:" . $id);
                    $this->success("品牌提交成功，等待审核！～");
                } else {
                    $this->error("品牌提交失败！～");
                }
            } else {
                $this->error($file_info['msg']);
            }
        }

        $sgt_value = $this->sgt->getList(['ss_id' => $this->sm_info['ss_id']]); /* 商家商品类型 */
        $mgc_valuess = $this->ss->getRow(['ss_id' => $this->sm_info['ss_id']]);/*商家分类权限*/

        $mgc_valuese=$mgc_valuess['mgc_id'].','.$mgc_valuess['ss_mgc_ids'];
       

        $mgc_value = $this->mgc->getList(['mgc_is_show' => '1','mgc_id'=>array("in",$mgc_valuese)], "*", ['concat(mgc_parent_path,mgc_id)' => 'mgc_parent_path']);


        $mgc_values1 =array();
        $mgc_values2 =array();
        $mgc_values3 =array();
        /* 总平台分类 */
        if (!empty($mgc_value)) {
            /* 添加区分类别大小的样式 */
            foreach ($mgc_value as $mgck => $mgcv) {

                $mgc_value = explode(',', $mgcv['mgc_parent_path']);
                $mgc_count = count($mgc_value) - 1;

                 if ($mgc_count=='1') {
                    $mgcv['mgc_name'] = $mgcv['mgc_name'];
                    $mgc_values1[] = $mgcv;
                 }
                 if($mgc_count=='2'){
                    $mgcv['mgc_name'] = $mgcv['mgc_name'];
                    $mgc_values2[] = $mgcv;
                 }

                 if($mgc_count=='3'){
                    $mgcv['mgc_name'] = $mgcv['mgc_name'];
                    $mgc_values3[] = $mgcv;

                 }
              
            }
        } else {
            $mgc_values1 = '';
            $mgc_values2 = '';
            $mgc_values3 = '';
        }

        $mgb_value = $this->mgb->getList(); /* 总平台品牌 */

       $mgc_id=$_POST['mgc_id'];
       $mgb_id=$_POST['mgb_id'];
       $sgc_id=$_POST['sgc_id'];
       $sgc1_id=$_POST['sgc1_id'];
        $guarantes = unserialize($mgc_valuess['guarantes']);

        foreach ($guarantes as $k=>$g){

            if(!isset($g["guname"])){
                $guOne = $this->gu->getOne(["gu_id"=>$g["guids"]],"gu_name");
                $guarantes[$k]["guname"] = $guOne;
            }
        }
        return view(
            "goodsAdd",
            [   
                "mgc_value3" => $mgc_values3,
                "mgc_value2" => $mgc_values2,
                "mgc_value1" => $mgc_values1,
                "mgb_value" => $mgb_value,
                // "sgc_value" => $sgc_values,
                "sgt_value" => $sgt_value,
                "mgc_id" => $mgc_id,
                "mgb_id" => $mgb_id,
                "sgc_id" => $sgc_id,
                "sgc1_id" => $sgc1_id,
                'province' => getRegion(),
                "guarantes" => $guarantes
            ]
        );
    }

    public function goodsAdd1()
    {   
        $list = array(
            'sp_id'=>$_POST['sp_id'],
            'kucuns'=>$_POST['kucuns'],
            'jiages'=>$_POST['jiages'],
            "weight1"=>$_POST['weight1'],
            "filenames"=>$_POST['filenames']
        );

        if ($this->request->isPost()) {
            ini_set("post_max_size", "100");
            ini_set("upload_max_filesize", "100");
            /* 只获取post以下参数 */
            $post_g_info = $this->request->only(['mgc_id', 'sgc_id','sgt_id', 'g_name', 'g_sn', 'g_original_price', 'g_current_price', 'g_cost_price', 'g_inventory', 'g_warning_inventory', 'mgb_id', 'g_desc', 's_is_show', 'g_shop_sort', 'g_unit_name', 'g_keywords', 'g_unit_value', 'g_content','g_weight','g_show_img_path1'], "post");
            $pathfile1 = array();
            $pathfilenum= array();
            if(isset($_POST["file1"]) && isset($_POST["file1num"])){
                $pathfile1 = $_POST["file1"];
                $pathfilenum= $_POST["file1num"];
            }

            foreach($pathfile1 as $k=>$value){
                $pathfile1[$pathfilenum[$k]] = $value;
            }

            /* 商品价格转换位浮点型 */
            // $post_g_info['g_original_price'] = floatval($post_g_info['g_original_price']);
            // $post_g_info['g_current_price'] = floatval($post_g_info['g_current_price']);
            // $post_g_info['g_cost_price'] = floatval($post_g_info['g_cost_price']);
            /* 商品主图 */
//            $path = "ShopGoods/" . date("y_m_d", time());
//            $goods_img = uploadImage($path, 'g_show_img_path');
//            $post_g_info['g_show_img_path'] = $goods_img['pic_cover'];

            $post_g_info['g_show_img_path'] = $post_g_info['g_show_img_path1'];
            unset($post_g_info['g_show_img_path1']);
            if (mingan($post_g_info['g_name'])) {
                $this->error('商品名包含敏感字！');
            }
            /*验证接到的值有没有问题*/
            $rule = array(

                // "mgc_id" => 'require|number|max:11',
                //"sgc_id" => 'require|number',
                "g_name" => 'require', //|chsAlphaNum|max:5020
                // "g_sn" => 'require|max:20',
                // "g_original_price" => 'require|float|gt:0.009|lt:999999999',
                // "g_current_price" => 'require|float|gt:0.009|lt:999999999',
                // "g_cost_price" => 'require|float|gt:0.009|lt:999999999',
                // "g_inventory" => 'require|max:7|number',
                // "g_warning_inventory" => 'require|max:3|number',
                "mgb_id" => 'require', //|number
                "s_is_show" => 'require', //|number
                //"is_free_shipping" => 'require',//|number
                // "g_shop_sort" => 'require|number|lt:255',
                // "g_unit_name" => 'require|chsAlpha|max:6',
                "g_keywords" => 'max:30',
                // "g_unit_value" => 'require|number|max:7',
                "g_show_img_path" => 'require',
                "g_weight" => 'require'
            );
            $msg = array(
                // "mgc_id.require" => '请选择店铺一级分类噢!~',
                // "mgc_id.number" => '数据格式不正确噢!~',
                // "mgc_id.max" => '数据格式不正确噢!~',
                "sgc_id.require" => '请选择店铺分类噢!~',
                "sgc_id.number" => '数据格式不正确噢!~',
                "g_name.require" => '请填写商品名称噢!~',
                "g_name.max" => '商品名称不能超过20个字符噢!~',
                "g_name.chsAlphaNum" => '商品名称只能是汉字,字母,数字噢!~',
                // "g_sn.require" => '请填写商品号噢!~',
                // "g_sn.max" => '商品号不能超过20个字符噢!~',
                // "g_original_price.require" => '请填写市场售价噢!~',
                // "g_original_price.float" => '市场售价只能填写数字和小数点噢!~',
                // "g_original_price.gt" => '市场售价要大于0.009',
                // "g_original_price.lt" => '市场售价要小于999999999',
                // "g_current_price.require" => '请填写本店售价噢!~',
                // "g_current_price.float" => '本店售价只能填写数字和小数点噢!~',
                // "g_current_price.gt" => '本店售价要大于0.009',
                // "g_current_price.lt" => '本店售价要小于999999999',
                // "g_cost_price.require" => '请填写成本价噢!~',
                // "g_cost_price.float" => '成本价只能填写数字和小数点噢!~',
                // "g_cost_price.gt" => '成本价要大于0.009',
                // "g_cost_price.lt" => '成本价要小于999999999',
                // "g_inventory.require" => '请填写库存数量噢!~',
                // "g_inventory.max" => '库存数量不能超过7位数字噢!~',
                // "g_inventory.number" => '库存数量只能是数字噢!~',
                // "g_warning_inventory.require" => '请填写警告库存数量噢!~',
                // "g_warning_inventory.max" => '警告库存数量不能超过3位数字噢!~',
                // "g_warning_inventory.number" => '警告库存只能是数字噢!~',
                "mgb_id.require" => '请选择商品品牌噢!~',
                "mgb_id.number" => '数据格式不正确噢!~',
                "s_is_show.require" => '请选择商品是否上下架噢!~',
                "s_is_show.number" => '是否上下架数据格式不正确噢!~',
//                "is_free_shipping.require" => '请选择商品是否包邮噢!~',
               // "is_free_shipping.number" => '是否包邮数据格式不正确噢!~',
                // "g_shop_sort.require" => '请输入商品排序噢!~',
                // "g_shop_sort.number" => '商品排序只能输入数字噢!~',
                // "g_shop_sort.lt" => '商品排序不能超过255噢!~',
                // "g_unit_name.require" => '请输入单位名称噢!~',
                // "g_unit_name.chsAlpha" => '单位名称输入字母或汉字噢!~',
                // "g_unit_name.max" => '单位名称不能超过6个字符噢!~',
                "g_keywords.max" => '商品关键字不能超过30个字符噢!~',
                // "g_unit_value.require" => '请输入单位值噢!~',
                // "g_unit_value.number" => '单位值只能是数字噢!~',
                // "g_unit_value.max" => '单位值不能超过7位数噢!~',
                "g_show_img_path.require" => '请上传商品主图',
                "g_weight.require" => '请填写商品重量'
            );
            $data = verify($post_g_info, $rule, $msg);

            if ($data['code'] === 1) {
                /*  添加时间 */
                $post_g_info['g_add_time'] = time();
                /* 最后更新时间 */
                $post_g_info['g_edit_time'] = time();
                /* 店铺id */
                $post_g_info['ss_id'] = $this->sm_info['ss_id'];
                /* 商品审核状态(查询这个商品是否需要审核) */
                $ss_goods_verify = $this->ss->getOne(['ss_id' => $this->sm_info['ss_id']], 'ss_goods_verify');
                /* 如果等于0的时候是需要审核的 */
                if ($ss_goods_verify === '0') {
                    $post_g_info['g_goods_verify'] = '0'; /* 审核中 */
                } else {
                    $post_g_info['g_goods_verify'] = '1'; /* 已审核 */
                }
                /* 判断商品是上架状态并且是审核完成状态 */
                if ($post_g_info['s_is_show'] == 0 && $post_g_info['g_goods_verify'] == '1') {
                    $post_g_info['g_show_start_time'] = time();
                } else {
                    $post_g_info['g_show_start_time'] = '';
                }

                /* 商品规格ID */
                if(isset($_REQUEST['sgt_id']) && $_REQUEST['sgt_id'] != ''){
                    $post_g_info['sgt_id'] = $_REQUEST['sgt_id'];
                }

                /*处理关键字*/
                if(isset($post_g_info['g_keywords'])){
                    $post_g_info['g_keywords'] = implode(",",$post_g_info['g_keywords']);
                }
//                if(isset($newarr)){
//                    $post_g_info['guige'] = serialize($newarr);
//                }
                /* 把第一张图添加到图片列表中 */
                $post_g_info['g_ismsg'] = "1";
                $g_id = $this->g->save($post_g_info);

                //生成商品号
                $gsn = $this->getGsn($this->sm_info['ss_id'],$g_id);
                $this->g->save(["g_sn"=>$gsn],["g_id"=>$g_id]);

                /* 添加到商品规格表中 */

                foreach($list['kucuns'] as $k=>$v){
                    $a["g_id"] = $g_id;
                    $a["sp_id"] = $list['sp_id'][$k];
                    $a["gsp_inventory"] = ($v=="")?0:$v;
                    $a["gsp_price"] = $list['jiages'][$k];
                    $a["gsp_weight"] = $list['weight1'][$k];
                    $a["gsp_filepath"] = $list['filenames'][$k];
                    $this->gsp->save($a);
                }






                /*添加轮播图*/

                if(isset($pathfile1)){

                    foreach ($pathfile1 as $key=>$value){
                        $basepath = $this->saveBase64Image($value);

                        $sgp_value = [
                            'g_id' => $g_id,
                            'gp_picture_path' => $basepath["image_url"],
                        ];
                        $this->gp->save($sgp_value);
                    }
                }


                /* 循环添加属性 */
                if (isset($_REQUEST['item'])) {
                    foreach ($_REQUEST['item'] as $key => $value) {
                        $sgp_value = [
                            'goods_id' => $g_id,
                            'keys' => $key,
                            'key_name' => $value['key_name'],
                            'price' => $value['price'],
                            'store_count' => $value['store_count'],
                        ];
                        $this->sgp->save($sgp_value);
                    }
                }
                if (intval($g_id) > 0) {
                    $this->sellerManagerLog("添加商品,添加的商品id为:" . $g_id);
                    $this->success("添加成功", url("seller/Goods/goodsList"));
                } else {
                    $this->success("添加失败");
                }
            } else {
                $this->error($data['msg'],url("seller/Goods/goodsAdd"));
            }
        }


         // id in (1,2,3)
        $sgt_value = $this->sgt->getList(['ss_id' => $this->sm_info['ss_id']]); /* 商家商品类型 */
        $mgc_valuess = $this->ss->getRow(['ss_id' => $this->sm_info['ss_id']]);/*商家分类权限*/
        $mgc_valuese=$mgc_valuess['mgc_id'].','.$mgc_valuess['ss_mgc_ids'];
       

        $mgc_value = $this->mgc->getList(['mgc_is_show' => '1','mgc_id'=>array("in",$mgc_valuese)], "*", ['concat(mgc_parent_path,mgc_id)' => 'mgc_parent_path']);
            // dump($mgc_value);die();

        $mgc_values1 =array();
        $mgc_values2 =array();
        $mgc_values3 =array();
        /* 总平台分类 */
        if (!empty($mgc_value)) {
            /* 添加区分类别大小的样式 */
            foreach ($mgc_value as $mgck => $mgcv) {

                $mgc_value = explode(',', $mgcv['mgc_parent_path']);
                $mgc_count = count($mgc_value) - 1;
            // dump($mgc_value);

                 // dump($mgc_count);
                 if ($mgc_count=='1') {
                    $mgcv['mgc_name'] = $mgcv['mgc_name'];
                    $mgc_values1[] = $mgcv;
                 }
                 if($mgc_count=='2'){
                    $mgcv['mgc_name'] = $mgcv['mgc_name'];
                    $mgc_values2[] = $mgcv;
                 }

                 if($mgc_count=='3'){
                    $mgcv['mgc_name'] = $mgcv['mgc_name'];
                    $mgc_values3[] = $mgcv;

                 }
                /* 添加的时候不显示三级分类 */
                // $style = "├";
                // for ($i = 1; $i <= $mgc_count; $i++) {
                //     $style .= "─";
                // }
            }
        } else {
            $mgc_values1 = '';
            $mgc_values2 = '';
            $mgc_values3 = '';
        }

        $mgb_value = $this->mgb->getList(); /* 总平台品牌 */
        $sgc_value = $this->sgc->getList(['ss_id' => $this->sm_info['ss_id']]); /*商户分类*/
        // dump($this->sm_info);exit;

        if (!empty($sgc_value)) {
            /* 添加区分类别大小的样式 */
            foreach ($sgc_value as $sgck => $sgcv) {
                $sgc_value = explode(',', $sgcv['sgc_parent_path']);
                $sgc_count = count($sgc_value) - 1;
                /* 添加的时候不显示三级分类 */
                $style = "├";
                for ($i = 1; $i <= $sgc_count; $i++) {
                    $style .= "─";
                }
                $sgcv['sgc_name'] = $style . $sgcv['sgc_name'];
                $sgc_values[] = $sgcv;
            }
        } else {
            $sgc_values = '';
        }
        // dump($_GET);die();
        $mgc_id=$_GET['mgc_id'];
        $mgb_id=$_GET['mgb_id'];
        $sgc_id=$_GET['sgc_id'];
        $sgc1_id=$_GET['sgc1_id'];
        return view(
            "goodsAdd",
            [   
                "mgc_value3" => $mgc_values3,
                "mgc_value2" => $mgc_values2,
                "mgc_value1" => $mgc_values1,
                "mgb_value" => $mgb_value,
                "sgc_value" => $sgc_values,
                "sgt_value" => $sgt_value,
                "mgc_id" => $mgc_id,
                "mgb_id" => $mgb_id,
                "sgc_id" => $sgc_id,
                "sgc1_id" => $sgc1_id,
                'province' => getRegion(),
            ]
        );
    }
    /**
     * [生成商品号]
     * 作者：wangmutian
     * @param $ssid 店铺id
     * @param $gid 商品id
     */
    public function getGsn($ssid,$gid){
        $num = "0000000000";
        $num = substr_replace($num,$ssid,3 - strlen($ssid),strlen($ssid));
        $num = substr_replace($num,$gid,10 - strlen($gid),strlen($gid));
        return $num;
    }

    /**
     * 作者：袁中旭
     * 时间：2017-10-14
     * 功能：商户后台商品修改
     */
    public function goodsEdit($id)
    {
        if (isset($id) && $id > 0) {
            
            $where = ["g_id" => intval($id)];
            $g_value = $this->g->getRow($where);
            $ajax_mgbse = $this->sp->getList(['sgt_id' =>  $g_value['sgt_id']]);
            foreach ($ajax_mgbse as $k=>$row){
                $ajax_mgbse[$k]["sp_name"] = trim($row["sp_name"]);
            }

            if ($this->request->isPost()) {
                $list = array(
                    'sp_id'=>$_POST['sp_id'],
                    'kucuns'=>$_POST['kucuns'],
                    'jiages'=>$_POST['jiages'],
                    'weight1'=>$_POST['weight1'],
                    'filenames'=>$_POST['filenames']
                );


                ini_set("post_max_size", "100");
                ini_set("upload_max_filesize", "100");

                /* 只获取post以下参数 */
                $post_g_info = $this->request->only(['mgc_id', 'sgc_id', 'sgt_id','g_name', 'g_sn', 'g_original_price', 'g_current_price', 'g_cost_price', 'g_inventory', 'g_warning_inventory', 'mgb_id', 'g_desc', 's_is_show', 'g_shop_sort', 'g_unit_name', 'g_keywords', 'g_unit_value', 'g_content','g_weight'], "post");

                $post_g_info['g_keywords'] = implode(",",$post_g_info['g_keywords']);
                /* 商品价格转换位浮点型 */
                // $post_g_info['g_original_price'] = floatval($post_g_info['g_original_price']);
                // $post_g_info['g_current_price'] = floatval($post_g_info['g_current_price']);
                // $post_g_info['g_cost_price'] = floatval($post_g_info['g_cost_price']);
                /*验证接到的值有没有问题*/
                $rule = array(
                    // "mgc_id" => 'require|number|max:11',
                    // "sgc_id" => 'require|number',
                    "g_name" => 'require|chsAlphaNum|max:20',
                    // "g_sn" => 'require|max:20',
                    // "g_original_price" => 'require|float|gt:0.009|lt:999999999',
                    // "g_current_price" => 'require|float|gt:0.009|lt:999999999',
                    // "g_cost_price" => 'require|float|gt:0.009|lt:999999999',
                    // "g_inventory" => 'require|max:7|number',
                    // "g_warning_inventory" => 'require|max:3|number',
                    "mgb_id" => 'require|number',
                    "s_is_show" => 'require|number',
                    //"is_free_shipping" => 'require|number',
                    // "g_shop_sort" => 'require|number|lt:255',
                    // "g_unit_name" => 'require|chsAlpha|max:6',
                    "g_keywords" => 'max:30',
                    // "g_unit_value" => 'require|number|max:7',
                    "g_weight" => 'require|number'
                );
                $msg = array(
                    // "mgc_id.require" => '请选择商品分类噢!~',
                    // "mgc_id.number" => '数据格式不正确噢!~',
                    // "mgc_id.max" => '数据格式不正确噢!~',
                    // "sgc_id.require" => '请选择店铺分类噢!~',
                    // "sgc_id.number" => '数据格式不正确噢!~',
                    "g_name.require" => '请填写商品名称噢!~',
                    "g_name.max" => '商品名称不能超过20个字符噢!~',
                    "g_name.chsAlphaNum" => '商品名称只能是汉字,字母,数字噢!~',
                    // "g_sn.require" => '请填写商品号噢!~',
                    // "g_sn.max" => '商品号不能超过20个字符噢!~',
                    // "g_original_price.require" => '请填写市场售价噢!~',
                    // "g_original_price.float" => '市场售价只能填写数字和小数点噢!~',
                    // "g_original_price.gt" => '市场售价要大于0.009',
                    // "g_original_price.lt" => '市场售价要小于999999999',
                    // "g_current_price.require" => '请填写本店售价噢!~',
                    // "g_current_price.float" => '本店售价只能填写数字和小数点噢!~',
                    // "g_current_price.gt" => '本店售价要大于0.009',
                    // "g_current_price.lt" => '本店售价要小于999999999',
                    // "g_cost_price.require" => '请填写成本价噢!~',
                    // "g_cost_price.float" => '成本价只能填写数字和小数点噢!~',
                    // "g_cost_price.gt" => '成本价要大于0.009',
                    // "g_cost_price.lt" => '成本价要小于999999999',
                    // "g_inventory.require" => '请填写库存数量噢!~',
                    // "g_inventory.max" => '库存数量不能超过7位数字噢!~',
                    // "g_inventory.number" => '库存数量只能是数字噢!~',
                    // "g_warning_inventory.require" => '请填写警告库存数量噢!~',
                    // "g_warning_inventory.max" => '警告库存数量不能超过3位数字噢!~',
                    // "g_warning_inventory.number" => '警告库存只能是数字噢!~',
                    "mgb_id.require" => '请选择商品品牌噢!~',
                    "mgb_id.number" => '数据格式不正确噢!~',
                    "s_is_show.require" => '请选择商品是否上下架噢!~',
                    "s_is_show.number" => '是否上下架数据格式不正确噢!~',
                    //"is_free_shipping.require" => '请选择商品是否包邮噢!~',
                    //"is_free_shipping.number" => '是否包邮数据格式不正确噢!~',
                    // "g_shop_sort.require" => '请输入商品排序噢!~',
                    // "g_shop_sort.number" => '商品排序只能输入数字噢!~',
                    // "g_shop_sort.lt" => '商品排序不能超过255噢!~',
                    // "g_unit_name.require" => '请输入单位名称噢!~',
                    // "g_unit_name.chsAlpha" => '单位名称只能输入字母或汉字噢!~',
                    // "g_unit_name.max" => '单位名称不能超过6个字符噢!~',
                    "g_keywords.max" => '商品关键字不能超过30个字符噢!~',
                    // "g_unit_value.require" => '请输入单位值噢!~',
                    // "g_unit_value.number" => '单位值只能是数字噢!~',
                    // "g_unit_value.max" => '单位值不能超过7位数噢!~',
                    "g_weight.require" => '商品重量不能为空噢!~',
                    "g_weight.number" => '商品重量格式不正确噢!~',

                );
                $data = verify($post_g_info, $rule, $msg);
                if ($data['code'] === 1) {

                    /* 商品主图 */
                    $path = "ShopGoods/" . date("y_m_d", time());
                    $goods_img = uploadImage($path, 'g_show_img_path');
                    if ($goods_img['code'] == '200') {
                        $post_g_info['g_show_img_path'] = $goods_img['pic_cover'];
                    }

                    /*  添加时间 */
                    $post_g_info['g_add_time'] = time();
                    /* 最后更新时间 */
                    $post_g_info['g_edit_time'] = time();
                    /* 店铺id */
                    $post_g_info['ss_id'] = $this->sm_info['ss_id'];
                    /* 商品审核状态(查询这个商品是否需要审核) */
                    $ss_goods_verify = $this->ss->getOne(['ss_id' => $this->sm_info['ss_id']], 'ss_goods_verify');
                    if ($g_value['g_goods_verify'] == '2') {
                        $post_g_info['g_goods_verify'] = '0'; /* 审核中 */
                    } else {
                        if ($ss_goods_verify === '0') {
                            $post_g_info['g_goods_verify'] = '0'; /* 审核中 */
                        } else {
                            $post_g_info['g_goods_verify'] = '1'; /* 已审核 */
                        }
                    }

                    /* 判断商品是上架状态并且是审核完成状态 */
                    if ($post_g_info['s_is_show'] == 0 && $post_g_info['g_goods_verify'] == '1') {
                        $post_g_info['g_show_start_time'] = time();
                    } else {
                        $post_g_info['g_show_start_time'] = '';
                    }
                    $post_g_info['g_ismsg'] = "1";
                    $g_id = $this->g->save($post_g_info, $where);
                    if (isset($_REQUEST['gp_picture_path']) && $_REQUEST['gp_picture_path'] != '') {
                        foreach ($_REQUEST['gp_picture_path'] as $gpk => $gpv) {
                            if ($gpv != '') {
                                /*$file = UPLOAD.'/'.$gpv; //获取已经上传文件的路径
                                $newFile = UPLOAD.'/ShopGoods/'. date("y_m_d", time()).'/'; //新目录
                                move_uploaded_file($file,$newFile); //拷贝到新目录
                                unlink($_SERVER['DOCUMENT_ROOT'].$file); //删除旧目录下的文件
                                $gp_url = substr($gpv, 18);*/

                                $gp = [
                                    'g_id' => $id,
                                    'gp_picture_path' => $gpv,
                                ];
                                $this->gp->save($gp);
                            }
                        }
                    }

                    $pathfile1 = array();
                    $pathfilenum= array();
                    if(isset($_POST["file1"]) && isset($_POST["file1num"])){
                        $pathfile1 = $_POST["file1"];
                        $pathfilenum= $_POST["file1num"];
                    }

                    foreach($pathfile1 as $k=>$value){
                        $pathfile1[$pathfilenum[$k]] = $value;
                    }


                    /*添加轮播图*/
                    if(isset($pathfile1)){
                        $this->gp->del(["g_id"=>$id]);
                        foreach ($pathfile1 as $key=>$value){
                            $basepath = $this->saveBase64Image($value);
                            $sgp_value = [
                                'g_id' => $id,
                                'gp_picture_path' => $basepath["image_url"],
                            ];
                            $this->gp->save($sgp_value);
                        }
                    }

                    /* 循环添加属性 */
                    if (isset($_REQUEST['item'])) {
                        $this->sgp->del(['goods_id' => $id]);
                        foreach ($_REQUEST['item'] as $key => $value) {
                            $sgp_value = [
                                'goods_id' => $id,
                                'keys' => $key,
                                'key_name' => $value['key_name'],
                                'price' => $value['price'],
                                'store_count' => $value['store_count'],
                            ];
                            $this->sgp->save($sgp_value);
                        }
                    }

                    /*添加商品规格表*/
                    if(isset($list['kucuns'])){
                        $this->gsp->del(["g_id"=>$id]);
                        foreach($list['kucuns'] as $k=>$v){
                            $a["g_id"] = $id;
                            $a["sp_id"] = $list['sp_id'][$k];
                            $a["gsp_inventory"] = ($v=="")?0:$v;
                            $a["gsp_price"] = $list['jiages'][$k];
                            $a["gsp_weight"] = $list['weight1'][$k];
                            $a["gsp_filepath"] = $list['filenames'][$k];
                            $this->gsp->save($a);
                        }
                    }




                    if (intval($g_id) > 0) {
                        $this->sellerManagerLog("修改商品,修改的商品id为:" . $id);
                        $this->success("修改成功", url("seller/Goods/goodsList"));
                    } else {
                        $this->success("修改失败");
                    }
                } else {
                    $this->error($data['msg']);
                }
            }
            /* 商品轮播图 */
            $gp_value = $this->gp->getList($where);

            $gp_count = count($gp_value);

            /* 商家商品类型 */
            $sgt_value = $this->sgt->getList(['ss_id' => $this->sm_info['ss_id']]);
            $mgc_valuess = $this->ss->getRow(['ss_id' => $this->sm_info['ss_id']]);/*商家分类权限*/
//            $mgc_valuese=$mgc_valuess['mgc_id'].','.$mgc_valuess['ss_mgc_ids'];
           
            // $mgc_value = $this->mgc->getList(['mgc_is_show' => '1','mgc_id'=>array("in",$mgc_valuese)], "*", ['concat(mgc_parent_path,mgc_id)' => 'mgc_parent_path']);
            $mgc_values2 = $this->mgc->getRow(['mgc_is_show' => '1','mgc_id'=>$g_value['sgc_id']]);
            $mgc_values3 = $this->mgc->getRow(['mgc_is_show' => '1','mgc_id'=>$g_value['mgc_id']]);

            $mgc_values1 = null;
            if(!empty($mgc_values2)){
                $mgc_values1 = $this->mgc->getRow(['mgc_is_show' => '1','mgc_id'=>$mgc_values2['mgc_parent_id']]);
            }

            

            $mgb_value = $this->mgb->getList(); /* 总平台品牌 */
            $sgc_value = $this->sgc->getList(['ss_id' => $this->sm_info['ss_id']]); /*商户分类*/
            if (!empty($sgc_value)) {
                /* 添加区分类别大小的样式 */
                foreach ($sgc_value as $sgck => $sgcv) {
                    $sgc_value = explode(',', $sgcv['sgc_parent_path']);
                    $sgc_count = count($sgc_value) - 1;
                    /* 添加的时候不显示三级分类 */
                    $style = "├";
                    for ($i = 1; $i <= $sgc_count; $i++) {
                        $style .= "─";
                    }
                    $sgcv['sgc_name'] = $style . $sgcv['sgc_name'];
                    $sgc_values[] = $sgcv;
                }
            } else {
                $sgc_values = '';
            }

            $r_id_value = explode(',', $g_value['r_id']);
            foreach ($r_id_value as $r_id_k => $r_id_v) {
                $g_value['r_id_' . $r_id_k] = $r_id_v;
            }

            $g_keywords = explode(",",$g_value["g_keywords"]);


            $g_guiges=$this->gsp->getList(["g_id"=>$id]);
            $gids = array();
            foreach ($g_guiges as $k=>$row){
                $gids[] = $sp_id = $row["sp_id"];
                $sp_name = $this->sp->getOne(["id"=>$sp_id],"sp_name");
                if(!empty($sp_name)){
                    $g_guiges[$k]["sp_name"] = empty($sp_name)?"":$sp_name;
                }else{
                    unset($g_guiges[$k]);
                }
            }
            $guarantes = unserialize($mgc_valuess['guarantes']);

            foreach ($guarantes as $k=>$g){

                if(!isset($g["guname"])){
                    $guOne = $this->gu->getOne(["gu_id"=>$g["guids"]],"gu_name");
                    $guarantes[$k]["guname"] = $guOne;
                }
            }


            return view(
                "goodsEdit",
                [
                    "mgc_value1" => isset($mgc_values1['mgc_name'])?$mgc_values1['mgc_name']:"",
                    "mgc_value2" => isset($mgc_values2['mgc_name'])?$mgc_values2['mgc_name']:"",
                    "mgc_value3" => isset($mgc_values3['mgc_name'])?$mgc_values3['mgc_name']:"",
                    "mgb_value" => $mgb_value,
                    "sgc_value" => $sgc_values,
                    "sgt_value" => $sgt_value,
                    "g_value_keyword"=>$g_keywords,
                    "g_value_guige"=>$g_guiges,
                    "ajax_mgbse" =>$ajax_mgbse,
                    "gids" =>$gids,
                    "province" => getRegion(),
                    "g_value" => $g_value,
                    "gp_count" => $gp_count,
                    "gp_value" => $gp_value,
                    "boxaddleft"=> count($gp_value)*230,
                    "guarantes" => $guarantes
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-11-29
     * 功能：AJAX修改商品上下架状态
     */
    public function changeGoodsInfo()
    {
        /*判断是不死ajax请求*/
        if ($this->request->isAjax()) {
            /*只接收id的值*/
            $info = $this->request->only(['id', 'val'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require',
                "val" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
                "val.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);

            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {

                /*where条件 强制转换整型*/
                $where['g_id'] = intval($info['id']);

                $changeGoodsInfo = $this->g->save(['s_is_show' => $info['val']],$where);
                if ($changeGoodsInfo !== false ) {
                    return json(format($info, '1', 'success'));
                } else {
                    return json(format('', '-1', "修改失败,请联系管理员!~"));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-11-06
     * 功能：AJAX删除商品轮播图
     */
    public function ajaxGpDel()
    {
        /*判断是不死ajax请求*/
        if ($this->request->isAjax()) {
            /*只接收id的值*/
            $info = $this->request->only(['id', 'url'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require',
                "url" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
                "url.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);

            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {

                /*where条件 强制转换整型*/
                $where['gp_id'] = intval($info['id']);
                $gp = $this->gp->del($where);
                $gp_del = $_SERVER['DOCUMENT_ROOT'] . UPLOAD . '/' . $info['url'];
                unlink($gp_del);

                if (false === $gp) {
                    return json(format('', '-1', "删除失败!~"));
                } else {
                    return json(format('', '1', "删除成功!~"));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-14
     * 功能：商户后台商品删除
     */
    public function goodsDel()
    {
        /*判断是不死ajax请求*/
        if ($this->request->isAjax()) {
            /*只接收id的值*/
            $info = $this->request->only(['id'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                /*where条件 强制转换整型*/
                $where['g_id'] = intval($info['id']);

                $ret = $this->g->del($where);
                $gas = $this->gas->del($where);
                $gp = $this->gp->del($where);
                if (false === $ret) {
                    return json(format('', '-1', "删除失败!~"));
                } else {
                    $this->sellerManagerLog("删除商品,删除的商品id为:" . $info['id']);
                    return json(format('', '1', "删除成功!~"));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-19
     * 功能：上传商品轮播
     */
    public function goodsImg()
    {
        $path = "ShopGoods/" . date("y_m_d", time());
        $goods_img = uploadImage($path);
        echo $goods_img['pic_cover'];
    }


    /**
     * 作者：wangmutian
     * 时间：2017-10-19
     * 功能：上传图片
     */
    public function goodsImgupdate()
    {
        $path = "ShopGoods/" . date("y_m_d", time());
        $goods_img = uploadImage($path);
        return json_encode($goods_img);
    }

    /**
     * 作者：wangmutian
     * 时间：2018-05-24
     * 功能：上传多图片
     */
    public function goodsImgupdates(){
        $path = "shopAd/" . date("y_m_d", time());
        $ss_file = uploadImages($path,'g_show_img_ss_file');
        return json_encode($ss_file);
    }


    /**
     * 作者：袁中旭
     * 时间：2017-10-13
     * 功能：商户后台商品分类列表
     */
    public function goodsCategoryList()
    {
        if ($this->request->isAjax()) {
            $info = $this->request->only(['id'], "post");
            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require|number',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
                "id.number" => '数据格式不正确噢!~',
            );
            $data = verify($info, $rule, $msg);

            if ($data['code'] === 1) {
                /*获取列表*/
                $sgc_list = $this->sgc->getList(["sgc_parent_id" => $info['id'], 'ss_id' => $this->sm_info['ss_id']], [], ['sgc_sort' => "desc"]);
                return json(format($sgc_list, '1'));
            } else {
                return json(format('', '-1', $data['msg']));
            }
            exit();
        }
        /*获取列表*/
        $sgc_list = $this->sgc->getAll(["sgc_parent_id" => 0, 'ss_id' => $this->sm_info['ss_id']], [], ['sgc_sort' => "desc"]);
        return view(
            "goodsCategoryList",
            [
                "list" => $sgc_list['data'], /*查询结果*/
                "page" => $sgc_list['page'], /*分页和html代码*/
                "lastPage" => $sgc_list['lastPage'], /*总页数*/
                "currentPage" => $sgc_list['currentPage'], /*当前页码*/
                "total" => $sgc_list['total'], /*总条数*/
                "listRows" => $sgc_list['listRows'], /*每页显示条数*/
            ]
        );
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-13
     * 功能：商户商品分类添加
     */
    public function goodsCategoryAdd()
    {
        if ($this->request->isPost()) {
            /* 只获取post以下参数 */
            $post_sgc_info = $this->request->only(['sgc_name', 'sgc_parent_id', 'sgc_sort', 'sgc_desc', 'sgc_is_show', 'is_hot'], "post");

            /* 验证接到的值有没有问题 */
            $rule = array(
                "sgc_name" => 'require|max:30',
                "sgc_parent_id" => 'require|number',
                "sgc_sort" => "number",
                "sgc_desc" => "max:30",
            );
            $msg = array(
                "sgc_name.require" => "名称是必须要填写的噢!~",
                "sgc_name.max" => "亲，名字的最大长度不能超过30个字符噢!~",
                "sgc_parent_id.require" => "分类归属必须要选择的噢!~",
                "sgc_parent_id.number" => "分类归属必须说数字噢!~",
                "sgc_sort.number" => "排序只能填写数字噢!~",
                "sgc_desc.max" => "分类描述的最大长度不能超过30个字符噢!~",
            );
            $data = verify($post_sgc_info, $rule, $msg);
            /* code 等于1 说明成功 否则失败 */
            if ($data['code'] === 1) {
                /*检验名称是否重复*/
                $sgc_name = $this->sgc->getOne(["sgc_name" => $post_sgc_info['sgc_name'],'ss_id' => $this->sm_info['ss_id']], "sgc_name");

                if ($sgc_name == $post_sgc_info['sgc_name'] && !empty($sgc_name)) {
                    $this->error("您输入的名称重复了噢,请重新输入!~");
                    exit();
                }

                /* 判断是否开启 */
                (isset($post_sgc_info['sgc_is_show']) && !empty($post_sgc_info['sgc_is_show']) && $post_sgc_info['sgc_is_show'] == 'on') ? $post_sgc_info['sgc_is_show'] = 1 : $post_sgc_info['sgc_is_show'] = 0;
                /* 判断是否热门 */
                (isset($post_sgc_info['is_hot']) && !empty($post_sgc_info['is_hot']) && $post_sgc_info['is_hot'] == 'on') ? $post_sgc_info['is_hot'] = 1 : $post_sgc_info['is_hot'] = 0;
                /* 判断是否是一级分类 */
                if ($post_sgc_info['sgc_parent_id'] > 0) {
                    /* 拼接添加的父类路径 */
                    $sgc_row = $this->sgc->getRow(['sgc_id' => $post_sgc_info['sgc_parent_id']]);
                    $post_sgc_info['sgc_parent_path'] = $sgc_row['sgc_parent_path'] . $sgc_row['sgc_id'] . ',';
                } else {
                    /* 拼接添加的父类路径 */
                    $post_sgc_info['sgc_parent_path'] = $post_sgc_info['sgc_parent_id'] . ',';
                }
                /* 店铺id */
                $post_sgc_info['ss_id'] = $this->sm_info['ss_id'];
                /* 执行添加 */
                $id = $this->sgc->save($post_sgc_info);
                if ($id > 0) {
                    /* 记录日志 */
                    $this->sellerManagerLog("商品分类添加,添加后的id:" . $id);
                    $this->success("添加成功");
                } else {
                    $this->error("添加失败");
                }
            }
        }

        /* 获取父类id和名称 */
        $first_cate = $this->sgc->getList(['ss_id' => $this->sm_info['ss_id']], "*", ['concat(sgc_parent_path,sgc_id)' => 'sgc_parent_path']);
        if (!empty($first_cate)) {
            /* 添加区分类别大小的样式 */
            foreach ($first_cate as $gcak => $gcav) {
                $gca_value = explode(',', $gcav['sgc_parent_path']);
                $gca_count = count($gca_value) - 1;
                /* 添加的时候不显示三级分类 */
                if ($gca_count > 2) {
                    unset($gcav);
                } else {
                    $style = "├";
                    for ($i = 1; $i <= $gca_count; $i++) {
                        $style .= "─";
                    }
                    $gcav['sgc_name'] = $style . $gcav['sgc_name'];
                    $first_cates[] = $gcav;
                }
            }
        } else {
            $first_cates = $first_cate;
        }

        return view(
            "goodsCategoryAdd",
            [
                'first_cate' => $first_cates,
            ]
        );
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-13
     * 功能：商户商品分类显示
     */
    public function goodsCategoryShow()
    {
        /*是不是ajax*/
        if ($this->request->isAjax()) {

            $info = $this->request->only(['id'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                $where["sgc_id"] = intval($info['id']);
                $info = $this->sgc->getRow($where);
                /*转换*/
                (isset($info['sgc_is_show']) && ($info['sgc_is_show'] == 1)) ? $info['sgc_is_show'] = "开启" : $info['sgc_is_show'] = "关闭";

                (isset($info['is_hot']) && ($info['is_hot'] == 1)) ? $info['is_hot'] = "开启" : $info['is_hot'] = "关闭";

                (isset($info['sgc_parent_id']) && ($info['sgc_parent_id'] == 0)) ? $info['sgc_parent_id'] = "顶级分类" : $info['sgc_parent_id'] = $this->sgc->getOne(["sgc_id" => $info['sgc_parent_id']], 'sgc_name');

                $this->sellerManagerLog("查看商品分类详情,查看的分类id为:" . $info['sgc_id']);
                return json(format($info, '1', $data['msg']));
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-13
     * 功能：商户商品分类修改
     */
    public function goodsCategoryEdit($id)
    {
        if (isset($id) && $id > 0) {
            /*转换整型*/
            $where["sgc_id"] = intval($id);
            $where['ss_id'] = $this->sm_info['ss_id'];
            $info = $this->sgc->getRow($where);
            if (empty($info)) {
                $this->error("该商品分类不存在!请联系管理员!~");
                exit();
            }
            if ($this->request->isPost()) {
                $post_sgc_info = $this->request->only(['sgc_name', 'sgc_parent_id', 'sgc_sort', 'sgc_desc', 'sgc_is_show', 'is_hot'], 'post');
                /* 验证接到的值有没有问题 */
                $rule = array(
                    "sgc_name" => 'require|max:30',
                    "sgc_parent_id" => 'require|number',
                    "sgc_sort" => "number",
                    "sgc_desc" => "max:30",
                );
                $msg = array(
                    "sgc_name.require" => "名称是必须要填写的噢!~",
                    "sgc_name.max" => "亲，名字的最大长度不能超过30个字符噢!~",
                    "sgc_parent_id.require" => "分类归属必须要选择的噢!~",
                    "sgc_parent_id.number" => "分类归属必须说数字噢!~",
                    "sgc_sort.number" => "排序只能填写数字噢!~",
                    "sgc_desc.max" => "分类描述的最大长度不能超过30个字符噢!~",
                );
                $data = verify($post_sgc_info, $rule, $msg);
                if ($data['code'] === 1) {

                    /*检验名称是否重复*/
                    $sgc_name = $this->sgc->getOne(["sgc_name" => $post_sgc_info['sgc_name'],'ss_id' => $this->sm_info['ss_id']], "sgc_name");

                    if ($sgc_name == $post_sgc_info['sgc_name'] && !empty($sgc_name)) {
                        $this->error("您输入的名称重复了噢,请重新输入!~");
                        exit();
                    }
                    if ($post_sgc_info['sgc_parent_id'] > 0) {
                        $sgc_parent_path = $this->sgc->getOne(['sgc_id' => $post_sgc_info['sgc_parent_id']], "sgc_parent_path");
                        $post_sgc_info['sgc_parent_path'] = $sgc_parent_path . $post_sgc_info['sgc_parent_id'] . ',';
                    } else {
                        $post_sgc_info['sgc_parent_path'] = $post_sgc_info['sgc_parent_id'] . ',';
                    }

                    (isset($post_sgc_info['sgc_is_show']) && !empty($post_sgc_info['sgc_is_show']) && $post_sgc_info['sgc_is_show'] == 'on') ? $post_sgc_info['sgc_is_show'] = 1 : $post_sgc_info['sgc_is_show'] = 0;

                    (isset($post_sgc_info['is_hot']) && !empty($post_sgc_info['is_hot']) && $post_sgc_info['is_hot'] == 'on') ? $post_sgc_info['is_hot'] = 1 : $post_sgc_info['is_hot'] = 0;

                    /*更新信息*/
                    $ret_info = $this->sgc->save($post_sgc_info, $where);
                    if (false !== $ret_info) {
                        $this->sellerManagerLog("商品分类修改,修改的id为:" . $id);
                        $this->success("修改成功", url("seller/Goods/goodsCategoryList"));
                    } else {
                        $this->error("修改失败");
                    }
                } else {
                    $this->error($data['msg']);
                }
                exit();
            }
            $sgc_value = $this->sgc->getRow(["sgc_parent_id" => $id, 'ss_id' => $this->sm_info['ss_id']]);
            if (empty($sgc_value)) {
                $goods_edit = $this->sgc->getList(['ss_id' => $this->sm_info['ss_id']]);
                /* 添加区分类别大小的样式 */
                foreach ($goods_edit as $gcak => $gcav) {
                    $gca_value = explode(',', $gcav['sgc_parent_path']);
                    $gca_count = count($gca_value) - 1;
                    /* 添加的时候不显示三级分类 */
                    if ($gca_count > 2) {
                        unset($gcav);
                    } else {
                        $style = "├";
                        for ($i = 1; $i <= $gca_count; $i++) {
                            $style .= "─";
                        }
                        $gcav['sgc_name'] = $style . $gcav['sgc_name'];
                        $first_cates[] = $gcav;
                    }
                }
                return view(
                    "goodsCategoryEdit",
                    [
                        'info' => $info,
                        'first_cate' => $first_cates,
                        'sgc_value' => '',
                        'info_sgc_parent_id' => $info['sgc_parent_id'],
                    ]
                );
            } else {
                return view(
                    "goodsCategoryEdit",
                    [
                        'info' => $info,
                        'first_cate' => '',
                        'sgc_value' => '这个分类下还有分类，请先把下面的分类修改到别的分类下',
                    ]
                );
            }
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-13
     * 功能：商户ajax获取商品分类
     */
    public function getGoodsCategory()
    {
        if ($this->request->isAjax()) {
            /*直接收id*/
            $id = $this->request->only(["id"], "post");
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($id, $rule, $msg);
            if ($data['code'] == 1) {
                $info = $this->sgc->getList(["sgc_parent_id" => $id['id'], 'ss_id' => $this->sm_info['ss_id']], "sgc_id,sgc_name");
                if (isset($info) && !empty($info)) {
                    return json(format($info));
                } else {
                    return json(format('', '-1', "该类目下没有下级分类了!~"));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-12
     * 功能：商户商品分类删除
     */
    public function goodsCategoryDel()
    {
        /*判断是不死ajax请求*/
        if ($this->request->isAjax()) {
            /*只接收id的值*/
            $info = $this->request->only(['id'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                /*where条件 强制转换整型*/
                $where['sgc_id'] = intval($info['id']);
                $sgc_info = $this->sgc->getRow($where);

                if ($sgc_info['sgc_parent_id'] != 0) {
                    $count = $this->sgc->getCount(['sgc_parent_id' => intval($info['id'])]);
                } else {
                    $count = $this->sgc->getCount(['sgc_parent_id' => intval($info['id'])]);
                }

                if ($count > 0) {
                    return json(format('', '-1', "该分类有下级分类,请先删除下级分类!~"));
                } else {
                    $g_count = $this->g->getCount(["sgc_id" => intval($info['id']), 'ss_id' => $this->sm_info['ss_id']]);
                    if ($g_count > 0) {
                        return json(format('', '-1', "该分类下面有商品哦,请先删除商品在删除分类!~"));
                    } else {
                        $ret = $this->sgc->del($where);
                        if (false === $ret) {
                            return json(format('', '-1', "删除失败!~"));
                        } else {
                            $this->sellerManagerLog("删除商品分类,删除的分类id为:" . $info['id']);
                            return json(format('', '1', "删除成功!~"));
                        }
                    }
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }

    /**
     * 作者：袁中旭
     * 时间：2017-10-14
     * 功能：商户后台品牌列表
     */
    public function goodsBrandList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["mgb_name", "mgb_english_name"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否接收到名称信息*/
            if (isset($condition['mgb_name']) && ('' != $condition['mgb_name'])) {
                /*模糊查询*/
                $where['mgb_name'] = ['like', "%" . $condition['mgb_name'] . "%"];
                $pageParam['query']['mgb_name'] = $condition['mgb_name'];
            }
            if (isset($condition['mgb_english_name']) && ('' != $condition['mgb_english_name'])) {
                /*模糊查询*/
                $where['mgb_english_name'] = ['like', "%" . $condition['mgb_english_name'] . "%"];
                $pageParam['query']['mgb_english_name'] = $condition['mgb_english_name'];
            }
        }
        $list = $this->mgb->getAll($where, $pageParam);
        return view(
            "goodsBrandList",
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
     * 作者：袁中旭
     * 时间：2017-10-14
     * 功能：商户申请品牌列表
     */
    public function sellerGoodsBrandList()
    {


        /*接收到的数据*/
        $condition = $this->request->only(["sba_name", "sba_english_name"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否接收到名称信息*/
            if (isset($condition['sba_name']) && ('' != $condition['sba_name'])) {
                /*模糊查询*/
                $where['sba_name'] = ['like', "%" . $condition['sba_name'] . "%"];
                $pageParam['query']['sba_name'] = $condition['sba_name'];
            }
            if (isset($condition['sba_english_name']) && ('' != $condition['sba_english_name'])) {
                /*模糊查询*/
                $where['sba_english_name'] = ['like', "%" . $condition['sba_english_name'] . "%"];
                $pageParam['query']['sba_english_name'] = $condition['sba_english_name'];
            }
        }
        $where['ss_id'] = $this->sm_info['ss_id'];
        $list = $this->sba->getAll($where, $pageParam);

        return view(
            "sellerGoodsBrandList",
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
     * 作者：袁中旭
     * 时间：2017-10-14
     * 功能：商户后台商品品牌添加
     */
    public function goodsBrandAdd()
    {
        if ($this->request->isPost()) {
            $post = $this->request->only(["sba_name", "sba_english_name", "sba_official_website", "sba_desc", "sba_logo_path"], "post");
            /*验证接到的值有没有问题*/
            $rule = array(
                "sba_name" => 'require|max:30',
                "sba_english_name" => 'require|max:30|alphaNum',
                // "sba_official_website" => 'url',
                "sba_desc" => 'max:255',
            );
            $msg = array(
                "sba_name.require" => '品牌名称是必须要填写的噢!~',
                "sba_name.max" => '品牌名称最多30个字符哦!~',
                "sba_english_name.require" => '品牌英文名必须要填写哦!~',
                "sba_english_name.max" => '品牌英文名最大长度为30个字符哦!',
                "sba_english_name.alphaNum" => '品牌英文名只能是英文和数字哦!~',
                // "sba_official_website.url" => '品牌官网地址填写有误!~',
                "sba_desc.max" => '描述最多只能填写255个字符噢!~',
            );
            $data = verify($post, $rule, $msg);
            if ($data['code'] === 1) {
                $count = $this->sba->getCount(["sba_name" => $post['sba_name']]);
                $countmgb = $this->mgb->getCount(['mgb_name' => $post['sba_name']]);

                if ($count > 0 || $countmgb > 0) {
                    $this->error("您输入的名称已存在!~");
                    exit();
                }
                if ($_FILES['sba_logo_path']['error'] == 4) {
                    $this->error("文章缩略图是必须要上传的哦!~");
                    exit();
                }
                /*先上传文件*/
                $path = "goodsBrand/" . date("y_m_d", time());
                $file_info = uploadImage($path);
                // dump($file_info);die();
                if ($file_info['code'] == 200) {
                    $post['sba_logo_path'] = $file_info['pic_cover'];
                    $post['sba_add_time'] = time();
                    $post['ss_id'] = $this->sm_info['ss_id'];
                    $post['sba_ismsg'] = 1;
                    /*存入数据库*/
                    $id = $this->sba->save($post);
                    if ($id > 0) {
                        $this->sellerManagerLog("添加品牌,id为:" . $id);
                        $this->success("添加成功！～", url("seller/Goods/sellerGoodsBrandList"));
                    } else {
                        $this->error("添加失败！～");
                    }
                } else {
                    $this->error($file_info['msg']);
                }
            } else {
                $this->error($data['msg']);
            }
            exit();
        }
        return view(
            "goodsBrandAdd"
        );
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-14
     * 功能：商户后台商品品牌修改
     */
    public function goodsBrandEdit($id)
    {
        if (isset($id) && $id > 0) {
            $where = ["sba_id" => intval($id)];
            $info = $this->sba->getRow($where);
            if ($this->request->isPost()) {
                $post = $this->request->only(["sba_name", "sba_english_name", "sba_official_website", "sba_desc", "sba_logo_path"], "post");
                /*验证接到的值有没有问题*/
                $rule = array(
                    "sba_name" => 'require|max:30',
                    "sba_english_name" => 'require|max:30|alphaNum',
                    // "sba_official_website" => 'url',
                    "sba_desc" => 'max:255',
                );
                $msg = array(
                    "sba_name.require" => '品牌名称是必须要填写的噢!~',
                    "sba_name.max" => '品牌名称最多30个字符哦!~',
                    "sba_english_name.require" => '品牌英文名必须要填写哦!~',
                    "sba_english_name.max" => '品牌英文名最大长度为30个字符哦!',
                    "sba_english_name.alphaNum" => '品牌英文名只能是英文和数字哦!~',
                    // "sba_official_website.url" => '品牌官网地址填写有误!~',
                    "sba_desc.max" => '描述最多只能填写255个字符噢!~',
                );
                $data = verify($post, $rule, $msg);
                if ($data['code'] === 1) {

                    $count = $this->sba->getCount(["sba_name" => $post['sba_name'],"sba_id"=>["neq",$id]]);

                    if ($count > 0) { ///&& $post['sba_name'] == $info['sba_name']
                        $this->error("您输入的名称重复了噢!~,请重新输入!~");
                        exit();
                    }

                    if ($_FILES['sba_logo_path']['error'] != 4) {
                        $path = "goodsBrand/" . date("y_m_d", time());
                        $file_info = uploadImage($path);
                        if ($file_info['code'] == 200) {
                            $post['sba_logo_path'] = $file_info['pic_cover'];
                        } else {
                            $this->error($file_info['msg']);
                        }
                    }

                    $ret = $this->sba->save($post, $where);
                    $this->sellerManagerLog("修改品牌,id为:" . $id);
                    if (false !== $ret) {
                        $this->success("修改成功!~", url("seller/Goods/sellerGoodsBrandList"));
                    } else {
                        $this->error("修改失败!~");
                    }
                } else {
                    $this->error($data['msg']);
                }
                exit();
            }
            $mgb_value = $this->mgb->getList();
            if (empty($mgb_value)) {
                $mgb_value = '';
                $this->error("请先联系管理员添加品牌!~");
            }
            return view(
                "goodsBrandEdit",
                [
                    "info" => $info,
                    "mgb_value" => $mgb_value,
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-14
     * 功能：商户后台商品品牌删除
     */
    public function goodsBrandDel()
    {
        // dump($_POST);die();
        if ($this->request->isAjax()) {
            /*只接收id的值*/
            $info = $this->request->only(['id'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);

            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                /*where条件 强制转换整型*/

                $count = $this->g->getCount(['mgb_id' => $info['id'], 'ss_id' => $this->sm_info['ss_id']]);
                if ($count > 0) {
                    return json(format('', '-1', '删除失败~!该品牌下面有商品哦~!'));
                }
                $sba = $this->sba->del(['sba_id' => $info['id'], 'ss_id' => $this->sm_info['ss_id']]);
                if (false === $sba) {
                    return json(format('', '-1', '删除失败~!请稍候重试~!'));
                } else {
                    // $this->sellerManagerLog("删除品牌，品牌id为:" . $id);
                    return json(format('', '1', '删除成功~!'));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数11据噢!~"));
        }
    }

    public function ajaxBrandspname()
    {
        if ($this->request->isAjax()) {
            /*只接收id的值*/
            $info = $this->request->only(['sgt_id'], 'post');

            /*验证接到的值有没有问题*/
            $rule = array(
                "sgt_id" => 'require',
            );
            $msg = array(
                "sgt_id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );

            $data = verify($info, $rule, $msg);

            /*验证返回如果code == 1 说明成功*/
            // if ($data['code'] === 1) {
            $ajax_mgb = $this->sp->getList(['sgt_id' =>  $info['sgt_id']]);
            $ajax_mgbs = implode('', array_column($ajax_mgb, 'sp_name'));
                if(empty($ajax_mgb)){
                    return json(format('', '-1'));
                }else{
                   return json(format($ajax_mgb,$ajax_mgbs));
                }
            // } else {
            //     return json(format('', '-1', $data['msg']));
            // }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-11-18
     * 功能：AJAX或者搜索品牌
     */
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
                $ajax_mgb = $this->mgb->getList(['mgb_name' => ["like", "%" . $info['brand_search'] . "%"]]);
                if(empty($ajax_mgb)){
                    return json(format('', '-1', "没有这个商品品牌噢!~"));
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
    /**
     * 作者：袁中旭
     * 时间：2017-10-14
     * 功能：商户后台商品类型列表
     */
    public function goodsTypeList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["sgt_name"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = ['ss_id' => $this->sm_info['ss_id']];
        if (is_array($condition)) {
            /*是否接收到名称信息*/
            if (isset($condition['sgt_name']) && ('' != $condition['sgt_name'])) {
                /*模糊查询*/
                $where['sgt_name'] = ['like', "%" . $condition['sgt_name'] . "%"];
                $pageParam['query']['sgt_name'] = $condition['sgt_name'];
            }
        }
        $list = $this->sgt->getAll($where, $pageParam);
        return view(
            "goodsTypeList",
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
     * 作者：袁中旭
     * 时间：2017-10-14
     * 功能：商户后台商品类型添加
     */
    public function goodsTypeAdd()
    {
        if ($this->request->isPost()) {
            $post = $this->request->only(['sgt_name', 'sgt_desc'], 'post');
            $sp_names=explode(PHP_EOL, $_POST['sp_name']);
            $sp_names = array_filter($sp_names);

            /*验证接到的值有没有问题*/
            $rule = array(
                "sgt_name" => 'require|max:20',
                "sgt_desc" => 'max:255',
            );
            $msg = array(
                "sgt_name.require" => '类型名称是必须要填写的噢!~',
                "sgt_name.max" => '名称的最大长度不能超过20个字符噢!~',
                "sgt_desc.max" => '描述最多只能填写255个字符噢!~',
            );
            $data = verify($post, $rule, $msg);
            if ($data['code'] === 1) {
                $post['sgt_add_time'] = time();
                $post['ss_id'] = $this->sm_info['ss_id'];

                $id = $this->sgt->save($post);

                $i=1;
                foreach ($sp_names as $k=>$row){
                    $datas[$i]['sgt_id']=$id;
                    $datas[$i]['sp_name']=$row;
                    $i++;
                }


                $this->sp->saveAll($datas);
                if ($id > 0) {
                    $this->sellerManagerLog("添加后台商品类型,添加的id为:" . $id);

                    $this->success("添加成功!~", url("seller/Goods/goodsTypeList"));
                } else {
                    $this->error("添加失败!~");
                }
            } else {
                $this->error($data['msg']);
            }
            exit();
        }
        return view(
            "goodsTypeAdd"
        );
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-14
     * 功能：商户后台商品类型修改
     */
    public function goodsTypeEdit($id)
    {
        if (isset($id) && $id > 0) {
            $where = ["sgt_id" => intval($id)];

            $info = $this->sgt->getRow($where);
            $items = $this->sp->getList($where);
        
            $sp_name = implode(PHP_EOL, array_column($items, 'sp_name'));
            // dump();
             // dump(array_reduce($items));
            // die();
            if ($this->request->isPost()) {
                $post = $this->request->only(['sgt_name', 'sgt_desc'], 'post');
                 $sp_names=explode(PHP_EOL, $_POST['sp_name']);
                /*验证接到的值有没有问题*/
                $rule = array(
                    "sgt_name" => 'require|max:20',
                    "sgt_desc" => 'max:255',
                );
                $msg = array(
                    "sgt_name.require" => '类型名称是必须要填写的噢!~',
                    "sgt_name.max" => '名称的最大长度不能超过20个字符噢!~',
                    "sgt_desc.max" => '描述最多只能填写255个字符噢!~',
                );
                $data = verify($post, $rule, $msg);
                if ($data['code'] === 1) {
                    $ret = $this->sgt->save($post, $where);
                    $length=count($sp_names); 
                    $this->sp->del($where);
                    for($i=1; $i<=$length; $i++){ 
                        $datas[$i]['sgt_id']=$id;
                        $datas[$i]['sp_name']=$sp_names[$i-1];
                    }
                $this->sp->saveAll($datas);
                    if (false !== $ret) {
                        $this->sellerManagerLog("修改后台商品类型,修改的id为:" . $id);
                        $this->success("修改成功!~", url("seller/Goods/goodsTypeList"));
                    } else {
                        $this->error("修改失败!~");
                    }
                } else {
                    $this->error($data['msg']);
                }
                exit();
            }
            return view(
                "goodsTypeEdit",
                [
                    "info" => $info,
                    "sp_name" => $sp_name,
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-14
     * 功能：商户后台商品类型删除
     */
    public function goodsTypeDel()
    {
        if ($this->request->isAjax()) {
            /*只接收id的值*/
            $info = $this->request->only(['id'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                /*where条件 强制转换整型*/
                $where = ['sgt_id' => intval($info['id'])];
                $count = $this->sgs->getCount($where);
                if ($count > 0) {
                    return json(format('', '-1', '删除失败~!该类别下面有规格属性噢~!'));
                }
                $sgt = $this->sgt->del($where);
                if (false === $sgt) {
                    return json(format('', '-1', '删除失败~!请稍候重试~!'));
                } else {
                    $this->sellerManagerLog("删除后台商品类型,删除的id为:" . $info['id']);
                    return json(format('', '1', '删除成功~!'));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-14
     * 功能：商户后台规格列表
     */
    public function goodsSpecificationList($id)
    {
        if (isset($id) && $id > 0) {
            $where = ["sgt_id" => intval($id), 'ss_id' => $this->sm_info['ss_id']];
            $sgt_name = $this->sgt->getOne($where, "sgt_name");
            $list = $this->sgs->getAll($where);
            foreach ($list['data'] as $sl_key => $sl_value) {
                /* 通过规格id查询出规格内容并赋值给sgs_value */
                $item_value = $this->sgi->getList(['sgs_id' => $sl_value['sgs_id']]);
                $sgi_item_val = '';
                foreach ($item_value as $sgs_key => $sgs_value) {
                    $sgi_item_val .= $sgs_value['sgi_item'] . '|';
                    $list['data'][$sl_key]['sgs_value'] = rtrim($sgi_item_val, '|');
                }
            }
            $this->sellerManagerLog("查看类型id为:" . $id . "的商品属性");
            return view(
                "goodsSpecificationList",
                [
                    "sgt_id" => $id,
                    "sgt_name" => $sgt_name,
                    "list" => $list['data'], /*查询结果*/
                    "page" => $list['page'], /*分页和html代码*/
                    "lastPage" => $list['lastPage'], /*总页数*/
                    "currentPage" => $list['currentPage'], /*当前页码*/
                    "total" => $list['total'], /*总条数*/
                    "listRows" => $list['listRows'], /*每页显示条数*/
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-14
     * 功能：商户后台规格添加
     */
    public function goodsSpecificationAdd($id)
    {
        if (isset($id) && $id > 0) {
            if ($this->request->isPost()) {
                $post = $this->request->only(["sgs_name", "sgs_type"], "post");
                $rule = array(
                    "sgs_name" => 'require|max:20',
                    "sgs_type" => 'require|in:0,1,2',
                );
                $msg = array(
                    "sgs_name.require" => '属性名称是必须要填写的噢!~',
                    "sgs_name.max" => '属性名称的最大长度不能超过20个字符噢!~',
                    "sgs_type.require" => '属性是否可选是必须要选择的噢!~',
                    "sgs_type.in" => '属性是否可选选择有误噢!~',
                );
                $data = verify($post, $rule, $msg);
                if ($data['code'] === 1) {

                    $post['sgt_id'] = intval($id);
                    $post['sgs_add_time'] = time();
                    $post['ss_id'] = $this->sm_info['ss_id'];
                    $sgs_id = $this->sgs->save($post);

                    if (isset($_REQUEST['sgs_value']) && $_REQUEST['sgs_value'] != '') {
                        $item = explode('|', $_REQUEST['sgs_value']);
                        foreach ($item as $item_k => $item_v) {
                            $item = [
                                'sgs_id' => $sgs_id,
                                'sgi_item' => $item_v,
                            ];
                            $item_id = $this->sgi->save($item);
                            $this->sellerManagerLog("添加商品规格内容,id为:" . $item_id);
                        }
                    }
                    if ($sgs_id > 0) {
                        $this->sellerManagerLog("添加商品规格,id为:" . $sgs_id);
                        $this->success("添加成功", url("seller/Goods/goodsSpecificationList", ["id" => $id]));
                    } else {
                        $this->error("添加失败!~");
                    }

                } else {
                    $this->error($data['msg']);
                }
            }
            return view(
                "goodsSpecificationAdd",
                [
                    "id" => $id,
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-14
     * 功能：商户后台规格修改
     */
    public function goodsSpecificationEdit($id)
    {
        if (isset($id) && $id > 0) {
            if ($this->request->isPost()) {
                $post = $this->request->only(["sgs_name", "sgs_type", "sgt_id"], "post");
                $rule = array(
                    "sgs_name" => 'require|max:20',
                    "sgs_type" => 'require|in:0,1,2',
                );
                $msg = array(
                    "sgs_name.require" => '属性名称是必须要填写的噢!~',
                    "sgs_name.max" => '属性名称的最大长度不能超过20个字符噢!~',
                    "sgs_type.require" => '属性是否可选是必须要选择的噢!~',
                    "sgs_type.in" => '属性是否可选选择有误噢!~',
                );
                $data = verify($post, $rule, $msg);
                if ($data['code'] === 1) {
                    $this->sgi->del(['sgs_id' => intval($id)]);
                    if (isset($_REQUEST['sgs_value']) && $_REQUEST['sgs_value'] != '') {
                        $item = explode('|', $_REQUEST['sgs_value']);
                        foreach ($item as $item_k => $item_v) {
                            $item = [
                                'sgs_id' => intval($id),
                                'sgi_item' => $item_v,
                            ];
                            $item_id = $this->sgi->save($item);
                            $this->sellerManagerLog("修改商品规格内容,id为:" . $item_id);
                        }
                    }
                    $sgt_id = $post['sgt_id'];
                    unset($post['sgt_id']);
                    $ret_id = $this->sgs->save($post, ["sgs_id" => intval($id)]);
                    if (false !== $ret_id) {
                        $this->sellerManagerLog("修改商品属性,id为:" . $id);
                        $this->success("修改成功", url("seller/Goods/goodsSpecificationList", ["id" => $sgt_id]));
                    } else {
                        $this->error("修改失败!~");
                    }

                } else {
                    $this->error($data['msg']);
                }
            }
            $info = $this->sgs->getRow(["sgs_id" => intval($id)]);
            /* 通过规格id查询出规格内容并赋值给sgs_value */
            $item_value = $this->sgi->getList(['sgs_id' => $info['sgs_id']]);
            $sgi_item_val = '';
            foreach ($item_value as $sgs_key => $sgs_value) {
                $sgi_item_val .= $sgs_value['sgi_item'] . '|';
                $info['sgs_value'] = rtrim($sgi_item_val, '|');
            }

            if (empty($info)) {
                $this->error("没有找到该数据!~");
            }
            return view(
                "goodsSpecificationEdit",
                [
                    "id" => $id,
                    "info" => $info,
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-14
     * 功能：商户后台规格删除
     */
    public function goodsSpecificationDel()
    {
        if ($this->request->isAjax()) {
            /*只接收id的值*/
            $info = $this->request->only(['id'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                /*where条件 强制转换整型*/
                $where = ['sgs_id' => intval($info['id'])];
                $count = $this->sgs->getCount($where);
                $sgs = $this->sgs->del($where);
                $this->sgi->del($where);
                if (false === $sgs) {
                    return json(format('', '-1', '删除失败~!请稍候重试~!'));
                } else {
                    $this->sellerManagerLog("删除商品属性,id为:" . $info['id']);
                    return json(format('', '1', '删除成功~!'));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-11-10
     * 功能：态获取商品规格选择框 根据不同的数据返回不同的选择框
     */
    public function ajaxSgsSelect()
    {
        if ($this->request->isAjax()) {
            if(!isset($_REQUEST['goods_id'])){
                $_POST['goods_id'] = 0;
            }
            /*只接收id的值*/
            $info = $this->request->only(['sgt_id','goods_id'], 'post');

            /*验证接到的值有没有问题*/
            $rule = array(
                "sgt_id" => 'require',
                "goods_id" => 'require',
            );
            $msg = array(
                "sgt_id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
                "goods_id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );

            $data = verify($info, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                /* 通过商品类型查询出商品规格 */
                $sgs_value = $this->sgs->getList(['sgt_id' => $info['sgt_id']]);

                /* 当修改商品的时候查询出这个商品所有的规格 */
                $items_id = $this->sgp->getList(['goods_id' => $info['goods_id']]);

                /* 判断这个商品是否有规格 */
                if(isset($items_id) && $items_id != '' && !empty($items_id)){
                    $str = '';
                    /* 循环把所有的规格都用下划线连接到一起 */
                    foreach ($items_id as $item_key => $item_value) {
                        $str .= $item_value['keys'].'_';
                    }  
                    /* 把用连接好的规格ID拆分成数组方便前台选中 */
                    $item_ids = explode('_',rtrim($str,'_'));
                }else{
                    $item_ids = '';
                }

                /* 查询出规格后循环查询出规格内容 */
                foreach ($sgs_value as $sgs_k => $sgs_val) {
                    $sgs_value[$sgs_k]['item'] = $this->sgi->getList(['sgs_id' => $sgs_val['sgs_id']]);
                }

                /* 往页面传值 */           
                return view(
                    "ajaxSgsSelect",
                    [
                        "sgs_value" => $sgs_value,
                        "item_ids" => $item_ids,
                        "goods_id" => $info['goods_id'],
                    ]
                );
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 获取 规格的 笛卡尔积
     * @param $goods_id 商品 id
     * @param $spec_arr 笛卡尔积
     * @return string 返回表格字符串
     */
    public function ajaxSgsInput($spec_arr,$goods_id)
    {        
        /* 把选中的规格ID和规格内容ID传过来并赋值给$spec_arr2 */
        $spec_arr2 = $spec_arr;
        /* 获取到选中规格的ID并且赋值给$clo_name */
        $clo_name = array_keys($spec_arr2);
        /* 通过笛卡尔积来匹配出所有的选中ID的组合 */
        $spec_arr2 = $this->combineDika($spec_arr2); //  获取 规格的 笛卡尔积

        /* 查询出规格 */
        $spec1 = $this->sgs->getList();
        $spec = [];
        /* 循环排序规格 */
        foreach ($spec1 as $spec_k => $spec_v) {
            $spec[$spec_v['sgs_id']] = $spec_v['sgs_name'];
        }
        /* 查询出所有规格内容 */
        $spec_item = $this->sgi->getList();
        /* 循环排序规格内容 */
        foreach ($spec_item as $si_k => $si_v) {
            $specItem[$si_v['sgi_id']] = $si_v;
        }
        /* 查询出要修改商品的规格 */
        $keySpecGoodsPrices = $this->sgp->getList(['goods_id' => $goods_id]);
        /* 循环排序选中的规格 */
        if($keySpecGoodsPrices != '' && !empty($keySpecGoodsPrices)){
            foreach ($keySpecGoodsPrices as $ksgp_k => $ksgp_v) {
                $keySpecGoodsPrice[$ksgp_v['keys']] = $ksgp_v;
            }
        }

        $str = "<table class='table table-bordered' id='spec_input_tab'>";
        $str .= "<tr>";
        /* 循环输出规格标题 */
        foreach ($clo_name as $k => $v) {
            $str .= " <td><b>{$spec[$v]}</b></td>";
        }

        $str .= "<td><b>价格</b></td>
               <td><b>库存</b></td>
             </tr>";

        /* 循环输出通过笛卡尔积来匹配出所有的选中ID的组合 */
        foreach ($spec_arr2 as $k => $v) {

            $item_key_name = array();
            /* 通过循环来查找出组合好的规格内容和组 */
            foreach ($v as $k2 => $v2) {
                $str .= "<td>{$specItem[$v2]['sgi_item']}</td>";
                $item_key_name[$v2] = $spec[$specItem[$v2]['sgs_id']] . ':' . $specItem[$v2]['sgi_item'];
            }
            /* 通过ID大小进行排序 */
            ksort($item_key_name);
            
            /* 把数组之间的key用下划线连接起来 */
            $item_key = implode('_', array_keys($item_key_name));
            /* 把所有组合好的规格和规格内容组合成字符串 */
            $item_name = implode(' ', $item_key_name);
            if (empty($keySpecGoodsPrice[$item_key]['price']) || $keySpecGoodsPrice[$item_key]['price'] == '' ) {
                $keySpecGoodsPrice[$item_key]['price'] = 0;
                $keySpecGoodsPrice[$item_key]['store_count'] = 0;
            }

            $str .= "<td><input name='item[$item_key][price]' value='{$keySpecGoodsPrice[$item_key]['price']}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")' /></td>";

            $str .= "<td><input name='item[$item_key][store_count]' value='{$keySpecGoodsPrice[$item_key]['store_count']}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")'/><input name='item[$item_key][key_name]' value='{$item_name}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")' type='hidden'/></td>";

            $str .= "</tr>";
        }
        $str .= "</table>";
        return $str;
    }

    /**
     * 作者：李鑫
     * 时间：2018-03-29
     * 功能：商户ajax获取商品分类
     */
    public function getGoodCategory()
    {
        if ($this->request->isAjax()) {
            /*直接收id*/
            $id = $this->request->only(["id","add"], "post");
            $rule = array(
                "id" => 'require',

            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($id, $rule, $msg);
            if ($data['code'] == 1) {
                $mgc_valuess = $this->ss->getRow(['ss_id' => $this->sm_info['ss_id']]);/*商家分类权限*/
                $mgc_valuese=$mgc_valuess['mgc_id'].','.$mgc_valuess['ss_mgc_ids'];
                if($id["add"] == 3){
                    $info = $this->mgc->getList(["mgc_parent_id" => $id['id'],'mgc_is_show' => '1'], "*", ['concat(mgc_parent_path,mgc_id)' => 'mgc_parent_path']);

                }else{
                    $info = $this->mgc->getList(["mgc_parent_id" => $id['id'],'mgc_is_show' => '1','mgc_id'=>array("in",$mgc_valuese)], "*", ['concat(mgc_parent_path,mgc_id)' => 'mgc_parent_path']);

                }
                if (isset($info) && !empty($info)) {
                    return json(format($info));
                } else {
                    return json(format('', '-1', "该类目下没有下级分类了!~"));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }

    /**
     * 作者：袁中旭
     * 时间：2017-11-18
     * 功能：AJAX或者搜索品牌
     */
    public function ajaxshanchu()
    {
        if ($this->request->isAjax()) {
            /*只接收id的值*/
            $info = $this->request->only(['sgtid'], 'post');
            // dump($info);die();
            /*验证接到的值有没有问题*/
            $rule = array(
                "sgtid" => 'require',
            );
            $msg = array(
                "sgtid.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );

            $data = verify($info, $rule, $msg);

            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                $ajax_mgb = $this->g->getList(['sgt_id' => $info['sgtid']]);
                // dump($ajax_mgb);die();
                if(!empty($ajax_mgb)){
                    return json(format('', '1', "这个系列下有商品，不可以删除!~"));
                }else{
                   return json(format('', '200', "删除成功!~"));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }

    

}
// function show_confirm(id){
//     var r=confirm("确定删除么!");
//         if (r==true){
//                     var sgtid  = id;

//             $.ajax({
//                     url:'{:url("seller/Goods/ajaxshanchu")}',
//                     type:"post",
//                     data:{sgtid:sgtid},
//                     dataType:"json",
//                     success:function (info) {

//                         if (info.code == 200) {
//                              del('sgt_{$info.sgt_id}',{$info.sgt_id},'{:url(\"seller/Goods/goodsTypeDel\")}','post','json',true);
//                         } else {
//                             swal({
//                                 title: "出错啦!",
//                                 text: info.msg,
//                             });
//                         }
//                     },
//                     error:function () {
//                         swal({
//                             title: "通讯出错!",
//                             text: "请联系开发人员或管理员!"
//                         });
//                     }
//                 })
//         }
//     }