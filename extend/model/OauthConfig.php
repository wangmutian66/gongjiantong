<?php
namespace model;

use model\Base as Base;
/**
 * 授权
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class OauthConfig extends Base
{
	protected $table = 'gjt_oauth_config';
    protected $pk = 'oc_id';
}