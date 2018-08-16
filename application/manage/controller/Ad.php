<?php
namespace app\manage\controller;

use model\ManageAd as ma;
use model\ManageAdPosition as map;
use model\SellerShop as ss;

/**
 * 轮播图,广告等
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-10-12
 */
class Ad extends Base
{
    protected $ma;
    protected $map;
    protected $ss;
    public function __construct()
    {
        parent::__construct();
        $this->ma = new ma();
        $this->map = new map();
        $this->ss = new ss();
    }
    /**
     * 广告添加
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-19
     */
    public function adAdd()
    {
        if ($this->request->isPost()) {
            $post = $this->request->only(["map_id", "ma_title", "ma_start_time", "ma_end_time", "ma_status","map_type","ma_link_type", "ma_link","ma_content","ma_ss_id"], "post");
            $rule = [
                "map_id" => "require|number",
                "ma_title" => "require|chsAlphaNum|max:30",
                "ma_start_time" => "dateFormat:Y-m-d",
                "ma_end_time" => "dateFormat:Y-m-d",
                "ma_status" => "require|in:0,1",
                "map_type" => "require",

            ];
            $msg = [
                "map_id.require" => "广告位必须选择!~",
                "map_id.number" => "广告位选择有误!~",
                "ma_title.require" => "标题必须填写!~",
                "ma_title.chsAlphaNum" => "标题只能填写中文,英文及数字!~",
                "ma_title.max" => "标题最大长度为30个字符!~",
                "ma_start_time.dateFormat" => "开始时间填写格式不正确!~",
                "ma_end_time.dateFormat" => "结束时间填写格式不正确!~",
                "ma_status.require" => "状态必须选择哦!~",
                "ma_status.in" => "状态选择有误!~",
                "map_type.require" => "请选择类型!~",

            ];
            $data = verify($post, $rule, $msg);
            if ($data['code'] === 1) {
            	$path = "ad/" . date("y_m_d", time());
                /*上传头像*/
                $file_info = uploadImage($path);
                if ($file_info['code'] == 200) {
                    $post['ma_image'] = $file_info['pic_cover'];

	            	(isset($post["ma_start_time"]) && $post["ma_start_time"] != '') ? $post["ma_start_time"] = date2time($post["ma_start_time"]) : $post["ma_start_time"] = 0;
	            	(isset($post["ma_end_time"]) && $post["ma_end_time"] != '') ? $post["ma_end_time"] = date2time($post["ma_end_time"]) : $post["ma_end_time"] = 0;
	            	// if ($post['r_id'] == '') {
	            	// 	$post['r_id'] = 1;
	            	// }
	            	$post['ma_add_time'] = $post['ma_edit_time'] = time();
	            	$post['ma_author'] = $post['ma_editor'] = $this->m_id;

	            	$id = $this->ma->save($post);
	            	if ($id > 0) {
                        $this->managerLog("添加广告,广告id为:".$id);
	            		$this->success("添加成功!~",url("manage/Ad/adList"));
	            	} else {
	            		$this->error("添加失败!~");
	            	}
	            } else {
	            	$this->error($file_info['msg']);
	            }
            } else {
            	$this->error($data['msg']);
            }
            exit();
        }
        $list = $this->map->getList();
        $sshop = $this->ss->getList(["state"=>0,"ss_approval_status"=>1]);
        return view(
            "adAdd",
            [
                "sshop"=>$sshop,
                "list" => $list,
            ]
        );
    }
    /**
     * 广告列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-19
     */
    public function adList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["ma.ma_title", "ma.ma_status"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否接收到名称信息*/
            if (isset($condition['ma.ma_title']) && ('' != $condition['ma.ma_title'])) {
                /*模糊查询*/
                $where['ma.ma_title'] = ['like', "%" . $condition['ma.ma_title'] . "%"];
                $pageParam['query']['ma.ma_title'] = $condition['ma.ma_title'];
            }
            /*是否接收到名称信息*/
            if (isset($condition['ma.ma_status']) && ('' != $condition['ma.ma_status'])) {
                /*模糊查询*/
                $where['ma.ma_status'] = $condition['ma.ma_status'];
                $pageParam['query']['ma.ma_status'] = $condition['ma.ma_status'];
            }
        }
        $join = [
            ["gjt_manage_ad_position map", "map.map_id = ma.map_id"],
            ["gjt_region r", "r.r_id = ma.r_id"],
            ["gjt_managers m", "m.m_id = ma.ma_author"],
        ];
        $alias = "ma";
        $field = "ma.*,r.r_name,m.m_name,map.map_name";
        $list = $this->ma->joinGetAll($join, $alias, $where, $pageParam, [], 0, $field);

