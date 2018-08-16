<?php
namespace app\seller\controller;

/**
 * 作者：袁中旭
 * 时间：2017-09-12
 * 主要功能：商户后台商品管理
 */

use \model\Plugin as log;
use \model\Pluginconfig as logc;
use model\Region as r;
use think\Config;

class Plugin extends Base
{
    protected $log;
    protected $logc;
    protected $r;
    public function __construct()
    {
        parent::__construct();
        $this->log = new log();
        $this->r = new r();
        $this->logc = new logc();

    }
    /**
     * 作者：李鑫
     * 时间：2018-04-18
     * 功能：物流列表
     */
    public function logList()
    {
        $where = ["ss_id" => $this->sm_info['ss_id']];
        $log_list = $this->log->getList($where);
        return view(
            "logList",
            [
               "list" => $log_list, /*查询结果*/
            ]
        );
    }
    /**
     * 作者：李鑫
     * 时间：2018-04-18
     * 功能：商户后台物流添加
     */
    public function logAdd()
    {
        $logistics = Config::get("logistics");
        if ($this->request->isPost()) {
            ini_set("post_max_size", "100");
            ini_set("upload_max_filesize", "100");

            /* 只获取post以下参数 */
            $post_g_info = $this->request->only(['log_name', 'log_describe','log_img'], "post");
            $pathfile1 = array();
            $pathfilenum= array();
            if(isset($_POST["file1"]) && isset($_POST["file1num"])){
                $pathfile1 = $_POST["file1"];
                $pathfilenum= $_POST["file1num"];
            }

            foreach($pathfile1 as $k=>$value){
                $pathfile1[$pathfilenum[$k]] = $value;
            }
            $path = "Wuliu/" . date("y_m_d", time());
            $goods_img = uploadImage($path, 'log_img');
            $post_g_info['log_img'] = $goods_img['pic_cover'];

            /*验证接到的值有没有问题*/
            $rule = array(
                "log_name" => 'require',
                "log_describe" => 'require',
                //"log_img" => 'require',
            );
            $msg = array(
                "log_name.require" => '物流名称不能为空!~',
                "log_describe.require" => '物流描述不能为空!~',
                //"log_img.require" => '物流图标不能为空!~',
            );
            $data = verify($post_g_info, $rule, $msg);
            if ($data['code'] === 1) {
                /* 循环添加属性 */
                if (isset($_REQUEST['log_name'])) {
                        $sgp_value = [
                            'log_code' => $post_g_info['log_name'] ,
                            'log_name' => $logistics[$post_g_info['log_name']],
                            'log_describe' => $post_g_info['log_describe'],
                            'log_img' => $post_g_info['log_img'],
                            'ss_id' => $this->sm_info['ss_id'],
                        ];
                        if ( $this->log->save($sgp_value)) {
                            $this->success("添加成功", url("seller/Plugin/logList"));
                        } else {
                            $this->success("添加失败");
                        }
                }
                
            } else {
                $this->error($data['msg']);
            }
        }

        return view(
            "logAdd",
            [
                "logistics" => $logistics
            ]
        );
    }
    

