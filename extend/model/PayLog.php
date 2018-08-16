<?php
namespace model;

use model\Base as Base;
/**
 * 支付日志
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class PayLog extends Base
{
	protected $table = 'gjt_pay_log';
    protected $pk = 'pl_id';
}