<?php
namespace model;

use model\Base as Base;
/**
 * 店铺文章
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class ShopArtical extends Base
{
	protected $table = 'gjt_shop_artical';
    protected $pk = 'sa_id';
}