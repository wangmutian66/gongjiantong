<?php
namespace model;

use model\Base as Base;
/**
 * 平台配置表
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class ManageBackgroundConfig extends Base
{
	protected $table = 'gjt_manage_background_config';
    protected $pk = 'mbc_id';
}