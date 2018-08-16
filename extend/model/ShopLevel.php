<?php
namespace model;

use model\Base as Base;
/**
 * 店铺消费等级
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class ShopLevel extends Base
{
	protected $table = 'gjt_shop_level';
    protected $pk = 'sl_id';
}