<?php
namespace app\manage\controller;

use model\Artical as a;
use model\ArticalCategory as ac;
use model\BiddingInformation as bi;
use model\EngineeringSpecifications as es;

/**
 * [Articles] 后台文章管理 招投标信息
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-19
 */

class Articles extends Base
{
    /**
     * 继承父类构造函数
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-19
     */
    protected $a;
    protected $ac;
    protected $bi;
    protected $es;
    public function __construct()
    {
        parent::__construct();
        /*文章*/
        $this->a = new a();
        /*文章分类*/
        $this->ac = new ac();
        /*招投标*/
        $this->bi = new bi();
        /*规范*/
        $this->es = new es();
    }
    /**
     * 文章分类列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-19
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
                $ac_list = $this->ac->getList(["ac_parent_id" => $info['id']], [], ['ac_sort_order' => "desc"]);
                return json(format($ac_list, '1'));
            } else {
                return json(format('', '-1', $data['msg']));
            }
            exit();
        }
        /*获取列表*/
        $ac_list = $this->ac->getAll(["ac_parent_id" => 0], [], ['ac_sort_order' => "desc"]);
        $this->managerLog("查看文章分类列表");
        return view(
            "articlesCategoryList",
            [
                "list" => $ac_list['data'], /*查询结果*/
                "page" => $ac_list['page'], /*分页和html代码*/
                "lastPage" => $ac_list['lastPage'], /*总页数*/
                "currentPage" => $ac_list['currentPage'], /*当前页码*/
                "total" => $ac_list['total'], /*总条数*/
                "listRows" => $ac_list['listRows'], /*每页显示条数*/
            ]
        );
    }
    /**
     * 文章分类添加
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-19
     */
    public function articlesCategoryAdd()
    {
        if ($this->request->isPost()) {
            /*只获取post以下参数*/
            $post_ac_info = $this->request->only(['ac_name', 'ac_keywords', 'ac_parent_id_1', 'ac_parent_id_2', 'ac_show_in_nav', 'ac_sort_order', 'ac_desc'], "post");
            /*验证接到的值有没有问题*/
            $rule = array(
                "ac_name" => 'require|max:30',
                "ac_parent_id_1" => 'require|number',
                "ac_parent_id_2" => 'number',
                "ac_sort_order" => "number",
            );
            $msg = array(
                "ac_name.require" => "名称是必须要填写的噢!~",
                "ac_name.max" => "亲，名字的最大长度不能超过30个字符噢!~",
                "ac_parent_id_1.require" => "分类归属必须要选择的噢!~",
                "ac_parent_id_1.number" => "分类归属必须说数字噢!~",
                "ac_parent_id_2.number" => "分类归属必须说数字噢!~",
                "ac_sort_order.number" => "排序只能填写数字噢!~",
            );
            $data = verify($post_ac_info, $rule, $msg);
            /*code 等于1 说明成功 否则失败*/
            if ($data['code'] === 1) {
                /*验证数据库中是否有该名称*/
                $count = $this->ac->getCount(["ac_name" => $post_ac_info['ac_name']]);
                if ($count > 0) {
                    $this->error("您输入的名称重复了噢!~,请重新输入!~");
                    exit();
                }
                /*如果两个父类id都大于0，说明他的父类是最后一个*/
                if ($post_ac_info['ac_parent_id_1'] > 0 && $post_ac_info['ac_parent_id_2'] > 0) {
                    $post_ac_info['ac_parent_id'] = $post_ac_info['ac_parent_id_2'];
                    $post_ac_info['ac_level'] = 3;
                    unset($post_ac_info['ac_parent_id_1']);
                    unset($post_ac_info['ac_parent_id_2']);
                    /*如果第一个父类大于0 第二个父类等于0 说明的的父类是第一个*/
                } else if ($post_ac_info['ac_parent_id_1'] > 0 && $post_ac_info['ac_parent_id_2'] == 0) {
                    $post_ac_info['ac_parent_id'] = $post_ac_info['ac_parent_id_1'];
                    $post_ac_info['ac_level'] = 2;
                    unset($post_ac_info['ac_parent_id_1']);
                    unset($post_ac_info['ac_parent_id_2']);
                    /*如果第一个父类等于0 说明是顶级分类*/
                } else if ($post_ac_info['ac_parent_id_1'] == 0) {
                    $post_ac_info['ac_level'] = 1;
                    $post_ac_info['ac_parent_id'] = $post_ac_info['ac_parent_id_1'];
                    unset($post_ac_info['ac_parent_id_1']);
                    unset($post_ac_info['ac_parent_id_2']);
                }
                /*转换是否在导航显示*/
                (isset($post_ac_info['ac_show_in_nav']) && !empty($post_ac_info['ac_show_in_nav']) && $post_ac_info['ac_show_in_nav'] == 'on') ? $post_ac_info['ac_show_in_nav'] = 1 : $post_ac_info['ac_show_in_nav'] = 0;
                /*保存数据 返回id*/
                $id = $this->ac->save($post_ac_info);
                if ($id > 0) {
                    $this->managerLog("文章分类添加,添加后的id:" . $id);
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
        $first_cate = $this->ac->getList(["ac_parent_id" => 0], "ac_id,ac_name");
        return view(
            "articlesCategoryAdd",
            [
                'first_cate' => $first_cate,
            ]
        );
    }
    /**
     * ajax获取文章分类
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-25
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
                $info = $this->ac->getList(["ac_parent_id" => $id['id']], "ac_id,ac_name");
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
     * 文章分类修改
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-19
     */
    public function articlesCategoryEdit($id)
    {
        if (isset($id) && $id > 0) {
            /*转换整型*/
            $where["ac_id"] = intval($id);
            $info = $this->ac->getRow($where);
            if (empty($info)) {
                $this->error("该文章分类不存在存在!请联系管理员!~");
                exit();
            }
            if ($this->request->isPost()) {
                $post_ac_info = $this->request->only(['ac_name', 'ac_keywords', 'ac_parent_id_1', 'ac_parent_id_2', 'ac_show_in_nav', 'ac_sort_order', 'ac_desc'], 'post');
                /*验证接到的值有没有问题*/
                $rule = array(
                    "ac_name" => 'require|max:30',
                    "ac_parent_id_1" => 'require|number',
                    "ac_parent_id_2" => 'number',
                    "ac_sort_order" => "number",
                );
                $msg = array(
                    "ac_name.require" => "名称是必须要填写的噢!~",
                    "ac_name.max" => "亲，名字的最大长度不能超过30个字符噢!~",
                    "ac_parent_id_1.require" => "分类归属必须要选择的噢!~",
                    "ac_parent_id_1.number" => "分类归属必须说数字噢!~",
                    "ac_parent_id_2.number" => "分类归属必须说数字噢!~",
                    "ac_sort_order.number" => "排序只能填写数字噢!~",
                );
                $data = verify($post_ac_info, $rule, $msg);
                if ($data['code'] === 1) {
                    /*检验名称是否重复*/
                    $ac_name = $this->ac->getOne(["ac_name" => $post_ac_info['ac_name']], "ac_name");
                    if ($ac_name != $post_ac_info['ac_name'] && !empty($ac_name)) {
                        $this->error("您输入的名称重复了噢!~,请重新输入!~");
                        exit();
                    }
                    /*如果第一个父类和第二个父类都大于0说明她的父类是第二个*/
                    if ($post_ac_info['ac_parent_id_1'] > 0 && $post_ac_info['ac_parent_id_2'] > 0) {
                        $post_ac_info['ac_parent_id'] = $post_ac_info['ac_parent_id_2'];
                        $post_ac_info['ac_level'] = 3;
                        /*如果第一个父类大于0第二个父类等于0说明父类是第一个*/
                    } else if ($post_ac_info['ac_parent_id_1'] > 0 && $post_ac_info['ac_parent_id_2'] == 0) {
                        $post_ac_info['ac_parent_id'] = $post_ac_info['ac_parent_id_1'];
                        $post_ac_info['ac_level'] = 2;
                        /*如果父类等于0 说明是顶级分类*/
                    } else if ($post_ac_info['ac_parent_id_1'] == 0) {
                        $post_ac_info['ac_parent_id'] = $post_ac_info['ac_parent_id_1'];
                        $post_ac_info['ac_level'] = 1;
                    }
                    $count = $this->ac->getCount(["ac_parent_id" => $info['ac_id']]);
                    if ($info['ac_level'] == "1" && $count > 0) {
                        $this->error("该顶级分类下有子级分类,请先修改子级分类后在修改此分类!~");
                        exit();
                    }

                    unset($post_ac_info['ac_parent_id_1']);
                    unset($post_ac_info['ac_parent_id_2']);

                    (isset($post_ac_info['ac_show_in_nav']) && !empty($post_ac_info['ac_show_in_nav']) && $post_ac_info['ac_show_in_nav'] == 'on') ? $post_ac_info['ac_show_in_nav'] = 1 : $post_ac_info['ac_show_in_nav'] = 0;
                    /*更新信息*/
                    $ret_info = $this->ac->save($post_ac_info, $where);
                    if (false !== $ret_info) {
                        $this->managerLog("文章分类修改,修改的id为:" . $id);
                        $this->success("修改成功", url("manage/Articles/articlesCategoryList"));
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
            if ($info['ac_parent_id'] > 0) {
                $p_info = $this->ac->getRow(["ac_id" => $info['ac_parent_id']]);
                if ($p_info['ac_parent_id'] != 0) {
                    $p_info = $this->ac->getRow(["ac_id" => $p_info['ac_parent_id']]);
                    $next_level = $p_info['ac_level'] + 1;
                    $p_list = $this->ac->getList(["ac_level" => $next_level, "ac_parent_id" => $p_info['ac_id']], "ac_id,ac_name,ac_parent_id");
                } else {
                    $next_level = $p_info['ac_level'] + 1;
                    $p_list = $this->ac->getList(["ac_level" => $next_level, "ac_parent_id" => $p_info['ac_id']], "ac_id,ac_name,ac_parent_id");
                }
            }
            if (!empty($p_info)) {
                $info['first'] = $p_info['ac_id'];
            } else {
                $info['first'] = $info['ac_parent_id'];
            }
            /*查询出顶级父类*/
            $first_cate = $this->ac->getList(["ac_parent_id" => 0], "ac_id,ac_name");

            if ($info['ac_level'] == '2') {
                foreach ($p_list as $key => $value) {
                    if ($value['ac_id'] == $info['ac_id']) {
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
     * 文章分类详情
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-20
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
                $where["ac_id"] = intval($info['id']);
                $ac_info = $this->ac->getRow($where);
                /*转换*/
                (isset($ac_info['ac_show_in_nav']) && ($ac_info['ac_show_in_nav'] == 1)) ? $ac_info['ac_show_in_nav'] = "开启" : $ac_info['ac_show_in_nav'] = "关闭";
                (isset($ac_info['ac_parent_id']) && ($ac_info['ac_parent_id'] == 0)) ? $ac_info['ac_parent'] = "顶级分类" : $ac_info['ac_parent'] = $this->ac->getOne(["ac_id" => $ac_info['ac_parent_id']], 'ac_name');
                $this->managerLog("查看文章分类详情,查看的分类id为:" . $ac_info['ac_id']);
                return json(format($ac_info, '1', $data['msg']));
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 文章分类删除
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-19
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
                $where['ac_id'] = intval($info['id']);
                $ac_info = $this->ac->getRow($where);
                // dump($ac_info);
                if ($ac_info['ac_parent_id'] != 0) {
                    $count = $this->ac->getCount(['ac_parent_id' => intval($info['id'])]);
                    if ($count > 0) {
                        return json(format('', '-1', "该分类有下级分类,请先删除下级分类!~"));
                    } else {
                        $a_count = $this->a->getCount(["ac_id" => intval($info['id'])]);
                        if ($a_count > 0) {
                            return json(format('', '-1', "该分类下面有文章哦,请先删除文章在删除分类!~"));
                        } else {
                            $ret = $this->ac->del($where);
                            if (false === $ret) {
                                return json(format('', '-1', "删除失败!~"));
                            } else {
                                $this->managerLog("删除文章分类,删除的分类id为:" . $info['id']);
                                return json(format('', '-1', "删除成功!~"));
                            }
                        }
                    }
                } else {
                    $count = $this->ac->getCount(['ac_parent_id' => intval($info['id'])]);
                    if ($count > 0) {
                        return json(format('', '-1', "该分类有下级分类,请先删除下级分类!~"));
                    } else {
                        $a_count = $this->a->getCount(["ac_id" => intval($info['id'])]);
                        if ($a_count > 0) {
                            return json(format('', '-1', "该分类下面有文章哦,请先删除文章在删除分类!~"));
                        } else {
                            $ret = $this->ac->del($where);
                            if (false === $ret) {
                                return json(format('', '-1', "删除失败!~"));
                            } else {
                                $this->managerLog("删除文章分类,删除的分类id为:" . $info['id']);
                                return json(format('', '-1', "删除成功!~"));
                            }
                        }
                    }
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }
    /**
     * 文章列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-19
     */
    public function articlesList()
    {
        $condition = $this->request->only(["a_title", "a_author"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否设置了状态值*/
            if (isset($condition['a_title']) && ('' !== $condition['a_title'])) {
                $where['a_title'] = ['like', "%" . $condition['a_title'] . "%"];
                /*保存查询条件状态*/
                $pageParam['query']['a_title'] = $condition['a_title'];
            }
            /*是否接收到名称信息*/
            if (isset($condition['a_author']) && ('' != $condition['a_author'])) {
                /*模糊查询*/
                $where['a_author'] = ['like', "%" . $condition['a_author'] . "%"];
                $pageParam['query']['a_author'] = $condition['a_author'];
            }
        }
        $list = $this->a->getAll($where);
        $this->managerLog("查看文章列表");
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
     * 文章添加
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-19
     */
    public function articlesAdd()
    {
        if ($this->request->isPost()) {
            $post = $this->request->only(['ac_parent_id_1', 'ac_parent_id_2', 'ac_parent_id_3', 'a_title', 'a_author', 'a_type', 'a_link', 'a_keywords', 'a_is_open', 'a_description', 'a_content'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "a_title" => 'require|max:50',
                "ac_parent_id_1" => 'require|number',
                "ac_parent_id_2" => 'number',
                "ac_parent_id_3" => 'number',
                "a_author" => "require|max:30|chsAlpha",
//                "a_type" => "require|in:0,1,2",
                "a_link" => "url",
                "a_keywords" => "max:100",
                "a_is_open" => "alpha",
                "a_description" => "require",
                "a_content" => "require",
            );
            $msg = array(
                "a_title.require" => '文章名称是必须要填写的哦!~',
                "a_title.max" => '文章名称最大程度不能超过50哦!~',
                "ac_parent_id_1.require" => '一级分类是必须选择的哦!~',
                "ac_parent_id_1.number" => '分类必须是数字哦!~',
                "ac_parent_id_2.number" => '分类必须是数字哦!~',
                "ac_parent_id_3.number" => '分类必须是数字哦!~',
                "a_author.require" => "作者是必须要填写的哦!~",
                "a_author.max" => "作者名称的最大长度不能超过30个字符哦!~",
                "a_author.chsAlpha" => "作者名称只能是中英文哦!~",
//                "a_type.require" => "类型是必须选择的哦!~",
//                "a_type.in" => "选择的类型有误!~",
                "a_link.url" => "链接地址填写有误!~",
                "a_keywords.max" => "关键字的最大长度是100个字符!~",
                "a_is_open.alpha" => "是否开启选择有误!~",
                "a_description.require" => "描述是必须填写的哦!~",
                "a_content.require" => "文章内容是必须填写的哦!~",
            );
            $data = verify($post, $rule, $msg);
            if ($data['code'] === 1) {
                $count = $this->a->getCount(["a_title" => $post['a_title']]);
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
                    $post['a_thumb_path'] = $file_info['pic_cover'];
                    /*一级父类大于0 并且二级父类大于0 并且三级父类大于0 说明他的父类是三级父类 依次类推*/
                    if (intval($post['ac_parent_id_1']) > 0 && intval($post['ac_parent_id_2']) > 0 && intval($post['ac_parent_id_3']) > 0) {
                        $post['ac_id'] = $post['ac_parent_id_3'];
                    } elseif (intval($post['ac_parent_id_1']) > 0 && intval($post['ac_parent_id_2']) > 0 && $post['ac_parent_id_3'] == '') {
                        $post['ac_id'] = $post['ac_parent_id_2'];
                    } elseif (intval($post['ac_parent_id_1']) > 0 && $post['ac_parent_id_2'] == '' && $post['ac_parent_id_3'] == '') {
                        $post['ac_id'] = $post['ac_parent_id_1'];
                    }

                    (isset($post['a_is_open']) && !empty($post['a_is_open']) && $post['a_is_open'] == 'on') ? $post['a_is_open'] = 1 : $post['a_is_open'] = 0;
                    /*销毁变量*/
                    unset($post['ac_parent_id_1']);
                    unset($post['ac_parent_id_2']);
                    unset($post['ac_parent_id_3']);
                    $post['a_add_time'] = time();
                    /*存入数据库*/
                    $id = $this->a->save($post);
                    if ($id > 0) {
                        $this->managerLog("添加文章,添加后的id为:" . $id);
                        $this->success("添加成功！～", url("manage/Articles/articlesList"));
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
        $first_cate = $this->ac->getList(["ac_parent_id" => 0], "ac_id,ac_name");
        return view(
            "articlesAdd",
            [
                'first_cate' => $first_cate,
            ]
        );
    }
    /**
     * 文章修改
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-19
     */
    public function articlesEdit($id)
    {
        $info = $this->a->getRow(["a_id" => intval($id)]);
        if (isset($id) && $id > 0) {
            if ($this->request->isPost()) {
                $save_info = $this->request->only(['ac_parent_id_1', 'ac_parent_id_2', 'ac_parent_id_3', 'a_title', 'a_author', 'a_type', 'a_link', 'a_keywords', 'a_is_open', 'a_description', 'a_content', 'a_thumb_path'], 'post');
                /*验证接到的值有没有问题*/
                $rule = array(
                    "a_title" => 'require|max:50',
                    "ac_parent_id_1" => 'require|number',
                    "ac_parent_id_2" => 'number',
                    "ac_parent_id_3" => 'number',
                    "a_author" => "require|max:30|chsAlpha",
//                    "a_type" => "require|in:0,1,2",
                    "a_link" => "url",
                    "a_keywords" => "max:100",
                    "a_is_open" => "alpha",
                    "a_description" => "require",
                    "a_content" => "require",
                );
                $msg = array(
                    "a_title.require" => '文章名称是必须要填写的哦!~',
                    "a_title.max" => '文章名称最大程度不能超过50哦!~',
                    "ac_parent_id_1.require" => '一级分类是必须选择的哦!~',
                    "ac_parent_id_1.number" => '分类必须是数字哦!~',
                    "ac_parent_id_2.number" => '分类必须是数字哦!~',
                    "ac_parent_id_3.number" => '分类必须是数字哦!~',
                    "a_author.require" => "作者是必须要填写的哦!~",
                    "a_author.max" => "作者名称的最大长度不能超过30个字符哦!~",
                    "a_author.chsAlpha" => "作者名称只能是中英文哦!~",
//                    "a_type.require" => "类型是必须选择的哦!~",
//                    "a_type.in" => "选择的类型有误!~",
                    "a_link.url" => "链接地址填写有误!~",
                    "a_keywords.max" => "关键字的最大长度是100个字符!~",
                    "a_is_open.alpha" => "是否开启选择有误!~",
                    "a_description.require" => "描述是必须填写的哦!~",
                    "a_content.require" => "文章内容是必须填写的哦!~",
                );
                $data = verify($save_info, $rule, $msg);
                if ($data['code'] === 1) {
                    $a_title = $this->a->getOne(["a_title" => $save_info['a_title']], "a_title");
                    /*验证名称是否重复*/
                    if (isset($a_title) && !empty($a_title) && ($a_title != $info['a_title'])) {
                        $this->error("您输入的名称重复了噢!~,请重新输入!~");
                        exit();
                    }
                    /*如果files下面的size大于0 说明有文件 执行上传*/
                    if ($_FILES['a_thumb_path']['size'] > 0) {
                        $path = "articles/" . date("y_m_d", time());
                        $file_info = uploadImage($path);

                        /*先上传文件*/
                        if ($file_info['code'] != 201) {
                            $save_info['a_thumb_path'] = $file_info['pic_cover'];
                        } else {
                            $this->error($data['msg']);
                        }
                    } else {
                        /*否则就是原来的图片*/
                        $save_info['a_thumb_path'] = $info['a_thumb_path'];
                    }
                    /*一级父类大于0 并且二级父类大于0 并且三级父类大于0 说明他的父类是三级父类 依次类推*/
                    if ($save_info['ac_parent_id_1'] > 0 && $save_info['ac_parent_id_2'] >= 0 && $save_info['ac_parent_id_3'] > 0) {
                        $save_info['ac_id'] = $save_info['ac_parent_id_3'];
                    } elseif ($save_info['ac_parent_id_1'] > 0 && $save_info['ac_parent_id_2'] > 0 && $save_info['ac_parent_id_3'] = '') {
                        $save_info['ac_id'] = $save_info['ac_parent_id_2'];
                    } elseif ($save_info['ac_parent_id_1'] > 0 && $save_info['ac_parent_id_2'] = '' && $save_info['ac_parent_id_3'] = '') {
                        $save_info['ac_id'] = $save_info['ac_parent_id_1'];
                    }
                    (isset($save_info['a_is_open']) && !empty($save_info['a_is_open']) && $save_info['a_is_open'] == 'on') ? $save_info['a_is_open'] = 1 : $save_info['a_is_open'] = 0;

                    /*销毁变量*/
                    unset($save_info['ac_parent_id_1']);
                    unset($save_info['ac_parent_id_2']);
                    unset($save_info['ac_parent_id_3']);
                    /*存入数据库*/
                    $ret_info = $this->a->save($save_info, ["a_id" => intval($id)]);
                    if (false !== $ret_info) {
                        $this->managerLog("修改文章,文章id为:" . $id);
                        $this->success("修改成功！～", url("manage/Articles/articlesList"));
                    } else {
                        $this->error("修改失败！～");
                    }
                } else {
                    $this->error($data['msg']);
                }
                exit();
            }
            $parent_1 = $this->ac->getRow(['ac_id' => $info['ac_id']]);
            $info['parent_1'] = $parent_1['ac_id'];
            $info['parent'] = $parent_1['ac_id'];
            if ($parent_1['ac_parent_id'] > 0) {
                $parent_2 = $this->ac->getRow(['ac_id' => $parent_1['ac_parent_id']]);
                $info['parent_2'] = $parent_2['ac_id'];
                $info['parent'] = $parent_2['ac_id'];
                if ($parent_2['ac_parent_id'] > 0) {
                    $parent_3 = $this->ac->getRow(['ac_id' => $parent_2['ac_parent_id']]);
                    $info['parent_3'] = $parent_3['ac_id'];
                    $info['parent'] = $parent_3['ac_id'];
                }
            }

            $parent_4 = $this->ac->getRow(['ac_parent_id' => $info['ac_id']]);
            if (!empty($parent_4)) {
                $info['parent_2'] = $parent_4['ac_parent_id'];
            } else {
                $info['parent_2'] = $parent_1['ac_parent_id'];
            }

            $first_cate = $this->ac->getList(['ac_parent_id' => 0]);
            $second_cate = $this->ac->getList(['ac_parent_id' => $info['parent']]);
            $third_cate = $this->ac->getList(['ac_parent_id' => $info['parent_2']]);
            return view(
                'articlesEdit',
                [
                    'info' => $info,
                    'first_cate' => $first_cate,
                    'second_cate' => $second_cate,
                    "third_cate" => $third_cate,
                ]
            );
        } else {
            $this->error("传输数据出错！请联系管理员！～");
        }
    }
    /**
     * 文章删除
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-19
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
                $where['a_id'] = intval($info['id']);
                $a_title = $this->a->getOne($where, "a_title");
                if (isset($a_title) && !empty($a_title)) {
                    $a_ret = $this->a->del($where);
                    if (false === $a_ret) {
                        return json(format('', '-1', '删除失败~!请稍候重试~!'));
                    } else {
                        $this->managerLog("删除文章,文章id为:" . $info['id']);
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
     * 文章展示
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-25
     */
    public function articlesShow()
    {
        /*是不是ajax*/
        if ($this->request->isAjax()) {

            $post = $this->request->only(['id'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($post, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {

                $where['a.a_id'] = intval($post['id']);
                $join = [
                    ["gjt_artical_category ac", "a.ac_id = ac.ac_id"],
                ];
                $alias = "a";
                $field = "a.*,ac.ac_name";
                $info = $this->a->joinGetRow($join, $alias, $where, $field);
                ($info['a_is_open'] == 0) ? $info['a_is_open'] = "关闭" : $info['a_is_open'] = "开启";
                if ($info['a_type'] == "0") {
                    $info['a_type'] = "原创";
                } elseif ($info['a_type'] == "1") {
                    $info['a_type'] = "转载";
                } else {
                    $info['a_type'] = "翻译";
                }
                $this->managerLog("查看文章,文章id为:" . $post['id']);
                return json(format($info, '1', $data['msg']));
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 招投标列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-19
     */
    public function biddingInformationList($bi_type)
    {
        /*接收到的数据*/
        $condition = $this->request->only(["bi_title", "bi_status"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = ['bi.bi_type' => $bi_type];
        $pageParam['query']['bi.bi_type'] = $bi_type;
        if (is_array($condition)) {
            /*是否设置了状态值*/
            if (isset($condition['bi_status']) && ($condition['bi_status'] > 0)) {
                $where['bi.bi_status'] = $condition['bi_status'];
                /*保存查询条件状态*/
                $pageParam['query']['bi.bi_status'] = $condition['bi_status'];
            }
            /*是否接收到名称信息*/
            if (isset($condition['bi_title']) && ('' != $condition['bi_title'])) {
                /*模糊查询*/
                $where['bi.bi_title'] = ['like', "%" . $condition['bi_title'] . "%"];
                $pageParam['query']['bi.bi_title'] = $condition['bi_title'];
            }
        }
        $join = [
            ['gjt_region r', "r.r_id = bi.r_id"],
            ['gjt_managers m', "bi.bi_author = m.m_id"],
        ];
        $field = "r.r_name,m.m_name,bi.*";
        $alias = "bi";
        $bi_list = $this->bi->joinGetAll($join, $alias, $where, $pageParam, [], 0, $field);
        //检查招标是否结束
        $bi_list = $this->inspectbiddingInformation($bi_list);
        $this->managerLog("浏览超投标列表");
        return view(
            "biddingInformationList",
            [
                "data" => $condition, /*查询条件*/
                "list" => $bi_list['data'], /*查询结果*/
                "page" => $bi_list['page'], /*分页和html代码*/
                "lastPage" => $bi_list['lastPage'], /*总页数*/
                "currentPage" => $bi_list['currentPage'], /*当前页码*/
                "total" => $bi_list['total'], /*总条数*/
                "listRows" => $bi_list['listRows'], /*每页显示条数*/
                'bi_type' => $bi_type,
            ]
        );
    }

    /**
     * [检查招标状态]
     * @author 王牧田
     * @date 2018-05-15
     * @param $bi_list
     */
    public function inspectbiddingInformation($bi_list){

        foreach ($bi_list["data"] as $k=>$row){
            if($row["bi_status"] != 3){
                $bi_list["data"][$k]["bi_status"]=0;
                if($row["bi_end_time"] < time()){
                    $bi_list["data"][$k]["bi_status"] = 2;
                }
                if($row["bi_end_time"] > time() && $row["bi_start_time"] > time()){
                    $bi_list["data"][$k]["bi_status"] = 1;
                }
                $this->bi->save(["bi_status"=>$bi_list["data"][$k]["bi_status"]],["bi_id"=>$row["bi_id"]]);
            }

        }
        return $bi_list;
    }

    /**
     * 招标信息添加
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-19
     */
    public function biddingInformationAdd()
    {
        if ($this->request->isPost()) {
            $add_info = $this->request->only(['bi_type', 'r_id', 'bi_title', 'bi_winning_bid', 'bi_winning_bid_time', 'bi_start_time', 'bi_is_show', 'bi_contents', 'bi_is_pay', "bi_desc",'bi_end_time'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
                "bi_type" => 'require|in:0,1,2',
                "r_id" => 'number',
                "bi_title" => 'require|max:50',
                "bi_winning_bid" => 'max:30',
                "bi_winning_bid_time" => "date",
                "bi_is_show" => "alpha",
                "bi_start_time" => "date",
                "bi_end_time" => "date",
                "bi_contents" => "require",
                "bi_is_pay" => "require|in:0,1",
//                "bi_agent" => "require",
//                "bi_proprietor_com" => "require",
//                "bi_industry" => "require",
            );
            $msg = array(
                "bi_type.require" => '发布类型是必须要选择的哦!~',
                "bi_type.in" => '发布类型选择有误!~',
                "r_id.number" => '投放地区选择有误噢!~',
                "bi_title.require" => '发布标题必须填写哦!~',
                "bi_title.max" => '作标题的最大长度不能超过50个字符哦!~',
                "bi_winning_bid.max" => "中标人名字的最大长度不能超过30个字符哦!~",
                "bi_winning_bid_time.date" => "中标时间格式有无哦!~",
                "bi_start_time.date" => "招标开始时间格式有误!~",
                "bi_end_time.date" => "招标结束时间格式有误!~",
                "bi_is_show.alpha" => "是否开启选择有误!~",
                "bi_contents.require" => "文章内容是必须填写的哦!~",
                "bi_is_pay.require" => "是否收费必须选择!~",
                "bi_is_pay.in" => "是否收费选择有误!~",
//                "bi_agent" => "招标代理必须填写哦!~",
//                "bi_proprietor_com" => "require",
//                "bi_industry" => "require",
            );
            $data = verify($add_info, $rule, $msg);
            if ($data['code'] === 1) {
                $count = $this->bi->getCount(["bi_title" => $add_info['bi_title']]);
                if ($count > 0) {
                    $this->error("您输入的名称重复了噢!~,请重新输入!~");
                    exit();
                }
                /* if (empty($_FILES)) {
                $this->error("文章缩略图是必须要上传的哦!~");
                }*/
                /*先上传文件*/
                /* $path = "articles/" . date("y_m_d", time());
                $file_info = uploadImage($path);
                if ($file_info['code'] != 201) {*/
                /* $add_info['bi_thumb'] = $file_info['pic_cover'];*/

                (isset($add_info['bi_winning_bid_time']) && !empty($add_info['bi_winning_bid_time'])) ? $add_info['bi_winning_bid_time'] = date2time($add_info['bi_winning_bid_time']) : false;
                (isset($add_info['bi_start_time']) && !empty($add_info['bi_start_time'])) ? $add_info['bi_start_time'] = date2time($add_info['bi_start_time']) : false;
                (isset($add_info['bi_end_time']) && !empty($add_info['bi_end_time'])) ? $add_info['bi_end_time'] = date2time($add_info['bi_end_time']) : false;
                (isset($add_info['bi_is_show']) && !empty($add_info['bi_is_show']) && $add_info['bi_is_show'] == 'on') ? $add_info['bi_is_show'] = 1 : $add_info['bi_is_show'] = 0;
                (isset($add_info['r_id']) && $add_info['r_id'] != '') ? $add_info['r_id'] = $add_info['r_id'] : $add_info['r_id'] = 1;
                $add_info['bi_add_time'] = time();
                $add_info['bi_author'] = $this->m_id;
                $add_info['bi_editor'] = $this->m_id;
                $add_info['bi_edit_time'] = time();
                /*存入数据库*/
                $id = $this->bi->save($add_info);
                if ($id > 0) {
                    $this->managerLog("添加招投标信息,id为:" . $id);
                    $this->success("添加成功！～", url("manage/Articles/biddingInformationList", ['bi_type' => $add_info['bi_type']]));
                } else {
                    $this->error("添加失败！～");
                }

                /*} else {
            $this->error($file_info['msg']);
            }*/
            } else {
                $this->error($data['msg']);
            }
            exit;
        }
        return view(
            'biddingInformationAdd'
        );
    }
    /**
     * 招标信息修改
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-1
     */
    public function biddingInformationEdit($id)
    {
        if (isset($id) && $id > 0) {
            $condition = ['bi_id' => intval($id)];
            $bi_info = $this->bi->getRow($condition);
            if (!empty($bi_info)) {
                if ($this->request->isPost()) {
                    $save_info = $this->request->only(['bi_type', 'r_id', 'bi_title', 'bi_winning_bid', 'bi_winning_bid_time', 'bi_start_time', 'bi_is_show', 'bi_contents', 'bi_is_pay', "bi_desc",'bi_end_time'], 'post');
                    /*验证接到的值有没有问题*/
                    $rule = array(
                        "bi_type" => 'require|in:0,1,2',
                        "r_id" => 'number',
                        "bi_title" => 'require|max:50',
                        "bi_winning_bid" => 'max:30',
                        "bi_winning_bid_time" => "date",
                        "bi_is_show" => "alpha",
                        "bi_start_time" => "date",
                        "bi_contents" => "require",
                        "bi_is_pay" => "require|in:0,1",
                    );
                    $msg = array(
                        "bi_type.require" => '发布类型是必须要选择的哦!~',
                        "bi_type.in" => '发布类型选择有误!~',
                        "r_id.number" => '投放地区选择有误噢!~',
                        "bi_title.require" => '发布标题必须填写哦!~',
                        "bi_title.max" => '作标题的最大长度不能超过50个字符哦!~',
                        "bi_winning_bid.max" => "中标人名字的最大长度不能超过30个字符哦!~",
                        "bi_winning_bid_time.date" => "中标时间格式有无哦!~",
                        "bi_start_time.date" => "招标开始时间格式有误!~",
                        "bi_end_time.date" => "招标结束时间格式有误!~",
                        "bi_is_show.alpha" => "是否开启选择有误!~",
                        "bi_contents.require" => "文章内容是必须填写的哦!~",
                        "bi_is_pay.require" => "是否收费必须选择!~",
                        "bi_is_pay.in" => "是否收费选择有误!~",
                    );
                    $data = verify($save_info, $rule, $msg);
                    if ($data['code'] === 1) {
                        $count = $this->bi->getCount(["bi_title" => $save_info['bi_title']]);
                        if ($count > 0 && $bi_info['bi_title'] != $save_info['bi_title']) {
                            $this->error("您输入的名称重复了噢!~,请重新输入!~");
                            exit();
                        }
                        /* if ($_FILES['bi_thumb']['size'] > 0) {
                        $path = "articles/" . date("y_m_d", time());
                        $file_info = uploadImage($path);

                        if ($file_info['code'] != 201) {
                        $save_info['bi_thumb'] = $file_info['pic_cover'];
                        } else {
                        $this->error($data['msg']);
                        }
                        } else {
                        $save_info['bi_thumb'] = $bi_info['bi_thumb'];
                        }*/
                        (isset($save_info['bi_winning_bid_time']) && !empty($save_info['bi_winning_bid_time'])) ? $save_info['bi_winning_bid_time'] = date2time($save_info['bi_winning_bid_time']) : false;
                        (isset($save_info['bi_start_time']) && !empty($save_info['bi_start_time'])) ? $save_info['bi_start_time'] = date2time($save_info['bi_start_time']) : false;
                        (isset($save_info['bi_end_time']) && !empty($save_info['bi_end_time'])) ? $save_info['bi_end_time'] = date2time($save_info['bi_end_time']) : false;
                        (isset($save_info['bi_is_show']) && !empty($save_info['bi_is_show']) && $save_info['bi_is_show'] == 'on') ? $save_info['bi_is_show'] = 1 : $save_info['bi_is_show'] = 0;
                        (isset($save_info['r_id']) && $save_info['r_id'] != '') ? $save_info['r_id'] = $save_info['r_id'] : $save_info['r_id'] = 1;
                        $save_info['bi_editor'] = $this->m_id;
                        $save_info['bi_edit_time'] = time();
                        /*存入数据库*/
                        $id = $this->bi->save($save_info, $condition);
                        if ($id > 0) {
                            $this->managerLog("修改招投标信息,id为:" . $id);
                            $this->success("修改成功！～", url("manage/Articles/biddingInformationList", ['bi_type' => $save_info['bi_type']]));
                        } else {
                            $this->error("修改失败！～");
                        }
                    } else {
                        $this->error($data['msg']);
                    }
                    exit;
                }
                return view(
                    'biddingInformationEdit',
                    [
                        "info" => $bi_info,
                    ]
                );
            } else {
                $this->error("没有查询到该数据哦,请稍候重试!~");
            }
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }

    public function biddingInformationEnd($id){
        $condition = ['bi_id' => intval($id)];
        if ($this->request->isPost()) {
            $save_info = $this->request->only(['bi_result','bi_winbid_con','bi_area','bi_pu_time','bi_recruit_sn'], 'post');
            /*验证接到的值有没有问题*/
            $rule = array(
//                "bi_result" => 'require',
                "bi_winbid_con" =>'require',
                "bi_area" =>'require',
                "bi_pu_time" =>'require',
                "bi_recruit_sn" =>'require',
            );
            $msg = array(
//                "bi_result.require" => '请填写招标结束结果!~',
                "bi_winbid_con.require" =>'请填写中标单位!~',
                "bi_area.require" =>'请填写所属地区!~',
                "bi_pu_time.require" =>'请填写公示时间!~',
                "bi_recruit_sn.require" =>'请填写招标编号!~',
            );
            $data = verify($save_info, $rule, $msg);
            if ($data['code'] === 1) {
                $save_info['bi_status'] = 3;
                $save_info['bi_pu_time'] = strtotime($save_info['bi_pu_time']);
                $biid = $this->bi->save($save_info,$condition);
                if ($biid > 0) {
                    $this->managerLog("结束招投标信息,id为:" . $id);
                    $this->success("已结束招标！～", url("manage/Articles/biddingInformationList", ['bi_type' => 0]));
                } else {
                    $this->error("操作失败，请稍后重试！～");
                }
            }else{
                $this->error($data['msg']);
            }
        }else{
            $bi_info = $this->bi->getRow($condition);
            $bi_info['bi_end_time'] = ($bi_info['bi_end_time'] == "")?0:$bi_info['bi_end_time'];


            return view(
                'biddingInformationEnd',
                [
                    "info" => $bi_info,
                ]
            );
        }

    }



    /**
     * 招标信息删除
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-19
     */
    public function biddingInformationDel()
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
                $where['bi_id'] = intval($info['id']);
                /*查询管理员表要是有数据不允许删除*/
                $m_id = $this->bi->getOne($where, "bi_id");
                if ($m_id < 0) {
                    return json(format('', '-1', '删除失败~!该文章不存在噢~!'));
                } else {
                    /*删除mpg表数据*/
                    $ret = $this->bi->del($where);
                    if (false === $ret) {
                        return json(format('', '-1', '删除失败~!请稍候重试~!'));
                    } else {
                        $this->managerLog("删除招投标信息,id为:" . $info['id']);
                        return json(format('', '1', '删除成功~!'));
                    }
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }
    /**
     * 招投标信息展示
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-26
     */
    public function biddingInformationShow()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->only(['id'], 'post');
            $rule = array(
                "id" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($post, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                $where['bi_id'] = intval($post['id']);
                $join = [
                    ['gjt_managers m', 'm.m_id = bi.bi_author'],
                    ['gjt_managers ms', 'ms.m_id = bi.bi_editor'],
                    ['gjt_region r', 'r.r_id = bi.r_id'],
                ];
                $field = "bi.*,m.m_name bi_author,ms.m_name bi_editor,r.r_name";
                $alias = "bi";
                $data = $this->bi->joinGetRow($join, $alias, $where, $field);
                if (!empty($data)) {
                    $data['bi_winning_bid_time'] = time2date($data['bi_winning_bid_time']);
                    $data['bi_add_time'] = time2date($data['bi_add_time']);
                    $data['bi_start_time'] = time2date($data['bi_start_time']);
                    $data['bi_edit_time'] = time2date($data['bi_edit_time']);
                    $this->managerLog("查看招投标信息,id为:" . $post['id']);
                    return json(format($data, '1', "success"));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 规范列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-29
     */
    public function engineeringSpecificationsList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["es_type", "es_title", "r_id"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        $pageParam = [];
        if (is_array($condition)) {
            /*是否设置了状态值*/
            if (isset($condition['r_id']) && ($condition['r_id'] > 0)) {
                $where['es.r_id'] = $condition['r_id'];
                /*保存查询条件状态*/
                $pageParam['query']['r_id'] = $condition['r_id'];
            }
            /*是否设置了状态值*/
            if (isset($condition['es_type']) && ($condition['es_type'] != '')) {
                $where['es.es_type'] = $condition['es_type'];
                /*保存查询条件状态*/
                $pageParam['query']['es_type'] = $condition['es_type'];
            }
            /*是否接收到名称信息*/
            if (isset($condition['es_title']) && ('' != $condition['es_title'])) {
                /*模糊查询*/
                $where['es.es_title'] = ['like', "%" . $condition['es_title'] . "%"];
                $pageParam['query']['es_title'] = $condition['es_title'];
            }
        }
        $join = [
            ["gjt_region r", "r.r_id = es.r_id"],
        ];
        $field = "es.*,r.r_name";
        $list = $this->es->joinGetAll($join, "es", $where, $pageParam, [], 0, $field);
        return view(
            "engineeringSpecificationsList",
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
     * 添加规范
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-29
     */
    public function engineeringSpecificationsAdd()
    {
        if ($this->request->isPost()) {
            $post = $this->request->only(["es_type", "es_title", "es_file_sn", "es_implementation_time", "r_id", "es_is_show","es_department","es_statime","es_chiefeditor","es_page"], "post");
            $rule = [
                "es_type" => 'require|in:0,1,2',
                "es_title" => 'require|max:50',
                "es_file_sn" => 'require|max:40|alphaDash',
                "es_implementation_time" => 'require|date',
                "r_id" => 'number',
            ];
            $msg = [
                "es_type.require" => '规范类型必须选择!~',
                "es_type.in" => '规范类型选择有误!~',
                "es_title.require" => '书名必须填写!~',
                "es_title.max" => '书名最大长度不能超过50个字符!~',
                "es_file_sn.require" => '文件编号必须填写!~',
                "es_file_sn.max" => '文件编号最大长度不能超过40个字符!~',
                "es_file_sn.alphaDash" => '文件标号只能输入英文,数字,下划线及破折号!~',
                "es_implementation_time.require" => '实施日期必须填写!~',
                "es_implementation_time.date" => '实施日期格式不正确!~',
                "r_id.number" => '投放地区选择有误!~',
            ];
            $data = verify($post, $rule, $msg);
            if ($data['code'] === 1) {
                $path = "engineeringSpecifications/" . date("y_m_d", time());
                $es_thumb = uploadImage($path, "es_thumb");

                $pathPDF = "engineeringSpecificationsPDF/" . date("y_m_d", time());
                $es_path = uploadPdf($pathPDF, "es_path");
                /*先上传文件*/
                if ($es_thumb['code'] == 200) {
                    $post['es_thumb'] = $es_thumb['pic_cover'];
                } else {
                    $this->error($es_thumb['msg']);
                    exit();
                }

                if ($es_path['code'] == 200) {
                    $post['es_path'] = $es_path['pdf_cover'];
                } else {
                    //$es_thumb['msg']
                    $this->error("pdf_格式不正确");
                    exit();
                }

                $post['es_implementation_time'] = date2time($post['es_implementation_time']);
                /* 判断是否开启 */
                (isset($post['es_is_show']) && !empty($post['es_is_show']) && $post['es_is_show'] == 'on') ? $post['es_is_show'] = 1 : $post['es_is_show'] = 0;
                (isset($post['r_id']) && $post['r_id'] != '') ? $post['r_id'] = $post['r_id'] : $post['r_id'] = 1;

                $id = $this->es->save($post);
                if ($id > 0) {
                    $this->success("添加成功!~", url("manage/Articles/engineeringSpecificationsList"));
                } else {
                    $this->error("添加失败!~");
                }
            } else {
                $this->error($data['msg']);
            }
            exit();
        }
        return view(
            "engineeringSpecificationsAdd"
        );
    }
    /**
     * 修改规范
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-29
     */
    public function engineeringSpecificationsEdit($id)
    {

        if (isset($id) && $id > 0) {
            $es_where['es_id'] = intval($id);
            $data = $this->es->getRow($es_where);
            if ($this->request->isPost()) {
                $post = $this->request->only(["es_type", "es_title", "es_file_sn", "es_implementation_time", "r_id", "es_is_show","es_department","es_statime","es_chiefeditor","es_page"], "post");
                $rule = [
                    "es_type" => 'require|in:0,1,2',
                    "es_title" => 'require|max:50',
                    "es_file_sn" => 'require|max:40|alphaDash',
                    "es_implementation_time" => 'require|date',
                    "r_id" => 'number',
                ];
                $msg = [
                    "es_type.require" => '规范类型必须选择!~',
                    "es_type.in" => '规范类型选择有误!~',
                    "es_title.require" => '书名必须填写!~',
                    "es_title.max" => '书名最大长度不能超过50个字符!~',
                    "es_file_sn.require" => '文件编号必须填写!~',
                    "es_file_sn.max" => '文件编号最大长度不能超过40个字符!~',
                    "es_file_sn.alphaDash" => '文件标号只能输入英文,数字,下划线及破折号!~',
                    "es_implementation_time.require" => '实施日期必须填写!~',
                    "es_implementation_time.date" => '实施日期格式不正确!~',
                    "r_id.number" => '投放地区选择有误!~',
                ];
                $data = verify($post, $rule, $msg);

                if ($data['code'] === 1) {


                    if ($_FILES['es_thumb']['error'] != 4) {
                        $path = "engineeringSpecifications/" . date("y_m_d", time());
                        $es_thumb = uploadImage($path, "es_thumb");

                        /*先上传文件*/
                        if ($es_thumb['code'] != 201) {
                            $post['es_thumb'] = $es_thumb['pic_cover'];
                        } else {
                            $this->error($data['msg']);
                            exit();
                        }
                    }

                    if ($_FILES['es_path']['error'] != 4) {
                        $pathPDF = "engineeringSpecificationsPDF/" . date("y_m_d", time());
                        $es_path = uploadPdf($pathPDF, "es_path");
                        if ($es_path['code'] != 201) {
                            $post['es_path'] = $es_path['pdf_cover'];
                        } else {
                            $this->error($data['msg']);
                            exit();
                        }
                    }

                    $post['es_implementation_time'] = date2time($post['es_implementation_time']);
                    /* 判断是否开启 */
                    (isset($post['es_is_show']) && !empty($post['es_is_show']) && $post['es_is_show'] == 'on') ? $post['es_is_show'] = 1 : $post['es_is_show'] = 0;
                    (isset($post['r_id']) && $post['r_id'] != '') ? $post['r_id'] = $post['r_id'] : $post['r_id'] = 1;
                    // dump($post);exit();
                    $id = $this->es->save($post,$es_where);
                    if (false !== $id) {
                        $this->success("修改成功!~", url("manage/Articles/engineeringSpecificationsList"));
                    } else {
                        $this->error("修改失败!~");
                    }
                } else {
                    $this->error($data['msg']);
                }
                exit();
            }
            return view(
                "engineeringSpecificationsEdit",
                [
                    'data' => $data,
                ]
            );
        } else {
            $this->error('程序员都累吐血了也没有接到传输的数据噢!~');
        }
        
    }
    /**
     * 删除规范
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-29
     */
    public function engineeringSpecificationsDel()
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
                $where['es_id'] = intval($info['id']);
                /*删除mpg表数据*/
                $ret = $this->es->del($where);
                if (false === $ret) {
                    return json(format('', '-1', '删除失败~!请稍候重试~!'));
                } else {
                    return json(format('', '1', '删除成功~!'));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }
    /**
     * 多选删除规范
     * @author 王牧田
     * @e-mail zrkjhlc@gmail.com
     * @date   2018-05-28
     */
    public function engineeringSpecificationsallDel(){
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
                $where['es_id'] = ["in",$info['id']];

                /*删除mpg表数据*/
                $ret = $this->es->del($where);
                if (false === $ret) {
                    return json(format('', '-1', '删除失败~!请稍候重试~!'));
                } else {
                    return json(format('', '1', '删除成功~!'));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }

    /**
     * 多选删除招标
     * @author 王牧田
     * @date 2018-05-02
     * @return 1 成功 -1失败
     */
    public  function biddingInformationallDel(){
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
                $where['bi_id'] = ["in",$info['id']];

                /*删除mpg表数据*/
                $ret = $this->bi->del($where);
                if (false === $ret) {
                    return json(format('', '-1', '删除失败~!请稍候重试~!'));
                } else {
                    return json(format('', '1', '删除成功~!'));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }



}
