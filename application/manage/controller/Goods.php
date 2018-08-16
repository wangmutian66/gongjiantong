<?php
namespace app\manage\controller;

use model\GoodsActivity as ga;
use model\GoodsAndSpecifications as gas;
use model\Goods as g;
use model\GoodsEvaluation as ge;
use model\GoodsPicture as gp;
use model\GoodsSpecification as gs;
use model\GoodsType as gt;
use model\ManageGoodsBrand as mgb;
use model\ManageGoodsCategory as mgc;
use model\ShopBrandApplication as sba;
use model\GoodsSpecifications as gsp;
use model\Specifications as sp;
/**
 * 商品管理
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-10-12
 */
class Goods extends Base
{
    protected $g; /*商品*/
    protected $ga; /*商品活动*/
    protected $gas; /*商品和规格关联表*/
    protected $ge; /*商品评价*/
    protected $gp; /*商品图片*/
    protected $gs; /*商品规格*/
    protected $gt; /*商品类型*/
    protected $mgb; /*品牌*/
    protected $mgc; /*总后台分类*/
    protected $masb; /*商家品牌和平台品牌关联*/
    protected $sba; /*商户品牌申请表*/
    protected $gsp; /* 商品规格对应的库存和价格 */
    protected $sp;
    /**
     * [__construct description] 继承父类并且实例化该控制器所需的model
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-12
     */
    public function __construct()
    {
        parent::__construct();
        $this->g = new g(); /*商品*/
        $this->ga = new ga(); /*商品活动*/
        $this->gas = new gas(); /*商品和规格关联表*/
        $this->ge = new ge(); /*商品评价*/
        $this->gp = new gp(); /*商品图片*/
        $this->gs = new gs(); /*商品规格*/
        $this->gt = new gt(); /*商品类型*/
        $this->mgb = new mgb(); /*品牌*/
        $this->mgc = new mgc(); /*总后台分类*/
        $this->sba = new sba(); /*商户品牌申请表*/
        $this->gsp = new gsp();
        $this->sp = new sp();
    }
    /**
     * 商品列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-12
     */
    public function goodsList($ss_id=0)
    {
        /*接收到的数据*/
        $condition = $this->request->only(["is_show", "g_name","is_hot","is_recommend","is_new"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        $where['g.g_goods_verify'] = '1';
        
        if($ss_id!=0){
            $where['ss.ss_id'] = $ss_id;
            /*保存查询条件状态*/
            $pageParam['query']['ss.ss_id'] = $ss_id;
        }

        if (is_array($condition)) {
            /*是否设置了状态值*/
            if (isset($condition['is_show']) && ('' !== $condition['is_show'])) {
                $where['g.is_show'] = $condition['is_show'];
                /*保存查询条件状态*/
                $pageParam['query']['g.is_show'] = $condition['is_show'];
            }
            /*是否设置了状态值*/
            if (isset($condition['is_hot']) && ('' !== $condition['is_hot'])) {
                $where['g.is_hot'] = $condition['is_hot'];
                /*保存查询条件状态*/
                $pageParam['query']['g.is_show'] = $condition['is_show'];
            }
            /*是否设置了状态值*/
            if (isset($condition['is_recommend']) && ('' !== $condition['is_recommend'])) {
                $where['g.is_recommend'] = $condition['is_recommend'];
                /*保存查询条件状态*/
                $pageParam['query']['g.is_recommend'] = $condition['is_recommend'];
            }
            /*是否设置了状态值*/
            if (isset($condition['is_new']) && ('' !== $condition['is_new'])) {
                $where['g.is_new'] = $condition['is_new'];
                /*保存查询条件状态*/
                $pageParam['query']['g.is_new'] = $condition['is_new'];
            }
            /*是否接收到名称信息*/
            if (isset($condition['g_name']) && ('' != $condition['g_name'])) {
                /*模糊查询*/
                $where['g.g_name'] = ['like', "%" . $condition['g_name'] . "%"];
                $pageParam['query']['g.g_name'] = $condition['g_name'];
            }
        }
        $join = [
            ["gjt_seller_shop ss", "ss.ss_id = g.ss_id"],
            ["gjt_manage_goods_category mgc", "mgc.mgc_id = g.mgc_id"],
        ];
        $alias = "g";
        $field = "g.g_id, g.g_name, g.g_inventory, g.g_original_price, g.is_show, g.is_new, g.is_recommend, g.is_hot, g.g_sort, g.g_show_img_path, g.g_goods_verify, ss.ss_name, mgc.mgc_name,g.g_add_time";
        $list = $this->g->joinGetAll($join, $alias, $where, $pageParam, [], 0, $field);

        foreach ($list['data'] as $k=>$row){
            $gsp_price = $this->gsp->getOne(["g_id"=>$row["g_id"]],"gsp_price");
            $list['data'][$k]["g_original_price"] = empty($gsp_price)?0:$gsp_price;
        }

        $this->managerLog("查看商品列表");
        return view(
            "goodsList",
            [
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
     * 商品详情
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-11-21
     * @return [type]            [description]
     */
    public function goodsInfo()
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

                $where['g_id'] = intval($info['id']);

                $join = [
                    ["gjt_seller_shop ss", "ss.ss_id = g.ss_id"],
                    ["gjt_manage_goods_category mgc", "mgc.mgc_id = g.mgc_id"],
                    ["gjt_manage_goods_brand mgb", "mgb.mgb_id = g.mgb_id"],
                ];
                $alias = "g";
                $field = "g.*, ss.ss_name, mgc.mgc_name, mgb.mgb_name";
                $info = $this->g->joinGetRow($join, $alias, $where, $field);
                if ($info['is_show'] == "1") {
                    $info['is_show'] = "上架";
                } else {
                    $info['is_show'] = "下架";
                }
                $info['g_add_time'] = time2date($info['g_add_time']);
                /*规格*/
                $gsplist = $this->gsp->getList(["g_id"=>$info["g_id"]]);
                foreach ($gsplist as $k=>$row){
                    $gsplist[$k]["sp_name"] = $this->sp->getOne(["id"=>$row["sp_id"]],"sp_name");
                }
                $info["gsplist"] = $gsplist;


                $this->managerLog("查看商品信息,商品id为:".$where['g_id']);
                return json(format($info, '1', 'success'));
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }

    }
    public function changeGoodsInfo()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->only(["id", "val", "field"], "post");
            /*验证接到的值有没有问题*/
            $rule = array(
                "id" => 'require|number',
                "val" => 'require|in:0,1,2',
                "field" => 'require',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
                "id.number" => '传输数据出错哦!~',
                "val.require" => '缺少必要参数!~',
                "val.in" => '必要参数传输有误!~',
                "field.require" => '缺少必要参数!~',
            );
            $data = verify($post, $rule, $msg);
            /*验证返回如果code == 1 说明成功*/
            if ($data['code'] === 1) {
                $save[$post["field"]] = $post['val'];
                $where["g_id"] = intval($post['id']);
                $ret = $this->g->save($save, $where);
                if (false !== $ret) {
                    $this->managerLog("修改商品信息,商品id为:".$post['id']);
                    return json(format($post, '1', 'success'));
                } else {
                    return json(format('', '-1', "修改失败,请联系管理员!~"));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 商品删除
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-18
     */
    public function goodsDel()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->only(['id'], "post");
            $rule = array(
                "id" => 'require|number',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
                "id.number" => '数据格式不正确噢!~',
            );
            $data = verify($post, $rule, $msg);
            if ($data['code'] === 1) {
                $g_info = $this->g->getRow(['g_id' => $post['id']],"g_goods_verify,g_id,g_show_img_path");
                if (!empty($g_info)) {
                    if ($g_info['g_goods_verify'] != 2) {
                        return json(format('', '-1', "该商品处于审核状态,只有不通过才能删除~!"));
                    }
                } else {
                    return json(format('', '-1', "该商品不存在~!"));
                }
                $ret = $this->g->del(['g_id' => $post['id']]);
                $gp_list = $this->gp->getList(['g_id' => $post['id']]);
                foreach ($gp_list as $key => $value) {
                    unlink($_SERVER['DOCUMENT_ROOT'].UPLOAD.'/'.$value['gp_picture_path']);
                }
                unlink($_SERVER['DOCUMENT_ROOT'].UPLOAD.'/'.$g_info['g_show_img_path']);
                $this->gp->del(['g_id' => $post['id']]);
                $this->gas->del(['g_id' => $post['id']]);
                if (false === $ret) {
                    return json(format('', '-1', "删除失败~!"));
                } else {
                    $this->managerLog("删除商品,商品id为:".$post['id']);
                    return json(format('', '1', "删除成功~!"));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 商品分类
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-12
     */
    public function goodsCategoryList()
    {
        /*获取列表*/
        $list = $this->mgc->getAll(["mgc_parent_id" => 0], [], ['mgc_sort' => "desc"]);
        $this->managerLog("查看商品分类!");
        return view(
            "goodsCategoryList",
            [
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
     * 商品分类添加
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-15
     * @return [type]            [description]
     */
    public function goodsCategoryAdd()
    {
        if ($this->request->isPost()) {
            $post = $this->request->only(["mgc_name", "first_parent_id", "second_parent_id", "mgc_sort", "mgc_desc", "mgc_is_show", "is_hot"], "post");
            $rule = array(
                "mgc_name" => "require|chsAlphaNum",
                "first_parent_id" => "require|number",
                "second_parent_id" => "number",
                "mgc_sort" => "require|between:1,100",
            );
            $msg = array(
                "mgc_name.require" => "分类名称必须添加噢!~",
                "mgc_name.chsAlpha" => "分类名称只能是汉字,英文和数字!~",
                "first_parent_id.require" => "分类归属必须选择噢!~",
                "first_parent_id.number" => "分类归属选择有误!~",
                "second_parent_id.number" => "二级分类归属选择有误!~",
                "mgc_sort.require" => "排序是必须填写的哦!~",
                "mgc_sort.between" => "排序只能填写1至100的正整数!~",
            );
            $data = verify($post, $rule, $msg);
            if ($data['code'] === 1) {

                if ($post['first_parent_id'] > 0 && $post['second_parent_id'] > 0) {
                    $post['mgc_parent_path'] = "0," . $post['first_parent_id'] . "," . $post['second_parent_id'] . ",";
                    $post['mgc_parent_id'] = $post['second_parent_id'];
                } else if ($post['first_parent_id'] == 0) {
                    $post['mgc_parent_id'] = 0;
                    $post['mgc_parent_path'] = "0,";
                } else if ($post['first_parent_id'] > 0 && $post['second_parent_id'] == "") {
                    $post['mgc_parent_id'] = $post['first_parent_id'];
                    $post['mgc_parent_path'] = "0," . $post['first_parent_id'] . ",";
                }
                /*检验名称是否重复*/
                $mgc_name = $this->mgc->getOne(["mgc_name" => $post['mgc_name'], "mgc_parent_id" => $post['mgc_parent_id']], "mgc_name");

                if ($mgc_name == $post['mgc_name'] && !empty($mgc_name)) {
                    $this->error("您输入的名称重复了噢,请重新输入!~");
                    exit();
                }
                /* 判断是否开启 */
                (isset($post['mgc_is_show']) && !empty($post['mgc_is_show']) && $post['mgc_is_show'] == 'on') ? $post['mgc_is_show'] = 1 : $post['mgc_is_show'] = 0;
                /* 判断是否热门 */
                (isset($post['is_hot']) && !empty($post['is_hot']) && $post['is_hot'] == 'on') ? $post['is_hot'] = 1 : $post['is_hot'] = 0;

                unset($post['first_parent_id']);
                unset($post['second_parent_id']);
                $id = $this->mgc->save($post);
                if ($id > 0) {
                    $this->managerLog("添加商品分类,分类id为:".$id);
                    $this->success("分类添加成功!~", url("manage/Goods/goodsCategoryList"));
                } else {
                    $this->error("分类添加失败!~");
                }
            } else {
                $this->error($data['msg']);
            }
        }
        $list = $this->mgc->getList(["mgc_parent_id" => 0], [], ['mgc_sort' => "desc"]);
        return view(
            "goodsCategoryAdd",
            [
                "list" => $list,
            ]
        );
    }
    /**
     * 商品分类修改
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-16
     */
    public function goodsCategoryEdit($id)
    {
        if (isset($id) && $id > 0) {
            $where = ["mgc_id" => intval($id)];
            $info = $this->mgc->getRow($where);
            $mgc_parent_path = explode(",", $info['mgc_parent_path']);
            foreach ($mgc_parent_path as $key => $value) {
                if ($value === '') {
                    unset($mgc_parent_path[$key]);
                }
            }
            if ($this->request->isPost()) {
                $post = $this->request->only(["mgc_name", "first_parent_id", "second_parent_id", "mgc_sort", "mgc_desc", "mgc_is_show", "is_hot"], "post");
                $rule = array(
                    "mgc_name" => "require|chsAlphaNum",
                    "first_parent_id" => "require|number",
                    "second_parent_id" => "number",
                    "mgc_sort" => "require|between:1,100",
                );
                $msg = array(
                    "mgc_name.require" => "分类名称必须添加噢!~",
                    "mgc_name.chsAlpha" => "分类名称只能是汉字,英文和数字!~",
                    "first_parent_id.require" => "分类归属必须选择噢!~",
                    "first_parent_id.number" => "分类归属选择有误!~",
                    "second_parent_id.number" => "二级分类归属选择有误!~",
                    "mgc_sort.require" => "排序是必须填写的哦!~",
                    "mgc_sort.between" => "排序只能填写1至100的正整数!~",
                );
                $data = verify($post, $rule, $msg);
                if ($data['code'] === 1) {
                    if ($post['first_parent_id'] > 0 && $post['second_parent_id'] > 0) {
                        $post['mgc_parent_path'] = "0," . $post['first_parent_id'] . "," . $post['second_parent_id'] . ",";
                        $post['mgc_parent_id'] = $post['second_parent_id'];
                    } else if ($post['first_parent_id'] == 0) {
                        $post['mgc_parent_id'] = 0;
                        $post['mgc_parent_path'] = "0,";
                    } else if ($post['first_parent_id'] > 0 && $post['second_parent_id'] == "") {
                        $post['mgc_parent_id'] = $post['first_parent_id'];
                        $post['mgc_parent_path'] = "0," . $post['first_parent_id'] . ",";
                    }
                    /*检验名称是否重复*/
                    $mgc_name = $this->mgc->getOne(["mgc_name" => $post['mgc_name'], "mgc_parent_id" => $post['mgc_parent_id']], "mgc_name");

                    if ($mgc_name == $post['mgc_name'] && $post['mgc_name'] != $info['mgc_name']) {
                        $this->error("您输入的名称重复了噢,请重新输入!~");
                        exit();
                    }
                    $count = $this->mgc->getCount(["mgc_parent_id" => intval($id)]);
                    if ($count > 0 && $post['mgc_parent_path'] != $info['mgc_parent_path']) {
                        $this->error("该分类下边有子级分类,请先修改子级分类!~");
                        exit();
                    }
                    /* 判断是否开启 */
                    (isset($post['mgc_is_show']) && !empty($post['mgc_is_show']) && $post['mgc_is_show'] == 'on') ? $post['mgc_is_show'] = 1 : $post['mgc_is_show'] = 0;
                    /* 判断是否热门 */
                    (isset($post['is_hot']) && !empty($post['is_hot']) && $post['is_hot'] == 'on') ? $post['is_hot'] = 1 : $post['is_hot'] = 0;

                    unset($post['first_parent_id']);
                    unset($post['second_parent_id']);
                    $id = $this->mgc->save($post, $where);
                    if ($id > 0) {
                        $this->managerLog("修改商品分类,分类id为");
                        $this->error("分类修改成功!~", url("manage/Goods/goodsCategoryList"));
                    } else {
                        $this->error("分类修改失败!~");
                    }
                } else {
                    $this->error($data['msg']);
                }
            }
            return view(
                "goodsCategoryEdit",
                [
                    "info" => $info,
                    "mgc_parent_path" => $mgc_parent_path,
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }
    /**
     * 删除分类
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-18
     */
    public function goodsCategoryDel()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->only(['id'], "post");
            $rule = array(
                "id" => 'require|number',
            );
            $msg = array(
                "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
                "id.number" => '数据格式不正确噢!~',
            );
            $data = verify($post, $rule, $msg);
            if ($data['code'] === 1) {
                $mgc_count = $this->mgc->getCount(['mgc_parent_id' => $post['id']]);
                $g_count = $this->g->getCount(['mgc_id' => $post['id']]);
                if ($mgc_count > 0) {
                    return json(format('', '-1', "该分类下面包含子级分类!~请先删除子级分类~!"));
                }
                if ($g_count > 0) {
                    return json(format('', '-1', "该分类下包含商品,请先修改商品分类或删除商品在删除该分类~!"));
                }
                $ret = $this->mgc->del(['mgc_id' => $post['id']]);
                if (false === $ret) {
                    return json(format('', '-1', "删除失败~!"));
                } else {
                    $this->managerLog("删除商品分类,分类id为:".$post['id']);
                    return json(format('', '1', "删除成功~!"));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * ajax获取商品分类
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-15a
     */
    public function ajaxGetCategory()
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
                $mgc_list = $this->mgc->getList(["mgc_parent_id" => $info['id']], [], ['mgc_sort' => "desc"]);
                return json(format($mgc_list, '1'));
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 分类信息展示
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-15
     */
    public function goodsCategoryShow()
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
                $where["mgc_id"] = intval($info['id']);
                $info = $this->mgc->getRow($where);
                /*转换*/
                (isset($info['mgc_is_show']) && ($info['mgc_is_show'] == 1)) ? $info['mgc_is_show'] = "开启" : $info['mgc_is_show'] = "关闭";

                (isset($info['is_hot']) && ($info['is_hot'] == 1)) ? $info['is_hot'] = "开启" : $info['is_hot'] = "关闭";

                (isset($info['mgc_parent_id']) && ($info['mgc_parent_id'] == 0)) ? $info['mgc_parent_id'] = "顶级分类" : $info['mgc_parent_id'] = $this->mgc->getOne(["mgc_id" => $info['mgc_parent_id']], 'mgc_name');
                $this->managerLog("查看商品分类,分类id为:".$where["mgc_id"]);
                return json(format($info, '1', $data['msg']));
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 品牌管理
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-12
     */
    public function goodsBrandList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["mgb_name", "mgb_english_name"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否接收到名称信息*/
            if (isset($condition['mgb_name']) && ('' != $condition['mgb_name'])) {
                /*模糊查询*/
                $where['mgb_name'] = ['like', "%" . $condition['mgb_name'] . "%"];
                $pageParam['query']['mgb_name'] = $condition['mgb_name'];
            }
            if (isset($condition['mgb_english_name']) && ('' != $condition['mgb_english_name'])) {
                /*模糊查询*/
                $where['mgb_english_name'] = ['like', "%" . $condition['mgb_english_name'] . "%"];
                $pageParam['query']['mgb_english_name'] = $condition['mgb_english_name'];
            }
        }
        $list = $this->mgb->getAll($where, $pageParam);
        $this->managerLog("查看品牌列表!");
        return view(
            "goodsBrandList",
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
     * 商品品牌添加
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-13
     */
    public function goodsBrandAdd()
    {
        if ($this->request->isPost()) {
            $post = $this->request->only(["mgb_name", "mgb_english_name", "mgb_official_website", "is_recommend", "mgb_sort", "mgb_desc"], "post");
            /*验证接到的值有没有问题*/
            $rule = array(
                "mgb_name" => 'require|max:30|chsAlphaNum',
                "mgb_english_name" => 'require|max:30|alphaNum',
                "mgb_official_website" => 'url',
                "is_recommend" => 'require|in:0,1',
                "mgb_sort" => 'require|between:1,100',
                "mgb_desc" => 'max:255',
            );
            $msg = array(
                "mgb_name.require" => '品牌名称是必须要填写的噢!~',
                "mgb_name.max" => '品牌名称最多三十个字符哦!~',
                "mgb_name.chsAlphaNum" => '品牌名称只能是只能是汉字,英文和数字哦!~',
                "mgb_english_name.require" => '品牌英文名必须要填写哦!~',
                "mgb_english_name.max" => '品牌英文名最大长度为60个字符哦!',
                "mgb_english_name.alphaNum" => '品牌英文名只能是英文和数字哦!~',
                "mgb_official_website.url" => '品牌官网地址填写有误!~',
                "is_recommend.require" => '是否推荐必须选择!~',
                "is_recommend.in" => '是否推荐选择有误!~',
                "mgb_sort.require" => '排序是必须填写的哦!~',
                "mgb_sort.between" => '排序只能填写1至100的正整数!~',
                "mgb_desc.max" => '描述最多只能填写255个字符噢!~',
            );
            $data = verify($post, $rule, $msg);
            if ($data['code'] === 1) {
                $count = $this->mgb->getCount(["mgb_name" => $post['mgb_name']]);
                if ($count > 0) {
                    $this->error("您输入的名称重复了噢!~,请重新输入!~");
                    exit();
                }
                if ($_FILES['mgb_logo_path']['error'] == 4) {
                    $this->error("文章缩略图是必须要上传的哦!~");
                    exit();
                }
                /*先上传文件*/
                $path = "goods_brand/" . date("y_m_d", time());
                $file_info = uploadImage($path);
                if ($file_info['code'] == 200) {
                    $post['mgb_logo_path'] = $file_info['pic_cover'];
                    $post['mgb_add_time'] = time();
                    /*存入数据库*/
                    $id = $this->mgb->save($post);
                    if ($id > 0) {
                        $this->managerLog("添加商品品牌,品牌id为:".$id);
                        $this->success("添加成功！～", url("manage/Goods/goodsBrandList"));
                    } else {
                        $this->error("添加失败！～");
                    }
                } else {
                    $this->error($file_info['msg']);
                }
            } else {
                $this->error($data['msg']);
            }
            exit();
        }
        return view(
            "goodsBrandAdd"
        );
    }
    /**
     * 品牌修改
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-18
     */
    public function goodsBrandEdit($id)
    {
        if (isset($id) && $id > 0) {
            $where = ["mgb_id" => intval($id)];
            $info = $this->mgb->getRow($where);
            if ($this->request->isPost()) {
                $post = $this->request->only(["mgb_name", "mgb_english_name", "mgb_official_website", "is_recommend", "mgb_sort", "mgb_desc"], 'post');
                /*验证接到的值有没有问题*/
                $rule = array(
                    "mgb_name" => 'require|max:30|chs',
                    "mgb_english_name" => 'require|max:30|alphaNum',
                    "mgb_official_website" => 'url',
                    "is_recommend" => 'require|in:0,1',
                    "mgb_sort" => 'require|between:1,100',
                    "mgb_desc" => 'max:255',
                );
                $msg = array(
                    "mgb_name.require" => '品牌名称是必须要填写的噢!~',
                    "mgb_name.max" => '品牌名称最多三十个字符哦!~',
                    "mgb_name.chs" => '品牌名称只能是汉字哦!~',
                    "mgb_english_name.require" => '品牌英文名必须要填写哦!~',
                    "mgb_english_name.max" => '品牌英文名最大长度为60个字符哦!',
                    "mgb_english_name.alphaNum" => '品牌英文名只能是英文和数字哦!~',
                    "mgb_official_website.url" => '品牌官网地址填写有误!~',
                    "is_recommend.require" => '是否推荐必须选择!~',
                    "is_recommend.in" => '是否推荐选择有误!~',
                    "mgb_sort.require" => '排序是必须填写的哦!~',
                    "mgb_sort.between" => '排序只能填写1至100的正整数!~',
                    "mgb_desc.max" => '描述最多只能填写255个字符噢!~',
                );
                $data = verify($post, $rule, $msg);
                if ($data['code'] === 1) {
                    $count = $this->mgb->getCount(["mgb_name" => $post['mgb_name']]);
                    if ($count > 0 && $post['mgb_name'] != $info['mgb_name']) {
                        $this->error("您输入的名称重复了噢!~,请重新输入!~");
                        exit();
                    }
                    if ($_FILES['mgb_logo_path']['error'] != 4) {
                        $path = "goodsBrand/" . date("y_m_d", time());
                        $file_info = uploadImage($path);
                        if ($file_info['code'] == 200) {
                            $post['mgb_logo_path'] = $file_info['pic_cover'];
                        } else {
                            $this->error($file_info['msg']);
                        }
                    }
                    $ret = $this->mgb->save($post, $where);
                    if (false !== $ret) {
                        $this->managerLog("修改商品品牌,品牌id为:".$id);
                        $this->success("修改成功!~", url("manage/Goods/goodsBrandList"));
                    } else {
                        $this->error("修改失败!~");
                    }
                } else {
                    $this->error($data['msg']);
                }
                exit();
            }
            return view(
                "goodsBrandEdit",
                [
                    "info" => $info,
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }
    /**
     * 品牌删除
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-18
     */
    public function goodsBrandDel()
    {
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
                $where = ['mgb_id' => intval($info['id'])];
                $count = $this->g->getCount($where);
                if ($count > 0) {
                    return json(format('', '-1', '删除失败~!该品牌下面有商品哦~!'));
                }
                $mgb = $this->mgb->del($where);
                if (false === $mgb) {
                    return json(format('', '-1', '删除失败~!请稍候重试~!'));
                } else {
                    $this->managerLog("删除商品品牌,品牌id为:".$info['id']);
                    return json(format('', '1', '删除成功~!'));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    // /**
     // * 商品类型
     // * @author 户连超
     // * @e-mail zrkjhlc@gmail.com
     // * @date   2017-10-12
     // */
    // public function goodsTypeList()
    // {
        // /*接收到的数据*/
        // $condition = $this->request->only(["gt_name"], "get");
        // /*保存查询信息*/
        // $pageParam = [];
        // /*查询套件*/
        // $where = [];
        // if (is_array($condition)) {
            // /*是否接收到名称信息*/
            // if (isset($condition['gt_name']) && ('' != $condition['gt_name'])) {
                // /*模糊查询*/
                // $where['gt_name'] = ['like', "%" . $condition['gt_name'] . "%"];
                // $pageParam['query']['gt_name'] = $condition['gt_name'];
            // }
        // }
        // $list = $this->gt->getAll($where, $pageParam);
        // return view(
            // "goodsTypeList",
            // [
                // "data" => $condition, /*查询条件*/
                // "list" => $list['data'], /*查询结果*/
                // "page" => $list['page'], /*分页和html代码*/
                // "lastPage" => $list['lastPage'], /*总页数*/
                // "currentPage" => $list['currentPage'], /*当前页码*/
                // "total" => $list['total'], /*总条数*/
                // "listRows" => $list['listRows'], /*每页显示条数*/
            // ]
        // );
    // }
    // /**
     // * 商品类型添加
     // * @author 户连超
     // * @e-mail zrkjhlc@gmail.com
     // * @date   2017-10-13
     // */
    // public function goodsTypeAdd()
    // {
        // if ($this->request->isPost()) {
            // $post = $this->request->only(['gt_name', 'gt_desc'], 'post');
            // /*验证接到的值有没有问题*/
            // $rule = array(
                // "gt_name" => 'require|max:30',
                // "gt_desc" => 'max:255',
            // );
            // $msg = array(
                // "gt_name.require" => '类型名称是必须要填写的噢!~',
                // "gt_name.max" => '名称的最大长度不能超过30个字符噢!~',
                // "gt_desc.max" => '描述最多只能填写255个字符噢!~',
            // );
            // $data = verify($post, $rule, $msg);
            // if ($data['code'] === 1) {
                // $post['gt_add_time'] = time();
                // $id = $this->gt->save($post);
                // if ($id > 0) {
                    // $this->success("添加成功!~", url("manage/Goods/goodsTypeList"));
                // } else {
                    // $this->error("添加失败!~");
                // }
            // } else {
                // $this->error($data['msg']);
            // }
            // exit();
        // }
        // return view(
            // "goodsTypeAdd"
        // );
    // }
    // /**
     // * 商品类型修改
     // * @author 户连超
     // * @e-mail zrkjhlc@gmail.com
     // * @date   2017-10-13
     // */
    // public function goodsTypeEdit($id)
    // {
        // if (isset($id) && $id > 0) {
            // $where = ["gt_id" => intval($id)];
            // $info = $this->gt->getRow($where);
            // if ($this->request->isPost()) {
                // $post = $this->request->only(['gt_name', 'gt_desc'], 'post');
                // $rule = array(
                    // "gt_name" => 'require|max:30',
                    // "gt_desc" => 'max:255',
                // );
                // $msg = array(
                    // "gt_name.require" => '类型名称是必须要填写的噢!~',
                    // "gt_name.max" => '名称的最大长度不能超过30个字符噢!~',
                    // "gt_desc.max" => '描述最多只能填写255个字符噢!~',
                // );
                // $data = verify($post, $rule, $msg);
                // if ($data['code'] === 1) {
                    // $ret = $this->gt->save($post, $where);
                    // if (false !== $ret) {
                        // $this->success("修改成功!~", url("manage/Goods/goodsTypeList"));
                    // } else {
                        // $this->error("修改失败!~");
                    // }
                // } else {
                    // $this->error($data['msg']);
                // }
                // exit();
            // }
            // return view(
                // "goodsTypeEdit",
                // [
                    // "info" => $info,
                // ]
            // );
        // } else {
            // $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        // }
    // }
    // /**
     // * 商品类型删除
     // * @author 户连超
     // * @e-mail zrkjhlc@gmail.com
     // * @date   2017-10-13
     // */
    // public function goodsTypeDel()
    // {
        // if ($this->request->isAjax()) {
            // /*只接收id的值*/
            // $info = $this->request->only(['id'], 'post');
            // /*验证接到的值有没有问题*/
            // $rule = array(
                // "id" => 'require',
            // );
            // $msg = array(
                // "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            // );
            // $data = verify($info, $rule, $msg);
            // /*验证返回如果code == 1 说明成功*/
            // if ($data['code'] === 1) {
                // /*where条件 强制转换整型*/
                // $where = ['gt_id' => intval($info['id'])];
                // $count = $this->gs->getCount($where);
                // if ($count > 0) {
                    // return json(format('', '-1', '删除失败~!该类别下面有规格属性噢~!'));
                // }
                // $gt = $this->gt->del($where);
                // if (false === $gt) {
                    // return json(format('', '-1', '删除失败~!请稍候重试~!'));
                // } else {
                    // return json(format('', '1', '删除成功~!'));
                // }
            // } else {
                // return json(format('', '-1', $data['msg']));
            // }
        // } else {
            // return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        // }
    // }
    // /**
     // * 规格列表
     // * @author 户连超
     // * @e-mail zrkjhlc@gmail.com
     // * @date   2017-10-13
     // */
    // public function goodsSpecificationList($id)
    // {
        // if (isset($id) && $id > 0) {
            // $where = ["gt_id" => intval($id)];
            // $gt_name = $this->gt->getOne($where, "gt_name");
            // $list = $this->gs->getAll($where);
            // return view(
                // "goodsSpecificationList",
                // [
                    // "gt_id" => $id,
                    // "gt_name" => $gt_name,
                    // "list" => $list['data'], /*查询结果*/
                    // "page" => $list['page'], /*分页和html代码*/
                    // "lastPage" => $list['lastPage'], /*总页数*/
                    // "currentPage" => $list['currentPage'], /*当前页码*/
                    // "total" => $list['total'], /*总条数*/
                    // "listRows" => $list['listRows'], /*每页显示条数*/
                // ]
            // );
        // } else {
            // $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        // }
    // }
    // /**
     // * 规格添加
     // * @author 户连超
     // * @e-mail zrkjhlc@gmail.com
     // * @date   2017-10-13
     // */
    // public function goodsSpecificationAdd($id)
    // {
        // if (isset($id) && $id > 0) {
            // if ($this->request->isPost()) {
                // $post = $this->request->only(["gs_name", "gs_type", "gs_value"], "post");
                // $rule = array(
                    // "gs_name" => 'require|max:30',
                    // "gs_type" => 'require|in:0,1,2',
                    // "gs_value" => 'max:255',
                // );
                // $msg = array(
                    // "gs_name.require" => '属性名称是必须要填写的噢!~',
                    // "gs_name.max" => '属性名称的最大长度不能超过30个字符噢!~',
                    // "gs_type.require" => '属性是否可选是必须要选择的噢!~',
                    // "gs_type.in" => '属性是否可选选择有误噢!~',
                    // "gs_value.max" => '描述最多只能填写255个字符噢!~',
                // );
                // $data = verify($post, $rule, $msg);
                // if ($data['code'] === 1) {
                    // if ($post['gs_type'] == 2) {
                        // $gs_value = explode("|", $post['gs_value']);
                        // unset($post['gs_value']);
                        // $post['gs_value'] = $gs_value[0];
                    // }
                    // $post['gt_id'] = intval($id);
                    // $post['gs_add_time'] = time();
                    // $ret_id = $this->gs->save($post);
                    // if ($ret_id > 0) {
                        // $this->success("添加成功", url("manage/Goods/goodsSpecificationList", ["id" => $id]));
                    // } else {
                        // $this->error("添加失败!~");
                    // }
                // } else {
                    // $this->error($data['msg']);
                // }
            // }
            // return view(
                // "goodsSpecificationAdd",
                // [
                    // "id" => $id,
                // ]
            // );
        // } else {
            // $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        // }
    // }
    /**
     * 规格修改
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-13
     */
    // public function goodsSpecificationEdit($id)
    // {
        // if (isset($id) && $id > 0) {
            // if ($this->request->isPost()) {
                // $post = $this->request->only(["gs_name", "gs_type", "gs_value", "gt_id"], "post");
                // $rule = array(
                    // "gs_name" => 'require|max:30',
                    // "gs_type" => 'require|in:0,1,2',
                    // "gs_value" => 'max:255',
                    // "gt_id" => 'require|number',
                // );
                // $msg = array(
                    // "gs_name.require" => '属性名称是必须要填写的噢!~',
                    // "gs_name.max" => '属性名称的最大长度不能超过30个字符噢!~',
                    // "gs_type.require" => '属性是否可选是必须要选择的噢!~',
                    // "gs_type.in" => '属性是否可选选择有误噢!~',
                    // "gs_value.max" => '描述最多只能填写255个字符噢!~',
                    // "gt_id.require" => '缺少必要参数!~',
                    // "gt_id.number" => '必要参数传输有误!~',
                // );
                // $data = verify($post, $rule, $msg);
                // if ($data['code'] === 1) {
                    // if ($post['gs_type'] == 2) {
                        // $gs_value = explode("|", $post['gs_value']);
                        // unset($post['gs_value']);
                        // $post['gs_value'] = $gs_value[0];
                    // }
                    // $gt_id = $post['gt_id'];
                    // unset($post['gt_id']);
                    // $ret_id = $this->gs->save($post, ["gs_id" => intval($id)]);
                    // if (false !== $ret_id) {
                        // $this->success("修改成功", url("manage/Goods/goodsSpecificationList", ["id" => $gt_id]));
                    // } else {
                        // $this->error("修改失败!~");
                    // }
                // } else {
                    // $this->error($data['msg']);
                // }
            // }
            // $info = $this->gs->getRow(["gs_id" => intval($id)]);
            // if (empty($info)) {
                // $this->error("没有找到该数据!~");
            // }
            // return view(
                // "goodsSpecificationEdit",
                // [
                    // "id" => $id,
                    // "info" => $info,
                // ]
            // );
        // } else {
            // $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        // }
    // }
    // /**
     // * 删除规格
     // * @author 户连超
     // * @e-mail zrkjhlc@gmail.com
     // * @date   2017-10-18
     // */
    // public function goodsSpecificationDel()
    // {
        // if ($this->request->isAjax()) {
            // /*只接收id的值*/
            // $info = $this->request->only(['id'], 'post');
            // /*验证接到的值有没有问题*/
            // $rule = array(
                // "id" => 'require',
            // );
            // $msg = array(
                // "id.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            // );
            // $data = verify($info, $rule, $msg);
            // /*验证返回如果code == 1 说明成功*/
            // if ($data['code'] === 1) {
                // /*where条件 强制转换整型*/
                // $where = ['gs_id' => intval($info['id'])];
                // $count = $this->gs->getCount($where);
                // $gs = $this->gs->del($where);
                // if (false === $gs) {
                    // return json(format('', '-1', '删除失败~!请稍候重试~!'));
                // } else {
                    // return json(format('', '1', '删除成功~!'));
                // }
            // } else {
                // return json(format('', '-1', $data['msg']));
            // }
        // } else {
            // return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        // }
    // }
    // /**
     // * 用户评论
     // * @author 户连超
     // * @e-mail zrkjhlc@gmail.com
     // * @date   2017-10-12
     // */
    // public function goodsEvaluationlist()
    // {

    // }
    /**
     * 品牌申请
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-12
     */
    public function shopBrandApplicationList($ss_id=0)
    {
        /*接收到的数据*/
        $condition = $this->request->only(["sba_name", "sba_english_name"], "get");

        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        /*当前商铺的商品*/
        if($ss_id!=0){
            $where['ss.ss_id'] = $ss_id;
            $pageParam['query']['ss.ss_id'] = $ss_id;
        }
        if (is_array($condition)) {

            /*是否设置了状态值*/
            if (isset($condition['sba_name']) && ('' !== $condition['sba_name'])) {
                $where['sba_name'] = ['like', "%" . $condition['sba_name'] . "%"];
                /*保存查询条件状态*/
                $pageParam['query']['sba_name'] = $condition['sba_name'];
            }
            /*是否接收到名称信息*/
            if (isset($condition['sba_english_name']) && ('' != $condition['sba_english_name'])) {
                /*模糊查询*/
                $where['sba_english_name'] = ['like', "%" . $condition['sba_english_name'] . "%"];
                $pageParam['query']['sba_english_name'] = $condition['sba_english_name'];
            }
        }
        $join = [
            ["gjt_seller_shop ss", "ss.ss_id = sba.ss_id"],
        ];
        $alias = "sba";
        $field = "sba.*,ss.ss_name";
        $list = $this->sba->joinGetAll($join, $alias, $where, $pageParam, $order = [], $page_size = 0, $field);
        $this->managerLog("查看品牌申请列表");
        /*返回视图*/
        return view(
            "shopBrandApplicationList",
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
     * 后台品牌审核修改
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-17
     */
    public function shopBrandApplicationEdit($id)
    {
        if (isset($id) && $id > 0) {
            $where = ["sba_id" => intval($id)];
            $join = [
                ["gjt_seller_shop ss", "ss.ss_id = sba.ss_id"],
            ];
            $alias = "sba";
            $field = "sba.*,ss.ss_name";
            $info = $this->sba->joinGetRow($join, $alias, $where, $field);

            if ($this->request->isPost()) {
                $post = $this->request->only(["sba_status","sba_reason"], "post");
                $rule = array(
                    "sba_status" => 'require|in:0,1,2,3',
                );
                $msg = array(
                    "sba_status.require" => '状态选是必须要选择的噢!~',
                    "sba_status.in" => '状态选择有误噢!~',
                );
                $data = verify($post, $rule, $msg);
//                if ($info['sba_status'] == 2 || $info['sba_status'] == 0) {
//                    $this->error("该品牌已经审核过了,无需重新审核!~");
//                    exit();
//                }

                if($post['sba_status'] == 0 && $post['sba_reason'] == ""){
                    $this->error("请填写审核未通过原因！~");
                    exit();
                }

                if ($data['code'] === 1) {
                    if($post['sba_status'] == 3){
                        $this->mgb->del(['sba_id'=>$id]);
                    }
                    if ($post['sba_status'] == 2) {
                        $save['sba_id'] = $id;
                        $save['mgb_name'] = $info['sba_name'];
                        $save['mgb_english_name'] = $info['sba_english_name'];
                        $save['mgb_logo_path'] = $info['sba_logo_path'];
                        $save['mgb_official_website'] = $info['sba_official_website'];
                        $save['mgb_desc'] = $info['sba_desc'];
                        $save['mgb_add_time'] = $info['sba_add_time'];
                        $id = $this->mgb->save($save);
                    }
                    $ret = $this->sba->save($post, $where);

                    if (false !== $ret && $id > 0) {
                        $this->managerLog("修改后台品牌审核状态,品牌id为:".$id);
                        $this->success("修改成功!~", url("manage/Goods/shopBrandApplicationList",array("ss_id"=>$info['ss_id'])));
                    } else {
                        $this->error("修改失败!~");
                    }
                } else {
                    $this->error($data['msg']);
                }
                exit();
            }
            return view(
                "shopBrandApplicationEdit",
                [
                    "info" => $info,
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }
    /**
     * 商品上架申请
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-12
     */
    public function goodsShowApplyList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["g_name", "g_goods_verify"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where['g.g_goods_verify'] = ["neq", '1'];
        if (is_array($condition)) {
            /*是否设置了状态值*/
            if (isset($condition['g_goods_verify']) && ('' !== $condition['g_goods_verify'])) {
                $where['g.g_goods_verify'] = $condition['g_goods_verify'];
                /*保存查询条件状态*/
                $pageParam['query']['g.g_goods_verify'] = $condition['g_goods_verify'];
            }
            /*是否接收到名称信息*/
            if (isset($condition['g_name']) && ('' != $condition['g_name'])) {
                /*模糊查询*/
                $where['g.g_name'] = ['like', "%" . $condition['g_name'] . "%"];
                $pageParam['query']['g.g_name'] = $condition['g_name'];
            }
        }
        // dump($where);
        $join = [
            ["gjt_seller_shop ss", "ss.ss_id = g.ss_id"],
            ["gjt_manage_goods_category mgc", "mgc.mgc_id = g.mgc_id"],
        ];
        $alias = "g";
        $field = "g.g_id, g.g_name, g.g_inventory, g.g_original_price, g.is_show, g.g_goods_verify, g.g_sort, g.g_show_img_path, ss.ss_name, mgc.mgc_name";
        $list = $this->g->joinGetAll($join, $alias, $where, $pageParam, [], 0, $field);
        $this->managerLog("查看商品申请列表!");
        return view(
            "goodsShowApplyList",
            [
                "list" => $list['data'], /*查询结果*/
                "page" => $list['page'], /*分页和html代码*/
                "lastPage" => $list['lastPage'], /*总页数*/
                "currentPage" => $list['currentPage'], /*当前页码*/
                "total" => $list['total'], /*总条数*/
                "listRows" => $list['listRows'], /*每页显示条数*/
            ]
        );
    }
}
