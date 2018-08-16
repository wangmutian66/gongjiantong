<?php
namespace app\manage\controller;

/**
 * [Login description] 后台登录登录
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-12
 */
use think\Controller;
use \model\Managers;

class Login extends Controller
{
    /**
     * [Login description] 登录展示页面
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-12
     */
    public function Index()
    {
        return view(
            'index',
            [
                'title' => "工建通-平台管理登录",
                'company' => "黑龙江特讯科技有限责任公司",
            ]
        );
    }
    /**
     * [doLogin description] 登录操作
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-12
     */
    public function doLogin()
    {
        $request = request();
        if ($request->isAjax()) {
            /* 只接受这两个字段的值 */
            $m_info = $request->only(['m_name', 'm_passwd'], 'post');
            $rule = [
                'm_name' => 'require|max:25|chsDash',
                'm_passwd' => 'require|max:25',
            ];
            $msg = [
                'm_name.require' => '您没有填写用户名噢!~',
                'm_name.chsDash' => '用户名只能是汉字、字母、数字和下划线及破折号噢!~',
                'm_name.max' => '用户名最多不能超过25个字符噢!~',
                'm_passwd.require' => '您没有填写密码噢!~',
                'm_passwd.max' => '密码最多不能超过25个字符噢!~',
            ];
            /* 校验接收到值 */
            $data = verify($m_info, $rule, $msg);
            /* 有值 */
            if ($data['code']) {
                /* 实例化 manager model*/
                $manager = new Managers();
                $where = ['m_name' => $m_info['m_name']];
                /* 获取单条 */
                $ret_info = $manager->getRow($where);
                if (!empty($ret_info)) {
                    if ($ret_info['m_passwd'] != encryptPasswd($m_info['m_passwd'])) {
                        return json(format('', "-1", '您的密码输入有误噢!~'));
                    } else if ($ret_info['m_start_time'] != 0 && $ret_info['m_end_time'] > time()) {
                        $this->error("当前管理员账号还未启用,请联系超级管理员!~");
                    } else if ($ret_info['m_end_time'] != 0 && $ret_info['m_end_time'] < time()) {
                        $this->error("当前管理员账号已到期,请联系超级管理员!~");
                    } else if ($ret_info['m_status'] == 0) {
                        $this->error("当前管理员账号已被禁用,请联系超级管理员!~");
                    } else {
                        $saveData = array(
                            "m_last_ip" => getIp(),
                            "m_last_time" => time(),
                        );
                        $condition = [
                            "m_id" => $ret_info['m_id'],
                        ];
                        /* 存入最后登录时间 */
                        $manager->save($saveData, $condition);
                        $mpm = new \model\ManagersPrivilegesModules();
                        $mpm_list = $mpm->getList(["mpm_status" => '1'], 'mpm_name,mpm_id,mpm_value');
                        if (isset($ret_info['mpg_id']) && $ret_info['mpg_id'] > 0) {
                            $mpg = new \model\ManagersPrivilegesGroup();
                            $mpm_ids = $mpg->getOne(['mpg_id' => $ret_info['mpg_id']], "mpm_ids");
                            session("mpm_ids", $mpm_ids);
                        }
                        session("mpm_list", $mpm_list);
                        session("m_id", $ret_info['m_id']);
                        session("m_name", $ret_info['m_name']);
                        session("m_info", $ret_info);
                        cookie("m_id", $ret_info['m_id'], 3600);
                        cookie("m_name", $ret_info['m_name'], 3600);
                        $save['m_id'] = session('m_id');
                        $save['ml_add_time'] = time();
                        $save['ml_ip'] = getIp();
                        $save['ml_info'] = "管理员" . $ret_info['m_name'] . "登录";
                        $save['m_name'] = session('m_name');
                        $ml = new \model\ManagersLog();
                        $ret_info = $ml->save($save);
                        return json(format('', 1, '登录成功!'));
                    }
                } else {
                    return json(format('', "-1", '平台中没有这个管理员噢!~'));
                }
            } else {
                /* 没有值 */
                return json(format('', "-1", $data['msg']));
            }
        } else {
            return json(format('', "-1", '抱歉,我没收到任何信息哦!~'));
        }
    }
    public function logout()
    {
        session(null);
        cookie(null);
        $this->redirect(url("manage/login/index"));
    }
}
