<?php
namespace app\pc\Controller;

use \think\Controller;
/**
 * 作者：袁中旭
 * 时间：2017-09-12
 * 主要功能：PC端商户入驻登陆 , 
 */
class Base extends Controller{
	protected $request;
	public function __construct(){
		parent::__construct();
		$this->request = request();
	}

 	/**
     * 作者：袁中旭
     * 时间：2017-09-27
     * 功能：三级联动公共基类
     */
    public function getRegion()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->only(['id'], 'post');
            if ($post['id'] > 0) {
                $where['r_parent_id'] = intval($post['id']);
                return json(format(getRegion($where), 1, "success"));
            }
            return json(format(getRegion(), 1, "success"));
        } else {
            return json(format('', -1, "程序员都累吐血了,也没有收到任何的数据!~"));
        }
    }
   
}
?>