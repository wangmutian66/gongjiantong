<?php
namespace app\seller\controller;

use model\RecruitmentInfo as ri;
use model\Region as r;
use model\ShopArtical as sa;
use model\UserIndustry as ui;
use model\UserSkills as us;
use model\SellerManagers as sm;
use model\UserJobSearch as ujs;
use model\UserResume as ur;
use model\UserLearningExperience as ule;
use model\UserCertificate as uc;
use model\UserWorkExperience as uwe;
use model\SellerShop as ss;
/**
 * 作者：袁中旭
 * 时间：2017-11-21
 * 功能：商户后台招聘管理
 */

class Recruitment extends Base
{
    /**
     * 作者：袁中旭
     * 时间：2017-10-21
     * 功能：继承父类构造函数
     */
    protected $ri;
    protected $ui;
    protected $us;
    protected $r;
    protected $sa;
    protected $sm;
    protected $ujs;
    protected $ur;
    protected $ule;
    protected $uc;
    protected $uwe;
    protected $ss;
    public function __construct()
    {
        parent::__construct();
        /*招聘*/
        $this->ri = new ri();
        /*用户行业*/
        $this->ui = new ui();
        /*用户技能*/
        $this->us = new us();
        /*地区*/
        $this->r = new r();
        /*文章*/
        $this->sa = new sa();
        /*商户管理员*/
        $this->sm = new sm();
        /*投递简历表*/
        $this->ujs = new ujs();
        /*简历表*/
        $this->ur = new ur();
        /*学习经历*/
        $this->ule = new ule();
        /*证书*/
        $this->uc = new uc();
        /*工作经历*/
        $this->uwe = new uwe();
        /*商铺表*/
        $this->ss = new ss();
        /*获取当天的0:0:0 和 23:59:59 的时间戳*/
        $y = date("Y");$m = date("m");$d = date("d");
        $this->time_start = mktime(0,0,0,$m,$d,$y);
        $this->time_end = mktime(23,59,59,$m,$d,$y);
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-26
     * 功能：商户招聘列表
     */
    public function recruitmentList()
    {
        $ss_is_areview=$this->ss->getOne(['ss_id' => $this->sm_info['ss_id']],"ss_is_areview");
        $ri_list = $this->ri->getAll(['ss_id' => $this->sm_info['ss_id']]);
        foreach ($ri_list['data'] as $ri_k => $ri_v) {
            $ri_list['data'][$ri_k]['ui_name'] = $this->ui->getOne(['ui_id' => $ri_v['ui_id']],'ui_name');
            $ri_list['data'][$ri_k]['us_name'] = $this->us->getOne(['us_id' => $ri_v['us_id']],'us_name');
            $ri_list['data'][$ri_k]['sa_title'] = $this->sa->getOne(['sa_id' => $ri_v['ri_company_profile']],'sa_title');
            if($ri_v["ur_ids"]==""){
                $ri_list['data'][$ri_k]['ur_count'] = 0;
            }else{
                $ri_list['data'][$ri_k]['ur_count'] = (count(explode(",",$ri_v["ur_ids"]))-1);
            }
        }
        return view(
            "recruitmentList",
            [
                "ri_list" => $ri_list,
                "ss_is_areview" => $ss_is_areview
            ]
        );
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-21
     * 功能：商户招聘添加
     */
    public function recruitmentAdd()
    {
        if ($this->request->isPost()) {
            /*只获取post以下参数*/
            $post_ri_info = $this->request->only(['ui_id', 'us_id', 'ri_title','ri_personnel', 'ri_wage', 'ri_number_recruits', 'ri_work_province', 'ri_work_city', 'ri_work_area', 'ri_work_address','ri_company','ri_contact_mobile','ri_work_period','ri_contact','ri_other_contents','ri_startwork_time','ri_contents'], "post");

            /*验证接到的值有没有问题*/
            $rule = array(
                "ui_id" => 'require',
                // "us_id" => 'require',
                "ri_title" => 'require|max:50|chsAlphaNum',
                // "ri_wage" => 'require|max:20|alphaDash',
                "ri_number_recruits" => 'require|max:10',
                "ri_work_province" => 'require',
                "ri_work_city" => 'require',
                "ri_work_area" => 'require',
                "ri_work_address" => 'require',
                "ri_company" => 'require|chsAlpha|max:60',
                "ri_contact_mobile" => 'require|max:11',
                "ri_work_period" => 'require',
                "ri_contact" => 'require',
                "ri_other_contents" => 'require',
                "ri_startwork_time" => 'require',
                "ri_personnel" => 'require',

            );
            $msg = array(
                "ui_id.require" => "请选择行业噢!~",
                // "us_id.require" => "请选择技能噢!~",
                "ri_title.require" => "请填写招聘标题噢!~",
                "ri_title.max" => "招聘标题不能超过50个字符噢!~",
                "ri_title.chsAlphaNum" => "招聘标题只能输入汉字、字母、数字噢!~",
                // "ri_wage.require" => "请填写工资待遇噢!~",
                // "ri_wage.require" => "工资待遇不能超过20个字符噢!~",
                // "ri_wage.alphaDash" => "工资待遇只能输入字母和数字，下划线_及破折号-噢!~",
                "ri_number_recruits.require" => "请填写招聘人数噢!~",
                "ri_number_recruits.max" => "招聘人数不能超过10位数字噢!~",
                "ri_work_province.require" => "请选择工作所在省噢!~",
                "ri_work_city.require" => "请选择工作所在市噢!~",
                "ri_work_area.require" => "请选择工作所在区噢!~",
                "ri_work_address.require" => "请填写工作所在详细地址噢!~",
                "ri_company.require" => "请填写公司名称噢!~",
                "ri_company.chsAlpha" => "公司名称只能输入中文和字符噢!~",
                "ri_company.max" => "公司名称不能超过60个字符噢!~",
                "ri_contact_mobile.require" => "请填写联系方式噢!~",
                "ri_contact_mobile.max" => "联系方式格式正确噢!~",
                "ri_work_period.require" => "请填写工期!~",
                "ri_contact.require" => "请填写联系人!~",
                "ri_other_contents.require" => "请填写其他需求!~",
                "ri_startwork_time.require" => "请填写开工时间!~",
                "ri_personnel.require" => "请填招聘人员!~",
            );
            $data = verify($post_ri_info, $rule, $msg);
            /*code 等于1 说明成功 否则失败*/
            if ($data['code'] === 1) {
                $post_ri_info['ri_add_time'] = time();
                $post_ri_info['ri_refresh_time'] = time();
                $post_ri_info['ri_refresh_frequency'] = 1;
                $post_ri_info['ss_id'] = $this->sm_info['ss_id'];
                $post_ri_info['ri_author'] = $this->sm_id;

                $ri_id = $this->ri->save($post_ri_info);
                if ($ri_id > 0) {
                    $this->sellerManagerLog("招聘添加，添加id为:".$ri_id);
                    $this->success('添加成功');
                }
            } else {
                /*提示失败信息*/
                $this->error($data['msg']);
            }
        } else {
            $ui_value = $this->ui->getList(['ui_status'=>1]); /*用户行业*/
            $us_value = $this->us->getList(['us_status'=>1]); /*用户技能*/
            $r_value = $this->r->getList(['r_parent_id' => 1]); /*地区*/
            $sa_value = $this->sa->getList(['ss_id' => $this->sm_info['ss_id']]); /*文章*/
            return view(
                "recruitmentAdd",
                [
                    "ui_value" => $ui_value,
                    "us_value" => $us_value,
                    "r_value" => $r_value,
                    "sa_value" => $sa_value,
                ]
            );
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-21
     * 功能：商户招聘展示
     */
    public function recruitmentShow()
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

                $ri_show = $this->ri->getRow(['ss_id' => $this->sm_info['ss_id'],'ri_id' => intval($info['id'])]);  
                $ri_show['ui_name'] = $this->ui->getOne(['ui_id' => $ri_show['ui_id']],'ui_name');
                $ri_show['us_name'] = $this->us->getOne(['us_id' => $ri_show['us_id']],'us_name');
                $ri_show['sm_name'] = $this->sm->getOne(['sm_id' => $ri_show['ri_author']],'sm_seller_name');
                $ri_show['ri_add_time'] = date('Y-m-d H:i:s',$ri_show['ri_add_time']);
                $ri_show['ri_refresh_time'] = date('Y-m-d H:i:s',$ri_show['ri_refresh_time']);

                $ri_show['work_province'] = $this->r->getOne(['r_id' => $ri_show['ri_work_province']],'r_name');
                $ri_show['work_city'] = $this->r->getOne(['r_id' => $ri_show['ri_work_city']],'r_name');
                $ri_show['work_area'] = $this->r->getOne(['r_id' => $ri_show['ri_work_area']],'r_name');
                $ri_show['puton_city'] = $this->r->getOne(['r_id' => $ri_show['ri_puton_city']],'r_name');
                $ri_show['puton_province'] = $this->r->getOne(['r_id' => $ri_show['ri_puton_province']],'r_name');
                
                $this->sellerManagerLog("查看招聘,招聘id为:".$info['id']);
                return json(format($ri_show, '1', $data['msg']));
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-21
     * 功能：商户招聘修改
     */
    public function recruitmentEdit($id)
    {
        if (isset($id) && $id > 0) {
            $where = ["ri_id" => intval($id)];
            $ri_edit = $this->ri->getRow($where);

            if ($this->request->isPost()) {
                /*只获取post以下参数*/
                //$post_ri_info = $this->request->only(['ui_id', 'us_id', 'ri_label', 'ri_company_profile', 'ri_title', 'ri_contents', 'ri_wage', 'ri_number_recruits', 'ri_work_province', 'ri_work_city', 'ri_work_area', 'ri_work_address', 'ri_puton_province', 'ri_puton_city','ri_company','ri_contact_mobile'], "post");
                $post_ri_info = $this->request->only(['ui_id', 'us_id', 'ri_title','ri_personnel', 'ri_wage', 'ri_number_recruits', 'ri_work_province', 'ri_work_city', 'ri_work_area', 'ri_work_address','ri_company','ri_contact_mobile','ri_work_period', 'ri_contents','ri_contact','ri_other_contents','ri_startwork_time'], "post");

                /*验证接到的值有没有问题*/
                $rule = array(
                    "ui_id" => 'require',
                    // "us_id" => 'require',
                    "ri_title" => 'require|max:50|chsAlphaNum',
                    // "ri_wage" => 'require|max:20|alphaDash',
                    "ri_number_recruits" => 'require|max:10',
                    "ri_work_province" => 'require',
                    "ri_work_city" => 'require',
                    "ri_work_area" => 'require',
                    "ri_work_address" => 'require',
                    "ri_company" => 'require|chsAlpha|max:60',
                    "ri_contact_mobile" => 'require|max:11',
                    "ri_work_period" => 'require',
                    "ri_contact" => 'require',
                    "ri_other_contents" => 'require',
                    "ri_startwork_time" => 'require',
                    "ri_personnel" => "require",

                );
                $msg = array(
                    "ui_id.require" => "请选择行业噢!~",
                    // "us_id.require" => "请选择技能噢!~",
                    "ri_title.require" => "请填写招聘标题噢!~",
                    "ri_title.max" => "招聘标题不能超过50个字符噢!~",
                    "ri_title.chsAlphaNum" => "招聘标题只能输入汉字、字母、数字噢!~",
                    // "ri_wage.require" => "请填写工资待遇噢!~",
                    // "ri_wage.require" => "工资待遇不能超过20个字符噢!~",
                    // "ri_wage.alphaDash" => "工资待遇只能输入字母和数字，下划线_及破折号-噢!~",
                    "ri_number_recruits.require" => "请填写招聘人数噢!~",
                    "ri_number_recruits.max" => "招聘人数不能超过10位数字噢!~",
                    "ri_work_province.require" => "请选择工作所在省噢!~",
                    "ri_work_city.require" => "请选择工作所在市噢!~",
                    "ri_work_area.require" => "请选择工作所在区噢!~",
                    "ri_work_address.require" => "请填写工作所在详细地址噢!~",
                    "ri_company.require" => "请填写公司名称噢!~",
                    "ri_company.chsAlpha" => "公司名称只能输入中文和字符噢!~",
                    "ri_company.max" => "公司名称不能超过60个字符噢!~",
                    "ri_contact_mobile.require" => "请填写联系方式噢!~",
                    "ri_contact_mobile.max" => "联系方式格式正确噢!~",
                    "ri_work_period.require" => "请填写工期!~",
                    "ri_contact.require" => "请填写联系人!~",
                    "ri_other_contents.require" => "请填写其他需求!~",
                    "ri_startwork_time.require" => "请填写开工时间!~",
                    "ri_personnel.require" => "请填招聘人员!~",
                );
                $data = verify($post_ri_info, $rule, $msg);
                /*code 等于1 说明成功 否则失败*/
                if ($data['code'] === 1) {
                    $post_ri_info['ri_add_time'] = time();
                    $post_ri_info['ss_id'] = $this->sm_info['ss_id'];
                    $post_ri_info['ri_author'] = $this->sm_id;
                    $ri_id = $this->ri->save($post_ri_info,$where);
                    if ($ri_id > 0) {
                        $this->sellerManagerLog("修改招聘，修改id为:".$id);
                        $this->success('修改成功');
                    }
                } else {
                    /*提示失败信息*/
                    $this->error($data['msg']);
                }
            } else {
                $ui_value = $this->ui->getList(['ui_status'=>1]); /*用户行业*/
                $us_value = $this->us->getList(['us_status'=>1]); /*用户技能*/
                $r_value = $this->r->getList(['r_parent_id' => 1]); /*地区*/
                $sa_value = $this->sa->getList(['ss_id' => $this->sm_info['ss_id']]); /*文章*/
                return view(
                    "recruitmentEdit",
                    [
                        "id"  => $id,
                        "ri_edit"  => $ri_edit,
                        "ui_value" => $ui_value,
                        "us_value" => $us_value,
                        "r_value" => $r_value,
                        "sa_value" => $sa_value,
                    ]
                );
            }
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-21
     * 功能：商户招聘删除
     */
    public function recruitmentDel()
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
                $where['ri_id'] = intval($info['id']);
                $ri_title = $this->ri->getOne($where, "ri_title");
                if (isset($ri_title) && !empty($ri_title)) {
                    $ri_ret = $this->ri->del($where);
                    if (false === $ri_ret) {
                        return json(format('', '-1', '删除失败~!请稍候重试~!'));
                    } else {
                        $this->sellerManagerLog("删除招聘信息,删除id为:".$info['id']);
                        return json(format('', '1', '删除成功~!'));
                    }
                } else {
                    return json(format('', '-1', '数据不存在哦!~'));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-21
     * 功能：AJAX商户招聘刷新
     */
    public function recruitmentRefresh()
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
                $where['ri_id'] = intval($info['id']);
                $ri_value = $this->ri->getRow($where);
                if($ri_value['ri_refresh_frequency'] >= 5){
                    return json(format('', '-1', '刷新次数超出5次!~'));
                }else{
                    $ri_refresh = [
                        "ri_refresh_time" => time(),
                        "ri_refresh_frequency" => $ri_value['ri_refresh_frequency'] += 1,
                    ];
                    $ri_ret = $this->ri->save($ri_refresh,$where);
                    if (false === $ri_ret) {
                        return json(format('', '-1', '刷新失败~!请稍候重试~!'));
                    } else {
                        return json(format('', '1', '刷新成功~!'));
                    }
                }

            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-11-01
     * 功能：AJAX处理刷新
     */
    public function ajaxRefresh()
    {
        /*判断是不死ajax请求*/
        if ($this->request->isAjax()) {
            $ri_list = $this->ri->getAll(['ss_id' => $this->sm_info['ss_id']]);
            foreach ($ri_list['data'] as $ri_k => $ri_v) {
                if($this->time_start > $ri_v['ri_refresh_time'] && $this->time_end > $ri_v['ri_refresh_time']){
                   $this->ri->save(['ri_refresh_frequency' => '0'],['ri_id' => $ri_v['ri_id']]);
                }
            }
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-11-01
     * 功能：查看投递的简历，简历详情
     */
    public function deliveryResume($ri_id)
    {
        if($this->request->isAjax()){
            $post_ur_info = $this->request->only(['id'], "post");

            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => "程序员都累吐血了也没有接到传输的数据噢!~",
            );
            $data = verify($post_ur_info, $rule, $msg);
            if ($data['code'] === 1) {
                /*获取个人简历*/
                $ur_value = $this->ur->getRow(['ur_id' => $post_ur_info['id']]);
                /*判断工作经验*/
                if($ur_value['ur_year_of_work'] == 1){
                    $ur_value['ur_year_of_work'] = '无经验';
                }else if($ur_value['ur_year_of_work'] == 2){
                    $ur_value['ur_year_of_work'] = '应届生';
                }else if($ur_value['ur_year_of_work'] == 3){
                    $ur_value['ur_year_of_work'] = '一年以下';
                }else if($ur_value['ur_year_of_work'] == 4){
                    $ur_value['ur_year_of_work'] = '1-3年';
                }else if($ur_value['ur_year_of_work'] == 5){
                    $ur_value['ur_year_of_work'] = '3-5年';
                }else if($ur_value['ur_year_of_work'] == 6){
                    $ur_value['ur_year_of_work'] = '5年以上';
                }

                /*判断学历*/
                if($ur_value['ur_highest_education'] == 0){
                    $ur_value['ur_highest_education'] = '保密';
                }else if($ur_value['ur_highest_education'] == 1){
                    $ur_value['ur_highest_education'] = '中专/技校';
                }else if($ur_value['ur_highest_education'] == 2){
                    $ur_value['ur_highest_education'] = '大专';
                }else if($ur_value['ur_highest_education'] == 3){
                    $ur_value['ur_highest_education'] = '本科';
                }else if($ur_value['ur_highest_education'] == 4){
                    $ur_value['ur_highest_education'] = '硕士';
                }else if($ur_value['ur_highest_education'] == 5){
                    $ur_value['ur_highest_education'] = '博士';
                }
                /*判断性别*/
                if(intval($ur_value['ur_sex']) == 0){
                    $ur_value['ur_sex'] = '女';
                }else{
                    $ur_value['ur_sex'] = '男';
                }
                /*获取学习经历*/
                if($ur_value['ule_ids'] != ''){
                    $ule_ids = explode(',',$ur_value['ule_ids']);
                    $ur_value['ule_value'] = '';
                    foreach ($ule_ids as $ule_key => $ule_value) {
                        if($ule_value != ''){
                            $ule_values = $this->ule->getRow(['ule_id' => $ule_value]);
                            $ur_value['ule_value'] .= date('Y-m-d',$ule_values['ule_start_time']).' ~ '.date('Y-m-d',$ule_values['ule_end_time']).'&nbsp;&nbsp;&nbsp;&nbsp;'.$ule_values['ule_school_name'].'<br>';
                        }
                    }
                }
                
                /*获取学习经历*/
                if($ur_value['ur_living_province'] != ''){
                    $ur_living['r_id'] = ["in",$ur_value['ur_living_province'].','.$ur_value['ur_living_city'].','.$ur_value['ur_living_area']];
                    $ur_livings = $this->r->getList($ur_living);
                    $ur_value['ur_living'] = $ur_livings['0']['r_name'].','.$ur_livings['1']['r_name'].','.$ur_livings['2']['r_name'];
                }

                /*获取学习经历*/
                if($ur_value['ur_exoect_workplace_province'] != ''){
                    $ur_exoect_workplace['r_id'] = ["in",$ur_value['ur_exoect_workplace_province'].','.$ur_value['ur_exoect_workplace_city'].','.$ur_value['ur_exoect_workplace_area']];
                    $ur_exoect_workplacegs = $this->r->getList($ur_exoect_workplace);
                    $ur_value['ur_exoect_workplaceg'] = $ur_exoect_workplacegs['0']['r_name'].','.$ur_exoect_workplacegs['1']['r_name'].','.$ur_exoect_workplacegs['2']['r_name'];
                }
                /*获取行业名称*/
                if($ur_value['ui_id'] != ''){
                    $ur_value['ui_name'] = $this->ui->getOne(['ui_id' => $ur_value['ui_id']],'ui_name');
                }
                /*获取技能名称*/
                if($ur_value['us_ids'] != ''){
                    $us['us_id'] = ["in",$ur_value['us_ids']];
                    $uss = $this->us->getList($us);
                    $ur_value['us_name'] = '';
                    foreach ($uss as $us_key => $us_value) {
                        $ur_value['us_name'] .= $us_value['us_name'].',';
                    }
                }
                /*获取证书路径*/
                if($ur_value['uc_ids'] != ''){
                    $uc_ids = explode(',',$ur_value['uc_ids']);
                    $ur_value['uc_value'] = '';
                    foreach ($uc_ids as $uc_key => $uc_value) {
                        if($uc_value != '' && $uc_key < 5){
                            $uc_values = $this->uc->getRow(['uc_id' => $uc_value]);
                            $ur_value['uc_value'] .= '<img src="'.$uc_values['uc_img_path'].'" style="width:100px;height:100px">';
                        }
                    }
                }
                /*获取工作经历*/
                if($ur_value['uwe_ids'] != ''){
                    $uwe_ids = explode(',',$ur_value['uwe_ids']);
                    $ur_value['uwe_value'] = '';
                    foreach ($uwe_ids as $uwe_key => $uwe_value) {
                        if($uwe_value != ''){
                            $uwe_values = $this->uwe->getRow(['uwe_id' => $uwe_value]);
                            $ur_value['uwe_value'] .= date('Y-m-d',$uwe_values['uwe_start_time']).' ~ '.date('Y-m-d',$uwe_values['uwe_end_time']).'<p>工作单位：'.$uwe_values['uwe_employer'].'</p>'.'<p>职务'.$uwe_values['uwe_duties'].'</p>'.'<p>证明人'.$uwe_values['uwe_witness'].'</p>'.'<p>工作内容'.$uwe_values['uwe_contents'].'</p>'.'<p>离职原因'.$uwe_values['uwe_reason_for_leaving'].'</p>'; 
                        }
                    }
                }
                return json(format($ur_value, '1', $data['msg']));
            } else {
                /*提示失败信息*/
                $this->error($data['msg']);
            }
        }else{

            $ur_ids = $this->ri->getOne(["ri_id"=>$ri_id],"ur_ids");

            $ujs = $this->ur->getAll(["ur_id"=>["in",$ur_ids]],[],[],5);
            return view(
                "deliveryResume",
                [
                    "ri_id"=>$ri_id,
                    "ujs" => $ujs,
                    "page"=>$ujs["page"]
                ]
            );
        }
    }

    /**
     * [文章发布]
     * @author 王牧田
     * @date 2018-04-27
     */
    public function sendrecruitment(){
        $post_ur_info = $this->request->only(['id'], "post");
        $result = $this->ri->save(["ri_is_release"=>1,"ri_releasetime"=>time()],["ri_id"=>$post_ur_info["id"]]);
        if($result){
            return json(format());
        }else{
            return json(format([],203,"网络错误稍后重试"));
        }
    }

    /**
     * 店铺招聘申请
     * @author 王牧田
     * @date   2018-04-24
     */
    public function articlereview(){
        if ($this->request->isPost()) {

            $ss_is_areview = $this->ss->getOne(["ss_id"=>$this->sm_info['ss_id']],"ss_is_areview");
            if($ss_is_areview == 1){
                return json(format("", 201, "已发送到后台，请等待审核"));
            }
            $ss = $this->ss->save(["ss_is_areview"=>1],["ss_id"=>$this->sm_info['ss_id']]);

            if(empty($ss)){
                return json(format($ss, 201, "网络错误，请稍后重试"));
            }else{
                return json(format($ss, 200, "success"));
            }
        }
    }




}

