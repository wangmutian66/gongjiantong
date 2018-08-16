<?php
namespace app\manage\controller;

use \model\Artical;
use \model\Goods;
use \model\Order;
use model\SellerShop;
use model\UserBrowsingHistory;
use \model\Users;

/**
 * 平台首页
 * 包含数据分析
 * 公共赋值等
 */
class Index extends Base
{
    /**
     * 首页框架
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-26
     */
    public function index()
    {
        return view("index");
    }
    /**
     * 首页欢迎页面
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-26
     */
    public function welcome()
    {
        /*获取待处理订单*/
        $order = new Order();
        /*获取新增订单*/
        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $endToday = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
//        $add_order = $order->getCount('o_pay_time >= ' . $beginToday . ' and o_pay_time < ' . $endToday);
        /*待审评论*/
        $pending_review = $order->getCount('o_status = 3');
        /*获取商品总数*/
        $goods = new Goods();
        $goods_count = $goods->getCount(["g_goods_verify"=>1]);
        /*已入驻商铺总数*/
        $sellerShop = new SellerShop();
        $sellerShop_count = $sellerShop->getCount(["ss_approval_status"=>1]);

        /*获取会员总数*/
        $users = new Users();
        $users_count = $users->getCount();
        $add_users = $users->getCount('u_registered_time >= ' . $beginToday . ' and u_registered_time < ' . $endToday);

        $ubh = new UserBrowsingHistory();
        $last_login_users = $ubh->getCount("FROM_UNIXTIME(ubh_time,'%Y%m%d') = concat_ws('',year(now()),LPAD(month(now()),2,0),LPAD(day(now()),2,0))");
        //FROM_UNIXTIME(o_add_time,'%Y%m%d') = concat_ws('',year(now()),LPAD(month(now()),2,0),LPAD(day(now()),2,0))
        $add_order = $order->getCount("FROM_UNIXTIME(o_add_time,'%Y%m%d') = concat_ws('',year(now()),LPAD(month(now()),2,0),LPAD(day(now()),2,0))");

        /*数据库版本*/
        $conn = mysqli_connect(config('database')['hostname'], config('database')['username'], config('database')['password']);
        /*从服务器中获取GD库的信息*/
        if (function_exists("gd_info")) {
            $gd = gd_info();
            $gdinfo = $gd['GD Version'];
        } else {
            $gdinfo = "未知";
        }
        /*从PHP配置文件中获得最大上传限制*/
        $max_upload = ini_get("file_uploads") ? ini_get("upload_max_filesize") : "Disabled";
        /*从PHP配置文件中获得脚本的最大执行时间*/
        $max_ex_time = ini_get("max_execution_time") . "秒";
        date_default_timezone_set("Etc/GMT-8");
        $systemtime = date("Y-m-d H:i:s", time());

        $views = [
            'system' => PHP_OS,
            'ip' => $_SERVER['SERVER_NAME'] . '[' . $_SERVER["SERVER_ADDR"] . ']',
            'sysos' => $_SERVER["SERVER_SOFTWARE"],
            'sysversion' => PHP_VERSION,
            'MySQL' => $SQL = $conn->server_info,
            'gdinfo' => $gdinfo,
            'max_upload' => $max_upload,
            'max_ex_time' => $max_ex_time,
            'systemtime' => $systemtime,
            'osname' => php_uname(),
            'goods_count' => $goods_count,
            'users_count' => $users_count,
            'add_order' => $add_order,
            'add_users' => $add_users,
            "last_login_users" => $last_login_users,
            'pending_review' => $pending_review,
            "sellerShop_count"=>$sellerShop_count,
        ];
        return view(
            'welcome',
            [
                'views' => $views,
            ]
        );
    }

    public function newUserstatistics(){

        $wmy=input("date","day");
        $table=input("table","user");
        $year = date("Y");
        $month = date("m");
        $weeks = date("W", mktime(0, 0, 0, 12, 28, $year)); //当前周多少天
        $months = $this->getMonthLastDay($month,$year); //当前月多少天
        $yearmonth = 12; //一年12个月
        $x =$y= array();

        $t = null;
        $field = "";
        switch ($table){
            case "user":
                $t = new Users();
                $field = "u_registered_time";
                break;
            case "order":
                $t = new Order();
                $field = "o_add_time";
                break;
            case "uhb":
                $t = new UserBrowsingHistory();
                $field = "ubh_time";
                break;
        }

        switch ($wmy){
            case "day":
                for($i=1;$i<=$months;$i++){
                    $usercount = $t->getCount("year(FROM_UNIXTIME($field,'%Y-%m-%d')) = ".$year." and month(FROM_UNIXTIME($field,'%Y-%m-%d')) = ".$month."  and day(FROM_UNIXTIME($field,'%Y-%m-%d')) = ".$i);

                    $x[]=$i."天";
                    $y[]=$usercount;
                }
                break;
            case "weak":
                for($i=1;$i<=$weeks;$i++){
                    $usercount = $t->getCount("year(FROM_UNIXTIME($field,'%Y-%m-%d')) = ".$year." and week(FROM_UNIXTIME($field,'%Y-%m-%d')) = ".$i);
                    $x[]=$i."周";
                    $y[]=$usercount;
                }
                break;
            case "month":
                for($i=1;$i<=$yearmonth;$i++){
                    $usercount = $t->getCount("year(FROM_UNIXTIME($field,'%Y-%m-%d')) = ".$year." and month(FROM_UNIXTIME($field,'%Y-%m-%d')) = ".$i." ");
                    $x[]=$i."月";
                    $y[]=$usercount;
                }
                break;
        }


        return json(format(array("x"=>$x,"y"=>$y)));
    }





    public function getMonthLastDay($month, $year) {
        switch ($month) {
            case 4 :
            case 6 :
            case 9 :
            case 11 :
                $days = 30;
                break;
            case 2 :
                if ($year % 4 == 0) {
                    if ($year % 100 == 0) {
                        $days = $year % 400 == 0 ? 29 : 28;
                    } else {
                        $days = 29;
                    }
                } else {
                    $days = 28;
                }
                break;
            default :
                $days = 31;
                break;
        }
        return $days;
    }


     public function ajaxChatRecord1()
    {
        if ($this->request->isAjax()) {
            $info = $this->request->only(['u_im_ids','msgs','type'], 'post');
            $info['sm_im_id']='67d4d1d052ce3418';
            
            $uname = $this->im->getRow(["sm_im_id"=>$info['sm_im_id'],'u_im_id'=>$info['u_im_ids']]);
            $info['u_name']=$uname['u_name'];
            $info['s_name']=$uname['ss_name'];
            // dump($uname);
            // die();
            /*验证接到的值有没有问题*/
            $rule = array(
                "sm_im_id" => 'require',
                "u_im_ids" => 'require',
                "msgs" => 'require',
                "type" => 'require',
            );
            $msg = array(
                "sm_im_id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
                "u_im_ids.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
                "msgs.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
                "type.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                logs($info,$_REQUEST['sm_im_id'].'.js');
                return json(format('', '1'));
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
}
