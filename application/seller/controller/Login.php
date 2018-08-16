<?php
namespace app\seller\controller;

use \think\Controller;
/**
 * 作者：袁中旭
 * 时间：2017-09-12
 * 主要功能：PC端登陆 , 
 */
use model\SellerManagers as sm; /* 商户管理员表 */
use \model\Users as u;
use \model\SellerShop as ss;
use \model\SellerRegistered as sr;

class Login extends Controller
{
    protected $sm;
    protected $sr;
    public function __construct()
    {
        parent::__construct();
        /*用户信息*/
        $this->sm = new sm();
        $this->u = new u();
        $this->ss=new ss();
        $this->sr=new sr();
    }
    /**
     * 作者：袁中旭
     * 时间：2017-09-12
     * 功能：商户后台登录页面
     */
    public function Index()
    {
        return view('index',[    
            'title' => "工建通-商户后台登录",
            'company' => "黑龙江特讯科技有限责任公司",
        ]);
    }
    /**
     * 作者：袁中旭
     * 时间：2017-09-12
     * 功能：登陆操作
     */
    public function sellerLogin()
    {        
        if ($this->request->isAjax()) {
        	/* 只接受这两个字段的值 */
            $sm_info = $this->request->only(['sm_seller_name', 'sm_seller_passwd','sm_seller_captcha'], 'post');
            /* 校验接收到值 */
            $rules = [
                'sm_seller_name' => 'require',
                'sm_seller_passwd' => 'require|max:25',
                'sm_seller_captcha'=> 'require|captcha',
            ];
            $msgs = [
                'sm_seller_name.require' => '您没有填写用户名噢!~',
                'sm_seller_passwd.require' => '您没有填写密码噢!~',
                'sm_seller_passwd.max' => '密码最多不能超过25个字符噢!~',
                'sm_seller_captcha.require' => '您没有填写验证码噢!~',
                'sm_seller_captcha.captcha' => '您填写验证码不正确噢!~',
            ];

            $data = verify($sm_info,$rules,$msgs);
            
            if ($data['code'] == 1) {
                /* 获取单条数据 */
                $ret_info = $this->sm->getRow("sm_seller_name = '{$sm_info['sm_seller_name']}' and sm_status > 0 ");
                if (!empty($ret_info)) {
                    if ($ret_info['sm_seller_passwd'] != encryptPasswd($sm_info['sm_seller_passwd'])) {
                        return json(format('', "-1", '您的密码输入有误噢!~'));
                    } else {
                        $sr_id = $ret_info["sr_id"];
                        /*判断商家是否有冻结*/
                        $dongjie = $this->ss->getRow(['sr_id'=>$sr_id,'state'=>1]);

                        if(!empty($dongjie)){
                            return json(format('', "-1", '您的账号已冻结,请及时与管理员联系'));
                        }

                        $spm = new \model\SellerPrivilegesModules();

                        $spm_list = $spm->getList(["spm_status" => '1'], 'spm_name,spm_id,spm_value');

                        if (isset($ret_info['spg_id']) && $ret_info['spg_id'] > 0) {
                            $spg = new \model\SellerPrivilegesGroup();
                            $spm_ids = $spg->getOne(['spg_id' => $ret_info['spg_id']], "spm_ids");
                            session("spm_ids", $spm_ids);
                        }                        
                        session("spm_list" ,$spm_list);
                        session("sm_id", $ret_info['sm_id']);
                        session("sm_name", $ret_info['sm_seller_name']);
                        session("sm_info", $ret_info);
                        $save['sm_id'] = $ret_info['sm_id'];
                        $save['sml_add_time'] = time();
                        $save['ml_ip'] = getIp();
                        $save['sml_info'] = "管理员" . $ret_info['sm_seller_name'] . "登录";
                        $save['sml_name'] = session('sm_seller_name');
                        $sml = new \model\SellerManagerLog();
                        $sml->save($save);
                        $saveData = [
                            "sm_last_ip" => getIp(),
                            "sm_last_time" => time(),
                        ];
                        $condition = [
                            "sm_id" => $ret_info['sm_id'],
                        ];
                        /* 存入最后登录时间 */
                        if($ret_info['sm_im_id'] != '0' && $ret_info['sm_im_id'] != ''){
                            $customer_service = [
                                'data' => 1,
                            ];
                        }else{
                            $sm_im_name = md5_16($ret_info['sm_seller_name'].time());
                            $customer_service = [
                                'data' => 0,
                                'sm_im_name' => $sm_im_name,
                                'sm_im_nickname' => md5_16($ret_info['sm_seller_name'].time()),
                                'sm_im_pass' => '123456',
                            ];
                            $saveData['sm_im_id'] = $sm_im_name;
                        }
                        $this->sm->save($saveData,$condition);
                        return json(format($customer_service, '1', '登陆成功！'));
                    }
                } else {
//                    $where = array();
//                    $fanhui['u_mobile']=$sm_info['sm_seller_name'];
////                    $fanhui['u_passwd']=$sm_info['sm_seller_passwd'];
//                    $where['u_mobile'] = $sm_info['sm_seller_name'];
//                    /* 获取单条 */
//                    $ret_infos = $this->u->getRow($where);
//                    $fanhui['u_passwd']=$ret_infos['u_passwd'];
//                    $u_id = $ret_infos["u_id"];
//                    $sr_infos = $this->sr->getRow(["u_id"=>$u_id,'sr_isaudit'=>1]);


                    $where = array();
                    $fanhui['u_mobile']=$sm_info['sm_seller_name'];
                    $where['u_mobile'] = $sm_info['sm_seller_name'];
                    /* 获取单条 */
                    $ret_infos = $this->u->getRow($where);

                    if(empty($ret_infos)){
                        return json(format('', "-1", '请核实账号和密码!~'));
                    }
                    $fanhui['u_passwd']=$ret_infos['u_passwd'];
                    $u_id = $ret_infos["u_id"];
                    $sr_infos = $this->sr->getRow(["u_id"=>$u_id,'sr_isaudit'=>1]);


                    if ($sr_infos && ($sm_info['sm_seller_passwd'] == "123456")) {
                        return json(format($fanhui, '2', '请先入驻！'));
                    }else{
                        //return json(format('', "-1", '您还不是商户管理员哦!~'));
                        return json(format('', "-1", '请核实账号和密码!~'));
                    }
                }
            } else {
            	/* 没有值 */
                return json(format('', "-1", $data['msg']));
            }
        } else {
            return json(format('', "-1", '抱歉,我没收到任何信息哦!~'));
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-11
     * 功能：退出登陆操作
     */
    public function logout()
    {
        session(null);
        cookie(null);
        $this->redirect(url("seller/login/index"));
    }
}
