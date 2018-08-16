<?php
namespace model;

use model\Base as Base;
/**
 * 商户操作日志
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class SellerManagerLog extends Base
{
	protected $table = 'gjt_seller_manager_log';
    protected $pk = 'sml_id';
}