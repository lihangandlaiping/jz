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

    function addSalesRecord(){
        if(Request::instance()->isPost()){

        }else{

            return view('add_sales_record');
        }
    }

}