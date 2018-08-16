<?php
namespace model;

use model\Base as Base;
/**
 * 用户接收消息
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class UserReceiveMessage extends Base
{
	protected $table = 'gjt_user_receive_message';
    protected $pk = 'urm_id';
}