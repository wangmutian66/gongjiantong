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
class GoodsType extends Base
{
	protected $table = 'gjt_goods_type';
    protected $pk = 'gt_id';
}