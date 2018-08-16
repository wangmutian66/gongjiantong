<?php
namespace model;

use model\Base as Base;
/**
 * 招聘信息	
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class RecruitmentInfo extends Base
{
	protected $table = 'gjt_recruitment_info';
    protected $pk = 'ri_id';
}