<?php
namespace model;

use model\Base as Base;
/**
 * Artical 店铺活动
 * @author wangmutian
 * @e-mail zrkjhlc@gmail.com
 * @date   2018-04-06
 */
class Invoice extends Base
{
	protected $table = 'gjt_invoice';
    protected $pk = 'i_id';
}