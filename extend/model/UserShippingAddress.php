<?php
namespace model;

use model\Base as Base;
/**
 * 用户收货地址
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class UserShippingAddress extends Base
{
	protected $table = 'gjt_user_shipping_address';
    protected $pk = 'usa_id';
}