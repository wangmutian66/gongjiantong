<?php
namespace model;

use model\Base as Base;
/**
 * 店铺品牌申请
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class ShopBrandApplication extends Base
{
	protected $table = 'gjt_shop_brand_application';
    protected $pk = 'sba_id';
}