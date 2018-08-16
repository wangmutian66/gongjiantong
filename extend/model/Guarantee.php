<?php
namespace model;

use model\Base as Base;
/**
 * 商户店铺
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class Guarantee extends Base
{
	protected $table = 'gjt_shop_guarantee';
    protected $pk = 'gu_id';
}