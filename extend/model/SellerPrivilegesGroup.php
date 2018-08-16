<?php
namespace model;

use model\Base as Base;
/**
 * 商户管理员分组
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class SellerPrivilegesGroup extends Base
{
	protected $table = 'gjt_seller_privileges_group';
    protected $pk = 'spg_id';
}