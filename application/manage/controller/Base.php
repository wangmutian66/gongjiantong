<?php
namespace app\manage\controller;

use \think\Controller;

/**
 * 平台后台基类
 * 主要功能 :  判断登录状态
 *             公共前台赋值
 *             验证权限
 *             管理员日志等
 *
 */
class Base extends Controller
{
    protected $menu;
    protected $mpm_list;
    protected $m_id;
    protected $m_name;
    protected $m_info;
    protected $request;
    protected $user_mpm_ids;
    public function __construct()
    {
        parent::__construct();
        $this->menu = config('?managenav') ? config('managenav') : $this->error("缺少系统配置文件,请联系管理员~!");
        $this->request = request();
        $this->isLogin();
        $this->publicAssign();
        // dump($this->checkManagersPrivileges());die();
        if (!$this->checkManagersPrivileges()) {
            $this->error("您没有访问权限噢,请联系管理员!~", url("manage/index/welcome"));
        }



    }
    /**
     * [checkManagersPrivileges description] 检查管理员权限
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-11
     * @return [bool]
     */
    protected function checkManagersPrivileges()
    {
        $checked = true;
        /*当前控制器*/
        $controller = $this->request->controller();
        /*当前方法*/
        $action = $this->request->action();
        /*如果状态是9 说明是超级管理员*/
        if ($this->m_info['m_status'] == 9) {
            return true;
        } else if ($this->m_info['m_status'] == 2) {
            $this->error("您无权登陆",url('manage/Login/logout'));
        } else {
            /*判断不是index控制器 不是 index方法或者welcome方法*/
            if ($controller != "index" && !in_array($action, ["index", "welcome"])) {
                /*分组id 大于0*/
                if ($this->m_info['mpg_id'] > 0) {
                    /*加密控制器*/
                    $mpm_info = md5(strtolower($controller) . strtolower($action));
                    foreach ($this->mpm_list as $key => $value) {
                        /*循环所有权限 并且 当前控制器加密后的值是否和数据库里面的想的相等*/
                        // dump(session("mpm_list"));
                        // var_dump($value['mpm_value']);
                        // echo "<br/>";
                        // var_dump($mpm_info);
                        // echo "<br/>";
                        // var_dump($value['mpm_value'] == $mpm_info);
                        // echo "<hr>";

                        if ($value['mpm_value'] == $mpm_info) {
                            /*判断权限id是不是在用户的权限组里面 在返回true 不在  返回false*/

                            if (in_array($value['mpm_id'], $this->user_mpm_ids)) {
                                $checked = true;
                            } else {
                                $checked = false;
                                break;
                            }

                        }
                        // else{
                        //     $checked = false;
                        //     break;
                        // }

                    }
                    // var_dump($checked);
                    // exit();
                    return $checked;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
    }
    /**
     * [isLogin description] 检查是否登录
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-11
     * @return boolean
     */
    protected function isLogin()
    {
        $m_id = session("?m_id");
        $m_name = session("?m_name");
        if ($m_id && $m_name) {
            /*session读取用户信息*/
            $this->m_id = session("m_id");
            $this->m_name = session("m_name");
            /*session 读取权限信息*/
            $this->m_info = session("m_info");
            if (session("?mpm_list")) {
                $this->mpm_list = session("mpm_list");
            }
            if (session("?mpm_ids")) {
                $this->user_mpm_ids = explode(",", session("mpm_ids"));
            }
            return true;
        } else {
            $this->error("亲,您没有登录哦!请先登录!~", url("manage/login/index"));
        }
    }
    /**
     * [publicAssign description] 前台公共赋值
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-11
     * @return [type]            [description]
     */
    protected function publicAssign()
    {
        $this->assign('index', 'manage/index/index');
        $this->assign("menu_list", $this->menu);
        $this->assign("m_name", $this->m_name);
    }
    /**
     * [managerLog description] 管理员操作日志
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-11
     * @return [bool]
     */
    protected function managerLog($ml_info)
    {
        if (isset($ml_info) && !empty($ml_info)) {
            $save['m_id'] = session('m_id');
            $save['ml_add_time'] = time();
            $save['ml_ip'] = getIp();
            $save['ml_info'] = $ml_info;
            $save['m_name'] = session('m_name');
            $ml = new \model\ManagersLog();
            $ret_info = $ml->save($save);
            if ($ret_info > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public function getRegion()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->only(['id'], 'post');
            if (isset($post['id']) && ($post['id'] > 0)) {
                $where['r_parent_id'] = intval($post['id']);
                return json(format(getRegion($where), 1, "success"));
            }
            return json(format(getRegion(), 1, "success"));
        } else {
            return json(format('', -1, "程序员都累吐血了,也没有收到任何的数据!~"));
        }
    }

    

}
