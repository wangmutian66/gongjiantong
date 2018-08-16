<?php
namespace model;

use model\Base as Base;
/**
 * 店铺星级评价
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class ShopStarRating extends Base
{
	protected $table = 'gjt_shop_star_rating';
    protected $pk = 'ssr_id';
}