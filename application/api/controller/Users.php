<?php
namespace app\api\controller;

use model\SmsLog as sl;
use model\UserIndustry as ui;
use model\UserLearningExperience as ule;
use model\UserResume as ur;
use model\Users as u;
use model\UserShippingAddress as usa;
use model\UserSkills as us;
use model\UserWorkExperience as uwe;
use model\UserCertificate as uc;
use model\UserJobSearch as ujs;
use model\UserBrowsingHistory as ubh;
use model\Goods as g;
use model\Region as r;
use model\Invoice as i;
use model\Edition as e;
use model\Artical as a;
use model\GoodsSpecifications as gsp;

/**
 * 用户信息
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-10-09
 */
class Users extends Base
{
    protected $gsp;
    protected $sl;
    protected $u;
    protected $ui;
    protected $us;
    protected $ur;
    protected $usa;
    protected $ule;
    protected $uwe;
    protected $uc;
    protected $ujs;
    protected $ubh;
    protected $g;
    protected $r;
    protected $i;
    protected $e; 
    protected $a;
    public function __construct()
    {

        parent::__construct();
        $this->sl = new sl(); /*短信记录表*/
        $this->u = new u; /*用户表*/
        $this->usa = new usa(); /*用户收货地址*/
        $this->ui = new ui(); /*行业表*/
        $this->us = new us(); /*技能表*/
        $this->ur = new ur(); /*简历列表*/
        $this->ule = new ule(); /*学习经历表*/
        $this->uwe = new uwe(); /*工作经历表*/
        $this->uc = new uc();/*证书表*/
        $this->ujs = new ujs();
        $this->ubh = new ubh(); /*历史浏览记录表*/
        $this->g = new g;
        $this->r=new r();
        $this->e=new e();
        $this->i=new i();/*发票*/
        $this->a = new a();
        $this->gsp = new gsp(); /*商品规格*/
    }
    /**
     * 用户最后登陆时间
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-11-20
     * @return [type]            [description]
     */
    public function lastLogin()
    {
        if (empty($this->u_id)) {
            return json(format('',223,'缺少必要参数!~'));
        }
        $where['u_id'] = $this->u_id;
        $save['u_last_login_time'] = time();
        $save['u_last_login_ip'] = getIp();
        $this->u->save($save,$where);
        return json(format());
    }
    /**
     * 添加用户即时通讯账号
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-30
     */
    public function addUserIM()
    {
        if ($this->u_id > 0) {
            $where['u_id'] = $this->u_id;
            $save['u_im_id'] = $this->param['u_im_id'];
            $ret = $this->u->save($save,$where);
            if ($ret !== false) {
                return json(format());
            } else {
                return json(format('', 223, "更新失败!~"));
            }
        } else {
            return json(format('', 223, "缺少必要参数!~"));
        }
    }
    /**
     * 获取投递历史
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-29
     */
    public function getUserJobSearchHistroy()
    {
        $where['ujs.u_id'] = $this->u_id;
        if ($where['ujs.u_id'] > 0) {
            $join = [
                ['gjt_recruitment_info ri' , "ri.ri_id = ujs.ri_id"],
            ];
            $alias = "ujs";
            $field = "ujs.*,ri.ri_id,ri.ri_title,ri.ri_wage,ri.ri_work_province,ri.ri_work_city,ri.ri_work_area,ri.ri_refresh_time";
            $list = $this->ujs->joinGetAll($join, $alias, $where, [], [], 0, $field);
            return json(format($list["data"]));
        } else {
            return json(format('', 223, "缺少必要参数!~"));
        }
    }
    /**
     * 获取用户历史浏览记录(校验签名)
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-11-03
     * @param ubh_type      [浏览记录类型(0:店铺,1:商品,2:招标,3:规范)]
     */
    public function getUserBrowsingHistory()
    {
        $ubh_where['u_id'] = $this->u_id;
        $ubh_where['ubh_type'] = $this->param['ubh_type'];

        $rule = [
            "ubh_type" => "require|in:0,1,2,3,4",
            "u_id" => "require|number",
        ];
        $msg = [
            "ubh_type.require" => "缺少必要参数!~",
            "ubh_type.in" => "必要参数传输有误!~",
            "u_id.require" => "缺少必要参数!~",
            "u_id.number" => "必要参数传输有误!~",
        ];
        $data = verify($ubh_where, $rule, $msg);
        if ($data['code'] === 1) {
            $pageParam['query']['u_id'] = $ubh_where['u_id'];
            $pageParam['query']['ubh_type'] = $ubh_where['ubh_type'];
            $alias = "ubh";
            $group = "";
            $order =  ["ubh_time" => "desc"];
            switch ($ubh_where['ubh_type']) {
                case '0':
                    $join = [
                        ["gjt_seller_shop ss", "ss.ss_id = ubh.ubh_browsing_id"],
                        ["gjt_goods g", "g.ss_id = ss.ss_id"],
                        ["gjt_region p", "p.r_id = ss.ss_shop_province"],
                        ["gjt_region c", "c.r_id = ss.ss_shop_city"],
                        ["gjt_region a", "a.r_id = ss.ss_shop_area"],
                    ];
                    $field = "ss.ss_id, ss.ss_name, ss.ss_logo_img, ss.ss_shop_location, ss.ss_shop_address, count(g.g_id) as g_num, count(g.g_sales) as g_sales, p.r_name as ss_shop_province, c.r_name as ss_shop_city, a.r_name as ss_shop_area, ubh.ubh_id, ubh.ubh_time";
                    $group = "ubh.ubh_id";
                    $order = ["ss.ss_sort" => "desc" , "ss.ss_id" => "desc"];
                    break;
                case '1':
                    $join = [
                        ['gjt_goods g', "ubh.ubh_browsing_id = g.g_id"]
                    ];
                    $field = "g.g_id,g.g_name,g.g_show_img_path,g.g_current_price,ubh.ubh_id, ubh.ubh_time";
                    break;
                case '2':
                    $join = [
                        ['gjt_bidding_information bi', "ubh.ubh_browsing_id = bi.bi_id"]
                    ];
                    $field = "bi.bi_id,bi.bi_title,bi.bi_add_time,bi.bi_desc,ubh.ubh_id, ubh.ubh_time";
                    break;
                case '3':
                    $join = [
                        ['gjt_engineering_specifications es', "ubh.ubh_browsing_id = es.es_id"],
                    ];
                    $field = "es.es_id,es.es_title,es.es_file_sn,es.es_thumb,es.es_implementation_time,ubh.ubh_id, ubh.ubh_time";
                    break;
                default:
                    return json(format('', 223, "缺少必要参数"));
                    break;
            }
            $list = $this->ubh->joinGetAll($join, $alias, $ubh_where, $pageParam = [], $order, 0, $field, $group);
            if ($ubh_where['ubh_type'] == 3) {
                foreach ($list['data'] as $key => $value) {
                    $list['data'][$key]['es_thumb'] = IMG_URL . $value['es_thumb'];
                }
            } else if ($ubh_where['ubh_type'] == 1) {
                foreach ($list['data'] as $key => $value) {
                    $list['data'][$key]['g_show_img_path'] = IMG_URL . $value['g_show_img_path'];
                    $gsp_price = $this->gsp->getOne(["g_id"=>$value["g_id"]],"gsp_price");
                    $list['data'][$key]['g_current_price'] = empty($gsp_price)?0:$gsp_price;
                    // if (empty($gsp_price)) {
                    //     unset($list['data'][$key]);
                    // }
                    // sort($list['data']);
                }
            } else if ($ubh_where['ubh_type'] == 0) {
                foreach ($list['data'] as $key => $value) {
                    $list['data'][$key]['ss_logo_img'] = IMG_URL . $value['ss_logo_img'];
                    $g_where = ["ss_id" => $value['ss_id'], "g_goods_verify" => '1', "s_is_show" => '0'];
                    $g_field = "g_id, g_name, g_current_price, g_show_img_path";
                    /*查找三个商品*/
                    $list['data'][$key]['goods_info'] = $this->g->getList($g_where, $g_field, ["g_add_time"=>"desc"], 0, 3);
                    foreach ($list['data'][$key]['goods_info'] as $k => $val) {
                        $list['data'][$key]['goods_info'][$k]['g_show_img_path'] = IMG_URL . $val['g_show_img_path'];
                    }
                }
            }
            // dump($list['data']);
            return json(format($list['data']));
        } else {
            return json(format('', 223, $data['msg']));
        }
    }

