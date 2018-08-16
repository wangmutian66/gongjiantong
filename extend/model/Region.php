<?php
namespace model;

use model\Base as Base;
/**
 * 地区
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class Region extends Base
{
	protected $table = 'gjt_region';
    protected $pk = 'r_id';
}