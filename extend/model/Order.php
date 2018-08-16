<?php
namespace model;

use model\Base as Base;
/**
 * 订单
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class Order extends Base
{
	protected $table = 'gjt_order';
    protected $pk = 'o_id';
}