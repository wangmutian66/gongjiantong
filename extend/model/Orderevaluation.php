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
class Orderevaluation extends Base
{
	protected $table = 'gjt_order_evaluation';
    protected $pk = 'oe_id';
}