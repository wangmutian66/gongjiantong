<?php
namespace app\api\controller;

use model\Region;
use model\Users;
use \think\Controller;

class Base extends Controller
{
    protected $request;/*接收方式*/
    protected $sign;
    protected $u_id;/*用户id*/
    protected $param;/*传递的参数*/
    protected $key;/*加密key*/
    protected $user = [];/*用户信息*/
    protected $smsOvertimeYime;/*短信超时时间*/
    public function __construct()
    {

        parent::__construct();
        $this->request = request();
        /*接受post传输的数据*/
        $post = $this->request->post();
        /*把需要校验的东西传给$sign*/
        (isset($post['param']) && !empty($post['param'])) ? $this->sign['u_id'] = rc4Decrypt($post['param']) : $post['param'] = "";
        (isset($post['sign']) && !empty($post['sign'])) ? $this->sign['sign'] = $post['sign'] : $this->sign['sign'] = '';
        (isset($post['access_token']) && !empty($post['access_token'])) ? $this->sign['u_uuid'] = rc4Decrypt($post['access_token']) : $this->sign['u_uuid'] = "";
        unset($post['sign']);
        /*剩余的参数给$patam*/
        if (isset($post['is_encrypt']) && $post['is_encrypt'] == 1) {
            if (isset($post['data']) && !empty($post['data'])) {
                $this->param = rc4Decrypt($post["data"]) ? rc4Decrypt($post["data"]) : exit(json_encode(format('', 203, "解密失败,请重试!~"), JSON_UNESCAPED_UNICODE));
            }
        } else {
            $this->param = $post;
        }
        /*经纬度*/
        // $this->param['lat_lgt']='126.854346,45.751438';
        if (isset($this->param['lat_lgt']) && $this->param['lat_lgt'] != '') {
            /*经纬度转换成地址*/
            $info = latLgtToLocation($this->param['lat_lgt']);

            /*如果成功查询地址对应数据库的地址id*/
            if ($info) {
                $r = new Region();
                /*查找省份*/
                $this->param['province_id'] = $r->getOne(["r_name" => $info['province']], "r_id");
                if ($this->param['province_id'] > 0) {
                    /*查找城市id*/
                    $this->param['city_id'] = $r->getOne(["r_name" => $info['city'], "r_parent_id" => $this->param['province_id']], "r_id");
                    if ($this->param['city_id'] > 0) {
                        /*查找区域id*/
                        $this->param['district_id'] = $r->getOne(["r_name" => $info['district'], "r_parent_id" => $this->param['city_id']], "r_id");
                    }
                } else {
                    /*没查找到省份信息*/
                    $this->param['province_id'] = 12;
                    $this->param['city_id'] = 167;
                    $this->param['district_id'] = 1145;
                }
            } else {
                /*失败默认哈尔滨道里区*/
                $this->param['province_id'] = 12;
                $this->param['city_id'] = 167;
                $this->param['district_id'] = 1145;
            }
            unset($this->param['lat_lgt']);
        }
        /*单点登录有u_id */
        if (isset($this->sign['u_id']) && !empty($this->sign['u_id'])) {
            $oUser = new Users();
            $this->user = $oUser->getRow(["u_id" => intval($this->sign['u_id'])]);
        } else if (isset($this->param['u_id']) && !empty($this->param['u_id'])) {
            /*需要登陆后操作的地方*/
            $oUser = new Users();
            $this->user = $oUser->getRow(["u_id" => intval($this->param['u_id'])]);
        }
        if (isset($this->param['device_id'])) {
            $oUser = new Users();
            $uidse = $oUser->getRow(["u_uuid" => $this->param['device_id'], "u_id" => $this->param['u_id']]);
            if (empty($uidse)) {
                exit(json_encode(format('', 204, "账户在其他设备登陆,请重新登录!~")));
            }
        }
        
        /*单点登录app后期写*/
        // if (isset($this->user['u_uuid']) && isset($this->sign['device_id']) && ($this->user['u_uuid'] != $this->sign['device_id'])) {
        //     exit(json_encode(format('', 204, $this->sign['device_id'])));
        //     // exit(json_encode(format('', 204, "账户在其他设备登陆,请重新登录!~")));
        //     // echo json_encode(format('', 204, "账户在其他设备登陆,请重新登录!~"));
        // }
        if (!isset($this->user["u_uuid"]) && empty($this->user["u_uuid"])) {
            $this->user["u_uuid"] = "";
        }

        $this->key = config('?plugin') ? config('plugin')['AppSignKey'] : exit(json_encode(format('', 202, "校验签名失败,缺少配置文件,请联系管理员!~"), JSON_UNESCAPED_UNICODE));
        $this->smsOvertimeYime = config('?plugin') ? config('plugin')['sms']['overtimeYime'] : exit(json_encode(format('', 202, "缺少配置文件,请联系管理员!~"), JSON_UNESCAPED_UNICODE));
        $this->checkSign($post['param'], $this->sign['sign'], $this->user["u_uuid"]);
        if (isset($this->user['u_id'])) {
            $this->u_id = $this->user['u_id'];
        }
    }
    /**
     * 校验签名
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-09
     */
    protected function checkSign($str, $sign, $uuid)
    {
        /*需要校验*/
        if ($this->is_check()) {
            /*校验签名*/
            if ($this->generateSign($str, $uuid) !== $sign) {
                echo json_encode(format('', 201, "校验签名失败!~"), JSON_UNESCAPED_UNICODE);
                exit();
            } else {
                return true;
            }
        }
    }
    /**
     * 生成签名
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-09
     */
    protected function generateSign($str, $uuid)
    {
        if (isset($str) && !empty($str) && isset($uuid)) {
            return md5($str . $uuid . $this->key);
        } else {
            return false;
        }

    }
    /*验证是否需要校验*/
    protected function is_check()
    {

        /*当前控制器*/
        $controller = $this->request->controller();
        /*当前方法*/
        $action = $this->request->action();
        /*判断不是某个控制器 和某个方法*/
        $condition_controller = [
            "Login",/*登陆注册*/
            "Users",/*用户中心*/
            "BiddingInformation",/*招投标,招聘,规范*/
            "Index",/*首页*/
            "Payment",/*支付*/
            "ChatAndNews",/*新闻和系统消息*/
            "Shop",/*店铺*/
            "Goods",/*商品*/
        ];
        /*必须要校验签名的方法*/
        $condition_action = [
            "login",/*登陆*/
            "thirdLogin",/*三方登陆*/
            "registered",/*注册*/
            "checkBingThirdInfo",/*第三方绑定*/
            "unbindThirdInfo",/*解绑三方*/
            "bingMobile",/*绑定手机*/
            "switchMobileBind",/*切换手机绑定*/
            "getSmsCode",/*发送短信验证码*/
            "forgetPasswd",/*忘记密码*/
            "changePasswd",/*修改密码*/
            "getRecruitmentList",/*招聘信息列表*/
            "getSearchKeyword",/*获取热搜词*/
            "getUserSkills",/*获取行业*/
            "getUserIndustry",/*获取技能*/
            "getRecruitmentInfo",/*查看简历信息*/
            "engineeringSpecificationsList",/*规范列表*/
            "biddingInformationList",/*招投标信息列表*/
            "callback",/*支付回调*/
            "adList",/*广告列表*/
            "shopList",/*店铺列表*/
            "shopGoodsList",/*店铺商品*/
            "getShopGoodsCategory",/*获取店铺分类*/
            "GoodsList",/*商品列表*/
            "getShopInfo",/*获取店铺信息*/
            "index",/*首页猜你喜欢*/
            "serchGoodsAndShop",/*首页搜索商品*/
            "getNextCategory",/*获取下级分类*/
            "goodsInfo",/*商品详情*/
            "goodsShowInfo",/*新商品详情*/
            "recommendGoodsList",/*推荐商品列表*/
            "shopAdList",/*店铺banner*/
            "getGoodsMoney",
            "broadcastList",/*推送消息*/
            "getArticles",
            "report",
            "goodComplain", /*店铺投诉*/
            "getMailCode", /*发送邮箱验证码*/
            "editAddressAction", /*添加和修改地址*/
            "userAddressList", /*收货地址列表*/
            "addOrder", /*添加订单*/
            "shopEntry",/*添加商家入驻*/
            "spec",/*规格*/
            "getEngineeringSpecificationsInfo",/*规格*/
            "biddingInformationInfo",
            "getUserBrowsingHistory",
            "orderinfo",/*订单详情*/
            "collectionAdd",/*用户添加收藏*/
            "collectionDel",/*用户删除收藏*/
            "returnOrder",/*退货*/
            "orderlist",/*订单列表*/
            "GoodsScreen",/*商品搜索*/
            "delAllAddress",/*删除全部地址*/
            "bindingemail",/*绑定邮箱*/
            "snOderinfo",/*通过订单查看订单详情*/
            "shopGoodsimg",/*商家首页图片显示*/
            "collectionList",/*获取收藏列表*/
            "delUserBrowsingHistory",/*删除用户历史浏览*/
            "goodscollectionDel",/*用户删除商品收藏*/
            "cancelOrder",/*取消订单*/
            "okOrder",/*確認訂單*/
            "invoiceShow",/*专业发票显示*/
            "addInvoice",/*添加发票*/
            "orderGoodsPay",/*订单支付*/
            "bindingMailbox",/*邮箱绑定*/
            "uidMailCode",/*通过uid发送邮件*/
            "GoodsLists",/*商品推荐列表*/
            "edition",/*版本号*/
            "jineng",/*技能*/
            "registerednew",/*关联新账号*/
            "registeredold",/*关联老账号*/
            "getUserInfo",
            "wuliu",
            "indexad",/*首页广告一 首页广告二*/
            "GoodsToTransfer",/*货到转账*/
            "ordersGoodsPay",/*多条订单一起删除*/
            "defrayok",/*支付成功*/
            "adGongGao",/*公告列表*/
            "adGongGaoinfo",/*公告详情*/
            "severorderlist",/*客服订单列表*/
            "getIMList",
            "checkOrderTime",/*检查订单的时间（服务器使用）*/
            "shoporderlist",/*商家客服订单*/
            "xieyi",/*协议*/
            "othercollectionDel",
            "feedbacks",/*反馈*/
            "addUserIM",/*环信添加id*/
            
            "dongtaiMessageList",/*行业动态2.1*/
            "gonggaoMessageList",/*平台公告列表2.1*/
            "newsMessageList",/*平台新闻列表2.1*/
            "helpMessageList",/*帮助中心列表2.1*/
            "messageshow",/*行业动态详情2.1*/
            "rturnrecord",/*退货记录2.1*/
            "rturnrecordshow",/*退货记录详情2.1*/
            "rturnover",/*确认退货*/
            "returnorderlist",/*退款订单列表*/
            "isconnected",/*其他设备登录*/
            "rturnorderlist",/*退货订单2.1*/
            /*goods下*/
            "orderevaluation",/*订单评论2.2*/
            "orderevaluationshow",/*订单评论详情2.2*/
            "orderevaluationdel",/*删除评论2.2*/
            "orderevaluationzhui",/*订单追评2.2*/
            "shoppingcartadd",/*添加购物车2.2*/
            "shoppingcartshow",/*购物车列表2.2*/
            "shoppingcartdel",/*购物车删除2.2*/
            "shoppingcartnumedit",/*购物车数量加减2.2*/
            "orderdel",/*订单删除2.2*/
            "rturnovers",/*退换货评论2.2*/
            "goodscomments",/*商品评论2.2*/
            "evaluationcenter",/*评价中心2.2*/

        ];
        /*不需要校验的方法 true需要校验   false 不需要校验*/
        if (in_array(strtolower($controller), processData($condition_controller)) && in_array(strtolower($action), processData($condition_action))) {
            return false;
        } else {
            return true;
        }
    }
}
