<?php
namespace model;

use model\Base as Base;
/**
 * 商品和规格关联
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class GoodsAndSpecifications extends Base
{
	protected $table = 'gjt_goods_and_specifications';
    protected $pk = 'gas_id';
}