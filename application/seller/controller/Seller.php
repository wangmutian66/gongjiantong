<?php
namespace app\seller\controller;

/**
 * 作者：袁中旭
 * 时间：2017-09-27
 * 功能：后台管理员模块
 */
use model\SellerManagerLog as sml; /* 商户管理员日志表 */
use model\SellerManagers as sm; /* 商户管理员 */
use model\SellerPrivilegesGroup as spg; /* 商户管理员分组 */
use model\SellerPrivilegesModules as spm; /* 商户管理员权限 */
use model\SellerPrivilegesModulesDesc as spmd; /* 商户管理员权限描述 */


class seller extends Base
{
    protected $sml; /* 商户管理员日志表 */
    protected $sm;  /* 商户管理员 */
    protected $spg; /* 商户管理员分组 */
    protected $spm; /* 商户管理员权限 */
    protected $spmd; /* 商户管理员权限描述 */

    /**
     * 作者：袁中旭
     * 时间：2017-09-27
     * 功能：继承父类并且实例化该控制器所需的model
     */
    public function __construct()
    {
        parent::__construct();
        /* 商户管理员 */
        $this->sm = new sm();
        /* 商户管理员分组 */
        $this->spg = new spg();
        /* 商户管理员权限 */
        $this->spm = new spm();
        /* 商户管理员权限描述 */
        $this->spmd = new spmd();
        /* 商户管理员日志表 */
        $this->sml = new sml();
    }

    /**
     * 作者：袁中旭
     * 时间：2017-09-27
     * 功能：商户管理员列表
     */

