<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/1
 * Time: 17:19
 */
namespace Home;
use My\MasterController;
use My\MasterModel;
use think\Config;
use think\Request;
use wx_model\Weixinmodel;

class HomeController extends MasterController
{
    protected $model_name='';//数据库表名
    protected $member_info=['id'=>'1'];
    protected $wx_obj='';
    protected $wx_openid='';
    protected $wx_model='';
    protected $wx_user_info='';
    function __construct()
    {
        parent::__construct();
        //设置配置
//        $this->_redyConfig();
        //分页设置
        $this->pageSize=config('admin_page_size')?:$this->pageSize;
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if(empty($this->wx_user_info) && (strpos($user_agent, 'MicroMessenger') == true)){
//            $this->getWxUserInfo();
        }
//        $this->is_login();
    }

    function is_login(){
        $this->member_info=session('member_info');
        $url=Request::instance()->module().'/'.Request::instance()->controller().'/'.Request::instance()->action();
        if(empty( $this->member_info['account']) && ($url!='section/staff_home/login') && (!in_array(Request::instance()->module(),['cuisine','message']))){
            header('Location: http://' . $_SERVER['HTTP_HOST'] . url('section/StaffHome/login'));
        }
    }

    protected function setWxModel(){
        if(empty($this->wx_model) || !is_object($this->wx_model)){
            vendor('weixin/Weixinmodel');
            $wx_config=config('wx_config');
            $this->wx_model=new Weixinmodel($wx_config['appid'],$wx_config['appsecret']);
        }
    }

    /**
     * 获取员工信息并自动登录
     */
    function getWxUserInfo(){
        $wx_info= session('wx_user_info');
        if(empty($wx_info) || !is_array($wx_info)){
            if(empty($this->wx_obj) || !is_object($this->wx_obj)){
                vendor('weixin/Appweixin');
                $wx_config=config('wx_config');
                $this->wx_obj=new \Appweixin($wx_config['private_key'],$wx_config['appid'],$wx_config['mch_id'],$wx_config['appsecret']);
            }
            $wx_info=$this->wx_obj->authorize();
        }
        $this->wx_user_info=$wx_info;
        $this->wx_openid=$wx_info['openid'];
        $member_info=MasterModel::inIt('staff')->getOne(['openid'=>$this->wx_openid]);
        if(!empty($member_info)){
            session('member_info',$member_info);
        }
    }

    /**
     * 配置加载
     */
    private function _redyConfig()
    {
        //配置设置
        if(!Config::has('admin_action_log'))
        {
            $conf=cache('config_h'.MODULE_NAME)?:array();
            if(!$conf)
            {
                $module=MasterModel::inIt('module')->field('id')->getOne(array('name'=>MODULE_NAME));
                $where="is_show in ('1','4') ";
                if($module['id'])
                    $where.="and (module_id=0 or module_id={$module['id']})";
                else $where.="and module_id=0";
                $conf=MasterModel::inIt('config c')->field('name,content')->getListData($where);
                cache('config_h'.MODULE_NAME,$conf);
            }
            foreach($conf as $a)
            {
                config($a['name'],$a['content']?:'');
            }
            //设置后台操作日志
            $configs=MasterModel::inIt('config')->field('content')->getOne(array('name'=>'admin_action_log'));
            config('admin_action_log',$configs['content']);
        }
    }
    /**
     * 获取分页数据
     * @param $tableName 表名、或数据查询对象
     * @param $where 条件
     * @param $order 排序
     * @param $group 分组
     * @param $join
     */
    function getListData($tableName,$where=null,$field='*',$order=null,$group=null,$join=array())
    {
        $model=MasterModel::inIt($tableName);
        $count=$model->field($field)->getCount($where,$group,$join);
        $page=new \think\Page($count,$this->pageSize);
        $p=$page->show();
        $this->assign('_p',$p);
        $limit= $page->firstRow.','.$page->listRows;
        $list=$model->field($field)->getListData($where,$order,$group,$join,$limit);
        return $list;
    }

    /**
     * 操作成功跳转的快捷方法
     */
    protected function success($msg = '', $url = null, $data = '', $wait = 3)
    {
        $result = [
            'code' => '1',
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];
        if(Request::instance()->isAjax()){
            exit(json_encode($result));
        }else{
            return view('public/index/success',$result);
        }
    }

    /**
     * 操作错误跳转的快捷方法
     */
    function error($msg = '', $url = null, $data = '', $wait = 3,$parentwindow=0)
    {
        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait
        ];
        if(Request::instance()->isAjax()){
            exit(json_encode($result));
        }else{
            return view('public/index/error',$result);
        }
    }

}