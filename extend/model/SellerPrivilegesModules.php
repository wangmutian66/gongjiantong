<?php
namespace model;

use model\Base as Base;
/**
 * 商户权限模块
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class SellerPrivilegesModules extends Base
{
	protected $table = 'gjt_seller_privileges_modules';
    protected $pk = 'spm_id';
}