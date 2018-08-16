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
class Artical extends Base
{
	protected $table = 'gjt_artical';
    protected $pk = 'a_id';
}