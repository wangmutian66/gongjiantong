<?php
namespace model;

use model\Base as Base;
/**
 * 物流配置
 * 增删改查等操作
 * @author 李鑫
 * @e-mail 83795552@qq.com
 * @date   2018-04-18
 */
class Pluginconfig extends Base
{
	protected $table = 'gjt_logisticsconfig';
    protected $pk = 'logc_id';
}