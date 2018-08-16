<?php
namespace model;

use model\Base as Base;
/**
 * Artical 文章
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class UmengMessage extends Base
{
	protected $table = 'gjt_umeng_message';
    protected $pk = 'um_id';
}