<?php
namespace app\pc\Controller;

use \think\Controller;
/**
 * ���ߣ�Ԭ����
 * ʱ�䣺2017-09-12
 * ��Ҫ���ܣ�PC���̻���פ��½ , 
 */
class Base extends Controller{
	protected $request;
	public function __construct(){
		parent::__construct();
		$this->request = request();
	}

 	/**
     * ���ߣ�Ԭ����
     * ʱ�䣺2017-09-27
     * ���ܣ�����������������
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
            return json(format('', -1, "����Ա������Ѫ��,Ҳû���յ��κε�����!~"));
        }
    }
   
}
?>