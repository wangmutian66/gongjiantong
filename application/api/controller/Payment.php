<?php
namespace app\api\controller;

use model\PayLog as pl;
use model\TenderSpecificationOrder as tso;
use model\UserBrowsingHistory as ubh;

/**
 * 首页及常用接口
 */
class Payment extends Base
{
    protected $tso;
    protected $pl;
    protected $ubh;
    public function __construct()
    {
        parent::__construct();
        $this->tso = new tso(); /*招标和规范订单表*/
        $this->pl = new pl(); /*支付日志*/
        $this->ubh = new ubh(); /*历史浏览记录表*/
    }
    /**
     * 支付回掉地址
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-11-02
     * @param  [string]            $type [支付类型微信:wechatPay,支付宝:alipay]
     * @param  [string]            $order_type [订单类型:0:招投标,1:规范,2:正常商品订单]
     */
    public function callback($type, $order_type)
    {

        file_put_contents("./public/callback1.txt",$type."----".date("Y-m-d")."\n\r------------------",FILE_APPEND);
        die('1');
//        if ($type == "wechatPay") {
//    		Vendor('wechat.WechatAppPay');
//            $wechatPay = new \WechatAppPay(config("plugin")['wechatPay']['pay']);
//	        $xml = file_get_contents("php://input");
//	        $data = $wechatPay->xml_to_data($xml);
//            file_put_contents("./public/callback2.txt",json_encode($data)."----".date("Y-m-d")."\n\r------------------",FILE_APPEND);
//	        if ($data["return_code"] == "SUCCESS" && $data["result_code"] == "SUCCESS") {
//	        	$order_sn = substr($data['out_trade_no'],0,16);
//	        	/*查询支付信息*/
//	        	$pay_info = $this->pl->getRow(["order_type" => $order_type, "order_sn" => $order_sn]);
//	        	if (isset($pay_info) && $pay_info['is_paid'] == '0') {
//	        		if ($order_type == '0') {
//	        			# code...
//	        		} else if ($order_type == '1') {
//	        			$this->tso->save(['tso_pay_time' => time(), "tso_pay_status" => '1'],["tso_sn" => $order_sn, "tso_type" => $order_type, "tender_spec_id" => $pay_info["pay_goods_id"]]);
//	        			$tso_info = $this->tso->getRow(["tso_sn" => $order_sn, "tso_type" => $order_type, "tender_spec_id" => $pay_info["pay_goods_id"]]);
//	        			$count = $this->ubh->getCount(["u_id" => $tso_info['u_id'], "ubh_type" => "3", "ubh_browsing_id" => $tso_info['tender_spec_id']]);
//	        			if ($count < 1) {
//		        			$save = [
//		        				"u_id" => $tso_info['u_id'],
//		        				"ubh_type" => "3",
//		        				"ubh_browsing_id" => $tso_info['tender_spec_id'],
//		        				"ubh_time" => time(),
//		        			];
//		        			$this->ubh->save($save);
//	        			} else {
//	        				$where = [
//		        				"u_id" => $tso_info['u_id'],
//		        				"ubh_type" => "3",
//		        				"ubh_browsing_id" => $tso_info['tender_spec_id'],
//		        			];
//
//		        			$save = ["ubh_time" => time()];
//		        			$this->ubh->save($save,$where);
//	        			}
//	        		} else if ($order_type == '2') {
//	        			# code...
//	        		} else {
//	        			return false;
//	        		}
//	        		$info = $this->pl->save(["is_paid" => '1'],["pl_id" => $pay_info["pl_id"]]);
//	        		if (false === $info) {
//	        			logs("微信回掉失败,pay_log的id:".$pay_info["pl_id"]);
//	        		}
//	        	} else {
//	        		return false;
//	        	}
//	        } else {
//	        	return false;
//	        }
//        }
//	    else if ($type == "alipay") {
//
//	        $data = $_POST;
//            file_put_contents("./public/callback3.txt",json_encode($data)."----".date("Y-m-d")."\n\r------------------",FILE_APPEND);
//
//		    if (isset($data["trade_status"]) && ($data["trade_status"] == "TRADE_SUCCESS")) {
//
//	        	$order_sn = substr($data['out_trade_no'],0,16);
//
//	        	/*查询支付信息*/
//	        	$pay_info = $this->pl->getRow(["order_type" => $order_type, "order_sn" => $order_sn]);
//	        	if (isset($pay_info) && $pay_info['is_paid'] == '0') {
//	        		if ($order_type == '0') {
//	        			# code...
//	        		} else if ($order_type == '1') {
//	        			$this->tso->save(['tso_pay_time' => time(), "tso_pay_status" => '1'],["tso_sn" => $order_sn, "tso_type" => $order_type, "tender_spec_id" => $pay_info["pay_goods_id"]]);
//	        			$tso_info = $this->tso->getRow(["tso_sn" => $order_sn, "tso_type" => $order_type, "tender_spec_id" => $pay_info["pay_goods_id"]]);
//	        			$count = $this->ubh->getCount(["u_id" => $tso_info['u_id'], "ubh_type" => "3", "ubh_browsing_id" => $tso_info['tender_spec_id']]);
//	        			if ($count < 0) {
//		        			$save = [
//		        				"u_id" => $tso_info['u_id'],
//		        				"ubh_type" => "3",
//		        				"ubh_browsing_id" => $tso_info['tender_spec_id'],
//		        				"ubh_time" => time(),
//		        			];
//		        			$this->ubh->save($save);
//	        			} else {
//	        				$where = [
//		        				"u_id" => $tso_info['u_id'],
//		        				"ubh_type" => "3",
//		        				"ubh_browsing_id" => $tso_info['tender_spec_id'],
//		        			];
//
//		        			$save = ["ubh_time" => time()];
//		        			$this->ubh->save($save,$where);
//	        			}
//	        		} else if ($order_type == '2') {
//	        			# code...
//	        		} else {
//	        			return false;
//	        		}
//	        		$info = $this->pl->save(["is_paid" => '1'],["pl_id" => $pay_info["pl_id"]]);
//	        		if (false === $info) {
//	        			logs("支付宝回掉失败,pay_log的id:".$pay_info["pl_id"]);
//	        		}
//	        	} else {
//	        		return false;
//	        	}
//	        } else {
//	        	return false;
//	        }
//	    } else {
//	    	return false;
//	    }
	}
}
