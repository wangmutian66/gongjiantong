<?php
namespace app\seller\controller;
use think\paginator;
use \think\Controller;

/**
 * 作者：袁中旭
 * 时间：2017-09-27
 * 功能：判断登陆状态    商户后台记录日志
 */
class Base extends Controller
{
    protected $menu;
    protected $spm_list;
    protected $sm_id;
    protected $sm_name;
    protected $sm_info;
    protected $request;
    protected $user_spm_ids;
    public function __construct()
    {
        parent::__construct();
        $this->menu = config('?sellernav') ? config('sellernav') : $this->error("缺少系统配置文件,请联系管理员~!");
        $this->request = request();
        $this->isLogin();
        $this->publicAssign();
        $this->checkManagersPrivileges();
        if (!$this->checkManagersPrivileges()) {
            $this->error("您没有访问权限噢,请联系管理员!~", url("seller/index/welcome"));
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-11
     * 功能：检查管理员权限
     */
    protected function checkManagersPrivileges()
    {
        /*当前控制器*/
        $controller = $this->request->controller();
        /*当前方法*/
        $action = $this->request->action();
        /*如果状态是9 说明是超级管理员*/
        if ($this->sm_info['sm_status'] == 9) {
            return true;
        } else {
            /*判断不是index控制器 不是 index方法或者welcome方法*/
            if ($controller != "Index" && !in_array($action, ["seller", "Welcome"])) {
                /*分组id 大于0*/
                if ($this->sm_info['spg_id'] > 0) {
                    /*加密控制器*/
                    $checked = true;
                    $spm_info = encryptController(strtolower($controller . "@" . $action));
                    foreach ($this->spm_list as $key => $value) {
                        /*循环所有权限 并且 当前控制器加密后的值是否和数据库里面的想的相等*/

                        if ($value['spm_value'] == $spm_info) {
                            /*判断权限id是不是在用户的权限组里面 在返回true 不在  返回false*/
                            if (in_array($value['spm_id'], $this->user_spm_ids)) {
                                $checked = true;
                            } else {
                                $checked = false;
                                break;
                            }
                        }


                    }

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
     * 作者：袁中旭
     * 时间：2017-09-27
     * 功能：检查是否登陆
     */
    protected function isLogin()
    {
        $sm_id = session("?sm_id");
        $sm_name = session("?sm_name");
        if ($sm_id && $sm_name) {
            /*session读取用户信息*/
            $this->sm_id = session("sm_id");
            $this->sm_name = session("sm_name");
            /*session 读取权限信息*/
            $this->sm_info = session("sm_info");

            if (session("?spm_list")) {
                $this->spm_list = session("spm_list");
            }
            if (session("?spm_ids")) {
                $this->user_spm_ids = explode(",", session("spm_ids"));
            }

            return true;
        } else {

            $severurl = "http://".$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"].url("seller/Login/index");
            $domainurl = str_replace("/index.php/index.php","/index.php",$severurl);
            header("Location: ".$domainurl);
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-18
     * 功能：前台公共赋值
     */
    protected function publicAssign()
    {
        $this->assign('index', 'seller/index/index');
        $this->assign("menu_list", $this->menu);
        $this->assign("sm_name", $this->sm_name);
    }
    /**
     * 作者：袁中旭
     * 时间：2017-09-27
     * 功能：商户管理员操作日志
     */
    protected function sellerManagerLog($sml_info)
    {
        if (isset($sml_info) && !empty($sml_info)) {
            $save['sm_id'] = $this->sm_id;
            $save['sml_add_time'] = time();
            $save['ml_ip'] = getIp();
            $save['sml_info'] = $sml_info;
            $save['sml_name'] = $this->sm_name;
            $sml = new \model\SellerManagerLog();
            $ret_info = $sml->save($save);
            if ($ret_info > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-09-27
     * 功能：三级联动公共基类
     */
    public function getRegion()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->only(['id'], 'post');
            if ($post['id'] > 0) {
                $where['r_parent_id'] = intval($post['id']);
                return json(format(getRegion($where), 1, "success"));
            }
            return json(format(getRegion(), 1, "success"));
        } else {
            return json(format('', -1, "程序员都累吐血了,也没有收到任何的数据!~"));
        }
    }
    /**
     * 多个数组的笛卡尔积
     *
     * @param unknown_type $data
     */
    public function combineDika()
    {
        $data = func_get_args();
        $data = current($data);
        $cnt = count($data);

        $result = array();
        $arr1 = array_shift($data);

        foreach ($arr1 as $key => $item) {
            $result[] = array($item);
        }
        foreach ($data as $key => $item) {
            $result = $this->combineArray($result, $item);
        }
        return $result;
    }
    /**
     * 两个数组的笛卡尔积
     * @param unknown_type $arr1
     * @param unknown_type $arr2
     */
    public function combineArray($arr1, $arr2)
    {
        $result = array();
        foreach ($arr1 as $item1) {
            foreach ($arr2 as $item2) {
                $temp = $item1;
                $temp[] = $item2;
                $result[] = $temp;
            }
        }
        return $result;
    }
}
