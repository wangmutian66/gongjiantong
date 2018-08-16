<?php
namespace model;

use model\Base as Base;
/**
 * 短信记录
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class SmsLog extends Base
{
	protected $table = 'gjt_sms_log';
    protected $pk = 'sl_id';
}