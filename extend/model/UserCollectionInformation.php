<?php
namespace model;

use model\Base as Base;
/**
 * 用户证书
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class UserCollectionInformation extends Base
{
	protected $table = 'gjt_user_collection_information';
    protected $pk = 'uci_id';
}