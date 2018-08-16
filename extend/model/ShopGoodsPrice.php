<?php
namespace model;

use model\Base as Base;
/**
 * 工作经历
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class ShopGoodsPrice extends Base
{
	protected $table = 'gjt_shop_goods_price';
    protected $pk = 'goods_id';
}