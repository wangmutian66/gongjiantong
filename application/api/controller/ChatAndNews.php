<?php
namespace app\api\controller;
use model\InstantMessaging as im;
use model\UserSendMessage as usm;
use model\SellerShop as ss;
use model\UmengMessage as um;
use model\Users as u;
use model\Artical as a;
/**
 * 即时通信
 * 系统消息
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-10-30
 */
class ChatAndNews extends Base
{
	protected $im;
    protected $usm;
    protected $ss;
    protected $um;
    protected $u;
    protected $a;
    public function __construct()
    {
    	parent::__construct();
    	$this->im = new im();
        $this->usm = new usm();
        $this->ss = new ss();
        $this->um = new um();
        $this->u = new u();
        $this->a = new a();
    }
    /**
     * 添加即时通讯列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-30
     */
    public function addIMList()
    {
    	$add['u_id'] = $this->u_id;
    	$add['u_name'] = $this->user['u_name'];
    	$add['ss_id'] = $this->param['ss_id'];
    	$add['ss_name'] = $this->param['ss_name'];
    	$add['u_im_id'] = $this->param['u_im_id'];
    	$add['sm_im_id'] = $this->param['sm_im_id'];
		$rule = [
		    "u_id" => 'require|number',
		    "u_name" => 'require',
		    "ss_id" => 'require|number',
		    "ss_name" => 'require',
		    "u_im_id" => 'require|alphaNum',
		    "sm_im_id" => 'require|alphaNum',
		];
		$msg = [
		    "u_id.require" => '缺少用户信息!~',
		    "u_id.number" => '用户信息格式有误!~',
		    "u_name.require" => '用户名必须填写!~',
		    "ss_id.require" => '缺少商户信息!~',
		    "ss_id.number" => '商户信息格式有误!~',
		    "ss_name.require" => '商户名称必须填写!~',
		    "u_im_id.require" => '缺少用户即时通讯信息!~',
		    "u_im_id.alphaNum" => '用户即时通讯信息格式不正确!~',
		    "sm_im_id.require" => '缺少商户即时通讯信息!~',
		    "sm_im_id.alphaNum" => '商户即时通讯信息格式不正确!~',
		];
		$data = verify($add, $rule, $msg);
		if ($data['code'] === 1) {
    		$add['chat_time'] = time();
    		$count = $this->im->getCount(['u_id' => $this->u_id, "ss_id" => $add['ss_id']]);
            $add['ss_logo_img'] = $this->ss->getOne(['ss_id' => $this->param['ss_id']],'ss_logo_img');
    		if ($count > 0) {
    			$ret = $this->im->save($add,['u_id' => $this->u_id, "ss_id" => $this->param['ss_id']]);
    			if ($ret !== false) {
    				return json(format());
    			} else {
    				return json(format("",247,"添加列表失败!~"));
    			}
    		} else {
    			$id = $this->im->save($add);
    			if ($id > 0) {
    				return json(format());
    			} else {
    				return json(format("",247,"添加列表失败!~"));
    			}
    		}
		} else {
            return json(format('', 223, $data['msg']));
		}
    }
    /**
     * 特讯新闻列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-11-03
     * @return [type]            [description]
     */
    public function systemMessageList()
    {
        $where['usm_type'] = '1';
        $pageParam['query']['usm_type'] = '1';
        $where['usm_send_object'] = '1';
        $pageParam['query']['usm_send_object'] = '1';

        $join = [
            ['gjt_artical a', "a.a_id = usm.a_id"],
        ];
        $field = "usm.usm_add_time,a.a_title,a.a_content,a.a_thumb_path,a.a_description";
        $list = $this->usm->joinGetAll($join,  "usm", $where, $pageParam, [], 0, $field);
        foreach ($list['data'] as $key => $value) {
            $list['data'][$key]['a_description']= preg_replace('/(<img.+?src=")(.*?)/', '$1' . HTTP_HOST . '$2', $value['a_description']);
            $list['data'][$key]['a_thumb_path']= IMG_URL . $value['a_thumb_path'];
        }
        return json(format($list["data"]));
    }
    /**
     * 获取即时通讯聊天列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-11-15
     * @return [type]            [description]
     */
    public function getIMList()
    {   

        if (empty($this->u_id)) {
            return json(format('',223,"传输数据出错!~"));
        }
        $where['u_id'] = $this->u_id;
        $list = $this->im->getList($where);
        foreach ($list as $key => $value) {
            $list[$key]['ss_logo_img']= IMG_URL . $value['ss_logo_img'];
        }
        return json(format($list));
    }
    /**
     * [delIMInfo description]
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-11-15
     * @return [type]            [description]
     */
    public function delIMInfo()
    {
        if (empty($this->param['im_id'])) {
            return json(format('',223,"传输数据出错!~"));
        }
        $where['im_id'] = $this->param['im_id'];
        $ret = $this->im->del($where);
        if (false !== $ret) {
            return json(format());
        } else {
            return json(format('',223,"删除失败!~"));
        }
    }
    /**
     * 推送消息列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-11-28
     */
    public function broadcastList()
    {
        if (isset($this->param['um_sent_equipment']) && $this->param['um_sent_equipment'] == '') {
            return json(format('',223,"传输数据出错!~"));
        }
        $where['um_sent_equipment'] = $this->param['um_sent_equipment'];
        $list = $this->um->getList($where);
        return json(format($list));
    }
    /**
     * 行业动态列表
     * @author 李鑫
     * @date   2018-06-26
     * @return [type]            [description]
     */
    public function dongtaiMessageList()
    {
        $pageParam['page'] = $this->param['page'];
       $aList = $this->a->getAll(["ac_id"=>13,"a_is_open"=>1], $pageParam, [], 10,"a_id,a_title,a_add_time,a_thumb_path");
        foreach ($aList['data'] as $k=>$row){
            $aList['data'][$k]["a_add_time"] = date("Y-m-d",$row["a_add_time"]);
            $aList['data'][$k]["a_thumb_path"] = IMG_URL.$row['a_thumb_path'];
        }
        return json(format($aList));
    }
    /**
     * 平台公告列表
     * @author 李鑫
     * @date   2018-06-26
     * @return [type]            [description]
     */
    public function gonggaoMessageList()
    {
        $pageParam['page'] = $this->param['page'];
       $aList = $this->a->getAll(["ac_id"=>11,"a_is_open"=>1], $pageParam, [], 10,"a_id,a_title,a_add_time,a_thumb_path");
        foreach ($aList['data'] as $k=>$row){
            $aList['data'][$k]["a_add_time"] = date("Y-m-d",$row["a_add_time"]);
            $aList['data'][$k]["a_thumb_path"] = IMG_URL.$row['a_thumb_path'];
        }
        return json(format($aList));
    }
    /**
     * 平台新闻列表
     * @author 李鑫
     * @date   2018-06-26
     * @return [type]            [description]
     */
    public function newsMessageList()
    {
        $pageParam['page'] = $this->param['page'];
       $aList = $this->a->getAll(["ac_id"=>12,"a_is_open"=>1], $pageParam, [], 10,"a_id,a_title,a_add_time,a_thumb_path,a_description");
        foreach ($aList['data'] as $k=>$row){
            $aList['data'][$k]["a_add_time"] = date("Y-m-d",$row["a_add_time"]);
            $aList['data'][$k]["a_thumb_path"] = IMG_URL.$row['a_thumb_path'];
        }
        return json(format($aList));
    }
    /**
     * 帮助中心列表
     * @author 李鑫
     * @date   2018-06-26
     * @return [type]            [description]
     */
    public function helpMessageList()
    {
        $pageParam['page'] = $this->param['page'];
       $aList = $this->a->getAll(["ac_id"=>14,"a_is_open"=>1], $pageParam, [], 10,"a_id,a_title,a_add_time,a_content");
        foreach ($aList['data'] as $k=>$row){
            $aList['data'][$k]["a_add_time"] = date("Y-m-d",$row["a_add_time"]);
            $aList['data'][$k]['a_content'] = preg_replace('/(<img.+?src=")(.*?)/', '$1' . HTTP_HOST . '$2', $aList['data'][$k]['a_content']);
        }
        // dump($aList);die();
        return json(format($aList));
    }
    /**
     * 查看新闻详情
     * @author 李鑫
     * @date   2018-06-26
     * @return [type]            [description]
     */
    public function messageshow()
    {

        $aList = $this->a->getRow(["a_id"=>$this->param['a_id']]);
        $aList["a_add_time"] = date("Y-m-d",$aList["a_add_time"]);
        $aList["a_thumb_path"] = IMG_URL.$aList['a_thumb_path'];
        return json(format($aList));
    }

    /**
     * [delIMInfo description]
     * @author 李鑫
     * @date   2018-07-12
     * @return [type]            [description]
     */
    public function isconnected()
    {
        $oUser = new Users();
        $uidse = $oUser->getRow(["u_uuid" => $this->param['device_id'], "u_id" => $this->param['u_id']]);
        if (empty($uidse)) {
            exit(json_encode(format('', 204, "账户在其他设备登陆,请重新登录!~")));
        }
    }
}