    /**
     * 作者：李鑫
     * 时间：2018-04-18
     * 功能：商户后台物流修改
     */
    public function logEdit($id)
    {
        $logistics = Config::get("logistics");
        if (isset($id) && $id > 0) {
            
            $where = ["log_id" => intval($id)];
            $log_value = $this->log->getRow($where);
            if ($this->request->isPost()) {
           
                ini_set("post_max_size", "100");
                ini_set("upload_max_filesize", "100");

                /* 只获取post以下参数 */
                $post_g_info = $this->request->only(['log_name', 'log_describe'], "post");
             
                $rule = array(
                    "log_name" => 'require',
                    "log_describe" => 'require',
                );
                $msg = array(
                    "log_name.require" => '物流名称不能为空!~',
                    "log_describe.require" => '物流描述不能为空!~',
                );
                $data = verify($post_g_info, $rule, $msg);
                if ($data['code'] === 1) {

                    /* 商品主图 */
                    $path = "Wuliu/" . date("y_m_d", time());
                    $goods_img = uploadImage($path, 'log_img');
                    if ($goods_img['code'] != 201) {
                        $post_g_info['log_img'] = $goods_img['pic_cover'];
                    }
                    if (isset($_REQUEST['log_name'])) {
                            $sgp_value = [
                                'log_code' => $post_g_info['log_name'] ,
                                'log_name' => $logistics[$post_g_info['log_name']],
                                'log_describe' => $post_g_info['log_describe'],
                            ];


                            $where['log_id'] = intval($id);
                            if ($this->log->save($sgp_value,$where)) {
                                $this->success("修改成功", url("seller/Plugin/logList"));
                            } else {
                                $this->success("当前操作未做任何修改");
                            }
                    }
                } else {
                    $this->error($data['msg']);
                }
            }
            
            return view(
                "logEdit",
                [
                    "log_value" => $log_value,
                    "logistics" => $logistics
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }
    
    
    /**
     * 作者：李鑫
     * 时间：2018-04-18
     * 功能：商户后台物流删除
     */
    public function logDel()
    {
        /*判断是不死ajax请求*/
        if ($this->request->isAjax()) {
            /*只接收id的值*/
            $info = $this->request->only(['id'], 'post');
            // dump($_POST);die();
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
                $where['log_id'] = intval($info['id']);

                $ret = $this->log->del($where);
            
                if (false === $ret) {
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
     * 作者：李鑫
     * 时间：2018-04-18
     * 功能：商户后台物流删除
     */
    public function logDels()
    {
        /*判断是不死ajax请求*/
        if ($this->request->isAjax()) {
            /*只接收id的值*/
            $info = $this->request->only(['id'], 'post');
            // dump($_POST);die();
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
                $where['logc_id'] = intval($info['id']);

                $ret = $this->logc->del($where);
            
                if (false === $ret) {
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
     * 时间：2017-10-19
     * 功能：上传商品轮播
     */
   
    public function goodsImgupdate()
    {
        $path = "Wuliu/" . date("y_m_d", time());
        $goods_img = uploadImage($path);
        return json_encode($goods_img);
    }

     /**
     * 作者：李鑫
     * 时间：2018-04-18
     * 功能：商户后台物流配置
     */
    public function logconfigList($id)
    {
        if (isset($id) && $id > 0) {
            
            $where = ["log_id" => intval($id)];
            $log_value = $this->logc->getList($where);

            foreach ($log_value as $k=>$val){
                $logc_area = $this->r->getOnes(["r_id"=>["in",$val["logc_area"]]],"r_name");
                $log_value[$k]["logc_area"] = implode(",",$logc_area);
            }

            return view(
                "logconfigList",
                [
                    "log_id" => $id,
                    "log_value" => $log_value,
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }

    /**
     * 作者：李鑫
     * 时间：2018-04-18
     * 功能：商户后台物流配置添加
     */
    public function logconfigAdd($log_id)
    {   
       if (isset($log_id) && $log_id > 0) {
          
            if ($this->request->isPost()) {
             
                /* 只获取post以下参数 */
                $post_g_info = $this->request->only(['logc_name', 'logc_heavy','logc_cmoney','logc_area','log_id','logc_contheavys','logc_contcmoneys','logc_national'], "post");
                $post_g_info["logc_national"] = isset($post_g_info["logc_national"])?1:0;

                if(isset($post_g_info['logc_area'])){
                    $post_g_info['logc_area'] = implode(",",$post_g_info['logc_area']);
                }else{
                    $post_g_info['logc_area'] = "";
                }

                $rule = array(
                    "logc_name" => 'require',
                    "logc_heavy" => 'require',
                    "logc_cmoney" => 'require',
//                    "logc_area" => 'require',
                    "logc_contheavys" => 'require',
                    "logc_contcmoneys" => 'require',
                );
                $msg = array(
                    "logc_name.require" => '配送区域名称不能为空!~',
                    "logc_heavy.require" => '首重不能为空!~',
                    "logc_cmoney.require" => '首重价格不能为空!~',
//                    "logc_area.require" => '配送区域不能为空!~',
                    "logc_contheavys.require" => '续重不能为空!~',
                    "logc_contcmoneys.require" => '续重价格不能为空!~',
                );
                $data = verify($post_g_info, $rule, $msg);
                if ($data['code'] === 1) {

                    if (isset($_REQUEST['logc_name'])) {

                        $issave = $this->logc->save($post_g_info);
                        if ($issave) {
                            $this->success("添加成功", url("seller/Plugin/logconfigList",array('id'=>$post_g_info['log_id'])));
                        } else {
                            $this->success("添加失败");
                        }
                    }
                } else {
                    $this->error($data['msg']);
                }
            }
            $havenational = $this->logc->getRow(['log_id'=>$log_id,'logc_national'=>1]);

            return view(
                "logconfigAdd",
                [
                    "log_id" => $log_id,
                    "havenational" => empty($havenational)?0:1,
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }
    
    /**
     * 作者：李鑫
     * 时间：2018-04-18
     * 功能：商户后台物流配置修改
     */
    public function logconfigEdit($id)
    {

        if (isset($id) && $id > 0) {

            $where = ["logc_id" => intval($id)];
            $logc_value = $this->logc->getRow($where);
            $log_id = $this->logc->getOne(['logc_id'=>$id],"log_id");
            $havenational = $this->logc->getRow(['logc_id'=>["neq",$id],'log_id'=>$log_id,'logc_national'=>1]);


            $s_guarantee="";
            if (isset($logc_value["logc_area"])) {
                $s_guarantee = $this->r->getList(["r_id"=>["in",$logc_value["logc_area"]]],"r_id,r_name");

            }
            if ($this->request->isPost()) {

                /* 只获取post以下参数 */
                $post_g_info = $this->request->only(['logc_name', 'logc_heavy','logc_cmoney','logc_area','logc_contheavys','logc_contcmoneys','logc_national'], "post");
                $post_g_info["logc_national"] = isset($post_g_info["logc_national"])?1:0;
                if(isset($post_g_info['logc_area'])){
                    $post_g_info['logc_area'] = implode(",",$post_g_info['logc_area']);
                }else{
                    $post_g_info['logc_area'] = "";
                }

                if(!empty($havenational) && $post_g_info['logc_national'] == 1){
                    $this->error("当前物流公司不可在设置“全国”！~");
                    exit();
                }

                $rule = array(
                    "logc_name" => 'require',
                    "logc_heavy" => 'require',
                    "logc_cmoney" => 'require',
                    "logc_area" => 'require',
                    "logc_contheavys" => 'require',
                    "logc_contcmoneys" => 'require',
                );
                $msg = array(
                    "logc_name.require" => '配送区域名称不能为空!~',
                    "logc_heavy.require" => '首重不能为空!~',
                    "logc_cmoney.require" => '首重价格不能为空!~',
                    "logc_area.require" => '配送区域不能为空!~',
                    "logc_contheavys.require" => '续重不能为空!~',
                    "logc_contcmoneys.require" => '续重价格不能为空!~',
                );
                $data = verify($post_g_info, $rule, $msg);
                if ($data['code'] === 1) {

                    if (isset($_REQUEST['logc_name'])) {
                            $sgp_value = [
                                'logc_id' => $id,
                                'logc_name' => $post_g_info['logc_name'],
                                'logc_heavy' => $post_g_info['logc_heavy'],
                                'logc_cmoney' => $post_g_info['logc_cmoney'],
                                'logc_area' => $post_g_info['logc_area'],
                                'logc_contheavys' => $post_g_info['logc_contheavys'],
                                'logc_contcmoneys' => $post_g_info['logc_contcmoneys'],
                            ];

                            $where['logc_id'] = $logc_value['logc_id'];
                            $issave = $this->logc->save($sgp_value,$where);
//                            $log_id = $this->logc->getOne($where,"log_id");

                            if ($issave) {
                                $this->success("修改成功",url("seller/Plugin/logconfigList",array('id'=>$log_id)));
                            } else {
                                $this->success("修改失败");
                            }
                    }
                } else {
                    $this->error($data['msg']);
                }
            }



            return view(
                "logconfigEdit",
                [
                    "logc_value" => $logc_value,
                    "s_guarantee" => $s_guarantee,
                    "havenational" =>$havenational
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
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
                $ajax_mgb = $this->r->getList(['r_name' => ["like", "%" . $info['brand_search'] . "%"]]);
                $result=array();
                foreach ($ajax_mgb as $key => $value) {
                    $has = false;
                    foreach($result as $val){
                        if($val['r_name']==$value['r_name']){
                            $has = true;
                            break;
                        }
                    }
                    if(!$has){$result[]=$value;}
                }
                // dump($result);
                if(empty($result)){
                    return json(format('', '-1', "没有这个地址噢!~"));
                }else{
                   return json(format($result));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
}
