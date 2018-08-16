<?php
namespace model;

use model\Base as Base;
/**
 * 用户历史浏览记录
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class UserBrowsingHistory extends Base
{
	protected $table = 'gjt_user_browsing_history';
    protected $pk = 'ubh_id';
}