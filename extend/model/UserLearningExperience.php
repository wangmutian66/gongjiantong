<?php
namespace model;

use model\Base as Base;
/**
 * 用户学习经历
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class UserLearningExperience extends Base
{
	protected $table = 'gjt_user_learning_experience';
    protected $pk = 'ule_id';
}