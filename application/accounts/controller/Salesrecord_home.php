<?php
/**
 * Salesrecord
 * User: qls
 */
namespace app\accounts\controller;

use Home\HomeController;
use My\MasterModel;
use think\Request;

class SalesrecordHome extends HomeController
{
    protected $model_name = 'sales_record';

    function __construct()
    {
        parent::__construct();
        config('parent_temple', '');
    }

    /**
     * 添加零售数据
     * @return \think\response\View|void
     */
    function addSalesRecord()
    {
        if (Request::instance()->isPost()) {
            $data = input('post.');
            if (empty($data['data'])) $this->error('缺少必要参数');
            foreach ($data['data'] as $value) {
                if (isset($value['status_msg'])) unset($value['status_msg']);
                if (empty($value['id'])) {
                    $value['member_id'] = $this->member_info['id'];
                    $value['add_time'] = $value['record_time'] = time();
                    MasterModel::inIt('sales_record')->insertData($value);
                } else {
                    MasterModel::inIt('sales_record')->updateData($value, ['id' => $value['id'], 'member_id' => $this->member_info['id'], 'status' => '1']);
                }
            }
            $this->success('零售数据处理成功');
        } else {
            $sales_record_num = MasterModel::inIt('sales_record')->getCount(['member_id' => $this->member_info['id'], 'add_time' => ['>', strtotime(date('Y-m-d'))]]);
            $sales_record_list = MasterModel::inIt('sales_record')->field('id,client_id,status,money_amount,sales_sn,remark')->getListData(['member_id' => $this->member_info['id'], 'status' => '1'], 'id desc');
            $client_list = MasterModel::inIt('client')->field('id,name')->getListData(['member_id' => $this->member_info['id']], 'id desc');
            if (empty($client_list)) {
                return $this->error('请添加零售客户信息');
            }
            if (empty($sales_record_list)) {
                $sales_record_list = [['sales_sn' => date('Ymd') . sprintf("%0{$this->member_info['id']}s", 3) . '001', 'client_id' => $client_list[0]['id'], 'status' => '1']];
                $sales_record_num = 1;
            }
            return view('add_sales_record', ['sales_record_num' => $sales_record_num, 'sales_record_list' => $sales_record_list, 'client_list' => $client_list, 'member_id' => $this->member_info['id']]);
        }
    }

    /**
     * 删除零售数据
     */
    function delSales()
    {
        $id = input('id', '');
        if (empty($id)) $this->error('缺少必要参数');
        $ret = MasterModel::inIt('sales_record')->deleteData(['id' => $id, 'member_id' => $this->member_info['id']]);
        if ($ret === false) {
            $this->error('删除失败');
        } else {
            $this->success('删除成功', url('addSalesRecord'));
        }
    }

    /**
     * 获取欠款数据
     * @return \think\response\View
     */
    function confirmReceipt(){
        $client_id=input('client_id','0');
        $where=['s.status'=>'2','s.statistics_id'=>['<>',''],'s.member_id'=>$this->member_info['id']];
        if($client_id)$where['s.client_id']=$client_id;
        $list=MasterModel::inIt('sales_record s')->field('s.id,s.money_amount,s.record_time,s.sales_sn,s.remark,c.name')->getListData($where,'s.id desc','',[['client c','c.id=s.client_id','left']]);
        if(Request::instance()->isAjax()){
            $this->success('获取数成功','',['list'=>$list]);
        }else{
            $client_list=MasterModel::inIt('client')->field('id,name')->getListData(['member_id'=>$this->member_info['id']]);
            $client_list[0]='全部客户';
            return view('confirm_receipt',['list'=>$list,'client_list'=>$client_list,'client_id'=>$client_id]);
        }
    }

}