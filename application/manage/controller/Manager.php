<?php
namespace app\manage\controller;

/**
 * [manager] 后台管理员模块
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-12
 */
use model\Managers as m;
use model\ManagersPrivilegesGroup as mpg;
use model\ManagersPrivilegesModules as mpm;
use model\ManagersPrivilegesModulesDesc as mpmd;
use model\ManageGoodsCategory as mgc;

class Manager extends Base
{
    protected $m; /*管理员*/
    protected $mpg; /*管理员分组*/
    protected $mpm; /*管理员权限*/
    protected $mpmd; /*管理员权限描述*/
    protected $mgc; /*总后台分类*/
    /**
     * [__construct description] 继承父类并且实例化该控制器所需的model
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-12
     */
    public function __construct()
    {
        parent::__construct();
        /*管理员*/
        $this->m = new m();
        /*管理员分组*/
        $this->mpg = new mpg();
        /*管理员权限*/
        $this->mpm = new mpm();
        /*管理员权限描述*/
        $this->mpmd = new mpmd();
        $this->mgc = new mgc(); /*总后台分类*/
    }
    /**
     * 管理员列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-18
     */
    public function managersList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["m_status", "m_name"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否设置了状态值*/
            if (isset($condition['m_status']) && ('' !== $condition['m_status'])) {
                $where['m_status'] = $condition['m_status'];
                /*保存查询条件状态*/
                $pageParam['query']['m_status'] = $condition['m_status'];
            }
            /*是否接收到名称信息*/
            if (isset($condition['m_name']) && ('' != $condition['m_name'])) {
                /*模糊查询*/
                $where['m_name'] = ['like', "%" . $condition['m_name'] . "%"];
                $pageParam['query']['m_name'] = $condition['m_name'];
            }
        }
        $m_list = $this->m->getAll($where);
        $this->managerLog("浏览管理员列表");
        return view(
            "managersList",
            [
                "data" => $condition, /*查询条件*/
                "list" => $m_list['data'], /*查询结果*/
                "page" => $m_list['page'], /*分页和html代码*/
                "lastPage" => $m_list['lastPage'], /*总页数*/
                "currentPage" => $m_list['currentPage'], /*当前页码*/
                "total" => $m_list['total'], /*总条数*/
                "listRows" => $m_list['listRows'], /*每页显示条数*/
            ]
        );
    }
    /**
     * 管理员添加
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-18
     */
    public function managersAdd()
    {
        /*接收到post传参*/
        if ($this->request->isPost()) {
            /*直接收post传输过来的这几个参数*/
            $m_info = $this->request->only(["m_name", "m_passwd", "m_passwd_confirm", "m_status", "mpg_id", "m_start_time", "m_end_time"], "post");
            /*查询用户名是否存在*/
            $m_name = $this->m->getOne(['m_name' => $m_info['m_name']], "m_name");
            /*检查用户名是否存在*/
            if (!empty($m_name)) {
                $this->error("该用户名已存在!请使用其他用户名!~");
                exit();
            }
            /*验证接到的值有没有问题*/
            $rule = array(
                "m_name" => 'require|max:30',
                "m_status" => 'require|in:0,1,2,9',
                "m_passwd" => 'require|max:20|min:6|confirm',
                "mpg_id" => 'number',
                "m_start_time" => "dateFormat:Y-m-d",
                "m_end_time" => "dateFormat:Y-m-d",
            );
            $msg = array(
                "m_name.require" => '亲,用户名是必须要填写的噢!~',
                "m_name.max" => '用户名最大长度是30位哦!~',
                "m_name.chsAlpha" => '亲用户名只允许有汉字、字母噢!~',
                "m_status.require" => '亲,用户状态是必须选择的噢!~',
                "m_status.in" => '状态值选择有误噢!~',
                "m_passwd.require" => '亲,用户密码是必须要填写的噢!~',
                "m_passwd.max" => '密码长度不能超过20位噢!~',
                "m_passwd.min" => '密码长度不能小于位噢!~',
                "m_passwd.confirm" => '两次密码输入不一致噢!~',
                "mpg_id" => '分组信息选择错误,请重试!~',
                "m_start_time" => "您输入的日期格式不正确噢!~",
                "m_end_time" => "您输入的日期格式不正确噢!~",
            );
            $data = verify($m_info, $rule, $msg);
            if ($data['code'] === 1) {
                unset($m_info['m_passwd_confirm']);
                /*ip  时间等*/
                $m_info['m_registered_ip'] = getIp();
                $m_info['m_registered_time'] = time();
                $m_info['m_author_id'] = $this->m_id;
                /*验证日期是不是空*/
                (isset($m_info['m_start_time']) && empty($m_info['m_start_time'])) ? $m_info['m_start_time'] = 0 : $m_info['m_start_time'] = date2time($m_info['m_start_time']);
                (isset($m_info['m_end_time']) && empty($m_info['m_end_time'])) ? $m_info['m_end_time'] = 0 : $m_info['m_end_time'] = date2time($m_info['m_end_time']);
                $m_info['m_passwd'] = encryptPasswd($m_info['m_passwd']);
                /*保存*/
                $ret = $this->m->save($m_info);
                if ($ret > 0) {
                    $this->managerLog("添加管理员");
                    $this->success("添加成功!");
                } else {
                    $this->error("添加失败!");
                }
            } else {
                $this->error($data['msg']);
            }
            exit();
        }
        $mpg_list = $this->mpg->getList();
        return view(
            "managersAdd",
            [
                "mpg_list" => $mpg_list,
            ]
        );
    }
    /**
     * 管理员修改
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-18
     */
    public function managersEdit($id)
    {
        if (isset($id) && $id > 0) {
            $where["m_id"] = intval($id);
            /*获取当前管理员信息*/
            $alias = "m_id, m_name, m_passwd, m_status, mpg_id, m_start_time, m_end_time";
            $m_info = $this->m->getRow($where, $alias);
            if (empty($m_info)) {
                $this->error("该用户名不存在存在!请联系管理员!~");
            }
            if ($this->request->isPost()) {
                $m_post = $this->request->only(["m_name", "m_passwd", "m_passwd_confirm", "m_status", "mpg_id", "m_start_time", "m_end_time"], "post");
                /*检查用户名是否存在*/
                $m_name = $this->m->getOne(['m_name' => $m_post['m_name']], "m_name");
                if ($m_info['m_name'] != $m_post['m_name'] && $m_name == $m_post['m_name']) {
                    $this->error("该用户名已存在!请使用其他用户名!~");
                    exit();
                }
                // dump($m_post);exit;
                /*验证接到的值有没有问题*/
                $rule = array(
                    "m_name" => 'require|max:30',
                    "m_status" => 'require|in:0,1,2,9',
                    "m_passwd" => 'require|max:32|min:6|confirm',
                    "mpg_id" => 'number',
                    "m_start_time" => "dateFormat:Y-m-d",
                    "m_end_time" => "dateFormat:Y-m-d",
                );
                $msg = array(
                    "m_name.require" => '亲,用户名是必须要填写的噢!~',
                    "m_name.max" => '用户名最大长度是30位哦!~',
                    "m_name.chsAlpha" => '亲用户名只允许有汉字、字母噢!~',
                    "m_status.require" => '亲,用户状态是必须选择的噢!~',
                    "m_status.in" => '状态值选择有误噢!~',
                    "m_passwd.require" => '亲,用户密码是必须要填写的噢!~',
                    "m_passwd.max" => '密码长度不能超过20位噢!~',
                    "m_passwd.min" => '密码长度不能小于位噢!~',
                    "m_passwd.confirm" => '两次密码输入不一致噢!~',
                    "mpg_id" => '分组信息选择错误,请重试!~',
                    "m_start_time" => "您输入的日期格式不正确噢!~",
                    "m_end_time" => "您输入的日期格式不正确噢!~",
                );
                $data = verify($m_post, $rule, $msg);
                if ($data['code'] === 1) {
                    unset($m_post['m_passwd_confirm']);
                    /*验证日期是不是空*/
                    (isset($m_post['m_start_time']) && empty($m_post['m_start_time'])) ? $m_post['m_start_time'] = 0 : $m_post['m_start_time'] = date2time($m_post['m_start_time']);
                    (isset($m_post['m_end_time']) && empty($m_post['m_end_time'])) ? $m_post['m_end_time'] = 0 : $m_post['m_end_time'] = date2time($m_post['m_end_time']);
                    $m_post['m_passwd'] = ($m_info['m_passwd'] == $m_post['m_passwd']) ? $m_post['m_passwd'] : encryptPasswd($m_post['m_passwd']);
                    $ret = $this->m->save($m_post, $where);
                    if (false === $ret) {
                        $this->error("修改失败!");
                    } else {
                        $this->managerLog("修改管理员信息");
                        if ($this->m_id == $id) {
                            session(null);
                            cookie(null);
                            $this->success("修改成功!请重新登录!", url("manage/login/index"));
                        } else {
                            $this->success("修改成功!");
                        }
                    }
                } else {
                    $this->error($data['msg']);
                }
                exit();
            }
            /*获取权限分组列表*/
            $mpg_list = $this->mpg->getList(["mpg_status" => 1], "mpg_id,mpg_name");
            return view(
                "managersEdit",
                [
                    "m_info" => $m_info,
                    "mpg_list" => $mpg_list,
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }
    /**
     * 管理员信息展示
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-18
     */
    public function managersShow()
    {
        /*是不是ajax*/
        if ($this->request->isAjax()) {

            $info = $this->request->only(['id'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {

                $where['m_id'] = intval($info['id']);

                $m_info = $this->m->getRow($where);
                /*如果分组id 大于0 并且不是超级管理员 查询管理员信息*/
                if ($m_info['mpg_id'] > 0 && $m_info['m_status'] != 9) {
                    $m_info['mpg_name'] = $this->mpg->getOne(["mpg_id" => $m_info['mpg_id']], "mpg_name");
                } else {
                    $m_info['mpg_name'] = "无分组";
                }
                /*如果添加人的id大0 查询添加人信息*/
                if ($m_info['m_author_id'] > 0) {
                    $m_info['m_author'] = $this->m->getOne(["m_id" => $m_info['m_author_id']], "m_name");
                } else {
                    $m_info['m_author'] = "无添加人";
                }
                /*如果时间都等于0说明永久有效*/
                (($m_info['m_start_time'] == 0) && ($m_info['m_end_time'] == 0)) ? $m_info['m_time'] = "永久" : $m_info['m_time'] = date2time($m_info['m_start_time']) . " 至 " . date2time($m_info['m_end_time']);

                /*时间及ip转换*/
                $m_info['m_registered_time'] = time2date($m_info['m_registered_time']);
                $m_info['m_last_time'] = time2date($m_info['m_last_time']);
                $m_info['m_registered_ip'] = long2ip($m_info['m_registered_ip']);
                $m_info['m_last_ip'] = long2ip($m_info['m_last_ip']);
                /*状态转换*/
                if ($m_info['m_status'] == 0) {
                    $m_info['m_status'] = "禁用";
                } elseif ($m_info['m_status'] == 1) {
                    $m_info['m_status'] = "普通管理员";
                } else {
                    $m_info['m_status'] = "超级管理员";
                }
                $this->managerLog("查看" . $m_info['m_name'] . "信息");
                return json(format($m_info, '1', $data['msg']));
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 管理员删除
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-18
     */
    public function managersDel()
    {
        /*判断是不死ajax请求*/
        if ($this->request->isAjax()) {
            /*只接收id的值*/
            $info = $this->request->only(['id'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require',
            );

            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                /*where条件 强制转换整型*/
                $where['m_id'] = intval($info['id']);
                /*查询管理员表要是有数据不允许删除*/
                $m_id = $this->m->getOne($where, "m_id");
                if ($m_id < 0) {
                    return json(format('', '-1', '删除失败~!该用户不存在噢~!'));
                } else if ($m_id == 1) {
                    return json(format('', '-1', '改管理员为保护管理员,不能删除噢~!'));
                } else {
                    /*删除mpg表数据*/
                    $ret_m = $this->m->del($where);
                    if (false === $mpg_ret) {
                        return json(format('', '-1', '删除失败~!请稍候重试~!'));
                    } else {
                        $m_name = $this->m->getOne($where, "m_name");
                        $this->managerLog("删除" . $m_name . "管理员");
                        return json(format('', '1', '删除成功~!'));
                    }
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }
    /**
     * 分组信息列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-15
     */
    public function managersPrivilegesGroupList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["mpg_status", "mpg_name"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否设置了状态值*/
            if (isset($condition['mpg_status']) && ('' !== $condition['mpg_status'])) {
                $where['mpg_status'] = $condition['mpg_status'];
                /*保存查询条件状态*/
                $pageParam['query']['mpg_status'] = $condition['mpg_status'];
            }
            /*是否接收到名称信息*/
            if (isset($condition['mpg_name']) && ('' != $condition['mpg_name'])) {
                /*模糊查询*/
                $where['mpg_name'] = ['like', "%" . $condition['mpg_name'] . "%"];
                $pageParam['query']['mpg_name'] = $condition['mpg_name'];
            }
        }
        /*查询添加人和修改人人*/
        $join = [
            ["gjt_managers m", "mpg.mpg_author_id = m.m_id"],
            ["gjt_managers ms", "mpg.mpg_editor_id = ms.m_id"],
        ];
        $field = "mpg.*,m.m_name as mpg_author_name ,ms.m_name as mpg_editor_name";
        /*获取查询数据*/
        $mpg_list = $this->mpg->joinGetAll($join, $alias = "mpg", $where, $pageParam, $order = [], $page_size = 0, $field);
        $this->managerLog("查看分组信息");
        /*返回视图*/
        return view(
            "managersPrivilegesGroupList",
            [
                "data" => $condition, /*查询条件*/
                "list" => $mpg_list['data'], /*查询结果*/
                "page" => $mpg_list['page'], /*分页和html代码*/
                "lastPage" => $mpg_list['lastPage'], /*总页数*/
                "currentPage" => $mpg_list['currentPage'], /*当前页码*/
                "total" => $mpg_list['total'], /*总条数*/
                "listRows" => $mpg_list['listRows'], /*每页显示条数*/
            ]
        );
    }
    /**
     * 管理员分组添加
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-15
     */
    public function managersPrivilegesGroupAdd()
    {
        if ($this->request->isPost()) {

            $mpg_info = $this->request->only(['mpg_name', 'mpg_status', 'mpm_ids', 'mpg_class','mpg_ispay'], 'post');

            $info = $this->mpg->getOne(["mpg_name" => $mpg_info['mpg_name']], "mpg_name");
            if (!empty($info)) {
                $this->error("名字重复了噢!~请重新输入!~");
                exit();
            }
            /*验证接到的值有没有问题*/
            $rule = array(
                "mpg_name" => 'require|max:30',
                "mpg_status" => 'require|in:0,1',
                "mpm_ids" => 'require',
            );
            $msg = array(
                "mpg_name.require" => '权限名称是必须要填写的噢!~',
                "mpg_name.max" => '分组名称最多不能超过30个字符噢!~',
                "mpg_status.require" => '状态必须要选择的噢!~',
                "mpg_status.in" => '状态值必须是1或者0噢!~',
                "mpm_ids.require" => '权限模块是必须要选择的噢!~',
            );
            $data = verify($mpg_info, $rule, $msg);
            if ($data['code'] === 1) {
                $mpg_info['mpg_registered_time'] = $mpg_info['mpg_edit_time'] = time();
                $mpg_info['mpg_author_id'] = $mpg_info['mpg_editor_id'] = $this->m_id;
                $mpg_info['mpm_ids'] = implode(",", $mpg_info['mpm_ids']);
                /*保存到数据库中,返回刚添加的id*/
                $mpg = $this->mpg->save($mpg_info);
                if ($mpg > 0) {
                    $this->managerLog("添加分组,分组id为:" . $mpg);
                    $this->success("添加成功");
                } else {
                    $this->error("添加失败");
                }
            } else {
                $this->error($data['msg']);
            }
            exit();
        }
        $mgc = $this->mgc->getAll(["mgc_parent_id" => 0], [], ['mgc_sort' => "desc"]);
        $mpm_list = $this->mpm->getList(['mpm_status' => 1]);
        // dump($mgc);
        return view(
            "managersPrivilegesGroupAdd",
            [
                'mpm_list' => $mpm_list,
                'mgc' => $mgc['data'],
            ]
        );
    }
    /**
     * 分组信息展示
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-15
     */
    public function managersPrivilegesGroupShow()
    {
        /*是不是ajax*/
        if ($this->request->isAjax()) {

            $info = $this->request->only(['id'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                $where['mpg.mpg_id'] = intval($info['id']);
                $join = [
                    ["gjt_managers m", "mpg.mpg_author_id = m.m_id","left"],
                    ["gjt_managers ms", "mpg.mpg_editor_id = ms.m_id","left"],
                    ["gjt_manage_goods_category mgcs", "mpg.mpg_class = mgcs.mgc_id","left"],
                ];
                $alias = "mpg";
                $field = "mpg.*,m.m_name as mpg_author_name ,ms.m_name as mpg_editor_name,mgcs.mgc_name";
                /*查询数据*/
                $mpg_info = $this->mpg->joinGetRow($join, $alias, $where, $field);
                $mpm_ids = explode(",", $mpg_info['mpm_ids']);
                if (isset($mpg_info) && empty($mpg_info)) {
                    return json(format('', '-1', "该分组信息不存在噢!~"));
                }
                /*获取要显示的分组权限信息*/
                foreach ($this->mpm_list as $key => $value) {
                    foreach ($mpm_ids as $k => $v) {
                        if ($value['mpm_id'] == $v) {
                            $mpg_info['mpm_info'][] = $this->mpm_list[$key];
                        }
                    }
                }
                /*转化格式*/
                $mpg_info['mpg_status'] = ($mpg_info['mpg_status'] == 1) ? "开启" : "关闭";
                $mpg_info['mpg_edit_time'] = time2date($mpg_info['mpg_edit_time']);
                $mpg_info['mpg_registered_time'] = time2date($mpg_info['mpg_registered_time']);
                $this->managerLog("查看" . $mpg_info['mpg_name'] . "分组信息");
                return json(format($mpg_info, '1', $data['msg']));
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 分组信息修改
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-15
     */
    public function managersPrivilegesGroupEdit($id)
    {
        /*判断是有接受到id*/
        if (isset($id) && $id > 0) {
            if ($this->request->isPost()) {
                $where['mpg_id'] = intval($id);
                /*查询名称*/
                $mpg_name = $this->mpg->getOne($where, "mpg_name");
                /*不为空  说明有这条数据*/
                if (isset($mpg_name) && '' != $mpg_name) {
                    /*接受发过来的值*/
                    $mpg_info = $this->request->only(['mpg_name', 'mpg_status', 'mpm_ids', 'mpg_class'], 'post');
                    /*查询接收的明称*/
                    $info = $this->mpg->getOne(["mpg_name" => $mpg_info['mpg_name']], "mpg_name");
                    /*如果 名称不为空 并且 不等于现在的名称 */
                    if (!empty($info) && $mpg_name != $info) {
                        $this->error("名字重复了噢!~请重新输入!~");
                        exit();
                    }
                    /*验证接到的值有没有问题*/
                    $rule = array(
                        "mpg_name" => 'require|max:30',
                        "mpg_status" => 'require|in:0,1',
                        "mpm_ids" => 'require',
                    );
                    $msg = array(
                        "mpg_name.require" => '权限名称是必须要填写的噢!~',
                        "mpg_name.max" => '分组名称最多不能超过30个字符噢!~',
                        "mpg_status.require" => '状态必须要选择的噢!~',
                        "mpg_status.in" => '状态值必须是1或者0噢!~',
                        "mpm_ids.require" => '权限模块是必须要选择的噢!~',
                    );
                    $data = verify($mpg_info, $rule, $msg);

                    if ($data['code'] === 1) {
                        /*更新数据*/
                        $mpg_info['mpg_edit_time'] = time();
                        $mpg_info['mpg_editor_id'] = session("m_id");
                        $mpg_info['mpm_ids'] = implode(",", $mpg_info['mpm_ids']);
                        $mpg = $this->mpg->save($mpg_info, $where);
                        if (false !== $mpg) {
                            $this->managerLog("修改分组信息,分组id为:" . $id);
                            $this->success("修改成功");
                        } else {
                            $this->error("修改失败");
                        }
                    } else {
                        $this->error($data['msg']);
                    }

                } else {
                    $this->error("没有您要查找的数据噢!~");
                }
                exit();
            }
            $where['mpg.mpg_id'] = intval($id);
            $join = [
                ["gjt_managers m", "mpg.mpg_author_id = m.m_id"],
                ["gjt_managers ms", "mpg.mpg_editor_id = ms.m_id"],
            ];
            $alias = "mpg";
            $field = "mpg.mpg_id, mpg.mpg_name, mpg.mpg_status, mpg.mpg_class, mpg.mpm_ids, m.m_name as mpg_author_name, ms.m_name as mpg_editor_name,mpg.mpg_ispay";
            $mpg_info = $this->mpg->joinGetRow($join, $alias, $where, $field);
            $mpm_ids = explode(",", $mpg_info['mpm_ids']);
            if (isset($mpg_info) && empty($mpg_info)) {
                return $this->error('', '-1', "该分组信息不存在噢!~");
            }
            foreach ($this->mpm_list as $key => $value) {
                foreach ($mpm_ids as $k => $v) {
                    if ($value['mpm_id'] == $v) {
                        $this->mpm_list[$key]['checked'] = 1;
                    }
                }
            }
            $mgc = $this->mgc->getAll(["mgc_parent_id" => 0], [], ['mgc_sort' => "desc"]);
            // dump($mpg_info);exit();
            return view(
                "managersPrivilegesGroupEdit",
                [
                    "mgc" => $mgc['data'],
                    "mpg_info" => $mpg_info,
                    "mpm_list" => $this->mpm_list,
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }
    /**
     * 删除分组信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-15
     * @return [type]            [description]
     */
    public function managersPrivilegesGroupDel()
    {
        /*判断是不死ajax请求*/
        if ($this->request->isAjax()) {
            /*只接收id的值*/
            $info = $this->request->only(['id'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                /*where条件 强制转换整型*/
                $where['mpg_id'] = intval($info['id']);
                /*查询管理员表要是有数据不允许删除*/
                $m_id = $this->m->getOne($where, "m_id");
                if ($m_id > 0) {
                    return json(format('', '-1', '删除失败~!该分组下面还有用户呢~!'));
                } else {
                    /*删除mpg表数据*/
                    $mpg_ret = $this->mpg->del($where);
                    $this->managerLog("删除分组信息,分组id为:" . $info['id']);
                    if (false === $mpg_ret) {
                        return json(format('', '-1', '删除失败~!请稍候重试~!'));
                    } else {
                        return json(format('', '1', '删除成功~!'));
                    }
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }
    /**
     * 管理员权限列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-12
     */
    public function managersPrivilegesModulesList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["mpm_status", "mpm_name"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否设置了状态值*/
            if (isset($condition['mpm_status']) && ('' !== $condition['mpm_status'])) {
                $where['mpm_status'] = $condition['mpm_status'];
                /*保存查询条件状态*/
                $pageParam['query']['mpm_status'] = $condition['mpm_status'];
            }
            /*是否接收到名称信息*/
            if (isset($condition['mpm_name']) && ('' != $condition['mpm_name'])) {
                /*模糊查询*/
                $where['mpm_name'] = ['like', "%" . $condition['mpm_name'] . "%"];
                $pageParam['query']['mpm_name'] = $condition['mpm_name'];
            }
        }
        /*联表查询*/
        $join = [
            ["gjt_managers_privileges_modules_desc mpmd", "mpm.mpm_id = mpmd.mpm_id"],
        ];
        $alias = "mpm";
        /*获取连表查询数据*/
        $mpm_list = $this->mpm->joinGetAll($join, $alias, $where, $pageParam);
        $this->managerLog("查看管理员权限列表");
        /*返回视图*/
        return view(
            "managersPrivilegesModulesList",
            [
                "data" => $condition, /*查询条件*/
                "list" => $mpm_list['data'], /*查询结果*/
                "page" => $mpm_list['page'], /*分页和html代码*/
                "lastPage" => $mpm_list['lastPage'], /*总页数*/
                "currentPage" => $mpm_list['currentPage'], /*当前页码*/
                "total" => $mpm_list['total'], /*总条数*/
                "listRows" => $mpm_list['listRows'], /*每页显示条数*/
            ]
        );
    }
    /**
     * 管理员权限添加
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-12
     */
    public function managersPrivilegesModulesAdd()
    {
        /*dump($this->mpm);exit();*/
        if ($this->request->isPost()) {
            /*只接收一下三个值*/
            $mpm_info = $this->request->only(['mpm_name', 'mpm_value', 'mpm_status', 'mpm_desc'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "mpm_value" => 'require',
                "mpm_desc" => 'require',
            );
            $msg = array(
                "mpm_name.require" => '权限名称是必须要填写的噢!~',
                "mpm_name.require" => '权限名称最多不能超过30个字符噢!~',
                "mpm_value.require" => '权限值是必须要填写的噢!~',
                "mpm_desc.require" => '权限描述是必须要填写的噢!~',
            );
            $data = verify($mpm_info, $rule, $msg);

            if ($data['code'] === 1) {
                /*加密*/
                $mpm_info['mpm_value'] = encryptController(strtolower($mpm_info['mpm_value']));
                /* 状态 */
                (isset($mpm_info['mpm_status']) && !empty($mpm_info['mpm_status']) && $mpm_info['mpm_status'] == 'on') ? $mpm_info['mpm_status'] = 1 : $mpm_info['mpm_status'] = 0;

                $mpmd['mpm_desc'] = $mpm_info['mpm_desc'];
                unset($mpm_info['mpm_desc']);
                /*保存到数据库中,返回刚添加的id*/
                $mpmd['mpm_id'] = $this->mpm->save($mpm_info);
                if ($mpmd['mpm_id'] > 0) {
                    $this->managerLog("添加权限信息,权限id为:" . $mpmd['mpm_id']);
                    /*添加数据 到mpmd表*/
                    $this->mpmd->save($mpmd);
                    $this->success("添加成功");
                } else {
                    $this->error("添加失败");
                }
            } else {
                $this->error($data['msg']);
            }
            exit;
        }
        return view('managersPrivilegesModulesAdd');
    }
    /**
     * 管理员权限修改
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-1
     */
    public function managersPrivilegesModulesEdit($id)
    {
        /*接收get过来的id 这个id是地址栏后面的/id/1 */
        if (isset($id) && $id > 0) {
            /*联表查询*/
            $join = [
                ["gjt_managers_privileges_modules_desc mpmd", "mpm.mpm_id = mpmd.mpm_id"],
            ];
            $alias = "mpm";
            $where["mpm.mpm_id"] = intval($id);
            /*得到数据*/
            $data = $this->mpm->joinGetRow($join, $alias, $where);
            /*判断数据是否为空 要是空的话 说明数据不存在*/
            if (isset($data) && '' != $data) {
                /*接受post传输的数据*/
                if ($this->request->isPost()) {
                    /*只接收一下三个值*/
                    $mpm_info = $this->request->only(['mpm_name', 'mpm_value', 'mpm_status', 'mpm_desc'], 'post');
                    /*dump($mpm_info);exit;*/
                    /*验证接到的值有没有问题*/
                    $rule = array(
                        "mpm_value" => 'require',
                        "mpm_desc" => 'require',
                    );
                    $msg = array(
                        "mpm_name.require" => '权限名称是必须要填写的噢!~',
                        "mpm_name.require" => '权限名称最多不能超过30个字符噢!~',
                        "mpm_value.require" => '权限值是必须要填写的噢!~',
                        "mpm_desc.require" => '权限描述是必须要填写的噢!~',
                    );
                    /*验证*/
                    $ret_verify = verify($mpm_info, $rule, $msg);

                    if ($ret_verify['code'] === 1) {
                        /*更新是的where条件*/
                        $condition['mpm_id'] = intval($id);
                        /*加密判断是否更改*/
                        if ($mpm_info['mpm_value'] != $data['mpm_value']) {
                            $mpm_info['mpm_value'] = encryptController(strtolower($mpm_info['mpm_value']));
                        } else {
                            $mpm_info['mpm_value'] = $data['mpm_value'];
                        }
                        /* 状态 */
                        (isset($mpm_info['mpm_status']) && !empty($mpm_info['mpm_status']) && $mpm_info['mpm_status'] == 'on') ? $mpm_info['mpm_status'] = 1 : $mpm_info['mpm_status'] = 0;

                        $mpmd['mpm_desc'] = $mpm_info['mpm_desc'];
                        unset($mpm_info['mpm_desc']);
                        /*更新数据 返回的影响行数*/
                        $ret_mpm = $this->mpm->save($mpm_info, $condition);
                        if (false !== $ret_mpm) {
                            $this->managerLog("修改权限信息,权限id为:" . $id);
                            $this->mpmd->save($mpmd, $condition);
                            $this->success("修改成功");
                        } else {
                            $this->error("修改失败");
                        }
                    } else {
                        /*验证失败 返回信息*/
                        $this->error($ret_verify['msg']);
                    }
                    exit;
                }
            } else {
                $this->error("平台上没有查到该权限的信息,请稍候再试噢!~");
                exit();
            }
            return view(
                "managersPrivilegesModulesEdit",
                [
                    "mpm_info" => $data,
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }
    /**
     * 管理员权限删除
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-14
     * @return [json]
     */
    public function managersPrivilegesModulesDel()
    {
        /*判断是不死ajax请求*/
        if ($this->request->isAjax()) {
            /*只接收id的值*/
            $info = $this->request->only(['id'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                /*where条件 强制转换整型*/
                $where['mpm_id'] = intval($info['id']);
                /*删除mpm表数据*/
                $mpm_ret = $this->mpm->del($where);
                /*删除mpmd表数据*/
                $mpmd_ret = $this->mpmd->del($where);
                if (false === $mpm_ret) {
                    return json(format('', '-1', '删除失败~!请稍候重试~!'));
                } else {
                    $this->managerLog("删除权限信息,权限id为:" . $info['id']);
                    return json(format('', '1', '删除成功~!'));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 管理员日志列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-19
     */
    public function managerLogList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["m_name"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否设置了状态值*/
            if (isset($condition['m_name']) && ('' !== $condition['m_name'])) {
                $where['m_name'] = $condition['m_name'];
                /*保存查询条件状态*/
                $pageParam['query']['m_name'] = $condition['m_name'];
            }
        }
        $ml = new \model\ManagersLog();

        $order = ["ml_add_time" => 'desc'];
        /*获取连表查询数据*/
        $ml_list = $ml->getAll($where, $pageParam, $order);
        /*返回视图*/
        return view(
            "managerLogList",
            [
                "data" => $condition, /*查询条件*/
                "list" => $ml_list['data'], /*查询结果*/
                "page" => $ml_list['page'], /*分页和html代码*/
                "lastPage" => $ml_list['lastPage'], /*总页数*/
                "currentPage" => $ml_list['currentPage'], /*当前页码*/
                "total" => $ml_list['total'], /*总条数*/
                "listRows" => $ml_list['listRows'], /*每页显示条数*/
            ]
        );
    }
}