    /**
     * 删除用户历史浏览记录(校验签名)
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-11-03
     * @param ubh_type      [浏览记录类型(0:店铺,1:商品,2:招标,3:规范)]
     */
    public function delUserBrowsingHistory()
    {
        $usa_ids=$this->param['ubh_id'];
        if (empty($this->param['ubh_id'])) {
            return json(format('', 223, "传输数据有误!~"));
        }
        $usa_ids = json_decode($usa_ids,true);
       
        $ubh_where['ubh_id'] = ["in",implode(",",$usa_ids)];
        $ret = $this->ubh->del($ubh_where);
        if ($ret) {
            return json(format('', 200, '删除历史浏览成功!'));
        } else {
            return json(format('', 223, '删除失败!'));
        }
    }
    /**
     * 添加用户简历
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-14
     */
    public function userResumeAdd()
    {
        $save['u_id'] = $this->u_id;
        $save['ur_name'] = $this->param['ur_name'];
        $save['ur_user_name'] = $this->param['ur_user_name'];
        $save['ur_sex'] = $this->param['ur_sex'];
        $save['ur_birthday'] = $this->param['ur_birthday'];
        $save['ur_mobile'] = $this->param['ur_mobile'];
        $save['ur_email'] = $this->param['ur_email'];
        $save['ur_living_province'] = $this->param['ur_living_province'];
        $save['ur_living_city'] = $this->param['ur_living_city'];
        $save['ur_living_area'] = $this->param['ur_living_area'];
        $save['ur_exoect_workplace_province'] = $this->param['ur_exoect_workplace_province'];
        $save['ur_exoect_workplace_city'] = $this->param['ur_exoect_workplace_city'];
        $save['ur_exoect_workplace_area'] = $this->param['ur_exoect_workplace_area'];
        $save['ur_highest_education'] = $this->param['ur_highest_education'];
        $save['ur_year_of_work'] = $this->param['ur_year_of_work'];
        $save['us_ids'] = $this->param['us_ids'];
        $save['ui_id'] = $this->param['ui_id'];
        $save['ur_expected_salary'] = $this->param['ur_expected_salary'];
        $save['ur_emergency_contact'] = $this->param['ur_emergency_contact'];
        $save['ur_emergency_contact_mobile'] = $this->param['ur_emergency_contact_mobile'];
        $save['ur_self_evaluation'] = $this->param['ur_self_evaluation'];
        $rule = [
            "ur_name" => "require|max:30|chsAlpha",
            "ur_user_name" => "require|max:20|chsAlpha",
            "ur_sex" => "require|in:0,1",
            "ur_birthday" => "require",
            "ur_mobile" => "require",
            "ur_mobile" => ['regex' => '/^1(3|2|4|5|7|8)\d{9}$/'],
            "ur_email" => "email",
            "ur_living_province" => "number",
            "ur_living_city" => "number",
            "ur_living_area" => "number",
            "ur_exoect_workplace_province" => "number",
            "ur_exoect_workplace_city" => "number",
            "ur_exoect_workplace_area" => "number",
            "ur_highest_education" => "require|in:0,1,2,3,4,5",
            "ur_year_of_work" => "require|in:1,2,3,4,5,6",
            "us_ids" => "require",
            "ui_id" => "require|number",
            "ur_expected_salary" => "require|number|max:6",
            "ur_emergency_contact" => "require|max:30|chsAlpha",
            "ur_emergency_contact_mobile" => "require",
            "ur_emergency_contact_mobile" => ['regex' => '/^1(3|2|4|5|7|8)\d{9}$/'],
        ];
        $msg = [
            "ur_name.require" => "简历名称必须填写!~",
            "ur_name.max" => "简历名称长度为30个字符!~",
            "ur_name.chsAlpha" => "简历名称只能是中英文!~",
            "ur_user_name.require" => "姓名必须填写!~",
            "ur_user_name.max" => "姓名名称长度为20个字符!~",
            "ur_user_name.chsAlpha" => "姓名只能是中英文!~",
            "ur_sex.require" => "性别必须选择!~",
            "ur_sex.in" => "性别选择有误!~",
            "ur_birthday.require" => "出生年月必须选择!~",
            "ur_mobile.require" => "手机号必须填写~!",
            "ur_mobile.regex" => "手机号填写有误~!",
            "ur_email.email" => "邮箱必须填写",
            "ur_living_province.number" => "省份选择有误!~",
            "ur_living_city.number" => "城市选择有误!~",
            "ur_living_area.number" => "区域选择有误!~",
            "ur_exoect_workplace_province.number" => "省份选择有误!~",
            "ur_exoect_workplace_city.number" => "城市选择有误!~",
            "ur_exoect_workplace_area.number" => "区域选择有误!~",
            "ur_highest_education.require" => "最高学历必须选择!~",
            "ur_highest_education.in" => "最高学历选择有误!~",
            "ur_year_of_work.require" => "工作年限必须选择!~",
            "ur_year_of_work.in" => "工作年限选择有误!~",
            "us_ids.require" => "技能必须选择!~",
            "ui_id.require" => "行业必须选择!~",
            "ui_id.number" => "行业选择有误!~",
            "ur_expected_salary.require" => "期望工资必须填写!~",
            "ur_expected_salary.number" => "期望工资填写有误!~",
            "ur_expected_salary.max" => "期望工资最大长度为6位!~",
            "ur_emergency_contact.require" => "紧急联系人姓名必须填写!~",
            "ur_emergency_contact.max" => "紧急联系人姓名长度为30个字符",
            "ur_emergency_contact.chsAlpha" => "紧急联系人姓名只能是中英文",
            "ur_emergency_contact_mobile.require" => "紧急联系人电话必须填写!~",
            "ur_emergency_contact_mobile.regex" => "紧急联系人电话填写有误!~",
        ];
        $data = verify($save, $rule, $msg);
        $condition= ["u_id" => $this->u_id];
        $count = $this->ur->getCount($condition);
        if ($count >= config("plugin")['userResumeAddCount']) {
            return json(format('', 242, '最多可添加5个简历!~'));
        }
        if ($data['code'] === 1) {
            if (!empty($_FILES)) {
                $path = "user_resume_head/" . date("y_m_d", time());
                /*上传头像*/
                $file_info = uploadImage($path);
                if ($file_info['code'] == 200) {
                    $save['ur_headimg_path'] = $file_info['pic_cover'];
                } else {
                    return json(format('', 205, $file_info['msg']));
                }
            }
            $ret['ur_id'] = $this->ur->save($save);
            if ($ret['ur_id'] > 0) {
                return json(format(rc4Encypt($ret), 200, '简历添加成功!~'));
            } else {
                return json(format('', 241, '简历添加失败!~'));
            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 修改简历
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-18
     */
    public function userResumeEdit()
    {
        $where = ['ur_id' => $this->param['ur_id']];
        $save['u_id'] = $this->u_id;
        $save['ur_name'] = $this->param['ur_name'];
        $save['ur_user_name'] = $this->param['ur_user_name'];
        $save['ur_sex'] = $this->param['ur_sex'];
        $save['ur_birthday'] = $this->param['ur_birthday'];
        $save['ur_mobile'] = $this->param['ur_mobile'];
        $save['ur_email'] = $this->param['ur_email'];
        $save['ur_living_province'] = $this->param['ur_living_province'];
        $save['ur_living_city'] = $this->param['ur_living_city'];
        $save['ur_living_area'] = $this->param['ur_living_area'];
        $save['ur_exoect_workplace_province'] = $this->param['ur_exoect_workplace_province'];
        $save['ur_exoect_workplace_city'] = $this->param['ur_exoect_workplace_city'];
        $save['ur_exoect_workplace_area'] = $this->param['ur_exoect_workplace_area'];
        $save['ur_highest_education'] = $this->param['ur_highest_education'];
        $save['ur_year_of_work'] = $this->param['ur_year_of_work'];
        $save['us_ids'] = $this->param['us_ids'];
        $save['ui_id'] = $this->param['ui_id'];
        $save['ur_expected_salary'] = $this->param['ur_expected_salary'];
        $save['ur_emergency_contact'] = $this->param['ur_emergency_contact'];
        $save['ur_emergency_contact_mobile'] = $this->param['ur_emergency_contact_mobile'];
        $save['ur_self_evaluation'] = $this->param['ur_self_evaluation'];
        $rule = [
            "ur_name" => "require|max:30|chsAlpha",
            "ur_user_name" => "require|max:20|chsAlpha",
            "ur_sex" => "require|in:0,1",
            "ur_birthday" => "require",
            "ur_mobile" => "require",
            "ur_mobile" => ['regex' => '/^1(3|2|4|5|7|8)\d{9}$/'],
            "ur_email" => "email",
            "ur_living_province" => "number",
            "ur_living_city" => "number",
            "ur_living_area" => "number",
            "ur_exoect_workplace_province" => "number",
            "ur_exoect_workplace_city" => "number",
            "ur_exoect_workplace_area" => "number",
            "ur_highest_education" => "require|in:0,1,2,3,4,5",
            "ur_year_of_work" => "require|in:1,2,3,4,5,6",
            "us_ids" => "require",
            "ui_id" => "require|number",
            "ur_expected_salary" => "require|number|max:6",
            "ur_emergency_contact" => "require|max:30|chsAlpha",
            "ur_emergency_contact_mobile" => "require",
            "ur_emergency_contact_mobile" => ['regex' => '/^1(3|2|4|5|7|8)\d{9}$/'],
        ];
        $msg = [
            "ur_name.require" => "简历名称必须填写!~",
            "ur_name.max" => "简历名称长度为30个字符!~",
            "ur_name.chsAlpha" => "简历名称只能是中英文!~",
            "ur_user_name.require" => "姓名必须填写!~",
            "ur_user_name.max" => "姓名名称长度为20个字符!~",
            "ur_user_name.chsAlpha" => "姓名只能是中英文!~",
            "ur_sex.require" => "性别必须选择!~",
            "ur_sex.in" => "性别选择有误!~",
            "ur_birthday.require" => "出生年月必须选择!~",
            "ur_mobile.require" => "手机号必须填写~!",
            "ur_mobile.regex" => "手机号填写有误~!",
            "ur_email.email" => "邮箱必须填写",
            "ur_living_province.number" => "省份选择有误!~",
            "ur_living_city.number" => "城市选择有误!~",
            "ur_living_area.number" => "区域选择有误!~",
            "ur_exoect_workplace_province.number" => "省份选择有误!~",
            "ur_exoect_workplace_city.number" => "城市选择有误!~",
            "ur_exoect_workplace_area.number" => "区域选择有误!~",
            "ur_highest_education.require" => "最高学历必须选择!~",
            "ur_highest_education.in" => "最高学历选择有误!~",
            "ur_year_of_work.require" => "工作年限必须选择!~",
            "ur_year_of_work.in" => "工作年限选择有误!~",
            "us_ids.require" => "技能必须选择!~",
            "ui_id.require" => "行业必须选择!~",
            "ui_id.number" => "行业选择有误!~",
            "ur_expected_salary.require" => "期望工资必须填写!~",
            "ur_expected_salary.number" => "期望工资填写有误!~",
            "ur_expected_salary.max" => "期望工资最大长度为6位!~",
            "ur_emergency_contact.require" => "紧急联系人姓名必须填写!~",
            "ur_emergency_contact.max" => "紧急联系人姓名长度为30个字符",
            "ur_emergency_contact.chsAlpha" => "紧急联系人姓名只能是中英文",
            "ur_emergency_contact_mobile.require" => "紧急联系人电话必须填写!~",
            "ur_emergency_contact_mobile.regex" => "紧急联系人电话填写有误!~",
        ];
        $data = verify($save, $rule, $msg);
        if ($data['code'] === 1) {
            if (!empty($_FILES)) {
                $path = "user_resume_head/" . date("y_m_d", time());
                /*上传头像*/
                $file_info = uploadImage($path);
                if ($file_info['code'] == 200) {
                    $save['ur_headimg_path'] = $file_info['pic_cover'];
                } else {
                    return json(format('', 205, $file_info['msg']));
                }
            }
            $ret = $this->ur->save($save,$where);
            if (false !== $ret) {
                if (isset($save['ur_headimg_path']) && !empty($save['ur_headimg_path'])) {
                    $save['ur_headimg_path'] = IMG_URL . $save['ur_headimg_path'];
                }
                return json(format(rc4Encypt($save)));
            } else {
                return json(format('', 241, '简历添加失败!~'));
            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    public function userResumeDel()
    {
        $where['ur_id'] = $this->param['ur_id'];
         if ($this->param['ur_id'] > 0) {
            $ur_info = $this->ur->getRow($where);
            /*获取证书表*/
            $uc_where['uc_id'] = ["in",$ur_info['uc_ids']];
            if (!empty($uc_where['uc_id'])) {
                $this->uc->del($uc_where);
            }
            /*获取学习经历表*/
            $ule_where['ule_id'] = ["in",$ur_info['ule_ids']];
            if (!empty($uc_where['ule_id'])) {
                $this->ule->del($ule_where);
            }
            /*获取工作经历表*/
            $uwe_where['uwe_id'] = ["in",$ur_info['uwe_ids']];
            if (!empty($uc_where['uwe_id'])) {
                $this->uwe->del($uwe_where);
            }
            $this->ur->del($where);
            return json(format());
        }else{
            return json(format('', 242, '必要参数传输有误!~'));
        }
    }
    /**
     * 获取单条简历信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-18
     */
    public function getUserResumeInfo()
    {
        $where = ['ur_id' => $this->param['ur_id']];
        if ($this->param['ur_id'] > 0) {
            $ur_info = $this->ur->getRow($where);
            /*获取技能表*/
            $us_where['us_id'] = ["in",$ur_info['us_ids']];
            $ur_info['us_ids'] = $this->us->getList($us_where);
            /*获取证书表*/
            $uc_where['uc_id'] = ["in",$ur_info['uc_ids']];
            $ur_info['uc_ids'] = $this->uc->getList($uc_where);
            /*获取学习经历表*/
            $ule_where['ule_id'] = ["in",$ur_info['ule_ids']];
            $ur_info['ule_ids'] = $this->ule->getList($ule_where);
            /*获取工作经历表*/
            $uwe_where['uwe_id'] = ["in",$ur_info['uwe_ids']];
            $ur_info['uwe_ids'] = $this->uwe->getList($uwe_where);
            
            $ui_where['ui_id'] = $ur_info['ui_id'];
            $ur_info['ui_id'] = $this->ui->getList($ui_where);
            $ur_info['ur_headimg_path'] = IMG_URL . $ur_info['ur_headimg_path'];
            return json(format(rc4Encypt($ur_info)));
        }else{
            return json(format('', 242, '必要参数传输有误!~'));
        }
    }
    /**
     * 添加证书
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-18
     */
    public function userCertificateAdd()
    {
        $where['ur_id'] = $this->param['ur_id'];
        $save['u_id'] = $this->u_id;
        $save['uc_name'] = $this->param['uc_name'];
        $save['uc_get_time'] = $this->param['uc_get_time'];
        $rule = [
            "u_id" => 'require|number',
            "uc_name" => 'require|max:40',
            "uc_get_time" => 'require|number',
        ];
        $msg = [
            "u_id.require" => "缺少必要参数!~",
            "u_id.number" => "必要参数传输有误!~",
            "uc_name.require" => "证书名称必须填写!~",
            "uc_name.max" => "证书名称最多能填写40个字符!~",
            "uc_get_time.require" => "获取时间必须选择!~",
            "uc_get_time.number" => "获取证书时间选择有误!~",
        ];
        $data = verify($save, $rule, $msg);
        if ($data['code'] === 1) {
            if (!empty($_FILES)) {
                $path = "user_resume_cert/" . date("y_m_d", time());
                /*上传头像*/
                $file_info = uploadImage($path);
                if ($file_info['code'] == 200) {
                    $save['uc_img_path'] = $file_info['pic_cover'];
                } else {
                    return json(format('', 205, $file_info['msg']));
                }
            }
            $save['uc_id'] = $this->uc->save($save);

            $uc_ids = $this->ur->getOne($where, "uc_ids");
            if ($uc_ids) {
                $update['uc_ids'] = $uc_ids . $save['uc_id'] . ",";
            } else {
                $update['uc_ids'] = $save['uc_id'] . ",";
            }
            $this->ur->save($update, $where);
            if ($save['uc_id']> 0) {
                if (!empty($_FILES)) {
                    $save['uc_img_path'] = IMG_URL . $save['uc_img_path'];
                }
                return json(format($save));
            } else {
                return json(format('', 242, "证书信息添加失败!~"));
            }
        }else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 修改证书
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-18
     */
    public function userCertificateEdit()
    {
        $where['uc_id'] = $this->param['uc_id'];
        $save['u_id'] = $this->u_id;
        $save['uc_name'] = $this->param['uc_name'];
        $save['uc_get_time'] = $this->param['uc_get_time'];
        $rule = [
            "u_id" => 'require|number',
            "uc_name" => 'require|max:40',
            "uc_get_time" => 'require|number',
        ];
        $msg = [
            "u_id.require" => "缺少必要参数!~",
            "u_id.number" => "必要参数传输有误!~",
            "uc_name.require" => "证书名称必须填写!~",
            "uc_name.max" => "证书名称最多能填写40个字符!~",
            "uc_get_time.require" => "获取时间必须选择!~",
            "uc_get_time.number" => "获取证书时间选择有误!~",
        ];
        $data = verify($save, $rule, $msg);
        if ($data['code'] === 1) {
            /*如果文件不为空*/
            if (!empty($_FILES)) {
                $path = "user_resume_cert/" . date("y_m_d", time());
                /*上传头像*/
                $file_info = uploadImage($path);
                if ($file_info['code'] == 200) {
                    $save['uc_img_path'] = $file_info['pic_cover'];
                } else {
                    return json(format('', 205, $file_info['msg']));
                }
            }
            $ret = $this->uc->save($save,$where);
            if (false !== $ret) {
                $info = $this->uc->getRow($where);
                $info['uc_img_path'] = IMG_URL . $info['uc_img_path'];
                return json(format($info));
            } else {
                return json(format('', 243, "证书信息修改失败!~"));
            }
        }else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 添加工作经历
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-14
     */
    public function userWorkExperienceAdd()
    {
        $where['ur_id'] = $this->param['ur_id'];
        $save['u_id'] = $this->u_id;
        $save['uwe_start_time'] = $this->param['uwe_start_time'];
        $save['uwe_end_time'] = $this->param['uwe_end_time'];
        $save['uwe_employer'] = $this->param['uwe_employer'];
        $save['uwe_duties'] = $this->param['uwe_duties'];
        $save['uwe_witness'] = $this->param['uwe_witness'];
        $save['uwe_reason_for_leaving'] = $this->param['uwe_reason_for_leaving'];
        $save['uwe_contents'] = $this->param['uwe_contents'];
        $rule = [
            "uwe_start_time" => 'require|number', /*工作经历开始时间 */
            "uwe_end_time" => 'require|number', /*工作经历结束时间*/
            "uwe_employer" => 'require|chsAlphaNum|max:50', /*工作单位*/
            "uwe_duties" => 'require|max:20', /*职务*/
            "uwe_witness" => 'require|max:20', /*证明人*/
            "uwe_reason_for_leaving" => 'require|max:255', /*离职原因*/
            "uwe_contents" => 'require|max:255', /*工作内容*/
        ];
        $msg = [
            "uwe_start_time.require" => '开始时间是必须要填写的哦!~', /*时间戳只能校验是不是数字*/
            "uwe_start_time.number" => '开始时间格式不正确!~', /*时间戳只能校验是不是数字*/
            "uwe_end_time.require" => '结束时间必须要填写的!~', /*时间戳只能校验是不是数字*/
            "uwe_end_time.number" => '结束时间格式不正确!~', /*时间戳只能校验是不是数字*/
            "uwe_employer.require" => '工作单位必须要填写!~',
            "uwe_employer.chsAlphaNum" => '工作单位只能填写中文,英文和数字!~',
            "uwe_employer.max" => '单位名称最长不能超过50个字符!~',
            "uwe_duties.require" => '职务必须填写!~',
            "uwe_duties.max" => '职务的长度不能超过20个字符!~',
            "uwe_witness.require" => '证明人必须填写!~',
            "uwe_witness.max" => '证明人长度不能超过20个字符!~',
            "uwe_reason_for_leaving.require" => '离职原因必须填写!~',
            "uwe_reason_for_leaving.max" => '离职原因最多填写255个字符!~',
            "uwe_contents.require" => '工作内容必须填写!~',
            "uwe_contents.max" => '工作内容最多填写255个字符!~',
        ];
        $data = verify($save, $rule, $msg);
        if ($data['code'] === 1) {
            $save['uwe_id'] = $this->uwe->save($save);
            /*查询工作ID 如果有,在后面拼装,如果没有直接拼装*/
            $uwe_ids = $this->ur->getOne($where, "uwe_ids");
            if ($uwe_ids) {
                $update['uwe_ids'] = $uwe_ids . $save['uwe_id'] . ",";
            } else {
                $update['uwe_ids'] = $save['uwe_id'] . ",";
            }
            $this->ur->save($update, $where);
            if ($save['uwe_id'] > 0) {
                return json(format(rc4Encypt($save)));
            } else {
                return json(format('', 240, '工作经历添加失败!~'));
            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 修改工作经历
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-18
     */
    public function userWorkExperienceEdit()
    {
        $where['uwe_id'] = $this->param['uwe_id'];
        $save['u_id'] = $this->u_id;
        $save['uwe_start_time'] = $this->param['uwe_start_time'];
        $save['uwe_end_time'] = $this->param['uwe_end_time'];
        $save['uwe_employer'] = $this->param['uwe_employer'];
        $save['uwe_duties'] = $this->param['uwe_duties'];
        $save['uwe_witness'] = $this->param['uwe_witness'];
        $save['uwe_reason_for_leaving'] = $this->param['uwe_reason_for_leaving'];
        $save['uwe_contents'] = $this->param['uwe_contents'];
        $rule = [
            "uwe_start_time" => 'require|number', /*工作经历开始时间 */
            "uwe_end_time" => 'require|number', /*工作经历结束时间*/
            "uwe_employer" => 'require|chsAlphaNum|max:50', /*工作单位*/
            "uwe_duties" => 'require|max:20', /*职务*/
            "uwe_witness" => 'require|max:20', /*证明人*/
            "uwe_reason_for_leaving" => 'require|max:255', /*离职原因*/
            "uwe_contents" => 'require|max:255', /*工作内容*/
        ];
        $msg = [
            "uwe_start_time.require" => '开始时间是必须要填写的哦!~', /*时间戳只能校验是不是数字*/
            "uwe_start_time.number" => '开始时间格式不正确!~', /*时间戳只能校验是不是数字*/
            "uwe_end_time.require" => '结束时间必须要填写的!~', /*时间戳只能校验是不是数字*/
            "uwe_end_time.number" => '结束时间格式不正确!~', /*时间戳只能校验是不是数字*/
            "uwe_employer.require" => '工作单位必须要填写!~',
            "uwe_employer.chsAlphaNum" => '工作单位只能填写中文,英文和数字!~',
            "uwe_employer.max" => '单位名称最长不能超过50个字符!~',
            "uwe_duties.require" => '职务必须填写!~',
            "uwe_duties.max" => '职务的长度不能超过20个字符!~',
            "uwe_witness.require" => '证明人必须填写!~',
            "uwe_witness.max" => '证明人长度不能超过20个字符!~',
            "uwe_reason_for_leaving.require" => '离职原因必须填写!~',
            "uwe_reason_for_leaving.max" => '离职原因最多填写255个字符!~',
            "uwe_contents.require" => '工作内容必须填写!~',
            "uwe_contents.max" => '工作内容最多填写255个字符!~',
        ];
        $data = verify($save, $rule, $msg);
        if ($data['code'] === 1) {
            $ret = $this->uwe->save($save, $where);
            if (false !== $ret) {
                return json(format(rc4Encypt($save)));
            } else {
                return json(format('', 242, '工作经历修改失败!~'));
            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 添加学习经历(校验签名)
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-14
     */
    public function UserLearningExperienceAdd()
    {

        $where['ur_id'] = $this->param['ur_id'];
        $save['u_id'] = $this->u_id;
        $save['ule_start_time'] = $this->param['ule_start_time'];
        $save['ule_end_time'] = $this->param['ule_end_time'];
        $save['ule_school_name'] = $this->param['ule_school_name'];
        $save['ule_learning_content'] = $this->param['ule_learning_content'];
        $save['ule_learning_results'] = $this->param['ule_learning_results'];



        $rule = [
            "ule_start_time" => 'require|number', /*时间戳只能校验是不是数字*/
            "ule_end_time" => 'require|number', /*时间戳只能校验是不是数字*/
            "ule_school_name" => 'require|chsAlphaNum|max:50',
            "ule_learning_content" => 'require',
            "ule_learning_results" => 'require',
        ];
        $msg = [
            "ule_start_time.require" => '学习开始时间传输出错!~',
            "ule_start_time.number" => '学习开始时间必须选择!~',
            "ule_end_time.require" => '学习结束时间必须选择!~',
            "ule_end_time.number" => '学习结束时间传输出错!~',
            "ule_school_name.require" => '学校名称必须要填写!~',
            "ule_learning_content.require" => "参数传输出错!~",
            "ule_learning_results.require" => "参数传输出错!~",
        ];
        logs($save);
        $data = verify($save, $rule, $msg);
        if ($data['code'] === 1) {
            $save['ule_id'] = $this->ule->save($save);
            /*获取学习经id*/
            $update['ule_ids'] = $this->ur->getOne($where, "ule_ids");
            if ($update['ule_ids']) {
                $update['ule_ids'] = $update['ule_ids'] . $save['ule_id'] . ",";
            } else {
                $update['ule_ids'] = $save['ule_id'] . ",";
            }
            $this->ur->save($update, $where);
            if ($save['ule_id'] > 0) {
                return json(format(rc4Encypt($save)));
            } else {
                return json(format('', 240, '学习经历添加失败!~'));
            }

        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 修改学习经历(校验签名)
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-14
     */
    public function userLearningExperienceEdit()
    {
        $save['u_id'] = $this->u_id;
        $save['ule_start_time'] = $this->param['ule_start_time'];
        $save['ule_end_time'] = $this->param['ule_end_time'];
        $save['ule_school_name'] = $this->param['ule_school_name'];
        $save['ule_learning_content'] = $this->param['ule_learning_content'];
        $save['ule_learning_results'] = $this->param['ule_learning_results'];
        $where['ule_id'] = $this->param['ule_id'];
        $rule = [
            "ule_start_time" => 'require|number', /*时间戳只能校验是不是数字*/
            "ule_end_time" => 'require|number', /*时间戳只能校验是不是数字*/
            "ule_school_name" => 'require|chsAlphaNum|max:50',
            "ule_learning_content" => 'require',
            "ule_learning_results" => 'require',
        ];
        $msg = [
            "ule_start_time.require" => '学习开始时间传输出错!~',
            "ule_start_time" => '学习开始时间必须选择!~',
            "ule_end_time.require" => '学习结束时间必须选择!~',
            "ule_end_time" => '学习结束时间传输出错!~',
            "ule_school_name.require" => '学校名称必须要填写!~',
            "ule_learning_content" => "参数传输出错!~",
            "ule_learning_results" => "参数传输出错!~",
        ];
        $data = verify($save, $rule, $msg);
        if ($data['code'] === 1) {
            $ret = $this->ule->save($save, $where);
            if (false !== $ret) {
                return json(format(rc4Encypt($save)));
            } else {
                return json(format('', 241, '学习经历修改失败!~'));
            }

        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 获取简历的其他信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-18
     * @return [type]            [description]
     */
    public function getResumeOtherInformation()
    {
        $where = ['ur_id' => $this->param['ur_id']];
        /*类型有[ule:学习经历],[uwe:工作经历],[uc:证书]*/
        $type = $this->param['type'];
        $rule = [
            "ur_id" => 'require|number',
        ];
        $msg = [
            "ur_id.require" => '缺少必要参数!~',
            "ur_id.number" => '必要参数传输有误,请重试!~',
        ];
        if (empty($type)) {
            return json(format('', 241, "类型出错,请重试!~"));
        }
        $data = verify($where, $rule, $msg);
        if ($data['code'] === 1) {
            /*查询对应简历的相应类型id*/
            $type_ids = $this->ur->getOne($where, $type . "_ids");
            $type_info = explode(",", $type_ids);
            $ret_info = [];
            /*不为空的话循环类型 id*/
            if (!empty($type_info)) {
                if (count($type_info) > 1) {
                    foreach ($type_info as $key => $value) {
                        if ($value != '') {
                            /*查询类型信息*/
                            $ret_info[] = $this->$type->getRow([$type . "_id" => $value]);
                        }
                    }
                } else {
                    $ret_info = "";
                }
            } else {
                $ret_info = "";
            }
            if ($type == "uc") {
                if (isset($ret_info) && !empty($ret_info)) {
                    foreach ($ret_info as $key => $value) {
                        if ($value['uc_img_path']) {
                            $ret_info[$key]['uc_img_path'] = IMG_URL . $value['uc_img_path'];
                        }
                    }
                }
            }
            return json(format(rc4Encypt($ret_info)));
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 简历列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-14
     * @return [type]            [description]
     */
    public function getUserResumeList()
    {
        $list = $this->ur->getList(["u_id" => $this->u_id], "ur_headimg_path,ur_id,ur_name");
        foreach ($list as $key => $value) {
            $list[$key]['ur_headimg_path'] = IMG_URL .$value['ur_headimg_path'];
        }
        if (!empty($list)) {
            return json(format(rc4Encypt($list)));
        } else {
            return json(format());
        }
    }
    /**
     * 获取技能
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-14
     */
    public function getUserSkills()
    {
        $list = $this->us->getList(["us_status" => "1"]);
        return json(format($list));
    }
    /**
     * 获取行业信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-14
     */
    public function getUserIndustry()
    {
        $list = $this->ui->getList(["ui_status" => "1"]);
        return json(format($list));
    }
    /**
     * 统一获取用户信息接口(需要校验)
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-12
     */
    public function getUserInfo()
    {
        $rule = [
            "u_headimg" => 'url',
        ];
        $msg = [
            "u_headimg.url" => '品牌官网地址填写有误!~',
        ];
        $data = verify($this->user, $rule, $msg);
        /*校验头像是不是地址,不是的话拼装地址*/
        if ($data['code'] !== 1) {
            $this->user['u_headimg'] = IMG_URL . $this->user['u_headimg'];
        }
        if ($this->user) {
            return json(format(rc4Encypt($this->user)));
        }else{
            return json(format(rc4Encypt('')));
        }
    }
    /**
     * 修改用户信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-14
     */
    public function changeUserInfo()
    {
        /*判断性别是否存在*/
        if (isset($this->param['u_sex'])) {
            $save['u_sex'] = $this->param['u_sex'];
        }
        /*判断昵称是否存在*/
        if (isset($this->param['u_name']) && !empty($this->param['u_name'])) {
            $save['u_name'] = $this->param['u_name'];
        }
        /*判断生日是否存在*/
        if (isset($this->param['u_birthday']) && !empty($this->param['u_birthday'])) {
            $save['u_birthday'] = $this->param['u_birthday'];
        }
        $where['u_id'] = $this->u_id;
        /*查询用户信息是否存在*/
        $count = $this->u->getCount($where);
        if ($count < 0) {
            return json(format('', 228, "用户不存在!~"));
        } else {
            // $rule = [
            //     "u_sex" => 'in:0,1,2',
            //     "u_name" => 'chsDash|max:30',
            //     "u_birthday" => 'require',
            // ];
            // $msg = [
            //     "u_sex.in" => '性别选择有误!~',
            //     "u_name.max" => '昵称超出长度,最大长度为30!~',
            //     "u_name.chsDash" => '昵称只能是汉字、字母、数字和下划线_及破折号-!~',
            // ];
            // $data = verify($save, $rule, $msg);
            // if ($data['code'] === 1) {
                /*更新信息*/
                $ret = $this->u->save($save, $where);
                if ($ret === false) {
                    return json(format('', 239, "用户不存在!~"));
                } else {
                    return json(format($save));
                }
            // } else {
            //     return json(format('', 223, $data['msg']));
            // }
        }
    }
    /**
     * 修改用户头像(需要校验)
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-14
     */
    public function changeUserHeadImg()
    {
        $where["u_id"] = $this->user['u_id'];
        $path = "user_headimg/" . date("y_m_d", time());
        /*上传头像*/
        $file_info = uploadImage($path);
        if ($file_info['code'] == 200) {
            $save['u_headimg'] = $file_info['pic_cover'];
            /*更新头像信息*/
            $ret = $this->u->save($save, $where);
            if ($ret === false) {
                return json(format('', 205, $file_info['msg']));
            } else {
                $u_headimg['u_headimg'] = IMG_URL . $save['u_headimg'];
                return json(format($u_headimg, 200, "头像修改成功!~"));
            }
        } else {
            return json(format('', 205, $file_info['msg']));
        }
    }
    /**
     * 修改密码
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-12
     */
    public function changePasswd()
    {
        $old_passwd = encryptPasswd($this->param['old_passwd']);
        $save['u_passwd'] = encryptPasswd($this->param['new_passwd']);
        $save['u_passwd_comfirm'] = encryptPasswd($this->param['new_passwd_comfirm']);
        $where["u_id"] = $this->param['u_id'];
        if ($save['u_passwd'] !== $save['u_passwd_comfirm']) {
            return json(format('', 234, "两次输入密码不一致!~"));
        }
        $rule = array(
            "u_passwd" => 'require',
        );
        $msg = array(
            "u_passwd.require" => '亲,用户密码是必须要填写的噢!~',
        );
        if (isset($this->user['u_passwd']) && ($this->user['u_passwd'] != $old_passwd)) {
            return json(format('', 237, "原密码输入有误!~"));
        } else {
            $data = verify($save, $rule, $msg);
            if ($data['code'] === 1) {
                unset($save['u_passwd_comfirm']);
                $ret = $this->u->save($save, $where);
                if (false === $ret) {
                    return json(format('', 238, "密码修改失败!~"));
                } else {
                    return json(format('', 200, "密码修改成功!~"));
                }
            } else {
                return json(format('', 223, $data['msg']));
            }
        }
    }
    /**
     * 忘记密码
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-12
     */
    public function forgetPasswd()
    {

//        $this->param['u_mobile'] = "18745016473";
//        $this->param['u_passwd'] = "123456";

        $info['u_mobile'] = $this->param['u_mobile'];
        // $info['sl_code'] = $this->param['sl_code'];
        // $info['sl_id'] = $this->param['sl_id'];
        $info['u_passwd'] = $this->param['u_passwd'];
        $rule = [
            "u_mobile" => "require",
            "u_mobile" => ['regex' => '/^1(3|2|4|5|7|8)\d{9}$/'],
            // "sl_code" => "require|number",
            // "sl_id" => "require|number",
            "u_passwd" => "require",
        ];
        $msg = [
            "u_mobile.require" => "手机号是必须要填写的噢!~",
            "u_mobile.regex" => "手机号输入有误噢!~",
            // "sl_code.require" => "验证码必须填写哦!~",
            // "sl_code.number" => "验证码填写必须是数字哦!~",
            // "sl_id.require" => "传输数据缺少参数哦!~",
            // "sl_id.number" => "缺少的参数必须是正整数哦!~",
            "u_passwd.require" => "密码必须要填写哦!~",
        ];
        $data = verify($info, $rule, $msg);
        if ($data['code'] === 1) {
            // $where['sl_id'] = $info['sl_id'];
            // unset($info['sl_id']);
            /*查询验证码信息*/
            // $sl_info = $this->sl->getRow($where);
            /*如果空的话提示错误信息*/
            if (!empty($info)) {
                // if ($sl_info['sl_code'] != $info['sl_code']) {
                //     return json(format('', 224, "验证码填写有误!~"));
                // }
                /*300为验证码超时时间,*/
                // if ($sl_info['sl_add_time'] < time() - $this->smsOvertimeYime) {
                //     return json(format('', 225, "验证码超时,请重新发送!~"));
                // }
                $condition['u_mobile'] = $info['u_mobile'];
                $count = $this->u->getCount($condition);
                /*如果查询出用户信息*/
                if (!empty($count) && $count > 0) {
                    $add['u_passwd'] = encryptPasswd($info['u_passwd']);
                    $ret = $this->u->save($add, $condition);
                    if (false !== $ret) {
                        return json(format('', 200, "密码修改成功!~"));
                    } else {
                        return json(format('', 227, "密码修改失败!~"));
                    }
                } else {
                    return json(format('', 228, "用户不存在!~"));
                }
            } else {
                return json(format('', 230, "缺少必要参数哦!~"));
            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }
    /**
     * 用户收货地址列表(需要校验)
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-12
     */
    public function userAddressList()
    {
        $rule = [
            "u_id" => "require|number",
        ];
        $msg = [
            "u_id.require" => "用户id不能为空!~",
            "u_id.number" => "手机号输入有误噢!~",
        ];

        $info['u_id'] = $this->param['u_id'];
        $data = verify($info, $rule, $msg);

        if ($data['code'] === 1) {
            /*获取用户收货地址*/
            $userAddressList = $this->usa->getList($info);
            foreach ($userAddressList as $key => $value) {
                $pro = $this->r->getRow(["r_id"=>$value['usa_province']],'r_name');
                $cit = $this->r->getRow(["r_id"=>$value['usa_city']],'r_name');
                $dis = $this->r->getRow(["r_id"=>$value['usa_district']],'r_name');
                $userAddressList[$key]['addrs']=$pro["r_name"].' '.$cit["r_name"].' '.$dis["r_name"];

            }
            return json(format($userAddressList, 200, "获取列表成功!~"));
        } else {
            return json(format('', 223, $data['msg']));
        }
    }



    /**
     * 用户收货地址操作(添加,修改)
     * @author 王牧田
     * @date   2018-3-16
     */
    public function editAddressAction(){
        if(!isset($this->param['usa_province'])){
            return json(format('', 227, "请选择省份!~"));
        }
        if(!isset($this->param['usa_city'])){
            return json(format('', 227, "请选择城市!~"));
        }
        if(!isset($this->param['usa_district'])){
            return json(format('', 227, "请选择地区!~"));
        }
        $usa_province =  str_replace("省", "", $this->param['usa_province']);

        $usa_province =  str_replace("市", "", $usa_province);

        $usa_province_id = $this->r->getOne(["r_name"=>["like","%".$usa_province."%"]],"r_id");

        $usa_city =  str_replace("市", "", $this->param['usa_city']);
        $usa_city_id = $this->r->getOne(["r_name"=>["like","%".$usa_city."%"]],"r_id");

        $usa_district =   $this->param['usa_district'];
        $usa_district_id = $this->r->getOne(["r_name"=>["like","%".$usa_district."%"]],"r_id");

        $act = $this->param['act'];

        $info['u_id'] = $this->param['u_id'];  //用户id

        $info['usa_province'] = $usa_province_id; //用户收货省

        $info['usa_city'] = $usa_city_id;         //用户收货市

        $info['usa_district'] = $usa_district_id; //用户收货区

        $info['usa_address'] = $this->param['usa_address'];   //详情地址

        $info['usa_user_name'] = $this->param['usa_user_name']; //用户名

        $info['usa_mobile'] = $this->param['usa_mobile'];  //联系方式

        $info['default_address'] = $this->param['default_address']; //是否设置成默认地址 0 否 1:是

        $rule = [
            "u_id" => "require",
            "usa_province" => "require",
            "usa_city" => "require",
            "usa_district" => "require",
            "usa_address" => "require",
            "usa_user_name" => "require",
            "usa_mobile" => "require",
        ];
        $msg = [
            "u_id.require" => "用户id不能为空噢!~",
            "usa_province.require" => "用户收货省不能为空噢!~",
            "usa_city.require" => "用户收货市不能为空噢!~",
            "usa_district.require" => "用户收货区不能为空噢!~",
            "usa_address.require" => "详情地址不能为空噢!~",
            "usa_user_name.require" => "用户名不能为空噢!~",
            "usa_mobile.require" => "联系方式不能为空噢!~",

        ];
        $data = verify($info, $rule, $msg);

        if ($data['code'] === 1) {
            $where = [];
            switch ($act) {
                case 'edit':
                    $usa_id = $this->param['usa_id'];  //用户收货地址id
                    $where['usa_id'] = $usa_id;
                case 'add':
                    //如果设置当前为默认地址 清除其他所选过的默认地址
                    if($info['default_address']==1){
                        $this->usa->save(["default_address"=>0],["u_id"=>$info['u_id']]);
                    }
                    $ret = $this->usa->save($info, $where);

                    if (false !== $ret) {
                        return json(format($ret, 200, "地址操作成功!~"));
                    } else {
                        return json(format('', 227, "地址操作失败!~"));
                    }
                    break;
            }
        }else {
            return json(format('', 223, $data['msg']));
        }
    }

    /**
     * 用户地址全部删除
     * @author 王牧田
     * @date 2018-04-27
     */
    public function delAllAddress(){
        $usa_ids  = $info["usa_ids"] = $this->param['usa_ids'];//[1,2,3]
        $usa_ids = json_decode($usa_ids,true);
        $rule = [
            "usa_ids" => "require",
        ];
        $msg = [
            "usa_ids.require" => "地址id不能为空噢!~",
        ];
        $data = verify($info, $rule, $msg);

        if ($data['code'] === 1) {

            $ret = $this->usa->del(["usa_id" => ["in", implode(",", $usa_ids)]]);
            if (false !== $ret) {
                return json(format('', 200, "地址删除成功!~"));
            } else {
                return json(format('', 227, "地址删除失败!~"));
            }
        }else{
            return json(format('', 223, $data['msg']));
        }


    }


    /**
     * 用户收货地址操作(删除)
     * @author 王牧田
     * @date   2018-4-19
     */

    public function delAddressAction(){
        $usa_id = $this->param['usa_id'];  //用户地址id
        $where['usa_id'] = $usa_id;
        $ret = $this->usa->del($where);
        if (false !== $ret) {
            return json(format());
        } else {
            return json(format('', 223, '删除失败!'));
        }
    }


    /**
     * 账户安全
     * @author 李鑫
     * @date   2018-04-19
     */
    public function bindingemail()
    {
        $info['u_mobile'] = $this->param['u_mobile'];
        $info['sl_code'] = $this->param['sl_code'];
       
        $rule = [
            "u_mobile" => "require",
            "u_mobile" => ['regex' => '/^1(3|2|4|5|7|8)\d{9}$/'],
            "sl_code" => "require|number",
        ];
        $msg = [
            "u_mobile.require" => "手机号是必须要填写的噢!~",
            "u_mobile.regex" => "手机号输入有误噢!~",
            "sl_code.require" => "验证码必须填写哦!~",
            "sl_code.number" => "验证码填写必须是数字哦!~",
        ];
        $data = verify($info, $rule, $msg);
        if ($data['code'] === 1) {
            $where['sl_mobile'] = $info['u_mobile'];
            unset($info['u_mobile']);
            /*查询验证码信息*/
            $sl_info = $this->sl->getRow($where);
            /*如果空的话提示错误信息*/
            if (!empty($sl_info)) {
                if ($sl_info['sl_code'] != $info['sl_code']) {
                    return json(format('', 224, "验证码填写有误!~"));
                }
                /*300为验证码超时时间,*/
                if ($sl_info['sl_add_time'] < time() - $this->smsOvertimeYime) {
                    return json(format('', 225, "验证码超时,请重新发送!~"));
                }
                if ($sl_info['sl_code'] == $info['sl_code']) {
                    return json(format('',200,"验证码填写正确!~"));
                }
            } else {
                return json(format('', 230, "缺少必要参数哦!~"));
            }
        } else {
            return json(format('', 223, $data['msg']));
        }
    }


    /**
     * [绑定邮箱]
     * @author 王牧田
     * @date 2018-05-10
     */
    public function bindingMailbox(){

//        $this->param['u_mail']="824495596@qq.com";
//        $this->param['sl_code'] = "035310";
//        $this->param['u_id'] = 119;
        $info['u_mail'] = $this->param['u_mail'];
        $info['sl_code'] = $this->param['sl_code'];
        $info['u_id'] = $this->param['u_id'];
        $rule = [
            "u_mobile" => "require",
            "u_mobile" => ['regex' => '/^\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}$/'],
            "sl_code" => "require|number",
        ];
        $msg = [
            "u_mobile.require" => "邮箱不能为空!~",
            "u_mobile.regex" => "邮箱输入有误!~",
            "sl_code.require" => "验证码不能为空!~",
            "sl_code.number" => "验证码填写必须是数字!~",
        ];
        $data = verify($info, $rule, $msg);
        if ($data['code'] === 1) {
            $where['sl_mobile'] = $info['u_mail'];
            /*查询验证码信息*/
            $sl_info = $this->sl->getRow($where);
            /*如果空的话提示错误信息*/
            if (!empty($sl_info)) {
                if ($sl_info['sl_code'] == $info['sl_code']) {
                    $this->u->save(["u_email"=>$info['u_mail']],["u_id"=>$info['u_id']]);
                    return json(format('',200,"邮箱绑定成功!~"));
                }
            } else {
                return json(format('', 230, "缺少必要参数哦!~"));
            }

        }else{
            return json(format('', 223, $data['msg']));
        }
    }



    /**
     * [发票-专业发票]
     * @author 王牧田
     * @date 2018-05-09
     */
    public function invoiceShow(){
//        $this->param['u_id'] = 119;
        $info['u_id'] = $this->param['u_id'];
        $info['i_invtype'] = $this->param['i_invtype'];
        $iRow = $this->i->getRow(["u_id"=>$info['u_id'],"i_invtype"=>$info['i_invtype']]);
        if(empty($iRow)){
            $iListArr["i_id"]="";
            $iListArr["u_id"]="";
            $iListArr["i_name"]="";
            $iListArr["i_num"]="";
            $iListArr["i_email"]="";
            $iListArr["i_regtel"]="";
            $iListArr["i_regaddr"]="";
            $iListArr["i_bank"]="";
            $iListArr["i_banknum"]="";
            $iListArr["i_conname"]="";
            $iListArr["i_contel"]="";
            $iListArr["i_conaddr"]="";
            $iListArr["i_invtype"]=$info['i_invtype'];
            $iRow = $iListArr;
        }
        return json(format($iRow));
    }

    /**
     * [添加发票]
     * @author 王牧田
     * @date 2018-05-09
     */
    public function addInvoice(){

       // $this->param['u_id'] = 1119;
       // $this->param['i_invtype'] = 2;
       // $this->param['i_name'] = "不告诉你21";
       // $this->param['i_num'] = "119xx00000";
       // $this->param['i_email'] = "119xx00000";
       // $this->param['i_taitype'] = "0";
       // $this->param['i_regtel'] = "18744506473";
       // $this->param['i_regaddr'] = "xxxd地址";
       // $this->param['i_bank'] = "建设银行";
       // $this->param['i_banknum'] = "xxx22244556";
       // $this->param['i_conname'] = "王钢蛋";
       // $this->param['i_contel'] = "xxxooxxx";
       // $this->param['i_conaddr'] = "ddddcxxx";

        $info['u_id'] = $this->param['u_id'];
        $info['i_invtype'] = $this->param['i_invtype']; //发票类型(0:电子发票，1.普通发票，2.专用发票)
        $info['i_name'] = $this->param['i_name']; //抬头、公司名称
        $info['i_num'] = input("i_num",""); //纳税人识别号

        switch ($info['i_invtype']){
            case '0':
                $info['i_email'] = input("i_email",""); //收票人邮箱
                $info['i_taitype'] = input("i_taitype",""); //抬头类型(0:单位，1:个人)
                break;
            case '1':
                $info['i_taitype'] = input("i_taitype",""); //抬头类型(0:单位，1:个人)
                break;
            case '2':
                $info['i_regtel'] = input("i_regtel",""); //注册电话
                $info['i_regaddr'] = input("i_regaddr",""); //注册地址 
                $info['i_bank'] = input("i_bank",""); //开户银行
                $info['i_banknum'] = input("i_banknum",""); //开户账号
                $info['i_conname'] = input("i_conname",""); //收件人
                $info['i_contel'] = input("i_contel",""); //联系电话
                $info['i_conaddr'] = input("i_conaddr",""); //联系地址
                break;
        }
        $where = ["u_id"=>$info['u_id'],"i_invtype"=>$info['i_invtype']];
        $iRow = $this->i->getRow($where);
        if(empty($iRow)){
            $ret = $this->i->save($info);
        }else{
            $ret = $this->i->save($info,$where);
        }

        if (false !== $ret) {
            return json(format());
        } else {
            return json(format('', 223, '添加发票失败，稍后重试!'));
        }
        


    }
    /**
     * [版本号]
     * @author 李鑫
     * @date 2018-05-12
     */
    public function edition(){
        $code=$this->param['code'];
        $iRow = $this->e->getRow(["ed_id"=>'1']);
        $iRow['url']= "http://www.gongjiantong.com/apk/gjt.apk";
        if ($code <= $iRow['highestversion']) {
           $iRow['isneedupdata']='1';
        }else{
            $iRow['isneedupdata']='0';
        }
        // 
        // dump($iRow);die();
        return json(format($iRow));
    }

     /**
     * [物流]
     * @author 李鑫
     * @date 2018-05-15
     */

    public function wuliu(){
        // $this->param['type']='zhongtong';
        // $this->param['postid']='70005520146854';
        $type = $this->param['type'];
        $postid =$this->param['postid'];
        $url = "https://www.kuaidi100.com/query?type=".$type."&postid=".$postid;
        $list = http_get($url);
        $lists= json_decode($list,true);

        return json(format($lists['data']));
    }

    /**
     * [协议]
     * @author 李鑫
     * @date 2018-05-12
     */
    public function xieyi(){
        $type = $this->param['type'];
        if ($type == '1') {
            $iRow=$this->a->getRow(["ac_id"=>'2']);
        }else if ($type == '2') {
            $iRow=$this->a->getRow(["ac_id"=>'3']);
        }else if ($type == '3') {
            $iRow=$this->a->getRow(["ac_id"=>'15']);
        }
       
        return json(format($iRow));
    }
    



}
