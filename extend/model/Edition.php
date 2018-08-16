<?php
namespace model;

use model\Base as Base;
/**
 * 商品
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class Edition extends Base
{
	protected $table = 'gjt_edition';
    protected $pk = 'ed_id';
}