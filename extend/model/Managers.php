<?php
namespace model;

use model\Base as Base;
/**
 * 平台管理员
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class Managers extends Base
{
	protected $table = 'gjt_managers';
    protected $pk = 'm_id';
}