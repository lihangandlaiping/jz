<?php namespace app\accounts\model;

use My\MasterModel;

class Salesrecord extends MasterModel
{
    function __construct()
    {
        parent::__construct('sales_record');
    }
        /**
     * 获取数据条数
     * @param $where 条件
     * @param $group 分组
     * @param array $join 二维数组
     */
    function getCount($where=null,$group=null,$join=array())
    {
        return parent::getCount($where,$group,$join);
    }

    /**
     * 获取多条数据
     * @param null $where
     * @param null $order
     * @param null $group
     * @param array $join
     */
    public function getListData($where=null,$order=null,$group=null,$join=array(),$limit='')
    {
        return parent::getListData($where,$order,$group,$join,$limit);
    }

    /**
     * 获取一条数据
     * @param null $where
     * @param null $order
     * @param null $group
     * @param array $join
     * @return mixed
     */
    function getOne($where=null,$order=null,$group=null,$join=array())
    {
       return parent::getOne($where,$order,$group,$join);
    }
    /**
     * 插入
     * @param $data
     */
    function insertData($data)
    {
        return parent::insertData($data);
    }

    /**
     * 更新
     * @param null $where
     * @param $data
     */
    function updateData($data,$where=null)
    {
       return parent::updateData($data,$where);
    }

    /**
     * 删除
     * @param null $where
     */
    function deleteData($where)
    {
        return parent::deleteData($where);
    }
}

?>