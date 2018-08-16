<?php
namespace model;

use model\Base as Base;
/**
 * 工程规范
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class EngineeringSpecifications extends Base
{
	protected $table = 'gjt_engineering_specifications';
    protected $pk = 'es_id';
}