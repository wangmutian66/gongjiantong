<?php
namespace model;

use model\Base as Base;
/**
 * 商品活动
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class GoodsActivity extends Base
{
	protected $table = 'gjt_goods_activity';
    protected $pk = 'ga_id';
}