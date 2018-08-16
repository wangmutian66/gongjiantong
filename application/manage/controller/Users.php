<?php
namespace app\manage\controller;

use model\Goods; /*用户基本信息表*/
use model\Region as r; /*历史浏览表*/
use model\SellerShop; /*用户证书表*/
use model\UserAccountLog as ual; /*用户行业表*/
use model\UserBrowsingHistory as ubh; /*用户投递求职表*/
use model\UserCertificate as uc; /*学习经历表*/
use model\UserIndustry as ui; /*用户等级表*/
use model\UserJobSearch as ujs; /*用户接收消息表*/
use model\UserLearningExperience as ule; /*用户举报表*/
use model\UserLevel as ul; /*用户简历表*/
use model\UserReceiveMessage as urm; /*后台发送给用户信息表*/
use model\UserReportInfo as uri; /*用户收货地址表*/
use model\UserResume as ur; /*用户技能表*/
use model\Users as u; /*用户工作经历表*/
use model\UserSendMessage as usm; /*用户技能表*/
use model\UserShippingAddress as usa; /*用户工作经历表*/
use model\UserSkills as us; /*地区表*/
use model\UserWorkExperience as uwe;
use model\InstantMessaging as im;
use model\Users as uss;
use model\SellerManagers as sm;
use model\SellerShop as ss;
use model\Complain as co;
use model\Feedback as fb;
/**
 * [manager] 用户管理users
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-29
 */
