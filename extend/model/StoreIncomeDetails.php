<?php
namespace model;

use model\Base as Base;
/**
 * 商户收入明细
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class StoreIncomeDetails extends Base
{
	protected $table = 'gjt_store_income_details';
    protected $pk = 'sid_id';
}