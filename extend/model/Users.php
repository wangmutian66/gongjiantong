<?php
namespace model;

use model\Base as Base;
/**
 * 用户信息
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class Users extends Base
{
	protected $table = 'gjt_users';
    protected $pk = 'u_id';
}