    public function sellerManagerList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["sm_status", "sm_seller_name"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否设置了状态值*/
            if (isset($condition['sm_status']) && ('' !== $condition['sm_status'])) {
                $where['sm_status'] = $condition['sm_status'];
                /*保存查询条件状态*/
                $pageParam['query']['sm_status'] = $condition['sm_status'];
            }
            /*是否接收到名称信息*/
            if (isset($condition['sm_seller_name']) && ('' != $condition['sm_seller_name'])) {
                /*模糊查询*/
                $where['sm_seller_name'] = ['like', "%" . $condition['sm_seller_name'] . "%"];
                $pageParam['query']['sm_seller_name'] = $condition['sm_seller_name'];
            }
        }
        $where['ss_id'] = $this->sm_info['ss_id'];
        $sm_list = $this->sm->getAll($where);
        $this->sellerManagerLog("浏览管理员列表");
        return view(
            "sellerManagerList",
            [
                "data" => $condition, /*查询条件*/
                "list" => $sm_list['data'], /*查询结果*/
                "page" => $sm_list['page'], /*分页和html代码*/
                "lastPage" => $sm_list['lastPage'], /*总页数*/
                "currentPage" => $sm_list['currentPage'], /*当前页码*/
                "total" => $sm_list['total'], /*总条数*/
                "listRows" => $sm_list['listRows'], /*每页显示条数*/
            ]
        );
    }

    public function issllername(){
        /* 接收到post传参 */
        if ($this->request->isPost()) {
            $sm_info = $this->request->only(["sm_seller_name"], "post");
            /* 查询用户名是否存在 */
            $sm_name = $this->sm->getOne(['sm_seller_name' => $sm_info['sm_seller_name']], "sm_seller_name");
            /* 检查用户名是否存在 */
            if (!empty($sm_name)) {
                $this->error("该用户名已被使用!请使用其他用户名!~");
                exit();
            }else{
                return json(format([],200,""));
            }
        }
    }

    /**
     * 作者：袁中旭
     * 时间：2017-10-11
     * 功能：商户管理员添加
     */
    public function sellerManagerAdd()
    {
        /* 获取session中的值 */
        $sm_session = $this->request->session();
        /* 接收到post传参 */
        if ($this->request->isPost()) {
            /* 直接收post传输过来的这几个参数 */

            $sm_info = $this->request->only(["sm_seller_name", "sm_seller_passwd", "sm_seller_passwd_confirm", "sm_status", "spg_id", "sm_start_time", "sm_end_time"], "post");

            /* 查询用户名是否存在 */
            $sm_name = $this->sm->getOne(['sm_seller_name' => $sm_info['sm_seller_name']], "sm_seller_name");
            /* 检查用户名是否存在 */
            if (!empty($sm_name)) {
                $this->error("该用户名已被使用!请使用其他用户名!~");
                exit();
            }
            /* 验证接到的值有没有问题 */
            $rule = array(
                "sm_seller_name" => 'require|max:30',
                "sm_status" => 'require|in:0,1,9',
                "sm_seller_passwd" => 'require|max:20|min:6|confirm',
                "spg_id" => 'number',
                "sm_start_time" => "dateFormat:Y-m-d",
                "sm_end_time" => "dateFormat:Y-m-d",
            );
            $msg = array(
                "sm_seller_name.require" => '亲,用户名是必须要填写的噢!~',
                "sm_seller_name.max" => '用户名最大长度是30位哦!~',
                "sm_seller_name.chsAlpha" => '亲用户名只允许有汉字、字母噢!~',
                "sm_status.require" => '亲,用户状态是必须选择的噢!~',
                "sm_status.in" => '状态值必须是1或者0再或者9噢!~',
                "sm_seller_passwd.require" => '亲,用户密码是必须要填写的噢!~',
                "sm_seller_passwd.max" => '密码长度不能超过20位噢!~',
                "sm_seller_passwd.min" => '密码长度不能小于位噢!~',
                "sm_seller_passwd_confirm.confirm" => '两次密码输入不一致噢!~',
                "spg_id" => '分组信息选择错误,请重试!~',
                "sm_start_time" => "您输入的日期格式不正确噢!~",
                "sm_end_time" => "您输入的日期格式不正确噢!~",
            );

            $data = verify($sm_info, $rule, $msg);
            if ($data['code'] === 1) {
                unset($sm_info['sm_seller_passwd_confirm']);
                /* ip  时间等 */
                $sm_info['sm_registered_ip'] = getIp();
                $sm_info['sm_registered_time'] = time();
                $sm_info['sm_author_id'] = $this->sm_id;

                $sm_info['ss_id'] = $sm_session['sm_info']['ss_id'];
                $sm_info['sr_id'] = $sm_session['sm_info']['sr_id'];

                /* 验证日期是不是空 */
                (isset($sm_info['sm_start_time']) && empty($sm_info['sm_start_time'])) ? $sm_info['sm_start_time'] = 0 : $sm_info['sm_start_time'] = date2time($sm_info['sm_start_time']);
                (isset($sm_info['sm_end_time']) && empty($sm_info['sm_end_time'])) ? $sm_info['sm_end_time'] = 0 : $sm_info['sm_end_time'] = date2time($sm_info['sm_end_time']);
                $sm_info['sm_seller_passwd'] = encryptPasswd($sm_info['sm_seller_passwd']);

                /* 保存 */
                $ret = $this->sm->save($sm_info);
                if ($ret > 0) {
                    $this->sellerManagerLog("添加管理员,添加id为:".$ret);
                    $this->success("添加成功!", url("seller/Seller/sellerManagerList"));
                } else {
                    $this->error("添加失败!");
                }
            } else {
                $this->error($data['msg']);
            }
            exit();
        }
        $spg_list = $this->spg->getList(['spg_author_id' => $this->sm_id]);
        return view("sellerManagerAdd", [
            "spg_list" => $spg_list,
        ]);
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-11
     * 功能：商户管理员修改
     */
    public function sellerManagerEdit($id)
    {
        if (isset($id) && $id > 0) {
            $where["sm_id"] = intval($id);
            /*获取当前管理员信息*/
            $alias = "sm_id, sm_seller_name, sm_seller_passwd, sm_status, spg_id, sm_start_time, sm_end_time";
            $sm_info = $this->sm->getRow($where, $alias);
            if (empty($sm_info)) {
                $this->error("该用户名不存在存在!请联系管理员!~");
            }
            if ($this->request->isPost()) {
                $sm_post = $this->request->only(["sm_seller_name", "sm_seller_passwd", "sm_seller_passwd_confirm", "sm_status", "spg_id", "sm_start_time", "sm_end_time"], "post");
                /*检查用户名是否存在*/
                $sm_seller_name = $this->sm->getOne(['sm_seller_name' => $sm_post['sm_seller_name']], "sm_seller_name");
                if ($sm_info['sm_seller_name'] != $sm_post['sm_seller_name'] && $sm_seller_name == $sm_post['sm_seller_name']) {
                    $this->error("该用户名已存在!请使用其他用户名!~");
                    exit();
                }
                // var_dump($sm_post);exit;
                /*验证接到的值有没有问题*/
                $rule = array(
                    "sm_seller_name" => 'require|max:30',
                    "sm_status" => 'require|in:0,1,9',
                    "sm_seller_passwd" => 'require|max:32|min:6|confirm',
                    "spg_id" => 'number',
                    "sm_start_time" => "dateFormat:Y-m-d",
                    "sm_end_time" => "dateFormat:Y-m-d",
                );
                $msg = array(
                    "sm_seller_name.require" => '亲,用户名是必须要填写的噢!~',
                    "sm_seller_name.max" => '用户名最大长度是30位哦!~',
                    "sm_seller_name.chsAlpha" => '亲用户名只允许有汉字、字母噢!~',
                    "sm_status.require" => '亲,用户状态是必须选择的噢!~',
                    "sm_status.in" => '状态值必须是1或者0再或者9噢!~',
                    "sm_seller_passwd.require" => '亲,用户密码是必须要填写的噢!~',
                    "sm_seller_passwd.max" => '密码长度不能超过20位噢!~',
                    "sm_seller_passwd.min" => '密码长度不能小于位噢!~',
                    "sm_seller_passwd_confirm.confirm" => '两次密码输入不一致噢!~',
                    "spg_id" => '分组信息选择错误,请重试!~',
                    "sm_start_time" => "您输入的日期格式不正确噢!~",
                    "sm_end_time" => "您输入的日期格式不正确噢!~",
                );
                $data = verify($sm_post, $rule, $msg);

                if ($data['code'] === 1) {
                    unset($sm_post['sm_seller_passwd_confirm']);
                    /*验证日期是不是空*/
                    (isset($sm_post['sm_start_time']) && empty($sm_post['sm_start_time'])) ? $sm_post['sm_start_time'] = 0 : $sm_post['sm_start_time'] = date2time($sm_post['sm_start_time']);
                    (isset($sm_post['sm_end_time']) && empty($sm_post['sm_end_time'])) ? $sm_post['sm_end_time'] = 0 : $sm_post['sm_end_time'] = date2time($sm_post['sm_end_time']);
                    $sm_post['sm_seller_passwd'] = ($sm_info['sm_seller_passwd'] == $sm_post['sm_seller_passwd']) ? $sm_post['sm_seller_passwd'] : encryptPasswd($sm_post['sm_seller_passwd']);
                    $ret = $this->sm->save($sm_post, $where);
                    if (false === $ret) {
                        $this->error("修改失败!");
                    } else {
                        $this->sellerManagerLog("修改管理员,添加id为:".$id);
                        if ($this->sm_id == $id) {
                            session(null);
                            cookie(null);
                            $this->success("修改成功!请重新登录!", url("seller/login/index"));
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
            $spg_list = $this->spg->getList(["spg_status" => 1,'spg_author_id' => $this->sm_info['sm_id']], "spg_id,spg_name");
            return view(
                "sellerManagerEdit",
                [
                    "sm_info" => $sm_info,
                    "spg_list" => $spg_list,
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-11
     * 功能：商户管理员信息展示
     */
    public function sellerManagerShow()
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

                $where['sm_id'] = intval($info['id']);

                $sm_info = $this->sm->getRow($where);
                /*如果分组id 大于0 并且不是超级管理员 查询管理员信息*/
                if ($sm_info['spg_id'] > 0 && $sm_info['sm_status'] != 9) {
                    $sm_info['spg_name'] = $this->spg->getOne(["spg_id" => $sm_info['spg_id']], "spg_name");
                } else {
                    $sm_info['spg_name'] = "无分组";
                }

                /*如果添加人的id大0 查询添加人信息*/
                if ($sm_info['sm_author_id'] > 0) {
                    $sm_info['sm_author'] = $this->sm->getOne(["sm_id" => $sm_info['sm_author_id']], "sm_seller_name");
                } else {
                    $sm_info['sm_author'] = "无添加人";
                }

                /*如果时间都等于0说明永久有效*/
                (($sm_info['sm_start_time'] == 0) && ($sm_info['sm_end_time'] == 0)) ? $sm_info['sm_time'] = "永久" : $sm_info['sm_time'] = date2time($sm_info['sm_start_time']) . " 至 " . date2time($sm_info['sm_end_time']);

                /*时间及ip转换*/
                $sm_info['sm_registered_time'] = time2date($sm_info['sm_registered_time']);
                $sm_info['sm_last_time'] = time2date($sm_info['sm_last_time']);
                $sm_info['sm_registered_ip'] = long2ip($sm_info['sm_registered_ip']);
                $sm_info['sm_last_ip'] = long2ip($sm_info['sm_last_ip']);
                /*状态转换*/
                if ($sm_info['sm_status'] == 0) {
                    $sm_info['sm_status'] = "禁用";
                } elseif ($sm_info['sm_status'] == 1) {
                    $sm_info['sm_status'] = "普通管理员";
                } else {
                    $sm_info['sm_status'] = "超级管理员";
                }
                $this->sellerManagerLog("查看管理员" . $sm_info['sm_seller_name'] . "信息");
                return json(format($sm_info, '1', $data['msg']));
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-11
     * 功能：商户管理员删除
     */
    public function sellerManagerDel()
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
                $where['sm_id'] = intval($info['id']);
                /*查询管理员表要是有数据不允许删除*/
                $sm_id = $this->sm->getOne($where, "sm_id");
                if ($sm_id < 0) {
                    return json(format('', '-1', '删除失败~!该用户不存在噢~!'));
                } else if ($sm_id == 1) {
                    return json(format('', '-1', '改管理员为保护管理员,不能删除噢~!'));
                } else {
                    /*删除spg表数据*/
                    $ret_sm = $this->sm->del($where);
                    if (false === $ret_sm) {
                        return json(format('', '-1', '删除失败~!请稍候重试~!'));
                    } else {
                        $sm_name = $this->sm->getOne($where, "sm_seller_name");
                        $this->sellerManagerLogList("删除" . $sm_name . "管理员");
                        return json(format('', '1', '删除成功~!'));
                    }
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }

    /**
     * 作者：袁中旭
     * 时间：2017-09-28
     * 功能：商户管理员分组信息列表
     */
    public function sellerPrivilegesGroupList()
    {
        /* 接收到的数据 */
        $condition = $this->request->only(["spg_status", "spg_name"], "get");

        /* 保存查询信息 */
        $pageParam = [];
        /* 查询套件 */
        $where = [];
        if (is_array($condition)) {
            /* 是否设置了状态值 */
            if (isset($condition['spg_status']) && ('' !== $condition['spg_status'])) {
                $where['spg.spg_status'] = $condition['spg_status'];
                /* 保存查询条件状态 */
                $pageParam['query']['spg_status'] = $condition['spg_status'];
            }
            /* 是否接收到名称信息 */
            if (isset($condition['spg_name']) && ('' != $condition['spg_name'])) {
                /* 模糊查询 */
                $where['spg.spg_name'] = ['like', "%" . $condition['spg_name'] . "%"];
                $pageParam['query']['spg_name'] = $condition['spg_name'];
            }
        }
        $where['spg.ss_id'] = $this->sm_info['ss_id'];

        /* 查询添加人和修改人 */
        $join = [
            ["gjt_seller_managers sm", "spg.spg_author_id = sm.sm_id"],
            ["gjt_seller_managers sms", "spg.spg_editor_id = sms.sm_id"],
        ];
        $field = "spg.*,sm.sm_seller_name as spg_author_name ,sms.sm_seller_name as spg_editor_name";

        /* 获取查询数据 */
        $spg_list = $this->spg->joinGetAll($join, $alias = "spg", $where, $pageParam, [], $page_size = 0, $field);
        $this->sellerManagerLogList("查看分组信息");
        /* 返回视图 */
        return view("sellerPrivilegesGroupList", [
            "data" => $condition, /* 查询条件 */
            "list" => $spg_list['data'], /* 查询结果 */
            "page" => $spg_list['page'], /* 分页和html代码 */
            "lastPage" => $spg_list['lastPage'], /* 总页数 */
            "currentPage" => $spg_list['currentPage'], /* 当前页码 */
            "total" => $spg_list['total'], /* 总条数 */
            "listRows" => $spg_list['listRows'], /* 每页显示条数 */
        ]);
    }
    /**
     * 作者：袁中旭
     * 时间：2017-09-28
     * 功能：商户管理员分组添加
     */
    public function sellerPrivilegesGroupAdd()
    {
        if ($this->request->isPost()) {
            $spg_info = $this->request->only(['spg_name', 'spg_status', 'spm_ids'], 'post');

            $info = $this->spg->getOne(["spg_name" => $spg_info['spg_name']], "spg_name");
            if (!empty($info)) {
                $this->error("名字重复了噢!~请重新输入!~");
                exit();
            }
            /* 验证接到的值有没有问题 */
            $rule = array(
                "spg_name" => 'require|max:30',
                "spg_status" => 'require|in:0,1',
                "spm_ids" => 'require',
            );
            $msg = array(
                "spg_name.require" => '权限名称是必须要填写的噢!~',
                "spg_name.max" => '分组名称最多不能超过30个字符噢!~',
                "spg_status.require" => '状态必须要选择的噢!~',
                "spg_status.in" => '状态值必须是1或者0噢!~',
                "spm_ids.require" => '权限模块是必须要选择的噢!~',
            );
            $data = verify($spg_info, $rule, $msg);
            if ($data['code'] === 1) {
                $spg_info['spg_registered_time'] = $spg_info['spg_edit_time'] = time();
                $spg_session = $this->request->session();
                $spg_info['spg_author_id'] = $spg_info['spg_editor_id'] = $this->sm_id;
                $spg_info['spm_ids'] = implode(",", $spg_info['spm_ids']);
                $spg_info['ss_id'] = $this->sm_info['ss_id'];

                /* 保存到数据库中,返回刚添加的id */
                $spg = $this->spg->save($spg_info);

                if ($spg > 0) {
                    $this->sellerManagerLogList("添加分组,分组id为:" . $spg);
                    $this->success("添加成功", url("seller/Seller/sellerPrivilegesGroupList"));
                } else {
                    $this->error("添加失败");
                }
            } else {
                $this->error($data['msg']);
            }
            exit();
        }
        $spm_list = $this->spm->getList(['spm_status' => 1]);
        return view("sellerPrivilegesGroupAdd", [
            'spm_list' => $spm_list,
        ]);
    }

    /**
     * 作者：袁中旭
     * 时间：2017-10-10
     * 功能：商户管理员分组信息展示
     */
    public function sellerPrivilegesGroupShow()
    {
        /* 是不是ajax */
        if ($this->request->isAjax()) {

            $info = $this->request->only(['id'], 'post');
            /* 验证接到的值有没有问题 */
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);
            /* 验证返回如果code == 1 说明成功 */
            if ($data['code'] === 1) {
                $where['spg.spg_id'] = intval($info['id']);
                $join = [
                    ["gjt_seller_managers sm", "spg.spg_author_id = sm.sm_id"],
                    ["gjt_seller_managers sms", "spg.spg_editor_id = sms.sm_id"],
                ];
                $alias = "spg";
                $field = "spg.*,sm.sm_seller_name as spg_author_name ,sms.sm_seller_name as spg_editor_name";

                /* 查询数据 */
                $spg_info = $this->spg->joinGetRow($join, $alias, $where, $field);

                $spm_ids = explode(",", $spg_info['spm_ids']);
                if (isset($spg_info) && empty($spg_info)) {
                    return json(format('', '-1', "该分组信息不存在噢!~"));
                }
                /* 获取要显示的分组权限信息 */
                $spm_list = $this->spm->getList();
                foreach ($spm_list as $key => $value) {
                    foreach ($spm_ids as $k => $v) {
                        if ($value['spm_id'] == $v) {
                            $spg_info['spm_info'][] = $spm_list[$key];
                        }
                    }
                }
                /* 转化格式 */
                $spg_info['spg_status'] = ($spg_info['spg_status'] == 1) ? "开启" : "关闭";
                $spg_info['spg_edit_time'] = time2date($spg_info['spg_edit_time']);
                $spg_info['spg_registered_time'] = time2date($spg_info['spg_registered_time']);
                $this->sellerManagerLogList("查看" . $spg_info['spg_name'] . "分组信息");
                return json(format($spg_info, '1', $data['msg']));
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-10
     * 功能：商户管理分组员信息修改
     */
    public function sellerPrivilegesGroupEdit($id)
    {
        /* 判断是有接受到id */
        if (isset($id) && $id > 0) {
            if ($this->request->isPost()) {
                $where['spg_id'] = intval($id);
                /*查询名称*/
                $spg_name = $this->spg->getOne($where, "spg_name");

                /*不为空  说明有这条数据*/
                if (isset($spg_name) && '' != $spg_name) {
                    /*接受发过来的值*/
                    $spg_info = $this->request->only(['spg_name', 'spg_status', 'spm_ids'], 'post');
                    /*查询接收的明称*/
                    $info = $this->spg->getOne(["spg_name" => $spg_info['spg_name']], "spg_name");
                    /*如果 名称不为空 并且 不等于现在的名称 */
                    if (!empty($info) && $spg_name != $info) {
                        $this->error("名字重复了噢!~请重新输入!~");
                        exit();
                    }
                    /*验证接到的值有没有问题*/
                    $rule = array(
                        "spg_name" => 'require|max:30',
                        "spg_status" => 'require|in:0,1',
                        "spm_ids" => 'require',
                    );
                    $msg = array(
                        "spg_name.require" => '权限名称是必须要填写的噢!~',
                        "spg_name.max" => '分组名称最多不能超过30个字符噢!~',
                        "spg_status.require" => '状态必须要选择的噢!~',
                        "spg_status.in" => '状态值必须是1或者0噢!~',
                        "spm_ids.require" => '权限模块是必须要选择的噢!~',
                    );
                    $data = verify($spg_info, $rule, $msg);

                    if ($data['code'] === 1) {
                        /*更新数据*/
                        $spg_info['spg_edit_time'] = time();
                        $spg_info['spg_editor_id'] = $this->sm_id;
                        $spg_info['spm_ids'] = implode(",", $spg_info['spm_ids']);
                        $spg = $this->spg->save($spg_info, $where);
                        if (false !== $spg) {
                            $this->sellerManagerLogList("修改分组信息,分组id为:" . $id);
                            $this->success("修改成功", 'Seller/sellerPrivilegesGroupList');
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
            $where['spg.spg_id'] = intval($id);
            $join = [
                ["gjt_users u", "spg.spg_author_id = u.u_id"],
                ["gjt_users us", "spg.spg_editor_id = us.u_id"],
            ];
            $alias = "spg";
            $field = "spg.spg_id, spg.spg_name, spg.spg_status, spg.spm_ids, u.u_name as spg_author_name, us.u_name as spg_editor_name";
            $spg_info = $this->spg->joinGetRow($join, $alias, $where, $field);

            $spm_ids = explode(",", $spg_info['spm_ids']);
            if (isset($spg_info) && empty($spg_info)) {
                return $this->error('', '-1', "该分组信息不存在噢!~");
            }
            $spm_list = $this->spm->getList();
            foreach ($spm_list as $key => $value) {
                foreach ($spm_ids as $k => $v) {
                    if ($value['spm_id'] == $v) {
                        $spm_list[$key]['checked'] = 1;
                    }
                }
            }
            $s_spginfo = explode(",",$spg_info["spm_ids"]);
        // dump($s_spginfo);die();

            return view("sellerPrivilegesGroupEdit", [
                "spg_info" => $spg_info,
                "spm_list" => $spm_list,
                "s_spginfo" => $s_spginfo,
            ]);
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }

    /**
     * 作者：袁中旭
     * 时间：2017-10-11
     * 功能：商户管理员分组信息删除
     */
    public function sellerPrivilegesGroupDel()
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
                $where['spg_id'] = intval($info['id']);
                /*查询管理员表要是有数据不允许删除*/
                $sm_id = $this->sm->getOne($where,"spg_id");
                if ($sm_id > 0) {
                    return json(format('', '-1', '删除失败~!该分组下面还有用户呢~!'));
                } else {
                    /*删除mpg表数据*/
                    $spg_ret = $this->spg->del($where);
                    $this->sellerManagerLogList("删除分组信息,分组id为:" . $info['id']);
                    if (false === $spg_ret) {
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
     * 作者：袁中旭
     * 时间：2017-09-27
     * 功能：商户管理员权限列表
     */
    public function sellerPrivilegesModulesList()
    {
        /* 接收到的数据 */
        $condition = $this->request->only(["spm_status", "spm_name"], "get");
        /* 保存查询信息 */
        $pageParam = [];
        /* 查询套件 */
        $where = [];
        if (is_array($condition)) {
            /* 是否设置了状态值 */
            if (isset($condition['spm_status']) && ('' !== $condition['spm_status'])) {
                $where['spm_status'] = $condition['spm_status'];
                /* 保存查询条件状态 */
                $pageParam['query']['spm_status'] = $condition['spm_status'];
            }
            /* 是否接收到名称信息 */
            if (isset($condition['spm_name']) && ('' != $condition['spm_name'])) {
                /* 模糊查询 */
                $where['spm_name'] = ['like', "%" . $condition['spm_name'] . "%"];
                $pageParam['query']['spm_name'] = $condition['spm_name'];
            }
        }
        /* 联表查询 */
        $join = [
            ["gjt_seller_privileges_modules_desc spmd", "spm.spm_id = spmd.spm_id"],
        ];
        $alias = "spm";
        /* 获取连表查询数据 */
        $spm_list = $this->spm->joinGetAll($join, $alias, $where, $pageParam);
        $this->sellerManagerLogList("查看管理员权限列表");
        /* 返回视图 */
        return view(
            "sellerPrivilegesModulesList",
            [
                "data" => $condition, /* 查询条件 */
                "list" => $spm_list['data'], /* 查询结果 */
                "page" => $spm_list['page'], /* 分页和html代码 */
                "lastPage" => $spm_list['lastPage'], /* 总页数 */
                "currentPage" => $spm_list['currentPage'], /* 当前页码 */
                "total" => $spm_list['total'], /* 总条数 */
                "listRows" => $spm_list['listRows'], /* 每页显示条数 */
            ]
        );
    }

    /**
     * 作者：袁中旭
     * 时间：2017-09-27
     * 功能：商户管理员权限添加
     */
    public function sellerPrivilegesModulesAdd()
    {
        if ($this->request->isPost()) {
            /* 只接收一下三个值 */
            $spm_info = $this->request->only(['spm_name', 'spm_value', 'spm_status', 'spm_desc'], 'post');
            /* 验证接到的值有没有问题 */
            $rule = array(
                "spm_value" => 'require',
                "spm_desc" => 'require',
            );
            $msg = array(
                "spm_name.require" => '权限名称是必须要填写的噢!~',
                "spm_name.require" => '权限名称最多不能超过30个字符噢!~',
                "spm_value.require" => '权限值是必须要填写的噢!~',
                "spm_desc.require" => '权限描述是必须要填写的噢!~',
            );
            $data = verify($spm_info, $rule, $msg);

            if ($data['code'] === 1) {
                /* 加密 */
                $spm_info['spm_value'] = encryptController(strtolower($spm_info['spm_value']));
                /* 状态 */
                (isset($spm_info['spm_status']) && !empty($spm_info['spm_status']) && $spm_info['spm_status'] == 'on') ? $spm_info['spm_status'] = 1 : $spm_info['spm_status'] = 0;

                $spmd['spm_desc'] = $spm_info['spm_desc'];
                unset($spm_info['spm_desc']);
                /* 保存到数据库中,返回刚添加的id */
                $spmd['spm_id'] = $this->spm->save($spm_info);
                if ($spmd['spm_id'] > 0) {
                    $this->sellerManagerLogList("添加权限信息,权限id为:" . $spmd['spm_id']);
                    /* 添加数据 到spmd表 */
                    $this->spmd->save($spmd);
                    $this->success("添加成功");
                } else {
                    $this->error("添加失败");
                }
            } else {
                $this->error($data['msg']);
            }
            exit;
        }
        return view('sellerPrivilegesModulesAdd');
    }

    /**
     * 作者：袁中旭
     * 时间：2017-09-27
     * 功能：商户管理员权限修改
     */
    public function sellerPrivilegesModulesEdit($id)
    {
        /* 接收get过来的id 这个id是地址栏后面的/id/1 */
        if (isset($id) && $id > 0) {
            /* 联表查询 */
            $join = [
                ["gjt_seller_privileges_modules_desc spmd", "spm.spm_id = spmd.spm_id"],
            ];
            $alias = "spm";
            $where["spm.spm_id"] = intval($id);
            /* 得到数据 */
            $data = $this->spm->joinGetRow($join, $alias, $where);
            /* 判断数据是否为空 要是空的话 说明数据不存在 */
            if (isset($data) && '' != $data) {
                /* 接受post传输的数据 */
                if ($this->request->isPost()) {
                    /* 只接收一下三个值 */
                    $spm_info = $this->request->only(['spm_name', 'spm_value', 'spm_status', 'spm_desc'], 'post');
                    /* 验证接到的值有没有问题 */
                    $rule = array(
                        "spm_value" => 'require',
                        "spm_desc" => 'require',
                    );
                    $msg = array(
                        "spm_name.require" => '权限名称是必须要填写的噢!~',
                        "spm_name.require" => '权限名称最多不能超过30个字符噢!~',
                        "spm_value.require" => '权限值是必须要填写的噢!~',
                        "spm_desc.require" => '权限描述是必须要填写的噢!~',
                    );
                    /* 验证 */
                    $ret_verify = verify($spm_info, $rule, $msg);

                    if ($ret_verify['code'] === 1) {
                        /* 更新是的where条件 */
                        $condition['spm_id'] = intval($id);
                        /* 加密判断是否更改 */
                        if ($spm_info['spm_value'] != $data['spm_value']) {
                            $spm_info['spm_value'] = encryptController(strtolower($spm_info['spm_value']));
                        } else {
                            $spm_info['spm_value'] = $data['spm_value'];
                        }
                        /* 状态 */
                        (isset($spm_info['spm_status']) && !empty($spm_info['spm_status']) && $spm_info['spm_status'] == 'on') ? $spm_info['spm_status'] = 1 : $spm_info['spm_status'] = 0;

                        $spmd['spm_desc'] = $spm_info['spm_desc'];
                        unset($spm_info['spm_desc']);
                        /* 更新数据 返回的影响行数 */
                        $ret_spm = $this->spm->save($spm_info, $condition);
                        if (false !== $ret_spm) {
                            $this->sellerManagerLogList("修改权限信息,权限id为:" . $id);
                            $this->spmd->save($spmd, $condition);
                            $this->success("修改成功");
                        } else {
                            $this->error("修改失败");
                        }
                    } else {
                        /* 验证失败 返回信息 */
                        $this->error($ret_verify['msg']);
                    }
                    exit;
                }
            } else {
                $this->error("平台上没有查到该权限的信息,请稍候再试噢!~");
                exit();
            }
            return view(
                "sellerPrivilegesModulesEdit",
                [
                    "spm_info" => $data,
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }

    /**
     * 作者：袁中旭
     * 时间：2017-09-27
     * 功能：商户管理员权限删除
     */
    public function sellerPrivilegesModulesDel()
    {
        /* 判断是不死ajax请求 */
        if ($this->request->isAjax()) {
            /* 只接收id的值 */
            $info = $this->request->only(['id'], 'post');
            /* 验证接到的值有没有问题 */
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);
            /* 验证返回如果code == 1 说明成功 */
            if ($data['code'] === 1) {
                /* where条件 强制转换整型 */
                $where['spm_id'] = intval($info['id']);
                /* 删除mpm表数据 */
                $spm_ret = $this->spm->del($where);
                /* 删除spmd表数据 */
                $spmd_ret = $this->spmd->del($where);
                if (false === $spm_ret) {
                    return json(format('', '-1', '删除失败~!请稍候重试~!'));
                } else {
                    $this->sellerManagerLogList("删除权限信息,权限id为:" . $info['id']);
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
     * 作者：袁中旭
     * 时间：2017-10-11
     * 功能：商户管理员日志列表
     */
    public function sellerManagerLogList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["sml_name"], "post");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否设置了状态值*/
            if (isset($condition['sml_name']) && ('' !== $condition['sml_name'])) {
                $where['sml_name'] = ['like', "%" . $condition['sml_name'] . "%"];
                /*保存查询条件状态*/
                $pageParam['query']['sml_name'] = $condition['sml_name'];
            }
        }
        $order = ["sml_add_time" => 'desc'];
        /*获取连表查询数据*/
        $sml_list = $this->sml->getAll($where, $pageParam, $order);
        /*返回视图*/
        return view(
            "sellerManagerLogList",
            [
                "data" => $condition, /*查询条件*/
                "list" => $sml_list['data'], /*查询结果*/
                "page" => $sml_list['page'], /*分页和html代码*/
                "lastPage" => $sml_list['lastPage'], /*总页数*/
                "currentPage" => $sml_list['currentPage'], /*当前页码*/
                "total" => $sml_list['total'], /*总条数*/
                "listRows" => $sml_list['listRows'], /*每页显示条数*/
            ]
        );
    }
}
