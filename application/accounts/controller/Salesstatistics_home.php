<?php
/**
 * Salesstatistics
 * User: qls
 */
namespace app\accounts\controller;

use Home\HomeController;
use My\MasterModel;
use think\Request;

class SalesstatisticsHome extends HomeController
{
    protected $model_name = 'sales_statistics';

    function __construct()
    {
        parent::__construct();
        config('parent_temple', '');
    }

    /**
     *
     */
    function generateSalesStatistics()
    {
        if(Request::instance()->isPost()){
            $purchase_history_id=input('purchase_history_id','');
            $sales_record_id=input('sales_record_id','');
            $date_num=input('date_num','');
            if(empty($date_num))$this->error('缺少必要参数');
            $time=time();
            if($purchase_history_id){
                MasterModel::inIt('purchase_history')->updateData(['status'=>'3','is_del'=>'1'],['id'=>['in',$purchase_history_id],'member_id'=>$this->member_info['id']]);
            }
            if($sales_record_id){
                MasterModel::inIt('sales_record')->updateData(['status'=>'4','is_del'=>'1'],['id'=>['in',$sales_record_id],'member_id'=>$this->member_info['id']]);
            }
            $sales_record_list = MasterModel::inIt('sales_record')->where(['member_id' => $this->member_info['id'], 'status' =>'1'])->column('id,money_amount');
            $arrears_list = MasterModel::inIt('sales_record')->where(['member_id' => $this->member_info['id'], 'status' =>'2'])->column('id,money_amount');
            $purchase_history_list = MasterModel::inIt('purchase_history')->where(['member_id' => $this->member_info['id'], 'status' => '1'])->column('id,total_price');
            $sales_record_ids=array_merge(array_keys($sales_record_list),array_keys($arrears_list));
            $purchase_history_ids=array_keys($purchase_history_list);
            $sales_record_money=array_sum($sales_record_list);
            $arrears_money=array_sum($arrears_list);
            $purchase_history_money=array_sum($purchase_history_list);
            $date_time=strtotime($date_num);
            $date_num=explode('-',$date_num);
            $statistics_info=['add_time'=>$time,'wholesale_money'=>$purchase_history_money,'retail_money'=>$sales_record_money+$arrears_money,'debt_money'=>$arrears_money,'returned_money'=>0,'residue_money'=>$arrears_money,'member_id'=>$this->member_info['id'],'month_num'=>$date_num[0].$date_num[1],'years_num'=>$date_num[0],'date_num'=>$date_num[0].$date_num[1].$date_num[2],'date_time'=>$date_time];
            $statistics_id=MasterModel::inIt('sales_statistics')->insertData($statistics_info);
            MasterModel::inIt('purchase_history')->updateData(['status'=>'2','record_time'=>$time,'statistics_id'=>$statistics_id],['id'=>['in',$purchase_history_ids],'member_id'=>$this->member_info['id']]);
            MasterModel::inIt('sales_record')->updateData(['record_time'=>$time,'statistics_id'=>$statistics_id],['id'=>['in',$sales_record_ids],'member_id'=>$this->member_info['id']]);
            $this->success('生成账单信息成功',url('index'));
        }else{
            $sales_record_list = MasterModel::inIt('sales_record s')->field('s.id,s.client_id,s.money_amount,s.sales_sn,c.name,s.status,s.is_del')->getListData(['s.member_id' => $this->member_info['id'], 's.status' => ['in','1,2'],'statistics_id'=>''], 's.id desc','',[['client c','c.id=s.client_id','left']]);
            if(empty($sales_record_list))return $this->error('请先录入零售数据',url('accounts/Salesrecord_home/addSalesRecord'));
            $purchase_history_list = MasterModel::inIt('purchase_history p')->field('p.id,p.wholesaler_id,p.unit_price,p.unit_amount,p.wholesaler_sn,p.total_price,w.name,p.status,p.is_del')->getListData(['p.member_id' => $this->member_info['id'], 'status' => '1'], 'p.id desc','',[['wholesaler w','w.id=p.wholesaler_id','left']]);
            if(empty($purchase_history_list))return $this->error('请先录入批发数据',url('accounts/Purchasehistory_home/addPurchaseHistory'));
            return view('generate_sales_statistics',['sales_record_list'=>$sales_record_list,'purchase_history_list'=>$purchase_history_list]);
        }
    }

    /**
     * 账目统计
     */
    function index(){
        $type=input('type','1');
        $statistics_list=MasterModel::inIt('sales_statistics')->getListData(['member_id'=>$this->member_info['id']]);
        return view('index',['statistics_list'=>$statistics_list,'type'=>$type]);
    }

}