class Users extends Base
{
    protected $u; /*用户基本信息表*/
    protected $ubh; /*历史浏览表*/
    protected $uc; /*用户证书表*/
    protected $ui; /*用户行业表*/
    protected $ujs; /*用户投递求职表*/
    protected $ule; /*学习经历表*/
    protected $ul; /*用户等级表*/
    protected $urm; /*用户接收消息表*/
    protected $uri; /*用户举报表*/
    protected $ur; /*用户简历表*/
    protected $usm; /*后台发送给用户信息表*/
    protected $usa; /*用户收货地址表*/
    protected $us; /*用户技能表*/
    protected $uwe; /*用户工作经历表*/
    protected $r; /*地区表*/
    protected $ual; /*用户资金变动表*/
    protected $uss;
    protected $im;
    protected $sm;
    protected $ss;
    protected $co;
    protected $fb;
    public function __construct()
    {
        parent::__construct();
        /*用户基本信息表*/
        $this->u = new u();
        /*历史浏览表*/
        $this->ubh = new ubh();
        /*用户证书表*/
        $this->uc = new uc();
        /*用户行业表*/
        $this->ui = new ui();
        /*用户投递求职表*/
        $this->ujs = new ujs();
        /*学习经历表*/
        $this->ule = new ule();
        /*用户等级表*/
        $this->ul = new ul();
        /*用户接收消息表*/
        $this->urm = new urm();
        /*用户举报表*/
        $this->uri = new uri();
        /*用户简历表*/
        $this->ur = new ur();
        /*后台发送给用户信息表*/
        $this->usm = new usm();
        /*用户收货地址表*/
        $this->usa = new usa();
        /*用户技能表*/
        $this->us = new us();
        /*用户工作经历表*/
        $this->uwe = new uwe();
        /*地区表*/
        $this->r = new r();
        /*用户资金变动表*/
        $this->ual = new ual();
        $this->uss = new uss();
        $this->im = new im();
        $this->sm = new sm();
        /*商家店铺*/
        $this->ss = new ss();
        /*投诉*/
        $this->co = new co();
        /*反馈*/
        $this->fb = new fb();

    }
    /**
     * 用户列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-30
     */
    public function usersList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["u_name"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = ["u_status" => '1'];
        if (is_array($condition)) {
            /*是否接收到名称信息*/
            if (isset($condition['u_name']) && ('' != $condition['u_name'])) {
                /*模糊查询*/
                $where['u_name|u_mobile'] = ['like', "%" . $condition['u_name'] . "%"];



                $pageParam['query']['u_name|u_mobile'] = $condition['u_name'];
            }
        }
        $u_list = $this->u->getAll($where, $pageParam,["u_id"=>"desc"]);

        $this->managerLog("查看用户列表");
        return view(
            "usersList",
            [
                "data" => $condition, /*查询条件*/
                "list" => $u_list['data'], /*查询结果*/
                "page" => $u_list['page'], /*分页和html代码*/
                "lastPage" => $u_list['lastPage'], /*总页数*/
                "currentPage" => $u_list['currentPage'], /*当前页码*/
                "total" => $u_list['total'], /*总条数*/
                "listRows" => $u_list['listRows'], /*每页显示条数*/
            ]
        );
    }
    /**
     * 添加用户
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-30
     */
    public function usersAdd()
    {
        if ($this->request->isPost()) {
            $user_info = $this->request->only(["u_name", "u_passwd", "u_mobile", "u_email", "u_sex" /*,"u_level_id"*/], 'post');
            $rule = array(
                "u_name" => 'require|max:30',
                "u_passwd" => 'require',
                "u_mobile" => 'require',
                "u_mobile" => ['regex' => '/^1(3|2|4|5|7|8)\d{9}$/'],
                "u_email" => 'require|email',
                "u_sex" => 'require|in:0,1,2',
                /*"u_level_id" => 'require|number',*/
            );
            $msg = array(
                "u_name.require" => '用户名是必须要填写的噢!~',
                "u_name.max" => '用户名最多能填写30个字符',
                "u_passwd.require" => '密码是必须要填写的噢!~',
                "u_mobile.require" => '手机号是必须要填写的噢!~',
                "u_mobile.regex" => '手机号填写有误噢!~',
                "u_email.require" => '邮箱是必须要填写的噢!~',
                "u_email.email" => '邮箱填写有误!~',
                "u_sex.require" => '性别是必须要选择的噢!~',
                "u_sex.in" => '性别选择有误!~',
                /*"u_level_id.require" => '用户等级是必须要选择的哦!~',
            "u_level_id.number" => '用户等级选择有误!~',*/
            );
            $data = verify($user_info, $rule, $msg);
            if ($data['code'] === 1) {
                $user_info['u_passwd'] = encryptPasswd($user_info['u_passwd']);
                $user_info['u_registered_time'] = time();
                $id = $this->u->save($user_info);
                if ($id > 0) {
                    $this->managerLog("添加用户,用户id为:".$id);
                    $this->success("添加成功!", url("manage/Users/usersList"));
                } else {
                    $this->error("添加失败!~");
                }
            } else {
                $this->error($data['msg']);
            }
            exit();
        }
        $ul_list = $this->ul->getList();
        return view(
            "usersAdd",
            [
                'ul_list' => $ul_list,
            ]
        );
    }
    /**
     * 删除用户信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-30
     */
    public function usersDel($id)
    {
        if (isset($id) && $id > 0) {
            $where = ['u_id' => intval($id)];
            $updata['u_status'] = 0;
            $ret = $this->u->save($updata,$where);

            if (false === $ret) {
                $this->error("删除失败!~");
            }else{
                $this->managerLog("删除用户,用户id为:".$id);
                $this->success("删除成功!~");
            }
        }else{
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }
    /**
     * 用户收货地址
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-08
     */
    public function userAddressList($id)
    {
        if (isset($id) && $id > 0) {
            $where = ["u_id" => intval($id)];
            $usa_list = $this->usa->getAll($where);
            if (count($usa_list['data']) > 0) {
                foreach ($usa_list['data'] as $key => $value) {
                    $usa_list['data'][$key]['usa_province'] = $this->r->getOne(['r_id' => $value['usa_province']], "r_name");
                    $usa_list['data'][$key]['usa_city'] = $this->r->getOne(['r_id' => $value['usa_city']], "r_name");
                    $usa_list['data'][$key]['usa_district'] = $this->r->getOne(['r_id' => $value['usa_district']], "r_name");
                }
            }
            $this->managerLog("查看用户收货地址列表");
            return view(
                "userAddressList",
                [
                    "list" => $usa_list['data'], /*查询结果*/
                    "page" => $usa_list['page'], /*分页和html代码*/
                    "lastPage" => $usa_list['lastPage'], /*总页数*/
                    "currentPage" => $usa_list['currentPage'], /*当前页码*/
                    "total" => $usa_list['total'], /*总条数*/
                    "listRows" => $usa_list['listRows'], /*每页显示条数*/
                ]
            );
        } else {
            $this->error("程序员都累到吐血了,也没有接收到任何参数!~");
        }
    }
    /**
     * 用户信息展示与修改用户信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-08Remote Desktop Connection
     */
    public function usersInfo($id)
    {
        if (isset($id) && $id > 0) {
            $where = ["u_id" => intval($id)];
            $info = $this->u->getRow($where);
            if ($this->request->isPost()) {
                $save = $this->request->only(["u_name", "u_passwd", "u_mobile", "u_email", "u_sex"], 'post');
                $rule = array(
                    "u_name" => 'require|max:30',
                    "u_mobile" => 'require',
                    "u_mobile" => ['regex' => '/^1(3|2|4|5|7|8)\d{9}$/'],
                    "u_email" => 'require|email',
                    "u_sex" => 'require|in:0,1,2',
                    /*"u_level_id" => 'require|number',*/
                );
                $msg = array(
                    "u_name.require" => '用户名是必须要填写的噢!~',
                    "u_name.max" => '用户名最多能填写30个字符',
                    "u_mobile.require" => '手机号是必须要填写的噢!~',
                    "u_mobile.regex" => '手机号填写有误噢!~',
                    "u_email.require" => '邮箱是必须要填写的噢!~',
                    "u_email.email" => '邮箱填写有误!~',
                    "u_sex.require" => '性别是必须要选择的噢!~',
                    "u_sex.in" => '性别选择有误!~',
                    /*"u_level_id.require" => '用户等级是必须要选择的哦!~',
                "u_level_id.number" => '用户等级选择有误!~',*/
                );
                $data = verify($save, $rule, $msg);
                if ($data['code'] === 1) {
                    if (isset($save['u_passwd']) && empty($save['u_passwd'])) {
                        unset($save['u_passwd']);
                    }
                    if (encryptPasswd($save['u_passwd']) != $info['u_passwd']) {
                        $save['u_passwd'] = encryptPasswd($save['u_passwd']);
                    }
                    $id = $this->u->save($save, $where);
                    if ($id > 0) {
                        $this->managerLog("修改用户信息,用户id为:".$id);
                        $this->success("修改成功!", url("manage/Users/usersList"));
                    } else {
                        $this->error("修改失败!~");
                    }
                } else {
                    $this->error($data['msg']);
                }
                exit();
            }
            return view(
                "usersInfo",
                [
                    "info" => $info,
                ]
            );
        } else {
            $this->error("程序员都累到吐血了,也没有接收到任何参数!~");
        }
    }
    /**
     * 用户账户资金变动展示
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-08
     */
    public function userMoneyInfo($id)
    {
        if (isset($id) && $id > 0) {
            $where = ["u_id" => intval($id)];
            $list = $this->ual->getAll($where);
            $this->managerLog("查看资金变动列表");
            return view(
                "userMoneyInfo",
                [
                    "id" => $id,
                    "list" => $list['data'], /*查询结果*/
                    "page" => $list['page'], /*分页和html代码*/
                    "lastPage" => $list['lastPage'], /*总页数*/
                    "currentPage" => $list['currentPage'], /*当前页码*/
                    "total" => $list['total'], /*总条数*/
                    "listRows" => $list['listRows'], /*每页显示条数*/
                ]
            );
        } else {
            $this->error("程序员都累到吐血了,也没有接收到任何参数!~");
        }
    }
    /**
     * 资金变动修改
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-08
     */
    public function userMoneyEdit($id)
    {
        if (isset($id) && $id > 0) {
            $where = ["u_id" => intval($id)];
            $info = $this->u->getRow($where, "u_money,u_integral");
            if ($this->request->isPost()) {
                $save = $this->request->only(["u_money_type", "u_money", "u_integral_type", "u_integral", "ual_desc"], "post");
                $rule = array(
                    "u_money_type" => 'require|in:0,1',
                    "u_money" => 'number',
                    "u_integral_type" => 'require|in:0,1',
                    "u_integral" => 'number',
                    "ual_desc" => 'require',
                );
                $msg = array(
                    "u_money_type.require" => '类型必须要选择噢!~',
                    "u_money_type.in" => '类型选择有误噢!~',
                    "u_money.number" => '金额数量必须是正整数',
                    "u_integral_type.require" => '类型必须要选择噢!~',
                    "u_integral_type.in" => '类型选择有误噢!~',
                    "u_integral.number" => '积分数量必须是正整数噢!~',
                    "ual_desc.require" => '描述必须要填写噢!~',
                );
                $data = verify($save, $rule, $msg);
                if ($data['code'] === 1) {
                    if ($save['u_money'] > 0) {
                        if ($save['u_money_type'] == 0) {
                            if ($info['u_money'] <= 0) {
                                $this->error("用户余额不足,不能在减少了!~");
                                exit();
                            }
                            $updata['u_money'] = $info['u_money'] - $save['u_money'];
                            if ($updata['u_money'] < 0) {
                                $this->error("用户余额减少后已经小于0了,不能在减少了!~");
                                exit();
                            }
                        }else if ($save['u_money_type'] == 1) {
                            $updata['u_money'] = $info['u_money'] + $save['u_money'];
                            if ($updata['u_money'] > 99999999) {
                                $this->error('修改后的值过大,总和请不要超过99999999');
                                exit();
                            }
                        }
                        $ual_money_save['ual_type'] = $save['u_money_type'];
                        $ual_money_save['u_money'] = $save['u_money'];
                        $ual_money_save['ual_desc'] = $save['ual_desc'];
                        $ual_money_save['u_id'] = $id;
                        $ual_money_save['change_time'] = time();
                    }
                    if ($save['u_integral'] > 0) {
                        if ($save['u_integral_type'] == 0) {
                            if ($info['u_money'] <= 0) {
                                $this->error("用户积分不足,不能在减少了!~");
                                exit();
                            }
                            $updata['u_integral'] = $info['u_integral'] - $save['u_integral'];
                            if ($updata['u_integral'] < 0) {
                                $this->error("用户积分减少后已经小于0了,不能在减少了!~");
                                exit();
                            }
                        }else if ($save['u_integral_type'] == 1) {
                            $updata['u_integral'] = $info['u_integral'] + $save['u_integral'];
                            if ($updata['u_integral'] > 99999999) {
                                $this->error('修改后的值过大,总和请不要超过99999999');
                                exit();
                            }
                        }
                        $ual_integral_save['ual_type'] = $save['u_integral_type'];
                        $ual_integral_save['u_integral'] = $save['u_integral'];
                        $ual_integral_save['ual_desc'] = $save['ual_desc'];
                        $ual_integral_save['u_id'] = $id;
                        $ual_integral_save['change_time'] = time();
                    }
                    if (isset($ual_integral_save) && !empty($ual_integral_save)) {
                        $this->ual->save($ual_integral_save);
                    }
                    if (isset($ual_money_save) && !empty($ual_money_save)) {
                        $this->ual->save($ual_money_save);
                    }
                    $ret = $this->u->save($updata,$where);
                    if (false !== $ret) {
                        $this->managerLog("修改用户基金,用户id为:".$id);
                        $this->success("修改成功!", url("manage/Users/usersList"));
                    } else {
                        $this->error("修改失败!~");
                    }
                } else {
                    $this->error($data['msg']);
                }
                exit();
            }
            return view("userMoneyEdit", ["id" => $id]);
        } else {
            $this->error("程序员都累到吐血了,也没有接收到任何参数!~");
        }
    }
    /**
     * 用户简历信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-08
     */
    public function userResumeInfo($id)
    {
        if (isset($id) && $id > 0) {
            $where = ["u_id" => intval($id)];
            $this->ur->getList($where);
        } else {
            $this->error("程序员都累到吐血了,也没有接收到任何参数!~");
        }
    }
    /**
     * 用户举报列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-30
     */
    public function userReportIndfoList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["uri_title"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否接收到名称信息*/
            if (isset($condition['uri_title']) && ('' != $condition['uri_title'])) {
                /*模糊查询*/
                $where['uri_title'] = ['like', "%" . $condition['uri_title'] . "%"];
                $pageParam['query']['uri_title'] = $condition['uri_title'];
            }
        }
        $join = [
            ["gjt_users u", "u.u_id = uri.u_id"],
        ];
        $alias = "uri";
        $field = "uri.*,u.u_name";
        $list = $this->uri->joinGetAll($join, $alias, $where, [], [], 0, $field);
        $this->managerLog("查看用户举报列表列表");
        return view(
            "userReportIndfoList",
            [
                "data" => $condition, /*查询条件*/
                "list" => $list['data'], /*查询结果*/
                "page" => $list['page'], /*分页和html代码*/
                "lastPage" => $list['lastPage'], /*总页数*/
                "currentPage" => $list['currentPage'], /*当前页码*/
                "total" => $list['total'], /*总条数*/
                "listRows" => $list['listRows'], /*每页显示条数*/
            ]
        );
    }
    /**
     * 查看和修改举报信息状态
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-30
     */
    public function userReportIndfoEdit($id)
    {
        if (isset($id) && $id > 0) {
            $where = ['uri_id' => intval($id)];
            $join = [
                ["gjt_users u", "u.u_id = uri.u_id"],
            ];
            $field = "uri.*, u.u_name";
            $alias = "uri";
            $info = $this->uri->joinGetRow($join, $alias, $where, $field);
            if ($info['uri_type'] == 1) {
                $goods = new Goods();
                $report_info = $goods->getRow(['g_id' => $info['uri_report_info_id']], 'g_id id,g_name report_info_name');
            } elseif ($info['uri_type'] == 0) {
                $SellerShop = new SellerShop();
                $report_info = $SellerShop->getRow(["ss_id" => $info['uri_report_info_id']], 'ss_id id,ss_name report_info_name');
            }
            if ($this->request->isPost()) {
                $save = $this->request->only(["uri_verify_status"], 'post');
                /*验证接到的值有没有问题*/
                $rule = array(
                    "uri_verify_status" => 'require|in:0,1,2',
                );
                $msg = array(
                    "uri_verify_status.require" => '亲,审核状态是必须要填写的噢!~',
                    "uri_verify_status.max" => '审核状态选择有误噢!~',
                );
                $data = verify($save, $rule, $msg);
                if ($data['code'] === 1) {
                    if ($info['uri_verify_status'] != 0) {
                        $this->error("审核状态已经修改过,无需重新修改");
                        exit();
                    }
                    $save['uri_verify_time'] = time();
                    $save['m_id'] = $this->m_id;
                    $ret = $this->uri->save($save, $where);
                    if (false === $ret) {
                        $this->error("修改失败!~");
                    } else {
                        $this->managerLog("修改举报状态,举报id为:".$id);
                        $this->success("修改成功!~", url('manage/Users/userReportIndfoList'));
                    }
                } else {
                    $this->error($data['msg']);
                }
                exit();
            }
            return view(
                "userReportIndfoEdit",
                [
                    "info" => $info,
                    "report_info" => $report_info,
                ]
            );
        } else {
            $this->error("程序员都累到吐血了,也没有接收到任何参数!~");
        }
    }
    /**
     * 删除举报信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-30
     * @return [type]            [description]
     */
    public function userReportIndfoDel()
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
                $where['uri_id'] = intval($info['id']);
                /*查询管理员表要是有数据不允许删除*/
                $uri_verify_status = $this->uri->getOne($where, "uri_verify_status");
                if ($uri_verify_status == 0) {
                    return json(format('', '-1', '删除失败~!还在审核中噢~!'));
                } else {
                    /*删除mpg表数据*/
                    $ret = $this->uri->del($where);
                    if (false === $ret) {
                        return json(format('', '-1', '删除失败~!请稍候重试~!'));
                    } else {
                        $this->managerLog("删除举报信息,举报信息id为:".$info['id']);
                        return json(format('', '1', '删除成功~!'));
                    }
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }
    /**
     * 用户等级列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-30
     */
    public function userLevelList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["ul_level_name"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否接收到名称信息*/
            if (isset($condition['ul_level_name']) && ('' != $condition['ul_level_name'])) {
                /*模糊查询*/
                $where['ul_level_name'] = ['like', "%" . $condition['ul_level_name'] . "%"];
                $pageParam['query']['ul_level_name'] = $condition['ul_level_name'];
            }
        }
        $list = $this->ul->getAll($where);
        $this->managerLog("查看用户等级列表");
        return view(
            "userLevelList",
            [
                "data" => $condition, /*查询条件*/
                "list" => $list['data'], /*查询结果*/
                "page" => $list['page'], /*分页和html代码*/
                "lastPage" => $list['lastPage'], /*总页数*/
                "currentPage" => $list['currentPage'], /*当前页码*/
                "total" => $list['total'], /*总条数*/
                "listRows" => $list['listRows'], /*每页显示条数*/
            ]
        );
    }
    /**
     * 添加用户等级
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-30
     */
    public function userLevelAdd()
    {
        if ($this->request->isPost()) {
            $add = $this->request->only(["ul_level_name", "ul_need_money", "ul_discount", "ul_desc"], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "ul_level_name" => 'require|max:30',
                "ul_need_money" => 'require|number',
                "ul_discount" => 'require|between:1,100',
            );
            $msg = array(
                "ul_level_name.require" => '亲,等级名是必须要填写的噢!~',
                "ul_level_name.max" => '等级名最大长度是30位哦!~',
                "ul_need_money.require" => '消费金额是必须要填写的噢!~',
                "ul_need_money.number" => '消费金额必须是数字噢!~',
                "ul_discount.require" => '享受折扣是必须填写的噢!~',
                "ul_discount.between" => '享受折扣只能是1-100的正整数噢!~',
            );
            $data = verify($add, $rule, $msg);
            if ($data['code'] === 1) {
                $ul_level_name = $this->ul->getOne(['ul_level_name' => $add['ul_level_name']], 'ul_level_name');
                if (!empty($ul_level_name)) {
                    $this->error("该名称重复,请重新填写!~");
                    exit();
                }
                $id = $this->ul->save($add);
                if ($id > 0) {
                    $this->managerLog("添加用户等级,等级ID为:".$id);
                    $this->success("添加成功!~", url("manage/Users/userLevelList"));
                } else {
                    $this->error("添加失败!~");
                }
            } else {
                $this->error($data['msg']);
            }
            exit();
        }
        return view(
            "userLevelAdd"
        );
    }
    /**
     * 修改用户等级
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-30
     */
    public function userLevelEdit($id)
    {
        if (isset($id) && $id > 0) {
            $where = ["ul_id" => intval($id)];
            $info = $this->ul->getRow($where);
            if ($this->request->isPost()) {
                $save = $this->request->only(["ul_level_name", "ul_need_money", "ul_discount", "ul_desc"], 'post');
                /*验证接到的值有没有问题*/
                $rule = array(
                    "ul_level_name" => 'require|max:30',
                    "ul_need_money" => 'require|number',
                    "ul_discount" => 'require|between:1,100',
                );
                $msg = array(
                    "ul_level_name.require" => '亲,等级名是必须要填写的噢!~',
                    "ul_level_name.max" => '等级名最大长度是30位哦!~',
                    "ul_need_money.require" => '消费金额是必须要填写的噢!~',
                    "ul_need_money.number" => '消费金额必须是数字噢!~',
                    "ul_discount.require" => '享受折扣是必须填写的噢!~',
                    "ul_discount.between" => '享受折扣只能是1-100的正整数噢!~',
                );
                $data = verify($save, $rule, $msg);
                if ($data['code'] === 1) {
                    $ul_level_name = $this->ul->getOne(['ul_level_name' => $save['ul_level_name']], 'ul_level_name');
                    if (!empty($ul_level_name) && ($save['ul_level_name'] != $ul_level_name)) {
                        $this->error("该名称重复,请重新填写!~");
                        exit();
                    }
                    $ret = $this->ul->save($save, $where);
                    if (false !== $ret) {
                        $this->managerLog("修改用户等级,用户等级id为:".$id);
                        $this->success("修改成功!~", url("manage/Users/userLevelList"));
                    } else {
                        $this->error("修改失败!~");
                    }
                } else {
                    $this->error($data['msg']);
                }
                exit();
            }
            return view(
                "userLevelEdit",
                [
                    "info" => $info,
                ]
            );
        } else {
            $this->error("程序员都累到吐血了,也没有接收到任何参数!~");
        }
    }
    /**
     * 删除用户等级
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-30
     */
    public function userLevelDel()
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
                $where['ul_id'] = intval($info['id']);
                /*查询管理员表要是有数据不允许删除*/
                $ul_id = $this->ul->getOne($where, "ul_id");
                if ($ul_id < 0) {
                    return json(format('', '-1', '删除失败~!该信息不存在噢~!'));
                } else {
                    /*删除mpg表数据*/
                    $ret = $this->ul->del($where);
                    if (false === $ret) {
                        return json(format('', '-1', '删除失败~!请稍候重试~!'));
                    } else {
                        $this->managerLog("删除用户等级信息,等级信息id为:".$info['id']);
                        return json(format('', '1', '删除成功~!'));
                    }
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }
    /**
     * 用户行业列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-30
     */
    public function userIndustryList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["ui_name"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否接收到名称信息*/
            if (isset($condition['ui_name']) && ('' != $condition['ui_name'])) {
                /*模糊查询*/
                $where['ui_name'] = ['like', "%" . $condition['ui_name'] . "%"];
                $pageParam['query']['ui_name'] = $condition['ui_name'];
            }
        }
        $list = $this->ui->getAll($where);
        $this->managerLog("查看用户行业列表");
        return view(
            "userIndustryList",
            [
                "data" => $condition, /*查询条件*/
                "list" => $list['data'], /*查询结果*/
                "page" => $list['page'], /*分页和html代码*/
                "lastPage" => $list['lastPage'], /*总页数*/
                "currentPage" => $list['currentPage'], /*当前页码*/
                "total" => $list['total'], /*总条数*/
                "listRows" => $list['listRows'], /*每页显示条数*/
            ]
        );
    }
    /**
     * 用户行业添加
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-30
     */
    public function userIndustryAdd()
    {
        if ($this->request->isPost()) {
            $add = $this->request->only(['ui_name', 'ui_status', 'ui_desc'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "ui_name" => 'require|max:30',
                "ui_status" => 'alpha',
            );
            $msg = array(
                "ui_name.require" => '亲,行业名是必须要填写的噢!~',
                "ui_name.max" => '行业名最大长度是30位哦!~',
                "ui_status.alpha" => '状态选择有误噢!~',
            );
            $data = verify($add, $rule, $msg);
            if ($data['code'] === 1) {
                $ui_id = $this->ui->getOne(['ui_name' => $add['ui_name']], 'ui_id');
                if ($ui_id > 0) {
                    $this->error("该名称重复,请重新填写!~");
                    exit();
                }
                (isset($add['ui_status']) && !empty($add['ui_status']) && $add['ui_status'] == 'on') ? $add['ui_status'] = 1 : $add['ui_status'] = 0;
                $add['ui_add_time'] = time();
                $id = $this->ui->save($add);
                if ($id > 0) {
                    $this->managerLog("添加用户行业,行业id为:".$id);
                    $this->success("添加成功!~", url("manage/Users/userIndustryList"));
                } else {
                    $this->error("添加失败!~");
                }
            } else {
                $this->error($data['msg']);
            }
            exit();
        }
        return view(
            "userIndustryAdd"
        );
    }
    /**
     * 用户行业修改
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-30
     */
    public function userIndustryEdit($id)
    {
        if (isset($id) && $id > 0) {
            $where = ["ui_id" => intval($id)];
            $info = $this->ui->getRow($where);
            if ($this->request->isPost()) {
                $save = $this->request->only(['ui_name', 'ui_status', 'ui_desc'], 'post');
                /*验证接到的值有没有问题*/
                $rule = array(
                    "ui_name" => 'require|max:30',
                    "ui_status" => 'alpha',
                );
                $msg = array(
                    "ui_name.require" => '亲,行业名是必须要填写的噢!~',
                    "ui_name.max" => '行业名最大长度是30位哦!~',
                    "ui_status.alpha" => '状态选择有误噢!~',
                );
                $data = verify($save, $rule, $msg);
                if ($data['code'] === 1) {
                    $ui_name = $this->ui->getOne(['ui_name' => $save['ui_name']], 'ui_name');
                    if (!empty($ui_name) && $ui_name != $save['ui_name']) {
                        $this->error("该名称重复,请重新填写!~");
                        exit();
                    }
                    (isset($save['ui_status']) && !empty($save['ui_status']) && $save['ui_status'] == 'on') ? $save['ui_status'] = 1 : $save['ui_status'] = 0;
                    $ret = $this->ui->save($save, $where);
                    if (false !== $ret) {
                        $this->managerLog("修改行业信息,行业id为".$id);
                        $this->success("修改成功!~", url("manage/Users/userIndustryList"));
                    } else {
                        $this->error("修改失败!~");
                    }
                } else {
                    $this->error($data['msg']);
                }
                exit();
            }
            return view(
                "userIndustryEdit",
                [
                    "info" => $info,
                ]
            );
        } else {
            $this->error("程序员都累到吐血了,也没有接收到任何参数!~");
        }
    }
    /**
     * 删除行业信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-30
     */
    public function userIndustryDel()
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
                $where['ui_id'] = intval($info['id']);
                /*查询管理员表要是有数据不允许删除*/
                $m_id = $this->ui->getOne($where, "ui_id");
                if ($m_id < 0) {
                    return json(format('', '-1', '删除失败~!该信息不存在噢~!'));
                } else {
                    /*删除mpg表数据*/
                    $ret = $this->ui->del($where);
                    if (false === $ret) {
                        return json(format('', '-1', '删除失败~!请稍候重试~!'));
                    } else {
                        $this->managerLog("删除行业信息,行业信息id为:".$info['id']);
                        return json(format('', '1', '删除成功~!'));
                    }
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }
    /**
     * 用户技能列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-30
     */
    public function userSkillsList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["us_name"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否接收到名称信息*/
            if (isset($condition['us_name']) && ('' != $condition['us_name'])) {
                /*模糊查询*/
                $where['us_name'] = ['like', "%" . $condition['us_name'] . "%"];
                $pageParam['query']['us_name'] = $condition['us_name'];
            }
        }
        $list = $this->us->getAll($where);
        $this->managerLog("查看用户技能列表");
        return view(
            "userSkillsList",
            [
                "data" => $condition, /*查询条件*/
                "list" => $list['data'], /*查询结果*/
                "page" => $list['page'], /*分页和html代码*/
                "lastPage" => $list['lastPage'], /*总页数*/
                "currentPage" => $list['currentPage'], /*当前页码*/
                "total" => $list['total'], /*总条数*/
                "listRows" => $list['listRows'], /*每页显示条数*/
            ]
        );
    }
    /**
     * 用户技能添加
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-30
     */
    public function userSkillsAdd()
    {
        if ($this->request->isPost()) {
            $add = $this->request->only(['us_name', 'us_status', 'us_desc'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "us_name" => 'require|max:30',
                "us_status" => 'alpha',
            );
            $msg = array(
                "us_name.require" => '亲,技能名是必须要填写的噢!~',
                "us_name.max" => '技能名最大长度是30位哦!~',
                "us_status.alpha" => '状态选择有误噢!~',
            );
            $data = verify($add, $rule, $msg);
            if ($data['code'] === 1) {
                $ui_id = $this->us->getOne(['us_name' => $add['us_name']], 'us_id');
                if ($ui_id > 0) {
                    $this->error("该名称重复,请重新填写!~");
                    exit();
                }
                (isset($add['us_status']) && !empty($add['us_status']) && $add['us_status'] == 'on') ? $add['us_status'] = 1 : $add['us_status'] = 0;
                $add['us_add_time'] = time();
                $id = $this->us->save($add);
                if ($id > 0) {
                    $this->managerLog("添加用户技能,技能id为:".$id);
                    $this->success("添加成功!~", url("manage/Users/userSkillsList"));
                } else {
                    $this->error("添加失败!~");
                }
            } else {
                $this->error($data['msg']);
            }
            exit();
        }
        return view(
            "userSkillsAdd"
        );
    }
    /**
     * 用户技能修改
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-30
     */
    public function userSkillsEdit($id)
    {
        if (isset($id) && $id > 0) {
            $where = ["us_id" => intval($id)];
            $info = $this->us->getRow($where);
            if ($this->request->isPost()) {
                $save = $this->request->only(['us_name', 'us_status', 'us_desc'], 'post');
                /*验证接到的值有没有问题*/
                $rule = array(
                    "us_name" => 'require|max:30',
                    "us_status" => 'alpha',
                );
                $msg = array(
                    "us_name.require" => '亲,行业名是必须要填写的噢!~',
                    "us_name.max" => '行业名最大长度是30位哦!~',
                    "us_status.alpha" => '状态选择有误噢!~',
                );
                $data = verify($save, $rule, $msg);
                if ($data['code'] === 1) {
                    $us_name = $this->us->getOne(['us_name' => $save['us_name']], 'us_name');
                    if (!empty($us_name) && $us_name != $save['us_name']) {
                        $this->error("该名称重复,请重新填写!~");
                        exit();
                    }
                    (isset($save['us_status']) && !empty($save['us_status']) && $save['us_status'] == 'on') ? $save['us_status'] = 1 : $save['us_status'] = 0;
                    $ret = $this->us->save($save, $where);
                    if (false !== $ret) {
                        $this->managerLog("修改技能信息,技能id为:".$id);
                        $this->success("修改成功!~", url("manage/Users/userSkillsList"));
                    } else {
                        $this->error("修改失败!~");
                    }
                } else {
                    $this->error($data['msg']);
                }
                exit();
            }
            return view(
                "userSkillsEdit",
                [
                    "info" => $info,
                ]
            );
        } else {
            $this->error("程序员都累到吐血了,也没有接收到任何参数!~");
        }
    }
    /**
     * 用户技能删除
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-30
     */
    public function userSkillsDel()
    {
        /*判断是不是ajax请求*/
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
                $where['us_id'] = intval($info['id']);
                /*查询管理员表要是有数据不允许删除*/
                $m_id = $this->us->getOne($where, "us_id");
                if ($m_id < 0) {
                    return json(format('', '-1', '删除失败~!该信息不存在噢~!'));
                } else {
                    /*删除mpg表数据*/
                    $ret = $this->us->del($where);
                    if (false === $ret) {
                        return json(format('', '-1', '删除失败~!请稍候重试~!'));
                    } else {
                        $this->managerLog("删除技能信息,技能信息id为:".$info['id']);
                        return json(format('', '1', '删除成功~!'));
                    }
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }


    /**
     * 作者：李鑫
     * 时间：2018-05-15
     * 功能：总后台客服
     */
    public function customerService()
    {
        $sm_im = $this->sm->getRow(['sm_id' => '67d4d1d052ce3418']);
        $im_val = $this->im->getList(['sm_im_id' => '67d4d1d052ce3418','ss_id' => '75'],'*',['end_time' => "desc"]);
        foreach ($im_val as $key => $value) {

            $uRow = $this->u->getRow(['u_id' => $value['u_id']],'u_name,u_headimg');

            if(!empty($uRow)){
                $im_val[$key]['u_name']=$uRow['u_name'];
                $im_val[$key]['u_headimg']=empty($uRow['u_headimg'])?'':$uRow['u_headimg'];
                if (empty($im_val[$key]['u_name'])) {
                    unset($im_val[$key]);
                }
            }else{
                unset($im_val[$key]);
            }

        }



        return view(
            "customerService",
            [
                "sm_im" => $sm_im,
                "im_val" => $im_val,
            ]
        );
    }

    /**
     * 作者：李鑫
     * 时间：2017-11-08
     * 功能：AJAX商户后台客服用户头像
     */
    public function ajaximg()
    {
        if ($this->request->isAjax()) {
            $info = $this->request->only(['u_im_id'], 'get');
            $im_val = $this->im->getRow(['u_im_id' => $info['u_im_id']]);
            $u_img = $this->uss->getRow(['u_id' => $im_val['u_id']]);
            $save['im_state']='1';
            $wheres['u_im_id']=$info['u_im_id'];
            $wheres['sm_im_id']='67d4d1d052ce3418';
            $this->im->save($save, $wheres);
            // dump($u_img['u_headimg']);die();
            return json(format($u_img['u_headimg']));

        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }

    /**
     * 作者：李鑫
     * 时间：2017-11-08
     * 功能：AJAX商户后台客服用户头像
     */
    public function ajaximgs()
    {
        if ($this->request->isAjax()) {
            // $info['u_im_id']='93599b4f0e3f7624';
            $info = $this->request->only(['u_im_id'], 'get');
            $im_val = $this->im->getRow(['u_im_id' => $info['u_im_id']]);
            $u_img = $this->uss->getRow(['u_id' => $im_val['u_id']]);
            // dump($u_img['u_headimg']);die();
            return json(format($u_img));

        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-11-08
     * 功能：AJAX商户后台客服用户列表
     */
    public function customerServiceUserList()
    {
        if ($this->request->isAjax()) {
            $info['sm_im_id']='67d4d1d052ce3418';
            // $info = $this->request->only(['sm_im_id'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "sm_im_id" => 'require',
            );
            $msg = array(
                "sm_im_id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($info, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                $im_val = $this->im->getList(['sm_im_id' => $info['sm_im_id'],'ss_id' => '75']);
                // dump($im_val);die();
                return json(format($im_val));
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    public function ajaxChatRecord1()
    {
        if ($this->request->isAjax()) {
            $info = $this->request->only(['u_im_ids','msgs','type'], 'post');
            $info['sm_im_id']='67d4d1d052ce3418';
            
            $uname = $this->im->getRow(["sm_im_id"=>$info['sm_im_id'],'u_im_id'=>$info['u_im_ids']]);
            $info['u_name']=$uname['u_name'];
            $info['s_name']=$uname['ss_name'];
            $info['time']=date("Y-m-d H:i:s");
            $save['im_state']='0';
            $save['end_time']=time();
            $wheres['im_id']=$uname['im_id'];
            $this->im->save($save, $wheres);
        
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

    /**
     * [发送邮件]
     * @author 王牧田
     * @date 2018-05-14
     */
    public function sendMails(){
        header("content-type:text/html;charset=gbk");
        /*判断是不是ajax请求*/
        if ($this->request->isAjax()) {

            $info = $this->request->only(['mailc','uid'], 'post');

            $u_email = $this->u->getOnes(["u_id"=>["in",$info["uid"]]],"u_email");

            $b = iconv("utf-8","gb2312",urldecode($info['mailc']));
            $uemailStr = implode(",",$u_email);

            if($uemailStr != ""){
                $result = sendEmail($uemailStr,$b);
                if($result!==false){
                    return json(format());
                }else{
                    return json(format("",-1,"发送失败，请稍后重试"));
                }
            }else{
                return json(format("",-1,"当前用户未绑定邮箱"));
            }

        }
    }

    public function complain(){
        $where = [];

        $ss_complain_msg = $this->ss->getOne($where,"ss_complain_msg");


        $complainAll = $this->uri->getAll([],[],["uri_id"=>"desc"]);
        $complain = $complainAll["data"];


        foreach ($complain as $k=>$row){
            $complain[$k]["co_imgs"] = [$row["uri_img_path"],$row["uri_img_path2"],$row["uri_img_path3"],$row["uri_img_path4"]];




            $complain[$k]["seller_name"] = $this->ss->getOne(["ss_id"=>$row["uri_report_info_id"]],"ss_name");
        }

        $this->assign("ss_complain_msg",$ss_complain_msg);
        $this->assign("page",$complainAll["page"]);
        $this->assign("complain",$complain);
        return view();
    }
    /*用户反馈*/
    public function feedback(){



        $feedbackAll = $this->fb->getAll([],[],["f_id"=>"desc"]);
        $feedback = $feedbackAll["data"];


        foreach ($feedback as $k=>$row){
            $feedback[$k]["co_imgs"] = [$row["f_img_path1"],$row["f_img_path2"],$row["f_img_path3"]];
        }

        $this->assign("page",$feedbackAll["page"]);
        $this->assign("feedback",$feedback);
        // dump($feedback);die();
        return view();
    }




}
