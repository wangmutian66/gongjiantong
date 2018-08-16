<?php
namespace model;

use model\Base as Base;
/**
 * 平台管理员日志
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class ManagersLog extends Base
{
	protected $table = 'gjt_manager_log';
    protected $pk = 'ml_id';
}