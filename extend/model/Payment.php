<?php
namespace model;

use model\Base as Base;
/**
 * 支付方式
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class Payment extends Base
{
	protected $table = 'gjt_payment';
    protected $pk = 'p_id';
}