<?php
namespace model;

use model\Base as Base;
/**
 * 订单交货
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class OrderDelivery extends Base
{
	protected $table = 'gjt_order_delivery';
    protected $pk = 'od_id';
}