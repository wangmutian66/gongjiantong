<?php
namespace model;

use model\Base as Base;
/**
 * 商品评价表
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class GoodsEvaluation extends Base
{
	protected $table = 'gjt_goods_evaluation';
    protected $pk = 'ge_id';
}