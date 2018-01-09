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

        }else{
            $sales_record_list = MasterModel::inIt('sales_record s')->field('s.id,s.client_id,s.money_amount,s.sales_sn,c.name')->getListData(['s.member_id' => $this->member_info['id'], 's.status' => '1'], 's.id desc','',['client c','c.id=s.client_id','left']);
            $purchase_history_list = MasterModel::inIt('purchase_history p')->field('p.id,p.wholesaler_id,p.unit_price,p.unit_amount,p.wholesaler_sn,p.total_price,w.name')->getListData(['member_id' => $this->member_info['id'], 'status' => '1'], 'id desc','',['wholesaler w','w.id=p.wholesaler_id','left']);
            return view('generate_sales_statistics');
        }
    }


}