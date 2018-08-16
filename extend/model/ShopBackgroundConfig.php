<?php
namespace model;

use model\Base as Base;
/**
 * 店铺后台设置
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class ShopBackgroundConfig extends Base
{
	protected $table = 'gjt_shop_background_config';
    protected $pk = 'sbc_id';
}