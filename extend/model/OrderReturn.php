<?php
namespace model;

use model\Base as Base;
/**
 * 订单操作
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class OrderReturn extends Base
{
	protected $table = 'gjt_order_return';
    protected $pk = 'or_id';
}