        $this->managerLog("查看广告列表!");
        return view(
            "adList",
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
     * 广告修改
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-19
     */
    public function adEdit($id)
    {
    	if (isset($id) && $id > 0) {
    		$ma_where = ['ma_id' => intval($id)];
    		$info = $this->ma->getRow($ma_where);
	        if ($this->request->isPost()) {
	            $post = $this->request->only(["map_id", "ma_title", "ma_start_time", "ma_end_time", "ma_status","map_type","ma_link_type", "ma_link","ma_content","ma_ss_id"], "post");
	            $rule = [
	                "map_id" => "require|number",
	                "ma_title" => "require|chsAlphaNum|max:30",
	                "ma_start_time" => "dateFormat:Y-m-d",
	                "ma_end_time" => "dateFormat:Y-m-d",
	                "ma_status" => "require|in:0,1",
                    "map_type" => "require",
	            ];
	            $msg = [
	                "map_id.require" => "广告位必须选择!~",
	                "map_id.number" => "广告位选择有误!~",
	                "ma_title.require" => "标题必须填写!~",
	                "ma_title.chsAlphaNum" => "标题只能填写中文,英文及数字!~",
	                "ma_title.max" => "标题最大长度为30个字符!~",
	                "ma_start_time.dateFormat" => "开始时间填写格式不正确!~",
	                "ma_end_time.dateFormat" => "结束时间填写格式不正确!~",
	                "ma_status.require" => "状态必须选择哦!~",
	                "ma_status.in" => "状态选择有误!~",
                    "map_type.require" => "请选择类型!~",
	            ];
	            $data = verify($post, $rule, $msg);
	            if ($data['code'] === 1) {
	            	if ($_FILES['ma_image']['error'] != 4) {
		            	$path = "ad/" . date("y_m_d", time());
		                /*上传头像*/
		                $file_info = uploadImage($path);
		                if ($file_info['code'] == 200) {
		                    $post['ma_image'] = $file_info['pic_cover'];
			            } else {
			            	$this->error($file_info['msg']);
			            }
	            	}
	            	$count = $this->ma->getCount(["ma_title" => $post['ma_title']]);
	            	if ($count > 0 && $post['ma_title'] != $info['ma_title']) {
	            		$this->error("名称重复!~");
	            	}
	            	(isset($post["ma_start_time"]) && $post["ma_start_time"] != '') ? $post["ma_start_time"] = date2time($post["ma_start_time"]) : $post["ma_start_time"] = 0;
	            	(isset($post["ma_end_time"]) && $post["ma_end_time"] != '') ? $post["ma_end_time"] = date2time($post["ma_end_time"]) : $post["ma_end_time"] = 0;
	            	// if ($post['r_id'] == '') {
	            	// 	$post['r_id'] = 1;
	            	// }
	            	$post['ma_add_time'] = $post['ma_edit_time'] = time();
	            	$post['ma_author'] = $post['ma_editor'] = $this->m_id;
	            	$ret = $this->ma->save($post,$ma_where);
	            	if ($ret !== false) {
                        $this->managerLog("修改广告,修改的广告id为:".$id);
	            		$this->success("修改成功!~",url("manage/Ad/adList"));
	            	} else {
	            		$this->error("修改失败!~");
	            	}
	            } else {
	            	$this->error($data['msg']);
	            }
	            exit();
	        }
	        $list = $this->map->getList();
            $sshop = $this->ss->getList(["state"=>0,"ss_approval_status"=>1]);

	        return view(
	            "adEdit",
	            [
	                "sshop" => $sshop,
	                "list" => $list,
	                "info" => $info,
	            ]
	        );
    	} else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
    	}
    }
    /**
     * 广告删除
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-19
     */
    public function adDel()
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
                $where['ma_id'] = intval($info['id']);
                $ret = $this->ma->del($where);
                if (false === $ret) {
                    return json(format('', '-1', '删除失败~!请稍候重试~!'));
                } else {
                    $this->managerLog("删除广告,广告id为:".$info['id']);
                    return json(format('', '1', '删除成功~!'));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }
    /**
     * 广告定位列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-19
     */
    public function adPositionList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["map_name"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否接收到名称信息*/
            if (isset($condition['map_name']) && ('' != $condition['map_name'])) {
                /*模糊查询*/
                $where['map_name'] = ['like', "%" . $condition['map_name'] . "%"];
                $pageParam['query']['map_name'] = $condition['map_name'];
            }
        }
        $list = $this->map->getAll($where, $pageParam);
        $this->managerLog("查看广告定位列表!");
        return view(
            "adPositionList",
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
     * 广告定位添加
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-19
     */
    public function adPositionAdd()
    {
        if ($this->request->isPost()) {
            $post = $this->request->only(["map_name", "map_value", "map_type", "map_status","map_width","map_height"], "post");
            $rule = array(
                "map_name" => "require|chsAlphaNum",
                "map_value" => "require|alphaDash",
                "map_type" => "require|in:0,1",
                "map_status" => "require|in:0,1",
                "map_width" => "require",
                "map_height" => "require",
            );
            $msg = array(
                "map_name.require" => "广告位名称必须填写!~",
                "map_name.chsAlpha" => "广告位名称只能是中文,英文和数字!~",
                "map_value.require" => "广告位值必须填写!~",
                "map_value.alphaNum" => "广告位值只能是字母,数字及瞎下划线!~",
                "map_type.require" => "类型必须选择!~",
                "map_type.in" => "类型选择有误!~",
                "map_status.require" => "状态必须选择!~",
                "map_status.in" => "状态选择有误!~",
                "map_width.require" => "填写广告图片宽度",
                "map_height.require" => "填写广告图片高度",
            );
            $data = verify($post, $rule, $msg);
            if ($data['code'] === 1) {
                $count = $this->map->getCount(["map_name" => $post['map_name']]);
                if ($count > 0) {
                    $this->error("文件名称重复~");
                }

                $id = $this->map->save($post);
                if ($id > 0) {
                    $this->managerLog("添加广告定位,定位id:".$id);
                    $this->success("添加成功", url('manage/Ad/adPositionList'));
                } else {
                    $this->error("添加失败!~");
                }
            } else {
                $this->error($data['msg']);
            }
            exit();
        }
        return view(
            "adPositionAdd"
        );
    }
    /**
     * 广告位修改
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-19
     */
    public function adPositionEdit($id)
    {
        if (isset($id) && $id > 0) {
            $info = $this->map->getRow(["map_id" => intval($id)]);
            if ($this->request->isPost()) {
                $post = $this->request->only(["map_name", "map_value", "map_type", "map_status"], "post");
                $rule = array(
                    "map_name" => "require|chsAlphaNum",
                    "map_value" => "require|alphaDash",
                    "map_type" => "require|in:0,1",
                    "map_status" => "require|in:0,1",
                );
                $msg = array(
                    "map_name.require" => "广告位名称必须填写!~",
                    "map_name.chsAlpha" => "广告位名称只能是中文,英文和数字!~",
                    "map_value.require" => "广告位值必须填写!~",
                    "map_value.alphaNum" => "广告位值只能是字母,数字及瞎下划线!~",
                    "map_type.require" => "类型必须选择!~",
                    "map_type.in" => "类型选择有误!~",
                    "map_status.require" => "状态必须选择!~",
                    "map_status.in" => "状态选择有误!~",
                );
                $data = verify($post, $rule, $msg);
                if ($data['code'] === 1) {
                    $count = $this->map->getCount(["map_name" => $post['map_name']]);
                    if ($count > 0 && $post['map_name'] != $info['map_name']) {
                        $this->error("文件名称重复~");
                    }
                    $ret = $this->map->save($post, ["map_id" => intval($id)]);
                    if (false !== $ret) {
                        $this->managerLog("修改广告定位,定位id为:".$id);
                        $this->success("修改成功", url('manage/Ad/adPositionList'));
                    } else {
                        $this->error("修改失败!~");
                    }
                } else {
                    $this->error($data['msg']);
                }
                exit();
            }
            return view(
                "adPositionEdit",
                [
                    "info" => $info,
                ]
            );
        } else {
            $this->error("程序员都累吐血了也没有接到传输的数据噢!~");
        }
    }
    /**
     * 广告位删除
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-19
     */
    public function adPositionDel()
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
                $where['map_id'] = intval($info['id']);
                $ret = $this->map->del($where);
                if (false === $ret) {
                    return json(format('', '-1', '删除失败~!请稍候重试~!'));
                } else {
                    $this->managerLog("删除广告定位,定位id为:".$info['id']);
                    return json(format('', '1', '删除成功~!'));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }

    /**
     * 作者：wangmutian
     * 时间：2017-10-19
     * 功能：上传商品轮播
     */
    public function goodsImgupdate()
    {
        $path = "ShopGoods/" . date("y_m_d", time());
        $goods_img = uploadImage($path);
        return json_encode($goods_img);
    }




}
