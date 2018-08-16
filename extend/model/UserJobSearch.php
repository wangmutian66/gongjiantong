<?php
namespace model;

use model\Base as Base;
/**
 * 用户投递记录
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class UserJobSearch extends Base
{
	protected $table = 'gjt_user_job_search';
    protected $pk = 'ujs_id';
}