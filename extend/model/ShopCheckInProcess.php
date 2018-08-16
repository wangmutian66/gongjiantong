<?php
namespace model;

use model\Base as Base;
/**
 * 招投标信息
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-27
 */
class ShopCheckInProcess extends Base
{
	protected $table = 'gjt_shop_check_in_process';
    protected $pk = 'cip_id';
}