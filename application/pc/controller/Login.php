<?php
namespace app\pc\controller;

use think\Controller;
use \model\Users as u;

/**
 * 作者：袁中旭
 * 时间：2017-09-12
 * 主要功能：PC端登陆 
 */
class Login extends Base
{
    protected $u;
    public function __construct()
    {
        parent::__construct();
        /*会员*/
        $this->u = new u();
    }
    /**
     * 作者：袁中旭
     * 时间：2017-09-12
     * 功能：PC登录展示页面
     */
    public function Index()
    {
        session(null);
        cookie(null);
        return view(
            'index',
            array(
                'title' => "工建通-PC登录",
                'company' => "黑龙江特讯科技有限责任公司",
            )
        );
    }
    /**
     * 作者：袁中旭
     * 时间：2017-09-12
     * 功能：登陆操作
     */
    public function pcLogin()
    {
        // echo 1;
        if ($this->request->isAjax()) {
        	/* 只接受这两个字段的值 */
            $u_info = $this->request->only(['u_mobile', 'u_passwd','u_captcha'], 'post');
            /* 校验接收到值 */
            $rules = array(
                'u_mobile' => 'require|number|length:11',
                'u_passwd' => 'require|max:25',
                'u_captcha'=> 'require|captcha',
            );
            $msgs = array(
                'u_mobile.require' => '您没有填写手机号噢!~',
                'u_mobile.number' => '你的手机号格式不真确噢!~',
                'u_mobile.length' => '你的手机号长度不真确噢!~',
                'u_passwd.require' => '您没有填写密码噢!~',
                'u_passwd.max' => '密码最多不能超过25个字符噢!~',
                'u_captcha.require' => '您没有填写验证码噢!~',
                'u_captcha.captcha' => '您填写验证码不正确噢!~',
            );

            $data = verify($u_info,$rules,$msgs);
            /* 有值 */
            if ($data['code']) {
            	/* 实例化 Users model*/
                $where = array();
                $where['u_mobile'] = $u_info['u_mobile'];
                /* 获取单条 */
                $ret_info = $this->u->getRow($where);
                if (!empty($ret_info)) {
                    if ($ret_info['u_passwd'] != encryptPasswd($u_info['u_passwd'])) {
                        return json(format('', '2', '您的密码输入有误噢!~'));
                    } else {
                        $saveData = array(
                            "u_last_login_ip" => getIp(),
                            "u_last_login_time" => time(),
                        );
                        $condition = array(
                            "u_id" => $ret_info['u_id'],
                        );
                        /* 存入最后登录时间 */
                        $this->u->save($saveData,$condition);
                        session("u_id", $ret_info['u_id']);
                        session("u_mobile", $ret_info['u_mobile']);
                        session("u_name", $ret_info['u_name']);
                        session("u_info", $ret_info);
                        return json(format('', '1', '登录成功!'));
                    }
                } else {
                    return json(format('', '2', '平台中没有这个这个用户噢!~'));
                }
            } else {                    
            	/* 没有值 */
                return json(format('', '2', $data['msg']));
            }
        } else {
            return json(format('', '2', '抱歉,我没收到任何信息哦!~'));
        }
    }

    /**
     * 作者：袁中旭
     * 时间：2017-09-12
     * 功能：登陆操作
     */
    public function pcLogins()
    {
        $u_info['u_mobile']=$_GET['u_mobile'];
        $u_info['u_passwd']=$_GET['u_passwd'];
        /* 校验接收到值 */
        $rules = array(
            'u_mobile' => 'require|number|length:11',
            'u_passwd' => 'require|max:25',
        );

        /* 实例化 Users model*/
        $where = array();
        $where['u_mobile'] = $u_info['u_mobile'];
        /* 获取单条 */
        $ret_info = $this->u->getRow($where);
        if (!empty($ret_info)) {
            //encryptPasswd
            if ($ret_info['u_passwd'] != ($u_info['u_passwd'])) {
//                return json(format('', '2', '您的密码输入有误噢!~'));
                $this->error('您的密码输入有误噢!~');
            } else {
                $saveData = array(
                    "u_last_login_ip" => getIp(),
                    "u_last_login_time" => time(),
                );
                $condition = array(
                    "u_id" => $ret_info['u_id'],
                );
                /* 存入最后登录时间 */
                $this->u->save($saveData,$condition);
                session("u_id", $ret_info['u_id']);
                session("u_mobile", $ret_info['u_mobile']);
                session("u_name", $ret_info['u_name']);
                session("u_info", $ret_info);
                // return json(format('', '1', '登录成功!'));
                // var_dump($_SESSION);
                // exit;
               header("Location: http://".$_SERVER["SERVER_NAME"].URL("pc/Index/index"));

            }
        } else {
            $this->error('平台中没有这个这个用户噢!~');
            //return json(format('', '2', '平台中没有这个这个用户噢!~'));
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
        $this->redirect(url("seller/Login/Index"));
    }
}
