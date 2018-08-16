<?php
namespace model;

use model\Base as Base;
/**
 * 平台商品分类
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class ManageGoodsCategory extends Base
{
	protected $table = 'gjt_manage_goods_category';
    protected $pk = 'mgc_id';
}