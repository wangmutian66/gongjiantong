<?php
namespace model;

use model\Base as Base;
/**
 * 平台管理员分组
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class ManagersPrivilegesGroup extends Base
{
	protected $table = 'gjt_managers_privileges_group';
    protected $pk = 'mpg_id';
}