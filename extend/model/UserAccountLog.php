<?php
namespace model;

use model\Base as Base;
/**
 * 文章分类
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class UserAccountLog extends Base
{
	protected $table = 'gjt_user_account_log';
	protected $pk = "ual_id";
}