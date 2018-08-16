<?php
namespace model;

use model\Base as Base;
/**
 * 招标和规范订单
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class TenderSpecificationOrder extends Base
{
	protected $table = 'gjt_tender_specification_order';
    protected $pk = 'tso_id';
}