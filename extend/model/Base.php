<?php
namespace model;

use think\config;
use think\Db;
use think\Model;

/**
 * model 基类
 * 增删改查等操作
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
class Base extends Model
{
    protected $table;
    protected $page_size;
    protected $pk;
    protected $order;
    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->page_size = !empty(Config::get('paginate.list_rows')) ? Config::get('paginate.list_rows') : 20;
        $this->order = [$this->pk => 'asc'];
    }
    /**
     * [save 添加/更新]
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-13
     * @param  array             $data  [要添加的数组]
     * @param  array             $where [where条件(有:更新,无:添加)]
     * @return int|bool                 [添加返回id , 新增返回影响行数]
     */
    public function save($data = [], $where = [], $sequence = null)
    {
        if (!empty($data)) {
            $data = deepAddslashes($data);
            if (!empty($where)) {
                return Db::table($this->table)
                    ->where($where)
                    ->update($data);
            } else {
                return Db::table($this->table)
                    ->insertGetId($data);
            }
        } else {
            return false;
        }
    }
    /**
     * [getAll description]
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-14
     * @param  array             $where  where条件
     * @param  array             $order  排序
     * @param  string            $field  要查询的字段
     * @param  array             $pageParam [保存分页搜索条件]
     * @param  integer           $page_size [每页显示条数]
     * @return [type]                    [description]
     */
    public function getAll($where = [], $pageParam = [], $order = [], $page_size = 0, $field = "*",$group="")
    {
        if (empty($order)) {
            $order = $this->order;
        }
        if ($page_size === 0) {
            $page_size = $this->page_size;
        }
        $list = Db::table($this->table)
            ->field($field)
            ->where($where)
            ->group($group)
            ->order($order)
            ->paginate($page_size, false, $pageParam);
                
        return [
            /*数据*/
            'data' => deepStripslashes($list->items()),
            /*分页*/
            'page' => $list->render(),
            /*总页数*/
            'lastPage' => $list->lastPage(),
            /*当前页码*/
            'currentPage' => $list->currentPage(),
            /*总条数*/
            'total' => $list->total(),
            /*每页显示条数*/
            'listRows' => $list->listRows(),
        ];
    }
    /**
     * 联合查询多条       带分页
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-14
     * @param  array             $join      [联合查询]
     * @param  string            $alias     [别名]
     * @param  array             $where     [where条件]
     * @param  array             $pageParam [保存分页搜索条件]
     * @param  array             $order     [排序]
     * @param  integer           $page_size [每页显示条数]
     * @param  string            $field     [要查询的字段]
     * @return array                        [查询出来的结果]
     */
    public function joinGetAll($join = [], $alias = "", $where = [], $pageParam = [], $order = [], $page_size = 0, $field = "*", $group = "")
    {
        if ($page_size === 0) {
            $page_size = $this->page_size;
        }
        if (empty($alias)) {
            $alias = $this->table;
        }
        if (empty($order)) {
            $order = [$alias . "." . $this->pk => 'desc'];
        }
        if ($group) {
            $list = Db::table($this->table)
                ->alias($alias)
                ->field($field)
                ->join($join)
                ->where($where)
                ->group($group)
                ->order($order)
                ->paginate($page_size, false, $pageParam);
        } else {
            $list = Db::table($this->table)
                ->alias($alias)
                ->field($field)
                ->join($join)
                ->where($where)
                ->order($order)
                ->paginate($page_size, false, $pageParam);
        }
        return [
            /*数据*/
            'data' => deepStripslashes($list->items()),
            /*分页*/
            'page' => $list->render(),
            /*总页数*/
            'lastPage' => $list->lastPage(),
            /*当前页码*/
            'currentPage' => $list->currentPage(),
            /*总条数*/
            'total' => $list->total(),
            /*每页显示条数*/
            'listRows' => $list->listRows(),
        ];
    }
    /**
     * [getList 获取多条不带参数]
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-13
     * @param  [type]            $where [where条件]
     * @param  string            $field [要查询的字段]
     * @param  array             $order [排序]
     * @return array             查询出来的数组
     */
    public function getList($where = [], $field = "*", $order = [], $offset = 0, $length = null)
    {
        if (empty($order)) {
            $order = $this->order;
        }
        if (empty($field)) {
            $field = "*";
        }
        $list = Db::table($this->table)
            ->field($field)
            ->where($where)
            ->order($order)
            ->limit($offset, $length)
            ->select();
//        echo db()->getLastSql(); die();
        return deepStripslashes($list);
    }
    /**
     * 联合查询多条  不带分页
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-14
     * @param  array             $join      [联合查询]
     * @param  string            $alias     [别名]
     * @param  array             $where     [where条件]
     * @param  array             $order     [排序]
     * @param  string            $field     [要查询的字段]
     * @return array                        [查询出来的结果]
     */
    public function joinGetList($join = [], $alias = "", $where = [], $order = [], $field = "*")
    {
        if (empty($alias)) {
            $alias = $this->table;
        }
        if (empty($order)) {
            $order = [$alias . "." . $this->pk => 'desc'];
        }
        $list = Db::table($this->table)
            ->alias($alias)
            ->field($field)
            ->join($join)
            ->where($where)
            ->order($order)
            ->select();
        return deepStripslashes($list);
    }
    /**
     * [getRow 获取单条数据]
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-13
     * @param  array             $where [where条件]
     * @param  string            $field [要查询的字段]
     * @return array                    [返回一维数组]
     */
    public function getRow($where = [], $field = "*", $order = [])
    {
        if (empty($order)) {
            $order = [$this->pk => 'desc'];
        }
        $data = Db::table($this->table)
            ->where($where)
            ->field($field)
            ->order($order)
            ->find();
        return deepStripslashes($data);
    }
    /**
     * [joinGetRow description]
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-14
     * @param  array             $join      [联合查询]
     * @param  string            $alias     [别名]
     * @param  array             $where     [where条件]
     * @param  string            $field     [要查询的字段]
     * @return array                        [查询出来的结果]
     */
    public function joinGetRow($join = [], $alias = "", $where = [], $field = "*")
    {
        if (empty($alias)) {
            $alias = $this->table;
        }
        $data = Db::table($this->table)
            ->alias($alias)
            ->field($field)
            ->join($join)
            ->where($where)
            ->find();
        return deepStripslashes($data);
    }
    /**
     * [getOne 查询某个字段的值]
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-13
     * @param  array             $where [where条件]
     * @param  string            $field [要查询的字段]
     * @return string            查询出来的值
     */
    public function getOne($where = [], $field = "",$order=[])
    {
        if (!empty($where) && !empty($field)) {
            $info = Db::table($this->table)
                ->where($where)
                ->order($order)
                ->value($field);
            if (null != $info) {
                $info = deepStripslashes($info);
            }
            return $info;
        } else {
            return false;
        }
    }


    /**
     * [getOne 查询某个字段的值]
     * @author wangmutian
     * @e-mail zrkjhlc@gmail.com
     * @date   2018-04-06
     * @param  array             $where [where条件]
     * @param  string            $field [要查询的字段]
     * @return array            查询出来的值
     */
    public function getOnes($where = [], $field = "")
    {
        if (!empty($where) && !empty($field)) {
            $info = Db::table($this->table)
                ->where($where)
                ->column($field);
            if (null != $info) {
                $info = deepStripslashes($info);
            }
            return $info;
        } else {
            return false;
        }
    }



    /**
     * [del 删除某条数据]
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-13
     * @param  array             $where [where条件]
     * @return bool
     */
    public function del($where = [])
    {
        if (!empty($where)) {
            return Db::table($this->table)
                ->where($where)
                ->delete();
        } else {
            return false;
        }
    }
    /**
     * [getCount 查询数据的数量]
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-11
     * @param  [str | array]     $condition [条件]
     * @return [int]             $count     [总条数]
     */
    public function getCount($condition = [])
    {
        $count = Db::table($this->table)
            ->where($condition)
            ->count();
        return $count;
    }
    /**
     * [getSum 查询某条件下总数量]
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-11
     * @param  [str | array]     $condition [where调教]
     * @param  [sting]           $field     [字段]
     * @return [int]             $sum       [总数量]
     */
    public function getSum($condition, $field)
    {
        $sum = Db::table($this->table)
            ->where($condition)
            ->sum($field);
        if (empty($sum)) {
            return 0;
        } else {
            return $sum;
        }
    }
    /**
     * [getMax 查询数据最大值]
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-11
     * @param  [str | array]     $condition [where调教]
     * @param  [sting]           $field     [字段]
     * @return [int]             $max       [最大值]
     */
    public function getMax($condition, $field)
    {
        $max = Db::table($this->table)
            ->where($condition)
            ->max($field);
        if (empty($max)) {
            return 0;
        } else {
            return $max;
        }
    }
    /**
     * [getMax 查询数据最小值]
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-11
     * @param  [str | array]     $condition [where调教]
     * @param  [sting]           $field     [字段]
     * @return [int]             $min       [最小值]
     */
    public function getMin($condition, $field)
    {
        $min = Db::table($this->table)
            ->where($condition)
            ->min($field);
        if (empty($min)) {
            return 0;
        } else {
            return $min;
        }
    }
    /**
     * [getFirstData 查询第一条数据]
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-09-11
     * @param  [str | array]     $condition [where调教]
     * @param  [str | array]     $order     [排序方式]
     * @return [array]           $data      [第一条数据]
     */
    public function getFirstData($condition = [], $order = [])
    {
        if (empty($order)) {
            $order = $this->order;
        }
        $data = Db::table($this->table)
            ->where($condition)
            ->order($order)
            ->limit(1)
            ->select();
        if (!empty($data)) {
            return $data[0];
        } else {
            return '';
        }
    }
    /**
     * 清空表内容主键归1
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-10-28
     */
    public function truncate()
    {
        Db::execute('TRUNCATE table ' . $this->table);
    }

    /**
     * 将数字字段值减少
     * @author 王牧田
     * @e-mail zrkjhlc@gmail.com
     * @date   2018-04-26
     */
    public function setDataDec($where = [],$field="",$data=0)
    {
        $value = Db::table($this->table)
                    ->where($where)
                    ->value($field);
        
        if ($value >= $data) {
            $result = Db::table($this->table)
                ->where($where)
                ->setDec($field, $data);
            return $result;
        }else{
            return -1;
        }
//        return $value;
    }

   /**
    * 匹配字段值
    * @author 李鑫
    * @date   2018-04-26
    */
   public function setfind($where = '',$field=""){
        $where='FIND_IN_SET("'.$where.'",'.$field.')';
        $data = Db::table($this->table)
            ->where($where)
            ->field('ss_id')
            ->select();
        return $data;
   }

   /**
    * 匹配字段值
    * @author 李鑫
    * @date   2018-05-25
    */
   public function setfinds($where = '',$field=""){
        $where='FIND_IN_SET("'.$where.'",'.$field.')';
        $data = Db::table($this->table)
            ->where($where)
            ->field('g_id')
            ->select();
        return $data;
   }


    /**
     * 将数字字段值增加
     * @author 王牧田
     * @e-mail zrkjhlc@gmail.com
     * @date   2018-04-26
     */
    public function setDataInc($where = [],$field="",$data=""){
        $data = Db::table($this->table)
            ->where($where)
            ->setInc($field,$data);
        return $data;
    }



}
