<?php
namespace model;

use model\Base as Base;
/**
 * 平台品牌和商家品牌关联
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class ManageAndShopBrand extends Base
{
	protected $table = 'gjt_manage_and_shop_brand';
    protected $pk = 'masb_id';
}