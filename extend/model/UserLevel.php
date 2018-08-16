<?php
namespace model;

use model\Base as Base;
/**
 * 用户等级表
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class UserLevel extends Base
{
	protected $table = 'gjt_user_level';
    protected $pk = 'ul_id';
}