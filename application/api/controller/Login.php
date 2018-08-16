<?php
namespace app\api\controller;

use model\SmsLog as sl;
use model\Users as u;

/**
 * 登陆,注册
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-10-09
 */
class Login extends Base
{
    protected $sl;
    protected $u;
    public function __construct()
    {
        parent::__construct();
        $this->sl = new sl();
        $this->u = new u();
    }
    /**
     * 登陆
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-09
     */
    public function login()
    {
        $info['u_mobile'] = $this->param['u_mobile'];
        $info['u_passwd'] = $this->param['u_passwd'];
        $rule = [
            "u_mobile" => "require",
            "u_mobile" => ['regex' => '/^1(3|2|4|5|7|8)\d{9}$/'],
            "u_passwd" => "require",
        ];
        $msg = [
            "u_mobile.require" => "手机号是必须要填写的噢!~",
            "u_mobile.regex" => "手机号输入有误噢!~",
            "u_passwd.require" => "密码必须要填写哦!~",
        ];
        $data = verify($info, $rule, $msg);
        if ($data['code'] === 1) {
            /*查询手机号是否存在*/
            $user_info = $this->u->getRow(["u_mobile" => $info['u_mobile']]);
            if (empty($user_info)) {
                return json(format('', 228, "用户不存在!~"));
            }
            if ($user_info['u_passwd'] != encryptPasswd($info['u_passwd'])) {
                return json(format('', 229, "密码错误!~"));
            }
            /*保存信息*/
            $this->u->save(["u_last_login_time" => time(), "u_last_login_ip" => getIp(), "u_uuid" => $this->param['uuid']], ["u_mobile" => $info['u_mobile']]);
            $user_info['u_uuid'] = $this->param['uuid'];
            $user_info['u_last_login_time'] = time();
            $user_info['u_last_login_ip'] = getIp();
            // $user_info['u_headimg'] = IMG_URL . $user_info['u_headimg'];
            if(strpos($user_info['u_headimg'],'http') !==false){
                $user_info['u_headimg'] = $user_info['u_headimg'];
            }else{
                $user_info['u_headimg'] = IMG_URL . $user_info['u_headimg'];
            }
            return json(format(rc4Encypt($user_info), 200, "登陆成功!~"));
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 第三方登录
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-09
     */
    public function thirdLogin()
    {

        $rule = [
            "info" => "require", /*三方登陆返回的信息*/
            "code" => "require|alpha", /*登陆方式*/
            "uuid" => "require", /*uuid*/
        ];
        $msg = [
            "info.require" => "缺少必要信息噢!~",
            "code.require" => "缺少登陆方式噢!~",
            "code.alpha" => "登陆方式传输有误噢!~",
            "uuid.require" => "uuid必须要传哦!~",
        ];
        $data = verify($this->param, $rule, $msg);
        if ($data['code'] === 1) {
            $info = json_decode($this->param['info'], true);
            /*判断是哪个登陆方式 并查询登陆方式*/
            switch ($this->param['code']) {
                case 'wechat':
                    $login_info['u_wechat_openid'] = $info['openid'];
                    $login_info['u_name'] = $info['nickname'];
                    // $login_info['u_headimg'] = $info['headimgurl'];
                    $login_info['u_sex'] = $info['sex'];
                    $where['u_wechat_openid'] = $info['openid'];
                    break;
                case 'qq':
                    $login_info['u_qq_openid'] = $this->param['openid'];
                    $login_info['u_name'] = $info['nickname'];
                    // $login_info['u_headimg'] = $info['figureurl_qq_2'] ? $info['figureurl_qq_2'] : $info['figureurl_2'];
                    if (isset($info['sex']) && ($info['sex'] == "男")) {
                        $login_info['u_sex'] = 1;
                    } else {
                        $login_info['u_sex'] = 0;
                    }
                    $where['u_qq_openid'] = $this->param['openid'];
                    break;
                case 'weibo':
                    $login_info['u_weibo_openid'] = $info['id'];
                    $login_info['u_name'] = $info['screen_name'];
                    // $login_info['u_headimg'] = $info['avatar_hd'];
                    if ($info['gender'] == "m") {
                        $login_info['u_sex'] = 1;
                    } else if ($info['gender'] == "f") {
                        $login_info['u_sex'] = 0;
                    } else {
                        $login_info['u_sex'] = 2;
                    }
                    $where['u_weibo_openid'] = $info['id'];
                    break;

                default:
                    return json(format('', 223, "参数传输有误噢!~"));exit();
                    break;
            }
            $login_info['u_uuid'] = $this->param['uuid'];
            /*纯用户信息是否存在*/
            $u_info = $this->u->getRow($where);
            /*存在升级*/
            if (!empty($u_info)) {
                /*判断基础信息存在不存在 存在销毁*/
                if (!empty($u_info['u_name'])) {
                    unset($login_info['u_name']);
                }
                if (!empty($u_info['u_headimg'])) {
                    unset($login_info['u_headimg']);
                }
                if (!empty($u_info['u_sex'])) {
                    unset($login_info['u_sex']);
                }
                $login_info['u_last_login_time'] = time();
                $login_info['u_last_login_ip'] = getIp();
                $ret = $this->u->save($login_info, $where);
                if (false === $ret) {
                    return json(format('', 230, "登陆失败!~"));
                } else {
                    $ret_infos = $this->u->getRow($where);
                    if (!empty($ret_infos)) {
                        $where['u_mobile'] =["neq",''];
                        $ret_info = $this->u->getRow($where);
                        if (!empty($ret_info)) {
                            if ($ret_info['u_mobile'] != '') {
                                $ret_info['is_bind'] = 0;
                            } else {
                                $ret_info['is_bind'] = 1;
                            }
                        }else{
                            $ret_info = $ret_infos;
                            $ret_info['is_bind'] = 1;
                        }
                        if(strpos($ret_info['u_headimg'],'http') !==false){
                            $ret_info['u_headimg'] = $ret_info['u_headimg'];
                        }else{
                            $ret_info['u_headimg'] = IMG_URL . $ret_info['u_headimg'];
                        }
                        return json(format(rc4Encypt($ret_info), 200, "登陆成功!~"));
                    } else {
                        return json(format('', 230, "登陆失败!~"));
                    }
                }
            } else {
                /*不存在添加*/
                $login_info['u_last_login_time'] = time();
                $login_info['u_last_login_ip'] = getIp();
                $login_info['u_registered_ip'] = time();
                $login_info['u_last_login_ip'] = getIp();
                $id = $this->u->save($login_info);
                if ($id > 0) {
                    $ret_infos = $this->u->getRow(["u_id" => $id]);
                    if (!empty($ret_infos)) {
                        // if ($ret_info['u_mobile'] != '') {
                        //     $ret_info['is_bind'] = 0;
                        // } else {
                        //     $ret_info['is_bind'] = 1;
                        // }
                        $where['u_mobile'] =["neq",''];
                        $ret_info = $this->u->getRow($where);
                        if (!empty($ret_info)) {
                            if ($ret_info['u_mobile'] != '') {
                                $ret_info['is_bind'] = 0;
                            } else {
                                $ret_info['is_bind'] = 1;
                            }
                        }else{
                            $ret_info = $ret_infos;
                            $ret_info['is_bind'] = 1;
                        }
                        if(strpos($ret_info['u_headimg'],'http') !==false){
                            $ret_info['u_headimg'] = $ret_info['u_headimg'];
                        }else{
                            $ret_info['u_headimg'] = IMG_URL . $ret_info['u_headimg'];
                        }
                        return json(format(rc4Encypt($ret_info), 200, "登陆成功!~"));
                    } else {
                        return json(format('', 230, "登陆失败!~"));
                    }
                } else {
                    return json(format('', 230, "登陆失败!~"));
                }
            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 手机号用户注册
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-09
     */
    public function registered()
    {
        $info['u_mobile'] = $this->param['u_mobile'];
        $info['sl_code'] = $this->param['sl_code'];
        $info['sl_id'] = $this->param['sl_id'];
        $info['u_passwd'] = $this->param['u_passwd'];
        $rule = [
            "u_mobile" => "require",
            "u_mobile" => ['regex' => '/^1(3|2|4|5|7|8)\d{9}$/'],
            "sl_code" => "require|number",
            "sl_id" => "require|number",
            "u_passwd" => "require",
        ];
        $msg = [
            "u_mobile.require" => "手机号是必须要填写的噢!~",
            "u_mobile.regex" => "手机号输入有误噢!~",
            "sl_code.require" => "验证码必须填写哦!~",
            "sl_code.number" => "验证码填写必须是数字哦!~",
            "sl_id.require" => "传输数据缺少参数哦!~",
            "sl_id.number" => "缺少的参数必须是正整数哦!~",
            "u_passwd.require" => "密码必须要填写哦!~",
        ];
        $data = verify($info, $rule, $msg);
        if ($data['code'] === 1) {
            /*查询验证码*/
            $where['sl_id'] = $info['sl_id'];
            unset($info['sl_id']);
            $sl_info = $this->sl->getRow($where);
            if ($sl_info['sl_code'] != $info['sl_code']) {
                return json(format('', 224, "验证码填写有误!~"));
            }
            /*300为验证码超时时间,*/
            if ($sl_info['sl_add_time'] < time() - $this->smsOvertimeYime) {
                return json(format('', 225, "验证码超时,请重新发送!~"));
            }
            /*查询手机号是否被注册*/
            $add['u_mobile'] = $info['u_mobile'];
            $count = $this->u->getCount($add);
            if ($count > 0) {
                return json(format('', 226, "该手机号以存在,请使用其他手机号注册!~"));
            }
            $add['u_passwd'] = encryptPasswd($info['u_passwd']);
            $add['u_registered_time'] = time();
            $add['u_registered_ip'] = getIp();
            $add['u_name'] = $info['u_mobile'];
            /*添加用户*/
            $id = $this->u->save($add);
            if ($id > 0) {
                return json(format('', 200, "注册成功!~"));
            } else {
                return json(format('', 227, "注册失败!~"));
            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 第三方绑定
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-10
     * @return [type]            [description]
     */
    public function checkBingThirdInfo()
    {
        $rule = [
            "info" => "require", /*三方登陆返回的信息*/
            "code" => "require|alpha", /*登陆方式*/
            "u_id" => "require", /*用户id*/
        ];
        $msg = [
            "info.require" => "缺少必要信息噢!~",
            "code.require" => "缺少登陆方式噢!~",
            "code.alpha" => "登陆方式传输有误噢!~",
            "u_id.require" => "缺少必要参数哦!~",
        ];
        
        $data = verify($this->param, $rule, $msg);
        if ($data['code'] === 1) {
            $info = json_decode($this->param['info'], true);
            switch ($this->param['code']) {
                case 'wechat':
                    $save['u_wechat_openid'] = $info['openid'];
                    $save['u_name'] = $info['nickname'];
                    // $save['u_headimg'] = $info['headimgurl'];
                    $save['u_sex'] = $info['sex'];
                    break;
                case 'qq':
                    $save['u_qq_openid'] = $this->param['openid'];
                    $save['u_name'] = $info['nickname'];
                    // $save['u_headimg'] = $info['figureurl_qq_2'] ? $info['figureurl_qq_2'] : $info['figureurl_2'];
                    (isset($info['sex']) && ($info['sex'] == "男")) ? $save['u_sex'] = 1 : $save['u_sex'] = 0;
                    break;
                case 'weibo':
                    $save['u_weibo_openid'] = $info['id'];
                    $save['u_name'] = $info['screen_name'];
                    // $save['u_headimg'] = $info['avatar_hd'];
                    if ($info['gender'] == "m") {
                        $save['u_sex'] = 1;
                    } else if ($info['gender'] == "f") {
                        $save['u_sex'] = 0;
                    } else {
                        $save['u_sex'] = 2;
                    }
                    break;
                default:
                    return json(format('', 223, "参数传输有误噢!~"));exit();
                    break;
            }
            /*查询条件*/
            $where['u_id'] = $this->param['u_id'];
            /*查询用户是否存在*/
            $user_info = $this->u->getRow($where);
            if (!empty($user_info)) {
                /*判断用户该种登陆方式是否已经授权过*/
                if ($this->param['code']) {
                    $wheres['u_'.$this->param['code'].'_openid'] = $save['u_'.$this->param['code'].'_openid'];
                    $wheres['u_mobile'] = ['neq',''];
                    /*查询用户是否存在*/
                    $user_infos = $this->u->getRow($wheres);
                    if (!empty($user_infos)) {
                        return json(format('', 233, "该帐号已被绑定过"));
                    }else{
                        if (!empty($user_info['u_' . $this->param['code'] . "_openid"])) {
                            return json(format('', 233, "授权信息已存在!~"));
                        } else {
                            /*判断用户基础信息是否存在*/
                            if (!empty($user_info['u_name'])) {
                                unset($save['u_name']);
                            }
                            if ($user_info['u_headimg']) {
                                unset($save['u_headimg']);
                            }
                            if ($user_info['u_sex']) {
                                unset($save['u_sex']);
                            }
                            /*升级用户信息*/
                            $ret = $this->u->save($save, $where);
                            if (false === $ret) {
                                return json(format('', 234, "绑定失败!~"));
                            } else {
                                return json(format('', 200, "绑定成功!~"));
                            }
                        }
                    }
                }
            } else {
                return json(format('', 228, "用户不存在!~"));
            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 解除绑定
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-10
     */
    public function unbindThirdInfo()
    {
        $info["u_id"] = $this->param["u_id"];
        $info["code"] = $this->param["code"];
        $rule = [
            "u_id" => "require|number",
            "code" => "require",
        ];
        $msg = [
            "u_id.require" => "传输参数有误噢!~",
            "u_id.number" => "参数格式不正确哦!~",
            "code" => "code传输有误噢!~",
        ];
        $data = verify($info, $rule, $msg);
        if ($data['code'] === 1) {
            switch ($this->param['code']) {
                case 'wechat':
                    $save['u_wechat_openid'] = "";
                    break;
                case 'qq':
                    $save['u_qq_openid'] = "";
                    break;
                case 'weibo':
                    $save['u_weibo_openid'] = "";
                    break;

                default:
                    return json(format('', 223, "参数传输有误噢!~"));exit();
                    break;
            }
            $where = ["u_id" => $info['u_id']];
            /*升级用户信息该种绑定方式为空*/
            $user_info = $this->u->getRow($where);
            if (!empty($user_info)) {
                $ret = $this->u->save($save, $where);
                if (false === $ret) {
                    return json(format('', 232, "解除绑定失败!~"));
                } else {
                    return json(format('', 200, "解除绑定成功!~"));
                }
            } else {
                return json(format('', 228, "用户不存在!~"));
            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 绑定手机号
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-10
     */
    public function bingMobile()
    {
        $info['u_mobile'] = $this->param['u_mobile'];
        $info['sl_code'] = $this->param['sl_code'];
        $info['sl_id'] = $this->param['sl_id'];
        $info['u_passwd'] = $this->param['u_passwd'];
        $info['openid'] = $this->param['openid'];
        $info['code'] = $this->param['code'];
        $rule = [
            "u_mobile" => "require",
            "u_mobile" => ['regex' => '/^1(3|2|4|5|7|8)\d{9}$/'],
            "sl_code" => "require|number",
            "sl_id" => "require|number",
            "u_passwd" => "require",
            "openid" => "require",
            "code" => "require",
        ];
        $msg = [
            "u_mobile.require" => "手机号是必须要填写的噢!~",
            "u_mobile.regex" => "手机号输入有误噢!~",
            "sl_code.require" => "验证码必须填写哦!~",
            "sl_code.number" => "验证码填写必须是数字哦!~",
            "sl_id.require" => "传输数据缺少参数哦!~",
            "sl_id.number" => "缺少的参数必须是正整数哦!~",
            "u_passwd.require" => "密码必须要填写哦!~",
            "openid" => "openid传输有误噢!~",
            "code" => "参数传输有误噢!~",
        ];
        $data = verify($info, $rule, $msg);
        if ($data['code'] === 1) {
            $where['sl_id'] = $info['sl_id'];
            unset($info['sl_id']);
            /*查询验证码信息*/
            $sl_info = $this->sl->getRow($where);
            /*如果空的话提示错误信息*/
            if (!empty($sl_info)) {
                if ($sl_info['sl_code'] != $info['sl_code']) {
                    return json(format('', 224, "验证码填写有误!~"));
                }
                /*300为验证码超时时间,*/
                if ($sl_info['sl_add_time'] < time() - 300) {
                    return json(format('', 225, "验证码超时,请重新发送!~"));
                }
                $add['u_mobile'] = $info['u_mobile'];
                $count = $this->u->getCount($add);
                /*如果用户存在,把第三方登录的信息查出来,然后转移到以前的用户上*/
                switch ($info['code']) {
                    case 'wechat':
                        $condition['u_wechat_openid'] = $info['openid'];
                        /*查询三方登陆信息*/
                        $third_info = $this->u->getRow($condition, "u_wechat_openid,u_uuid");
                        break;
                    case 'qq':
                        $condition['u_qq_openid'] = $info['openid'];
                        /*查询三方登陆信息*/
                        $third_info = $this->u->getRow($condition, "u_qq_openid,u_uuid");
                        break;
                    case 'weibo':
                        $condition['u_weibo_openid'] = $info['openid'];
                        /*查询三方登陆信息*/
                        $third_info = $this->u->getRow($condition, "u_weibo_openid,u_uuid");
                        break;

                    default:
                        return json(format('', 231, "参数传输有误噢!~"));
                        break;
                }
                /*如果查询出用户信息*/
                if (!empty($count) && $count > 0) {
                    $this->u->del($condition);
                    /*用户存在把三方数据存入到原有的账户*/
                    $ret = $this->u->save($third_info, $add);
                } else {
                    $add['u_passwd'] = encryptPasswd($info['u_passwd']);
                    $ret = $this->u->save($add, $condition);
                }
                if (false !== $ret) {
                    $ret_info = $this->u->getRow(["u_mobile" => $add['u_mobile']]);
                    return json(format(rc4Encypt($ret_info), 200, "绑定成功!~"));
                } else {
                    return json(format('', 227, "绑定失败!~"));
                }
            } else {
                return json(format('', 230, "缺少必要参数哦!~"));
            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 切换手机绑定
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-10
     */
    public function switchMobileBind()
    {
        $info['old_mobile'] = $this->param['old_mobile'];
        $save['u_mobile'] = $info['u_mobile'] = $this->param['new_mobile'];
        $info['sl_code'] = $this->param['sl_code'];
        $info['sl_id'] = $this->param['sl_id'];
        $info['u_id'] = $this->param['u_id'];
        logs($_SERVER['DOCUMENT_ROOT'], "1");
        $rule = [
            "old_mobile" => "require",
            "old_mobile" => ['regex' => '/^1(3|2|4|5|7|8)\d{9}$/'],
            "u_mobile" => "require",
            "u_mobile" => ['regex' => '/^1(3|2|4|5|7|8)\d{9}$/'],
            "sl_code" => "require|number",
            "sl_id" => "require|number",
            "u_id" => "require|number",
        ];
        $msg = [
            "old_mobile.require" => "旧手机号是必须要填写的噢!~",
            "old_mobile.regex" => "旧手机号输入有误噢!~",
            "u_mobile.require" => "新手机号是必须要填写的噢!~",
            "u_mobile.regex" => "新手机号输入有误噢!~",
            "sl_code.require" => "验证码必须填写哦!~",
            "sl_code.number" => "验证码填写必须是数字哦!~",
            "sl_id.require" => "传输数据缺少参数哦!~",
            "sl_id.number" => "缺少的参数必须是正整数哦!~",
            "u_id" => "缺少用户必要参数!~",
            "u_id" => "用户必要参数参数有误!~",
        ];
        $data = verify($info, $rule, $msg);
        if ($data['code'] === 1) {
            /*查询用户是否存在*/
            $u_info = $this->u->getRow(['u_id' => $info['u_id']]);
            if (empty($u_info)) {
                return json(format('', 228, "用户不存在!~"));
            } else {
                /*判断是否与原手机号一致*/
                if ($u_info['u_mobile'] != $info['old_mobile']) {
                    return json(format('', 235, "旧手机号输入有误噢!~"));
                    /*判断新手机号是否已经呗绑定*/
                } else if ($this->u->getCount(["u_mobile" => $info['u_mobile']]) > 0) {
                    return json(format('', 235, "新输入的手机号已经存在噢!~"));
                } else {
                    /*校验验证码*/
                    $where['sl_id'] = $info['sl_id'];
                    unset($info['sl_id']);
                    $sl_info = $this->sl->getRow($where);
                    if ($sl_info['sl_code'] != $info['sl_code']) {
                        return json(format('', 224, "验证码填写有误!~"));
                    }
                    /*300为验证码超时时间,*/
                    if ($sl_info['sl_add_time'] < time() - 300) {
                        return json(format('', 225, "验证码超时,请重新发送!~"));
                    }
                    /*升级用户信息*/
                    $ret = $this->u->save($save, ['u_id' => $info['u_id']]);
                    if (false === $ret) {
                        return json(format('', 236, "切换绑定手机号失败!~"));
                    } else {
                        return json(format('', 200, "成功切换绑定手机号!~"));
                    }
                }
            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 发送短信验证码
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-09
     */
    public function getSmsCode()
    {
        $info['sl_mobile'] = $this->param['sl_mobile'];
        /*验证接到的值有没有问题*/
        $rule = array(
            "sl_mobile" => 'require',
            "sl_mobile" => ['regex' => '/^1(3|2|4|5|7|8)\d{9}$/'],
        );
        $msg = array(
            "sl_mobile.require" => '手机号是必须要填写的噢!~',
            "sl_mobile.regex" => '手机号输入有误噢!~',
        );
        $data = verify($info, $rule, $msg);
        if ($data['code'] === 1) {
            /*今天0晨的时间*/
            $start_time = date2time(date("Y-m-d", time()));
            /*一天86400*/
            $end_time = $start_time + 86400;
            $where = [
                'sl_add_time' => [
                    'between',
                    [
                        $start_time,
                        $end_time,
                    ],
                ],
                "sl_mobile" => $info['sl_mobile'],
            ];
            /*验证每日发送条数*/
            $count = $this->sl->getCount($where);
            /*配置文件中验证码单日最大发送次数*/
            if ($count >= config("plugin")['sms']['sendCount']) {
                return json(format('', 220, '验证码发送次数超出限制!~'));
            }
            /*验证发送间隔*/
            $send_info = $this->sl->getRow(["sl_mobile" => $info['sl_mobile']]);
            if (!empty($send_info)) {
                /*配置文件中验证码发送间隔*/
                if ((int) $send_info['sl_add_time'] + config("plugin")['sms']['Timeout'] > time()) {
                    return json(format('', 221, '一分钟内只能发送一次获取验证码的请求!~'));
                }
            }
            /*发送短信*/
            $ret = sendSMS($info['sl_mobile']);

            if ($ret !== false && is_array($ret)) {
                $id = $this->sl->save($ret);
                return json(format(rc4Encypt(['sl_id' => $id, "sl_code" => $ret['sl_code']]), 200, '验证码发送成功!~'));
            } else {
                return json(format('', 222, '验证码发送失败!~'));
            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }

    /**
     * 发送邮箱验证码
     * @author 王牧田
     * @e-mail zrkjhlc@gmail.com
     * @date   2018-04-18
     */
    public function getMailCode(){
       // $this->param['sl_mail'] = "824495596@qq.com";
        $info['sl_mail'] = $this->param['sl_mail'];
        $rule = array(
            "sl_mail" => 'require',
            "sl_mail" => ['regex' => '/^\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}$/'],
        );
        $msg = array(
            "sl_mail.require" => '邮箱是必须要填写的噢!~',
            "sl_mail.regex" => '邮箱输入格式不对!~',
        );
        $data = verify($info, $rule, $msg);
        if ($data['code'] === 1) {
            /*发送邮箱*/
            $ret = sendEmail($info['sl_mail']);

            if ($ret !== false && is_array($ret)) {
                $id = $this->sl->save($ret);
                return json(format(['sl_id' => $id, "sl_code" => $ret['sl_code']], 200, '验证码发送成功!~'));
            }else{
                return json(format('', 222, '验证码发送失败!~'));
            }
        }else {
            return json(format('', 223, $data['msg']));
        }
    }


    /**
     * [使用邮箱(修改手机号) 发送验证码]
     * @author 王牧田
     * @date 2018-05-10
     */
    public function uidMailCode(){


        //$this->param['u_id'] = 119;
        $info['u_id'] = $this->param['u_id'];
        $rule = array(
            "u_id" => 'require',
        );
        $msg = array(
            "sl_mail.require" => 'u_id不能为空!~',
        );
        $data = verify($info, $rule, $msg);
        if ($data['code'] === 1) {
            $u_email = $this->u->getOne(["u_id"=>$info['u_id']],"u_email");
            if(empty($u_email)){
                return json(format('', 222, '未绑定邮箱!~'));
            }
            $ret = sendEmail($u_email);
            if ($ret !== false && is_array($ret)) {
                $id = $this->sl->save($ret);
                return json(format(['sl_id' => $id, "sl_code" => $ret['sl_code']], 200, '验证码发送成功!~'));
            }else{
                return json(format('', 222, '验证码发送失败!~'));
            }
        }else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 关联新账号
     * @author 李鑫
     * @date   2018-05-14
     */
    public function registerednew()
    {
        $info['u_mobile'] = $this->param['u_mobile'];
        $info['sl_code'] = $this->param['sl_code'];
        $info['sl_id'] = $this->param['sl_id'];
        $info['u_passwd'] = $this->param['u_passwd'];
        $info['openid'] = $this->param['openid'];
        $info['code'] = $this->param['code'];
        $rule = [
            "u_mobile" => "require",
            "u_mobile" => ['regex' => '/^1(3|2|4|5|7|8)\d{9}$/'],
            "sl_code" => "require|number",
            "sl_id" => "require|number",
            "u_passwd" => "require",
            "openid" => "require",
            "code" => "require",
        ];
        $msg = [
            "u_mobile.require" => "手机号是必须要填写的噢!~",
            "u_mobile.regex" => "手机号输入有误噢!~",
            "sl_code.require" => "验证码必须填写哦!~",
            "sl_code.number" => "验证码填写必须是数字哦!~",
            "sl_id.require" => "传输数据缺少参数哦!~",
            "sl_id.number" => "缺少的参数必须是正整数哦!~",
            "u_passwd.require" => "密码必须要填写哦!~",
            "openid" => "openid传输有误噢!~",
            "code" => "参数传输有误噢!~",
        ];
        $data = verify($info, $rule, $msg);
        if ($data['code'] === 1) {
            /*查询验证码*/
            $where['sl_id'] = $info['sl_id'];
            $where['sl_mobile'] = $info['u_mobile'];

            // unset($info['sl_id']);
            $sl_info = $this->sl->getRow($where);
            if (!isset($sl_info['sl_code'])) {
                return json(format('', 224, "验证码填写有误!~"));
            }
            if ($sl_info['sl_code'] != $info['sl_code']) {
                return json(format('', 224, "验证码填写有误!~"));
            }
            /*300为验证码超时时间,*/
            if ($sl_info['sl_add_time'] < time() - $this->smsOvertimeYime) {
                return json(format('', 225, "验证码超时,请重新发送!~"));
            }
            /*查询手机号是否被注册*/
            $add['u_mobile'] = $info['u_mobile'];
            $count = $this->u->getCount($add);
            if ($count > 0) {
                return json(format('', 226, "该手机号以存在,请使用其他手机号注册!~"));
            }
            $add['u_passwd'] = encryptPasswd($info['u_passwd']);
            $add['u_registered_time'] = time();
            $add['u_registered_ip'] = getIp();
            $add['u_name'] = $info['u_mobile'];
            $add['u_mobile'] = $info['u_mobile'];
             /*如果用户存在,把第三方登录的信息查出来,然后转移到以前的用户上*/
            switch ($info['code']) {
                case 'wechat':
                    $add['u_wechat_openid'] = $info['openid'];
                    /*查询三方登陆信息*/
                    break;
                case 'qq':
                    $add['u_qq_openid'] = $info['openid'];
                    /*查询三方登陆信息*/
                    break;
                case 'weibo':
                    $add['u_weibo_openid'] = $info['openid'];
                    /*查询三方登陆信息*/
                    break;
                default:
                    return json(format('', 231, "参数传输有误噢!~"));
                    break;
            }
            /*添加用户*/
            $id = $this->u->save($add);
            if ($id > 0) {
                return json(format('', 200, "注册成功!~"));
            } else {
                return json(format('', 227, "注册失败!~"));
            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }


    /**
     * 关联老账号
     * @author 李鑫
     * @date   2018-05-14
     */
    public function registeredold()
    {

        $info['u_mobile'] = $this->param['u_mobile'];
        $info['u_passwd'] = $this->param['u_passwd'];
        $info['openid'] = $this->param['openid'];
        $info['code'] = $this->param['code'];

        $rule = [
            "u_mobile" => "require",
            "u_mobile" => ['regex' => '/^1(3|2|4|5|7|8)\d{9}$/'],
            "u_passwd" => "require",
            "openid" => "require",
            "code" => "require",
        ];
        $msg = [
            "u_mobile.require" => "手机号是必须要填写的噢!~",
            "u_mobile.regex" => "手机号输入有误噢!~",
            "u_passwd.require" => "密码必须要填写哦!~",
            "openid" => "openid传输有误噢!~",
            "code" => "参数传输有误噢!~",
        ];
        $data = verify($info, $rule, $msg);
        if ($data['code'] === 1) {
          
            
            $where['u_mobile'] = $info['u_mobile'];
            $where['u_passwd'] = encryptPasswd($info['u_passwd']);
            $user_info = $this->u->getRow($where);

            if (!empty($user_info)) {
                $wheres['u_id'] = $user_info['u_id'];
                // $add['u_name'] = $info['u_mobile'];
                $add['u_passwd'] = encryptPasswd($info['u_passwd']);
                $add['u_mobile'] = $info['u_mobile'];
                $add['u_registered_time'] = time();
                $add['u_registered_ip'] = getIp();
                 /*如果用户存在,把第三方登录的信息查出来,然后转移到以前的用户上*/
                switch ($info['code']) {
                    case 'wechat':
                        $add['u_wechat_openid'] = $info['openid'];
                        break;
                    case 'qq':
                        $add['u_qq_openid'] = $info['openid'];
                        break;
                    case 'weibo':
                        $add['u_weibo_openid'] = $info['openid'];
                        break;
                    default:
                        return json(format('', 231, "参数传输有误噢!~"));
                        break;
                }

                /*添加用户*/

                $id = $this->u->save($add,$wheres);
                if ($id > 0) {
                    return json(format('', 200, "绑定成功!~"));
                } else {
                    return json(format('', 227, "绑定失败!~"));
                }
            }else{
                return json(format('', 227, "帐号或密码错误!~"));
            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }

     


}
