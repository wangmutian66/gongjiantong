<?php
namespace app\manage\controller;

use model\Artical as a;
use model\ManageBackgroundConfig as mbc;
use model\UserSendMessage as usm;
use model\UmengMessage as um;
use model\ArticalCategory as ac;
/**
 * 平台首页
 * 包含数据分析
 * 公共赋值等
 */
class System extends Base
{
    protected $a;
    protected $mbc;
    protected $usm;
    protected $production_mode;
    protected $IOSAppKey;
    protected $IOSAppSecret;
    protected $AndroidAppKey;
    protected $AndroidAppSecret;
    protected $um;
    protected $ac;
    public function __construct()
    {
        parent::__construct();
        $this->a = new a();
        $this->mbc = new mbc();
        $this->usm = new usm();
        $this->um = new um();
        $this->ac = new ac();
        $this->production_mode = config('plugin')['umeng']['production_mode'];
        $this->IOSAppKey = config('plugin')['umeng']['IOSAppKey'];
        $this->IOSAppSecret = config('plugin')['umeng']['IOSAppSecret'];
        $this->AndroidAppKey = config('plugin')['umeng']['AndroidAppKey'];
        $this->AndroidAppSecret = config('plugin')['umeng']['AndroidAppSecret'];
    }
    /**
     * 系统设置
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-26
     */
    public function system()
    {
        $info = getTableConfig("mbc_");
        if ($this->request->isPost()) {
            $post = $this->request->only(["platform", "other", "article"]);
            $rule = array(
                "platform" => 'require|array',
                "other" => 'require|array',
                "article" => 'require|array',
            );
            $msg = array(
                "platform.require" => '缺少必填项!~',
                "platform.array" => '必填项格式不正确!~',
                "other.require" => '缺少必填项!~',
                "other.array" => '必填项格式不正确!~',
                "article.require" => '缺少必填项!~',
                "article.array" => '必填项格式不正确!~',
            );
            $data = verify($post, $rule, $msg);
            if ($data['code'] === 1) {
            	if ($_FILES['logo_path']['error'] != 4) {
	            	$path = "logo/" . date("y_m_d", time());
	                /*上传头像*/
	                $file_info = uploadImage($path);
	                if ($file_info['code'] == 200) {
	                    $post['platform']['logo_path'] = $file_info['pic_cover'];
		            } else {
		            	$this->error($file_info['msg']);
		            }
            	} else {
            		if ($info['platform']['logo_path']) {
            			$post['platform']["logo_path"] = $info['platform']['logo_path'];
            		}
            	}
            	setTableConfig($post,"mbc_");
                $this->managerLog("修改系统设置!");
            	$this->success("设置成功!~");
            } else {
                $this->error($data['msg']);
            }
            exit();
        }
        $list = $this->a->getList([], "a_id,a_title");
        return view(
            "system",
            [
                "list" => $list,
                "data" => $info,
            ]
        );
    }
    /**
     * 获取文章列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-28
     */
    public function getArticle()
    {
        if ($this->request->isAjax()) {
            /*直接收id*/
            $post = $this->request->only(["value"], "post");
            $rule = array(
                "value" => 'require',
            );
            $msg = array(
                "value.require" => '程序员都累吐血了也没有接到传输的数据噢!~',
            );
            $data = verify($post, $rule, $msg);
            if ($data['code'] == 1) {
                $info = $this->a->getList(["a_title" => ["like", "%" . $post['value'] . "%"]], "a_id,a_title");
                if (isset($info) && !empty($info)) {
                    return json(format($info));
                } else {
                    return json(format('', '-1', "该文章不存在!~"));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        } else {
            return json(format('', '-1', "程序员都累吐血了也没有接到传输的数据噢!~"));
        }
    }
    /**
     * 特讯新闻列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-11-03
     */
    public function systemMessageList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["usm_send_object", "usm_type"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否设置了状态值*/
            if (isset($condition['usm_send_object']) && ('' !== $condition['usm_send_object'])) {
                $where['usm.usm_send_object'] = $condition['usm_send_object'];
                /*保存查询条件状态*/
                $pageParam['query']['usm.usm_send_object'] = $condition['usm_send_object'];
            }
            /*是否接收到名称信息*/
            if (isset($condition['usm_type']) && ('' != $condition['usm_type'])) {
                /*模糊查询*/
                $where['usm.usm_type'] = $condition['usm_type'];
                $pageParam['query']['usm.usm_type'] = $condition['usm_type'];
            }
        }
        $join = [
            ['gjt_artical a', "a.a_id = usm.a_id"],
            ['gjt_managers m', "m.m_id = usm.manager_id"],
        ];
        $field = "usm.*,a.a_title,m.m_name";
        $list = $this->usm->joinGetAll($join,  "usm", $where, $pageParam, [], 0, $field);
        $this->managerLog("浏览特讯新闻列表!");
        return view(
            "systemMessageList",
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
    public function systemMessageAdd()
    {
        if ($this->request->isPost()) {
            $post = $this->request->only(["a_id"],"post");
            $rule = array(
                "a_id" => 'require|number',
            );
            $msg = array(
                "a_id.require" => '文章必须选择!~',
                "a_id.number" => '文章选择有误!~',
            );
            $data = verify($post, $rule, $msg);
            if ($data['code'] === 1) {
                $post['usm_type'] = '1';
                $post['usm_send_object'] = '1';
                $post['manager_id'] = $this->m_id;
                $post['usm_add_time'] = time();
                $id = $this->usm->save($post);
                if ($id > 0) {
                    $this->managerLog("添加特讯新闻!id为:".$post['a_id']);
                    $this->success("特讯新闻发送成功",url("manage/System/systemMessageList"));
                } else {
                    $this->error("特讯新闻发送失败");
                }
            }
            exit();
        }
        $list = $this->a->getList([], "a_id,a_title");
        return view(
            "systemMessageAdd",
            [
                "list" => $list,
            ]
        );
    }
    public function systemMessageDel()
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
                $where['usm_id'] = intval($info['id']);
                
                /*删除mpg表数据*/
                $usm = $this->usm->del($where);
                if (false === $usm) {
                    return json(format('', '-1', '删除失败~!请稍候重试~!'));
                } else {
                    $this->managerLog("删除特讯新闻,id为:".$info['id']);
                    return json(format('', '1', '删除成功~!'));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }
    /**
     * 推送列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-11-27
     */
    public function broadcastList()
    {
        /*接收到的数据*/
        $condition = $this->request->only(["um_sent_equipment", "um_title"], "get");
        /*保存查询信息*/
        $pageParam = [];
        /*查询套件*/
        $where = [];
        if (is_array($condition)) {
            /*是否设置了状态值*/
            if (isset($condition['um_sent_equipment']) && ('' !== $condition['um_sent_equipment'])) {
                $where['um_sent_equipment'] = $condition['um_sent_equipment'];
                /*保存查询条件状态*/
                $pageParam['query']['um_sent_equipment'] = $condition['um_sent_equipment'];
            }
            /*是否接收到名称信息*/
            if (isset($condition['um_title']) && ('' != $condition['um_title'])) {
                /*模糊查询*/
                $where['um_title'] = ['like', "%" . $condition['um_title'] . "%"];
                $pageParam['query']['um_title'] = $condition['um_title'];
            }
        }
        $list = $this->um->getAll($where, $pageParam);
        // 14天以前的删除
        $end_time = date('Y-m-d H:i:s',date2time(date('Y-m-d')) - 60 * 60 * 24 * 14);
        $this->um->del(['um_add_time' => ["lt",$end_time]]);
        $this->managerLog("浏览友盟推送列表列表!");
        return view(
            "broadcastList",
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
     * 删除推送
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-11-27
     */
    public function delBroadcast()
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
                $where['um_id'] = intval($info['id']);
                
                $ret = $this->um->del($where);
                if (false === $ret) {
                    return json(format('', '-1', '删除失败~!请稍候重试~!'));
                } else {
                    $this->managerLog("删除推送,推送信息id为:".$info['id']);
                    return json(format('', '1', '删除成功~!'));
                }
            } else {
                return json(format('', '-1', $data['msg']));
            }
        }
    }
    /**
     * IOS消息推送
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-11-27
     */
    public function sendBroadcast()
    {
        if ($this->request->isPost()) {
            Vendor('umeng.Umeng');
            $post = $this->request->only(['um_title', 'um_content', 'um_url', 'um_sent_equipment'],"post");

            /*验证接到的值有没有问题*/
            $rule = array(
                "um_title" => 'require|max:50',
                "um_content" => 'require',
                "um_url" => 'url',
                "um_sent_equipment" => 'require|in:0,1',
            );
            $msg = array(
                "um_title.require" => '标题必须填写!~',
                "um_title.max" => '标题长度不能超过50个字符!~',
                "um_content.require" => '内容必须填写!~',
                "um_url.url" => 'URL地址填写不正确,要以http://或者https://开头!~',
                "um_sent_equipment.require" => '发送设备必须选择!~',
                "um_sent_equipment.in" => '发送设备选择有误!~',
            );
            $data = verify($post, $rule, $msg);
            if ($post['um_sent_equipment'] == '1') {
                $um = new \Umeng($this->IOSAppKey,$this->IOSAppSecret,$this->production_mode);
                $alert['title'] = $post['um_title'];
                $alert['body'] = $post['um_content'];
                $url = 'new_'.$post['um_url'];
                $info = $um->sendIOSBroadcast($alert, $url);
            } else{
                Vendor('umeng.Umeng');
                $um = new \Umeng($this->AndroidAppKey,$this->AndroidAppSecret,$this->production_mode);
                $ticker = "工建通-工程信息全都有";
                $title = $post['um_title'];
                $text = $post['um_content'];
                $url ='new_'.$post['um_url'];

                $info = $um->sendAndroidBroadcast($ticker, $title, $text, $url);
            }

            if ($info['ret'] == 'FAIL') {
                $this->error($info['msg']);
            } else {
                $post['um_url']='new_'.$post['um_url'];
                $post['um_add_time'] = time();
                $this->um->save($post);
                $this->success($info['msg'],url('manage/System/broadcastList'));
            }
            exit();
        }

        $ac_id = $this->ac->getOne(["ac_keywords"=>"xinwen"],"ac_id");
        $news = $this->a->getList(["ac_id"=>$ac_id], "a_id,a_title");

        return view(
            "sendBroadcast",
            [
                "news" => $news, /*查询条件*/
            
            ]
        );
    }
}
