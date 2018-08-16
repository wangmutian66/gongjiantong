<?php
namespace model;

use model\Base as Base;
/**
 * 商户信息
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class SellerManagers extends Base
{
	protected $table = 'gjt_seller_managers';
    protected $pk = 'sm_id';
}