<?php
namespace app\seller\controller;

use model\ShopArtical as sa;
use model\ShopArticalCategory as sac;

/**
 * 作者：袁中旭
 * 时间：2017-11-12
 * 功能：商户后台文章管理
 */

class Articles extends Base
{
    /**
     * 作者：袁中旭
     * 时间：2017-10-12
     * 功能：继承父类构造函数
     */
    protected $sa;
    protected $sac;
    public function __construct()
    {
        parent::__construct();
        /*文章*/
        $this->sa = new sa();
        /*文章分类*/
        $this->sac = new sac();
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-12
     * 功能：商户文章分类列表
     */
    public function articlesCategoryList()
    {
        if ($this->request->isAjax()) {
            $info = $this->request->only(['id'], "post");
            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require|number',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
                "id.number" => '数据格式不正确噢!~',
            );
            $data = verify($info, $rule, $msg);

            if ($data['code'] === 1) {
                /*获取列表*/
                $sac_list = $this->sac->getList(["sac_parent_id" => $info['id'],'ss_id' => $this->sm_info['ss_id']], [], ['sac_sort_order' => "desc"]);
                return json(format($sac_list, '1'));
            } else {
                return json(format('', '-1', $data['msg']));
            }
            exit();
        }
        /*获取列表*/
        $sac_list = $this->sac->getAll(["sac_parent_id" => 0,'ss_id' => $this->sm_info['ss_id']], [], ['sac_sort_order' => "desc"]);
        return view(
            "articlesCategoryList",
            [
                "list" => $sac_list['data'], /*查询结果*/
                "page" => $sac_list['page'], /*分页和html代码*/
                "lastPage" => $sac_list['lastPage'], /*总页数*/
                "currentPage" => $sac_list['currentPage'], /*当前页码*/
                "total" => $sac_list['total'], /*总条数*/
                "listRows" => $sac_list['listRows'], /*每页显示条数*/
            ]
        );
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-12
     * 功能：商户文章分类添加
     */
    public function articlesCategoryAdd()
    {
        if ($this->request->isPost()) {
            /*只获取post以下参数*/
            $post_sac_info = $this->request->only(['sac_name', 'sac_keywords', 'sac_parent_id_1', 'sac_parent_id_2', 'sac_show_in_nav', 'sac_sort_order', 'sac_desc','sac_alias'], "post");

            /*验证接到的值有没有问题*/
            $rule = array(
                "sac_name" => 'require|max:20',
                "sac_parent_id_1" => 'require|number',
                "sac_parent_id_2" => 'number',
                "sac_sort_order" => "number",
                "sac_desc" => 'max:50',
                "sac_keywords" => 'max:30',
                "sac_alias" => 'max:10',
            );
            $msg = array(
                "sac_name.require" => "名称是必须要填写的噢!~",
                "sac_name.max" => "亲，名字的最大长度不能超过20个字符噢!~",
                "sac_parent_id_1.require" => "分类归属必须要选择的噢!~",
                "sac_parent_id_1.number" => "分类归属必须说数字噢!~",
                "sac_parent_id_2.number" => "分类归属必须说数字噢!~",
                "sac_sort_order.number" => "排序只能填写数字噢!~",
                "sac_desc.max" => "描述的最大长度不能超过50个字符噢!~",
                "sac_keywords.max" => "关键字的最大长度不能超过30个字符噢!~",
                "sac_alias.max" => "别名的最大长度不能超过10个字符噢!~",
            );
            $data = verify($post_sac_info, $rule, $msg);
            /*code 等于1 说明成功 否则失败*/
            if ($data['code'] === 1) {
                /*验证数据库中是否有该名称*/
                $count = $this->sac->getCount(["sac_name" => $post_sac_info['sac_name']]);
                if ($count > 0) {
                    $this->error("您输入的名称重复了噢!~,请重新输入!~");
                    exit();
                }
                /*如果两个父类id都大于0，说明他的父类是最后一个*/
                if ($post_sac_info['sac_parent_id_1'] > 0 && $post_sac_info['sac_parent_id_2'] > 0) {
                    $post_sac_info['sac_parent_id'] = $post_sac_info['sac_parent_id_2'];
                    $post_sac_info['sac_level'] = 3;
                    unset($post_sac_info['sac_parent_id_1']);
                    unset($post_sac_info['sac_parent_id_2']);
                    /*如果第一个父类大于0 第二个父类等于0 说明的的父类是第一个*/
                } else if ($post_sac_info['sac_parent_id_1'] > 0 && $post_sac_info['sac_parent_id_2'] == 0) {
                    $post_sac_info['sac_parent_id'] = $post_sac_info['sac_parent_id_1'];
                    $post_sac_info['sac_level'] = 2;
                    unset($post_sac_info['sac_parent_id_1']);
                    unset($post_sac_info['sac_parent_id_2']);
                    /*如果第一个父类等于0 说明是顶级分类*/
                } else if ($post_sac_info['sac_parent_id_1'] == 0) {
                    $post_sac_info['sac_parent_id'] = $post_sac_info['sac_parent_id_1'];
                    $post_sac_info['sac_level'] = 1;
                    unset($post_sac_info['sac_parent_id_1']);
                    unset($post_sac_info['sac_parent_id_2']);
                }
                /*转换是否在导航显示*/
                // (isset($post_sac_info['sac_show_in_nav']) && !empty($post_sac_info['sac_show_in_nav']) && $post_sac_info['sac_show_in_nav'] == 'on') ? $post_sac_info['sac_show_in_nav'] = 1 : $post_sac_info['sac_show_in_nav'] = 0;

                $post_sac_info['ss_id'] = $this->sm_info['ss_id'];
                /*保存数据 返回id*/
                $id = $this->sac->save($post_sac_info);
                if ($id > 0) {
                    $this->sellerManagerLog("文章分类添加,添加后的id:".$id);
                    $this->success("添加成功");
                } else {
                    $this->error("添加失败");
                }
            } else {
                /*提示失败信息*/
                $this->error($data['msg']);
            }
            exit();
        }
        /*获取父类id和名称*/
        $first_cate = $this->sac->getList(["sac_parent_id" => 0,'ss_id' => $this->sm_info['ss_id']], "sac_id,sac_name");
        return view(
            "articlesCategoryAdd",
            [
                'first_cate' => $first_cate,
            ]
        );
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-12
     * 功能：商户ajax获取文章分类
     */
    public function getArticlesCategory()
    {
        if ($this->request->isAjax()) {
            /*直接收id*/
            $id = $this->request->only(["id"], "post");
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($id, $rule, $msg);
            if ($data['code'] == 1) {
                $info = $this->sac->getList(["sac_parent_id" => $id['id'],'ss_id' => $this->sm_info['ss_id']], "sac_id,sac_name");
                if (isset($info) && !empty($info)) {
                    return json(format($info));
                } else {
                    return json(format('', '-1', "该类目下没有下级分类了!~"));
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
     * 时间：2017-10-12
     * 功能：商户文章分类详情
     */
    public function articlesCategoryShow()
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
                $where["sac_id"] = intval($info['id']);
                $info = $this->sac->getRow($where);
                /*转换*/
                (isset($info['sac_show_in_nav']) && ($info['sac_show_in_nav'] == 1)) ? $info['sac_show_in_nav'] = "开启" : $info['sac_show_in_nav'] = "关闭";
                (isset($info['sac_parent_id']) && ($info['sac_parent_id'] == 0)) ? $info['sac_parent'] = "顶级分类" : $info['sac_parent'] = $this->sac->getOne(["sac_id" => $info['sac_parent_id']], 'sac_name');

                return json(format($info, '1', $data['msg']));
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }

    /**
     * 作者：袁中旭
     * 时间：2017-10-12
     * 功能：商户文章分类修改
     */
    public function articlesCategoryEdit($id)
    {
        if (isset($id) && $id > 0) {
            /*转换整型*/
            $where["sac_id"] = intval($id);
            $info = $this->sac->getRow($where);
            if (empty($info)) {
                $this->error("该文章分类不存在存在!请联系管理员!~");
                exit();
            }
            if ($this->request->isPost()) {
                $post_sac_info = $this->request->only(['sac_name', 'sac_keywords', 'sac_parent_id_1', 'sac_parent_id_2', 'sac_show_in_nav', 'sac_sort_order', 'sac_desc'], 'post');
                /*验证接到的值有没有问题*/
                $rule = array(
                    "sac_name" => 'require|max:30',
                    "sac_parent_id_1" => 'require|number',
                    "sac_parent_id_2" => 'number',
                    "sac_sort_order" => "number",
                );
                $msg = array(
                    "sac_name.require" => "名称是必须要填写的噢!~",
                    "sac_name.max" => "亲，名字的最大长度不能超过30个字符噢!~",
                    "sac_parent_id_1.require" => "分类归属必须要选择的噢!~",
                    "sac_parent_id_1.number" => "分类归属必须说数字噢!~",
                    "sac_parent_id_2.number" => "分类归属必须说数字噢!~",
                    "sac_sort_order.number" => "排序只能填写数字噢!~",
                );
                $data = verify($post_sac_info, $rule, $msg);
                if ($data['code'] === 1) {
                    /*检验名称是否重复*/
                    $sac_name = $this->sac->getOne(["sac_name" => $post_sac_info['sac_name'],'ss_id' => $this->sm_info['ss_id']], "sac_name");
                    if ($sac_name != $post_sac_info['sac_name'] && !empty($sac_name)) {
                        $this->error("您输入的名称重复了噢!~,请重新输入!~");
                        exit();
                    }
                    $sac_id_1 = $this->sac->getOne(["sac_id" => $post_sac_info['sac_parent_id_1']], "sac_id");
                    $sac_id_2 = $this->sac->getOne(["sac_id" => $post_sac_info['sac_parent_id_2']], "sac_id");
                    /*如果第一个父类和第二个父类都大于0说明她的父类是第二个*/
                    if ($post_sac_info['sac_parent_id_1'] > 0 && $post_sac_info['sac_parent_id_2'] > 0) {
                        $post_sac_info['sac_parent_id'] = $post_sac_info['sac_parent_id_2'];
                        $post_sac_info['sac_level'] = 3;
                        /*如果第一个父类大于0第二个父类等于0说明父类是第一个*/
                    } else if ($post_sac_info['sac_parent_id_1'] > 0 && $post_sac_info['sac_parent_id_2'] == 0) {
                        $post_sac_info['sac_parent_id'] = $post_sac_info['sac_parent_id_1'];
                        $post_sac_info['sac_level'] = 2;
                        /*如果父类等于0 说明是顶级分类*/
                    } else if ($post_sac_info['sac_parent_id_1'] == 0) {
                        $post_sac_info['sac_parent_id'] = $post_sac_info['sac_parent_id_1'];
                        $post_sac_info['sac_level'] = 1;
                    }

                    unset($post_sac_info['sac_parent_id_1']);
                    unset($post_sac_info['sac_parent_id_2']);

                    (isset($post_sac_info['sac_show_in_nav']) && !empty($post_sac_info['sac_show_in_nav']) && $post_sac_info['sac_show_in_nav'] == 'on') ? $post_sac_info['sac_show_in_nav'] = 1 : $post_sac_info['sac_show_in_nav'] = 0;
                    /*更新信息*/
                    $ret_info = $this->sac->save($post_sac_info, $where);
                    if (false !== $ret_info) {
                        $this->sellerManagerLog("文章分类修改,修改的id为:".$id);
                        $this->success("修改成功", url("seller/Articles/articlesCategoryList"));
                    } else {
                        $this->error("修改失败");
                    }
                } else {
                    $this->error($data['msg']);
                }
                exit();
            }
            $p_list = [];
            $p_info = [];
            /*获取顶级父类和二级父类*/
            if ($info['sac_parent_id'] > 0) {
                $p_info = $this->sac->getRow(["sac_id" => $info['sac_parent_id']]);
                if ($p_info['sac_parent_id'] != 0) {
                    $p_info = $this->sac->getRow(["sac_id" => $p_info['sac_parent_id']]);
                    $next_level = $p_info['sac_level'] + 1;
                    $p_list = $this->sac->getList(["sac_level" => $next_level, "sac_parent_id" => $p_info['sac_id']], "sac_id,sac_name,sac_parent_id");
                } else {
                    $next_level = $p_info['sac_level'] + 1;
                    $p_list = $this->sac->getList(["sac_level" => $next_level, "sac_parent_id" => $p_info['sac_id']], "sac_id,sac_name,sac_parent_id");
                }
            }
            if (!empty($p_info)) {
                $info['first'] = $p_info['sac_id'];
            } else {
                $info['first'] = $info['sac_parent_id'];
            }
            /*查询出顶级父类*/
            $first_cate = $this->sac->getList(["sac_parent_id" => 0], "sac_id,sac_name");

            if ($info['sac_level'] == '2') {
                foreach ($p_list as $key => $value) {
                    if ($value['sac_id'] == $info['sac_id']) {
                        unset($p_list[$key]);
                    }
                }
            }

            return view(
                "articlesCategoryEdit",
                [
                    'info' => $info,
                    'first_cate' => $first_cate,
                    'p_list' => $p_list,
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-12
     * 功能：商户文章分类删除
     */
    public function articlesCategoryDel()
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
                $where['sac_id'] = intval($info['id']);
                $sac_info = $this->sac->getRow($where);

                if ($sac_info['sac_parent_id'] != 0) {
                    $count = $this->sac->getCount(['sac_parent_id' => intval($info['id'])]);
                } else {
                    $count = $this->sac->getCount(['sac_parent_id' => intval($info['id'])]);
                }
                if ($count > 0) {
                    return json(format('', '-1', "该分类有下级分类,请先删除下级分类!~"));
                } else {
                    $sa_count = $this->sa->getCount(["sac_id" => intval($info['id'])]);
                    if ($sa_count > 0) {
                        return json(format('', '-1', "该分类下面有文章哦,请先删除文章在删除分类!~"));
                    } else {
                        $ret = $this->sac->del($where);
                        if (false === $ret) {
                            return json(format('', '-1', "删除失败!~"));
                        } else {
                            $this->sellerManagerLog("删除文章分类,删除的分类id为:".$info['id']);
                            return json(format('', '1', "删除成功!~"));
                        }
                    }
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-12
     * 功能：商户文章列表
     */
    public function articlesList()
    {
        $condition = $this->request->only(["sa_title", "sa_author"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否设置了状态值*/
            if (isset($condition['sa_title']) && ('' !== $condition['sa_title'])) {
                $where['sa_title'] = ['like', "%" . $condition['sa_title'] . "%"];
                /*保存查询条件状态*/
                $pageParam['query']['sa_title'] = $condition['sa_title'];
            }
            /*是否接收到名称信息*/
            if (isset($condition['sa_author']) && ('' != $condition['sa_author'])) {
                /*模糊查询*/
                $where['sa_author'] = ['like', "%" . $condition['sa_author'] . "%"];
                $pageParam['query']['sa_author'] = $condition['sa_author'];
            }
        }

        $where['ss_id'] = $this->sm_info['ss_id'];
        $list = $this->sa->getAll($where);
        return view(
            'articlesList',
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
     * 作者：袁中旭
     * 时间：2017-10-12
     * 功能：商户文章添加
     */
    public function articlesAdd()
    {
        if ($this->request->isPost()) {
            $post = $this->request->only(['sac_parent_id_1', 'sac_parent_id_2', 'sac_parent_id_3', 'sa_title', 'sa_author', 'sa_type', 'sa_link', 'sa_keywords', 'sa_is_open', 'sa_description', 'sa_content'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "sa_title" => 'require|max:30',
                "sac_parent_id_2" => 'require|number',
                "sac_parent_id_2" => 'number',
                "sac_parent_id_3" => 'number',
                "sa_author" => "require|max:30|chsAlpha",
                "sa_type" => "require|in:0,1,2",
                "sa_link" => "url",
                "sa_keywords" => "max:30",
                "sa_is_open" => "number",
                "sa_description" => "require|max:100",
                "sa_content" => "require",
            );
            $msg = array(
                "sa_title.require" => '文章名称是必须要填写的哦!~',
                "sa_title.max" => '文章名称最大程度不能超过30个字符哦!~',
                "sac_parent_id_1.require" => '一级分类是必须选择的哦!~',
                "sac_parent_id_1.number" => '分类必须是数字哦!~',
                "sac_parent_id_2.number" => '分类必须是数字哦!~',
                "sac_parent_id_3.number" => '分类必须是数字哦!~',
                "sa_author.require" => "作者是必须要填写的哦!~",
                "sa_author.max" => "作者名称的最大长度不能超过30个字符哦!~",
                "sa_author.chsAlpha" => "作者名称只能是中英文哦!~",
                "sa_type.require" => "类型是必须选择的哦!~",
                "sa_type.in" => "选择的类型有误!~",
                "sa_link.url" => "链接地址填写有误!~",
                "sa_keywords.max" => "关键字的最大长度是30个字符!~",
                "sa_is_open.number" => "是否开启选择有误!~",
                "sa_description.require" => "描述是必须填写的哦!~",
                "sa_description.max" => "描述的最大长度是100个字符!~",
                "sa_content.require" => "文章内容是必须填写的哦!~",
            );
            $data = verify($post, $rule, $msg);
            if ($data['code'] === 1) {
                $count = $this->sa->getCount(["sa_title" => $post['sa_title']]);
                if ($count > 0) {
                    $this->error("您输入的名称重复了噢!~,请重新输入!~");
                    exit();
                }
                if (empty($_FILES)) {
                    $this->error("文章缩略图是必须要上传的哦!~");
                }
                /*先上传文件*/
                $path = "articles/" . date("y_m_d", time());
                $file_info = uploadImage($path);
                if ($file_info['code'] != 201) {
                    $post['sa_thumb_path'] = $file_info['pic_cover'];
                    /*一级父类大于0 并且二级父类大于0 并且三级父类大于0 说明他的父类是三级父类 依次类推*/
                    if (intval($post['sac_parent_id_1']) > 0 && intval($post['sac_parent_id_2']) > 0 && intval($post['sac_parent_id_3']) > 0) {
                        $post['sac_id'] = $post['sac_parent_id_3'];
                    } elseif (intval($post['sac_parent_id_1']) > 0 && intval($post['sac_parent_id_2']) > 0 && $post['sac_parent_id_3'] == '') {
                        $post['sac_id'] = $post['sac_parent_id_2'];
                    } elseif (intval($post['sac_parent_id_1']) > 0 && $post['sac_parent_id_2'] == '' && $post['sac_parent_id_3'] == '') {
                        $post['sac_id'] = $post['sac_parent_id_1'];
                    }

                    /*销毁变量*/
                    unset($post['sac_parent_id_1']);
                    unset($post['sac_parent_id_2']);
                    unset($post['sac_parent_id_3']);
                    $post['sa_add_time'] = time();
                    $post['ss_id'] = $this->sm_info['ss_id'];
                    /*存入数据库*/
                    $id = $this->sa->save($post);
                    if ($id > 0) {
                        $this->sellerManagerLog("添加文章,添加后的id为:".$id);
                        $this->success("添加成功！～", url("seller/Articles/articlesList"));
                    } else {
                        $this->error("添加失败！～");
                    }

                } else {
                    $this->error($file_info['msg']);
                }
            } else {
                $this->error($data['msg']);
            }
            exit;
        }
        /*获取分类信息*/

        $first_cate = $this->sac->getList(["sac_parent_id" => 0,'ss_id' => $this->sm_info['ss_id']], "sac_id,sac_name");
        if(empty($first_cate)){
            $this->error("请先去添加文章分类噢!~");
        }else{
           return view(
                "articlesAdd",
                [
                    'first_cate' => $first_cate,
                ]
            ); 
        }
        
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-12
     * 功能：商户文章修改
     */
    public function articlesEdit($id)
    {
        $info = $this->sa->getRow(["sa_id" => intval($id)]);
        if (isset($id) && $id > 0) {
            if ($this->request->isPost()) {
                $save_info = $this->request->only(['sac_parent_id_1', 'sac_parent_id_2', 'sac_parent_id_3', 'sa_title', 'sa_author', 'sa_type', 'sa_link', 'sa_keywords', 'sa_is_open', 'sa_description', 'sa_content', 'sa_thumb_path'], 'post');
                    /*验证接到的值有没有问题*/
                    $rule = array(
                    "sa_title" => 'require|max:30',
                    "sac_parent_id_2" => 'require|number',
                    "sac_parent_id_2" => 'number',
                    "sac_parent_id_3" => 'number',
                    "sa_author" => "require|max:30|chsAlpha",
                    "sa_type" => "require|in:0,1,2",
                    "sa_link" => "url",
                    "sa_keywords" => "max:30",
                    "sa_is_open" => "number",
                    "sa_description" => "require|max:100",
                    "sa_content" => "require",
                );
                $msg = array(
                    "sa_title.require" => '文章名称是必须要填写的哦!~',
                    "sa_title.max" => '文章名称最大程度不能超过30个字符哦!~',
                    "sac_parent_id_1.require" => '一级分类是必须选择的哦!~',
                    "sac_parent_id_1.number" => '分类必须是数字哦!~',
                    "sac_parent_id_2.number" => '分类必须是数字哦!~',
                    "sac_parent_id_3.number" => '分类必须是数字哦!~',
                    "sa_author.require" => "作者是必须要填写的哦!~",
                    "sa_author.max" => "作者名称的最大长度不能超过30个字符哦!~",
                    "sa_author.chsAlpha" => "作者名称只能是中英文哦!~",
                    "sa_type.require" => "类型是必须选择的哦!~",
                    "sa_type.in" => "选择的类型有误!~",
                    "sa_link.url" => "链接地址填写有误!~",
                    "sa_keywords.max" => "关键字的最大长度是30个字符!~",
                    "sa_is_open.number" => "是否开启选择有误!~",
                    "sa_description.require" => "描述是必须填写的哦!~",
                    "sa_description.max" => "描述的最大长度是100个字符!~",
                    "sa_content.require" => "文章内容是必须填写的哦!~",
                );
                $data = verify($save_info, $rule, $msg);
                if ($data['code'] === 1) {
                    $sa_title = $this->sa->getOne(["sa_title" => $save_info['sa_title']], "sa_title");
                    /*验证名称是否重复*/
                    if (isset($sa_title) && !empty($sa_title) && ($sa_title != $info['sa_title'])) {
                        $this->error("您输入的名称重复了噢!~,请重新输入!~");
                        exit();
                    }
                    /*如果files下面的size大于0 说明有文件 执行上传*/
                    if ($_FILES['sa_thumb_path']['size'] > 0) {
                        $path = "articles/" . date("y_m_d", time());
                        $file_info = uploadImage($path);

                        /*先上传文件*/
                        if ($file_info['code'] != 201) {
                            $save_info['sa_thumb_path'] = $file_info['pic_cover'];
                        } else {
                            $this->error($data['msg']);
                        }
                    } else {
                        /*否则就是原来的图片*/
                        $save_info['sa_thumb_path'] = $info['sa_thumb_path'];
                    }
                    /*一级父类大于0 并且二级父类大于0 并且三级父类大于0 说明他的父类是三级父类 依次类推*/
                    if (intval($save_info['sac_parent_id_1']) > 0 && intval($save_info['sac_parent_id_2']) > 0 && intval($save_info['sac_parent_id_3']) > 0) {
                        $save_info['sac_id'] = $save_info['sac_parent_id_3'];
                    } elseif (intval($save_info['sac_parent_id_1']) > 0 && intval($save_info['sac_parent_id_2']) > 0 && $save_info['sac_parent_id_3'] == '') {
                        $save_info['sac_id'] = $save_info['sac_parent_id_2'];
                    } elseif (intval($save_info['sac_parent_id_1']) > 0 && $save_info['sac_parent_id_2'] == '' && $save_info['sac_parent_id_3'] == '') {
                        $save_info['sac_id'] = $save_info['sac_parent_id_1'];
                    }
                    /*销毁变量*/
                    unset($save_info['sac_parent_id_1']);
                    unset($save_info['sac_parent_id_2']);
                    unset($save_info['sac_parent_id_3']);
                    /*存入数据库*/
                    $ret_info = $this->sa->save($save_info, ["sa_id" => intval($id)]);
                    if (false !== $ret_info) {
                        $this->sellerManagerLog("修改文章,文章id为:".$id);
                        $this->success("修改成功！～", url("seller/Articles/articlesList"));
                    } else {
                        $this->error("修改失败！～");
                    }
                } else {
                    $this->error($data['msg']);
                }
                exit();
            }
            $sac_list = $this->sac->getList(['ss_id' => $this->sm_info['ss_id']]);
            $parent = $this->sac->getRow(['sac_id' => $info['sac_id'],'ss_id' => $this->sm_info['ss_id']]);
            $parent_1 = '';
            $parent_2 = '';
            $parent_3 = '';
            if($parent['sac_level'] === '1'){
                $parent_1 = $parent['sac_id'];
            }else if($parent['sac_level'] === '2'){
                if($parent['sac_parent_id'] > 0 && isset($parent) && !empty($parent) && $parent != ''){
                    $parents = $this->sac->getRow(['sac_id' => $parent['sac_parent_id'],'ss_id' => $this->sm_info['ss_id']]);
                    $parent_1 = $parents['sac_id'];
                    $parent_2 = $parent['sac_id'];
                }else{
                    $parent_1 = $parent['sac_id'];
                }
            }else if($parent['sac_level'] === '3'){
                if($parent['sac_parent_id'] > 0 && isset($parent) && !empty($parent) && $parent != ''){
                    $parents = $this->sac->getRow(['sac_id' => $parent['sac_parent_id'],'ss_id' => $this->sm_info['ss_id']]);
                    if($parents['sac_parent_id'] > 0 && isset($parents) && !empty($parents) && $parents != '' ){

                        $parentq = $this->sac->getRow(['sac_id' => $parents['sac_parent_id'],'ss_id' => $this->sm_info['ss_id']]);
                        if(isset($parentq) && !empty($parentq) && $parentq != ''){
                            $parent_1 = $parentq['sac_id'];
                            $parent_2 = $parents['sac_id'];
                            $parent_3 = $parent['sac_id'];
                        }else{
                            $parent_1 = $parent['sac_id'];
                            $parent_2 = $parents['sac_id'];
                        }
                    }else{
                        $parent_1 = $parent['sac_id'];
                    }
                }else{
                    $parent_1 = $parent['sac_id'];
                }
            }

            return view(
                'articlesEdit',
                [
                    'info' => $info,
                    'parent' => $sac_list,
                    'parent_1' => $parent_1,
                    'parent_2' => $parent_2,
                    "parent_3" => $parent_3,
                ]
            );
        } else {
            $this->error("传输数据出错！请联系管理员！～");
        }
    }
    /**
     * 作者：袁中旭
     * 时间：2017-10-12
     * 功能：商户文章删除
     */
    public function articlesDel()
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
                $where['sa_id'] = intval($info['id']);
                $sa_title = $this->sa->getOne($where, "sa_title");
                if (isset($sa_title) && !empty($sa_title)) {
                    $sa_ret = $this->sa->del($where);
                    if (false === $sa_ret) {
                        return json(format('', '-1', '删除失败~!请稍候重试~!'));
                    } else {
                        $this->sellerManagerLog("删除文章,文章id为:".$info['id']);
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
     * 时间：2017-10-12
     * 功能：商户文章展示
     */
    public function articlesShow()
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

                $where['sa.sa_id'] = intval($info['id']);
                $join = [
                    ["gjt_shop_artical_category sac", "sa.sac_id = sac.sac_id"],
                ];
                $alias = "sa";
                $field = "sa.*,sac.sac_name";
                $info = $this->sa->joinGetRow($join, $alias, $where, $field);
                ($info['sa_is_open'] == 0) ? $info['sa_is_open'] = "关闭" : $info['sa_is_open'] = "开启";
                if ($info['sa_type'] == "0") {
                    $info['sa_type'] = "原创";
                } elseif ($info['sa_type'] == "1") {
                    $info['sa_type'] = "转载";
                } else {
                    $info['sa_type'] = "翻译";
                }
                return json(format($info, '1', $data['msg']));
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
}
