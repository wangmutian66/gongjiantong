<?php
namespace model;

use model\Base as Base;
/**
 * 商品轮播
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class GoodsPicture extends Base
{
	protected $table = 'gjt_goods_picture';
    protected $pk = 'gp_id';
}