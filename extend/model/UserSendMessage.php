<?php
namespace model;

use model\Base as Base;
/**
 * 用户发送信息
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class UserSendMessage extends Base
{
	protected $table = 'gjt_user_send_message';
    protected $pk = 'usm_id';
}