<?php
namespace model;

use model\Base as Base;
/**
 * 商品规格
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class GoodsSpecification extends Base
{
	protected $table = 'gjt_goods_specification';
    protected $pk = 'gs_id';
}