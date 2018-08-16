<?php
namespace model;

use model\Base as Base;
/**
 * 商品类型
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class Shoppingcart extends Base
{
	protected $table = 'gjt_shopping_cart';
    protected $pk = 'sca_id';
}