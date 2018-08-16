<?php
namespace model;

use model\Base as Base;
/**
 * 商户注册
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class SellerRegistered extends Base
{
	protected $table = 'gjt_seller_registered';
    protected $pk = 'sr_id';
}