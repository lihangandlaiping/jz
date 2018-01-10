<?php
/**
 * Purchasehistory
 * User: qls
 */
namespace app\accounts\controller;

use Home\HomeController;
use My\MasterModel;
use think\Request;

class PurchasehistoryHome extends HomeController
{
    protected $model_name = 'purchase_history';

    function __construct()
    {
        parent::__construct();
        config('parent_temple', '');
    }

    /**
     * 添加批销信息
     * @return \think\response\View
     */
    function addPurchaseHistory()
    {
        if (Request::instance()->isPost()) {
            $data = input('post.');
            if (empty($data['data'])) $this->error('缺少必要参数');
            foreach ($data['data'] as $value) {
                if(isset($value['w_time_format']))unset($value['w_time_format']);
                if($value['unit_price'] && $value['unit_amount'])$value['total_price']=$value['unit_price']*$value['unit_amount'];
                if (!is_numeric($value['w_time'])) $value['w_time'] = strtotime($value['w_time']);
                if (empty($value['id'])) {
                    $value['member_id']=$this->member_info['id'];
                    $value['add_time'] = $value['update_time'] = $value['record_time'] = time();
                    MasterModel::inIt('purchase_history')->insertData($value);
                } else {
                    $value['update_time'] = time();
                    MasterModel::inIt('purchase_history')->updateData($value, ['id' => $value['id'],'member_id'=>$this->member_info['id'], 'status' => '1']);
                }
            }
            $this->success('批发数据处理成功');
        } else {
            $purchase_history_num = MasterModel::inIt('purchase_history')->getCount(['member_id' => $this->member_info['id'], 'w_time' => ['>', strtotime(date('Y-m-d'))]]);
            $purchase_history_list = MasterModel::inIt('purchase_history')->field('id,wholesaler_id,w_time,unit_price,unit_amount,wholesaler_sn,member_id,remark,total_price')->getListData(['member_id' => $this->member_info['id'], 'status' => '1'], 'id desc');
            $wholesaler_list = MasterModel::inIt('wholesaler')->field('id,name')->getListData(['member_id' => $this->member_info['id']], 'id desc');
            if (empty($wholesaler_list)) return $this->error('请添加批发商信息');
            if (empty($purchase_history_list)) {
                $purchase_history_list = [['wholesaler_sn' => date('Ymd') . sprintf("%0{$this->member_info['id']}s", 3) . '001', 'w_time' => date('Y-m-d'), 'wholesaler_id' => $wholesaler_list[0]['id'], 'unit_price' => '']];
                $purchase_history_num = 1;
            }
            return view('add_purchase_history', ['purchase_history_list' => $purchase_history_list, 'purchase_history_num' => $purchase_history_num, 'wholesaler_list' => $wholesaler_list, 'member_id' => $this->member_info['id']]);
        }
    }

    /**
     * 逻辑删除批销信息
     */
    function delSales(){
        $id=input('id','');
        if(empty($id))$this->error('缺少必要参数');
        $ret= MasterModel::inIt('purchase_history')->updateData(['status'=>'3','is_del'=>'2'],['id'=>$id,'member_id'=>$this->member_info['id']]);
        if($ret===false){
            $this->error('删除失败');
        }else{
            $this->success('删除成功',url('addPurchaseHistory'));
        }
    }

}