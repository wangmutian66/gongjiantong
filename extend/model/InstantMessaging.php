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
class InstantMessaging extends Base
{
	protected $table = 'gjt_instant_messaging';
	protected $pk = "im_id";
}