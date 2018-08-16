<?php
namespace model;

use model\Base as Base;
/**
 * 商家商品规格
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class ShopGoodsSpecification extends Base
{
	protected $table = 'gjt_shop_goods_specification';
    protected $pk = 'sgs_id';
}