<?php
namespace model;

use model\Base as Base;
/**
 * 第三方插件表
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class PartyPlugin extends Base
{
	protected $table = 'gjt_party_plugin';
    protected $pk = 'pp_id';
}