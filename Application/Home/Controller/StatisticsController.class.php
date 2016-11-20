<?php
/*************************************************
 * 文件名：StatisticsController.class.php
 * 功能：     统计控制器
 * 日期：     2015.11.27
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;

class StatisticsController extends AccessController{
    protected $faultModel;
    protected $companyModel;
    protected $repairdevModel;
    protected $query_map = array(
        'compid' => 'compid',
        's_time' => 'start_time',
        'e_time' => 'end_time',
        'cc_id' => 'cc_id',     //楼盘id  
        'tp' => 'type',             //公司类型,property是物业公司，repair是维修公司
        'flag' => 'flag',
    );
    protected $faulttimeoutModel;
    /**
     * 初始化
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->faultModel=D('fault');
        $this->companyModel=D('company');
    }

    /**
     * 故障分析页面
     */
    public function index(){
        $compid=I('request.compid','');
        $flag=I('request.flag',1);
        $isAjax=I('post.isAjax','');
        $ccId=I('post.ccId','');
        $startDate=I('post.startDate','');
        $endDate=I('post.endDate','');

        //获取公司类型
        $compType=$this->companyModel->selectCompanyDetail($compid)['cm_type'];
        //查询关联楼盘
        $propertyList=$this->getPropertyListByCompType($compid,$compType);

        //查询故障统计
        $function=new \ReflectionMethod(get_called_class(),C('STATISTICS')[$flag].'Statistics');
        $result=$function->invoke($this,$flag,$compid,$compType,$ccId,$startDate,$endDate);
        if ($isAjax){
            if (!$flag || !$compid){
                retMessage(false,null,'接收不到数据','接收不到数据',4001);
                exit;
            }
            if (!$result['dateDatas']){
                retMessage(false,null,4002);
                exit;
            }
            retMessage(true,$result);
            exit;
        }

        $this->assign('compType',$compType);
        $this->assign('ccList',$propertyList);
        $this->assign('labels',json_encode($result['labels']));
        $this->assign('all',json_encode($result['all']));
        $this->assign('repaired',json_encode($result['repaired']));
        $this->assign('dateDatas',$result['dateDatas']);
        $this->assign('totalCount',$result['totalCount']);
        $this->display();
    }

    /**
     * 根据企业类型获取相应的楼盘列表
     * @param $compid
     * @param $compType
     * @return mixed
     */
    public function getPropertyListByCompType($compid,$compType)
    {
        $this->repairdevModel=D('repairdev');
        if ($compType==C('COMPANY_TYPE.PROPERTY')) $propertyList=$this->repairdevModel->getPropertyByProCompid($compid);
        if ($compType==C('COMPANY_TYPE.REPAIR')) $propertyList=$this->repairdevModel->getPropertyByCompid($compid);
        foreach ($propertyList as $p=>$property){
            $ccList[$p]['id']=$property['cc_id'];
            $ccList[$p]['name']=$property['cc_name'];
        }
        $ccList=$this->unique_multidim_array($ccList,'id');
        return $ccList;
    }

    /**
     * 本周故障统计
     * @param $flag                         故障统计类型
     * @param $compid                   企业ID
     * @param $compType             企业类型
     * @return number|multitype:multitype:string  unknown
     */
    public function weekStatistics($flag,$compid,$compType,$ccId){
        //查询本周故障列表
        $monday=date('Y-m-d',$this->getThisMondy());
        $sunday=date('Y-m-d',$this->getThisSunday());
        $where=[
            'start_time'=>$monday,
            'end_time'=>date('Y-m-d H:i:s',strtotime($sunday.'+1 day')-1)
        ];
        if($ccId) $where['cc_id']=$ccId;
        $result=$this->getStatisticsData($flag,$compid,$compType,$where);
        return $result;
    }

    /**
     * 本月故障统计
     * @param $flag                         故障统计类型
     * @param $compid                   企业ID
     * @param $compType             企业类型
     * @return Ambigous <number, multitype:multitype:number  unknown Ambigous <multitype:string , unknown> >
     */
    public function monthStatistics($flag,$compid,$compType,$ccId){
        //获取本月
        $month=date('Y-m',time());
        $where=[
            'create_time'=>$month
        ];
        if($ccId) $where['cc_id']=$ccId;
        $result=$this->getStatisticsData($flag,$compid,$compType,$where);
        return $result;
    }

    /**
     * 本年故障统计
     * @param $flag                         故障统计类型
     * @param $compid                   企业ID
     * @param $compType             企业类型
     * @return multitype|number|string
     */
    public function yearStatistics($flag,$compid,$compType,$ccId){
        //获取本年
        $year=date('Y',time());
        $where=[
            'create_time'=>$year
            ];
        if($ccId) $where['cc_id']=$ccId;
        $result=$this->getStatisticsData($flag,$compid,$compType,$where);
        return $result;
    }

    /**
     * 自定义开始和结束日期统计
     * @param $flag                         故障统计类型
     * @param $compid                   企业ID
     * @param $compType             企业类型
     * @param string $ccId              楼盘ID
     * @param string $startDate     开始日期
     * @param string $endDate       结束日期
     * @return multitype|number|string
     */
    public function customStatistics($flag,$compid,$compType,$ccId='',$startDate='',$endDate=''){
        if($ccId) $where['cc_id']=$ccId;
        $dateRange=array_unique([$startDate,$endDate]);
        if(count($dateRange)==1){
            $where['create_time']=$startDate;
        }else{
            if($startDate) $where['start_time']=$startDate;
            if($endDate) $where['end_time']=date('Y-m-d H:i:s',strtotime($endDate.'+1 day')-1);
        }
        $result=$this->getStatisticsData($flag,$compid,$compType,$where);
        return $result;
    }

    /**
     * 故障统计数据
     * @param $flag                         故障统计类型
     * @param $compid                   企业ID
     * @param $compType             企业类型
     * @param $where                    搜索条件，默认为空数组
     * @return array
     */
    public function getStatisticsData($flag,$compid,$compType,$where=[]){
        //判断公司类型
        if ($compType==2){
            $where['rc_id']=$compid;
        }else {
            $where['cm_id']=$compid;
        }
        //查询故障列表
        $faultList=$this->faultModel->getFaultList($where,'create_time asc')['result'];
        //设置日期分组的空数组
        $list = [];
        if($where['start_time'] && $where['end_time']){
            //计算本周的日期范围
            $dRange=$this->getDateRange(strtotime($where['start_time']),strtotime($where['end_time']));
        }
        if($where['create_time']){
            //计算本月的日期范围
            if($flag==2){
                $firstDay=date('Y-m-01',strtotime($where['create_time']));
                $lastDay=date('Y-m-d',strtotime(date('Y-m-01',strtotime($where['create_time'])).'+1 month -1 day'));
                $dRange=$this->getDateRange(strtotime($firstDay),strtotime($lastDay));
            }
            //计算本年的月份范围
            if($flag==3){
                $firstMonth=date('Y-01',strtotime($where['create_time']));
                $lastMonth=date('Y-01',strtotime($where['create_time'].'+1 year'));
                $dRange=$this->getMonthRange(strtotime($firstMonth),strtotime($lastMonth));
            }
        }
        $list=array_fill_keys($dRange,null);
        //按每日/每月分组
        foreach ($faultList as $w=>$weekFault){
            if ($flag!=3){
                $list[date('Y-m-d',strtotime($weekFault['create_time']))][]=$weekFault;
            }else {
                $list[date('Y-m',strtotime($weekFault['create_time']))][]=$weekFault;
            }
        }
        foreach ($list as $wd=>$wlist){
            $monthLabels[]=$wd;
            //组装本周故障量
            $allCount[$wd]=0;
            $allCount[$wd]=count($wlist);
            //组装本周修复量
            $wn=0;
            $repairedCount[$wd]=count(array_filter($wlist,function($wli){
                if (($wli['status']==C('FAULT_STATUS')['REPAIRED'] || $wli['status']==C('FAULT_STATUS')['EVALUATED'])){
                    $wn++;
                    return $wn;
                }
            }));
        }
        //组装图表统计遍历的数据
        //设置故障量与修复量总数
        $totalCount=[
            'all'=>0,
            'repaired'=>0
        ];
        $n=0;
        foreach ($allCount as $aDate=>$wcount){
            $dataAll[]=$wcount;
            $totalCount['all']=$totalCount['all']+$wcount;
            foreach ($repairedCount as $rDate=>$wrcount){
                if ($aDate==$rDate){
                    //设置每日故障量
                    $dateDatas[$n]['date']=$aDate;
                    $dateDatas[$n]['all']=$wcount;
                    //设置每日修复量
                    $dateDatas[$n]['date']=$aDate;
                    $dateDatas[$n]['repaired']=$wrcount;
                    $n++;
                    $totalCount['repaired']+=$wrcount;
                    continue;
                }
            }
        }
        foreach ($repairedCount as $wrc=>$wrcount){
            $dataRepair[]=$wrcount;
        }

        if ($flag==1) $labels=["星期一","星期二","星期三","星期四","星期五","星期六","星期日"];
        if ($flag==2 || $flag==4) $labels=array_map(function($value){return date('Y年m月d日',strtotime($value));}, $monthLabels);;
        if ($flag==3) $labels=array_map(function($value){return date('m月',strtotime($value));}, $monthLabels);

        return $return=[
            'labels'=>$labels,
            'all'=>$dataAll,
            'repaired'=>$dataRepair,
            'dateDatas'=>$dateDatas,
            'totalCount'=>$totalCount
        ];
    }

    /**
     * 超时统计页面
     */
    public function timeout()
    {
        $compid=I('request.compid','');
        $flag=I('post.flag',1);
        $isAjax=I('post.isAjax','');
        $ccId=I('post.ccId','');
        $startDate=I('post.startDate','');
        $endDate=I('post.endDate','');
        $page=I('post.page',1);
        $ccId=I('post.ccId','');
        //获取公司类型
        $compType=$this->companyModel->selectCompanyDetail($compid)['cm_type'];
        //查询关联楼盘
        $propertyList=$this->getPropertyListByCompType($compid,$compType);
        //获取该企业本周超时列表
        $result=$this->assembleTimeout($flag,$compid,$compType,(($page-1)*10),10,$ccId,$startDate,$endDate);
        //dump($result);exit;
        if($isAjax){
            if(!$compid){retMessage(false,null,'参数不足','参数不足',4001);exit;}
            if(!$result){retMessage(false,null,'查询不到记录','查询不到记录',4002);exit;}
            $final=[];
            $i=0;
            foreach ($result['list'] as $date => $re) {
                $final['list'][$i]['date']=$date;
                $final['list'][$i]['once']=$re['once'];
                $final['list'][$i]['repair']=$re['repair'];
                $i++;
            }
            $final['total']=$result['total'];
            retMessage(true,$final);exit;
        }
        $this->assign('compType',$compType);
        $this->assign('ccLists',$propertyList);
        $this->assign('lists',$result['list']);
        $this->assign('total',$result['total']);
        $this->display();
    }

    /**
     * 获取并组装超时统计数据
     * @param $flag                         查询范围    1-本周，2-本月，3-本年，4-自定义日期
     * @param $compid                   企业ID
     * @param $compType
     * @param int $offset
     * @param int $length
     * @param string $ccId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function assembleTimeout($flag,$compid, $compType, $offset=0, $length=10, $ccId='', $startDate='', $endDate='')
    {
        $getData=$this->formatTimeoutWhere($flag,$compid, $compType, $offset, $length, $ccId, $startDate, $endDate);
        $this->faulttimeoutModel=D('faulttimeout');
        $lists=$this->faulttimeoutModel->getTimeoutStatistics($getData['where']);
        $tmps=$getData['tmps'];
        foreach ($lists as $list) {
            if($flag==3){
                $tmps[date('Y-m',strtotime($list['update_time']))][]=$list;
            }else{
                $tmps[date('Y-m-d',strtotime($list['update_time']))][]=$list;
            }
        }
        $result=[];
        foreach ($tmps as $date=>$tmp) {
            $result[$date]['once']=0;
            $result[$date]['repair']=0;
            foreach ($tmp as $t) {
                if($t['type']==C('FAULT_OVERTIME.OVERTIME')){
                    $result[$date]['once']=$result[$date]['once']+1;
                    continue;
                }
                if($t['type']==C('FAULT_OVERTIME.OVERTIME_REPAIR')){
                    $result[$date]['repair']=$result[$date]['repair']+1;
                    continue;
                }
            }
        }
        $result=['list'=>$result,'total'=>$getData['total']];
        return $result;
    }

    /**
     * 格式化超时统计WHERE条件、总页数和日期数组
     * @param $flag                 查询范围    1-本周，2-本月，3-本年，4-自定义日期
     * @param $compid           企业ID
     * @param $compType      企业类型
     * @param $offset              页码，默认为0
     * @param $length             记录数，默认为10
     * @param $ccId                 楼盘ID，默认为空
     * @return array
     */
    public function formatTimeoutWhere($flag,$compid, $compType, $offset=0, $length=10, $ccId='', $startDate, $endDate)
    {
        $timeStampRange='';
        if($flag==1){
            //获取本周所有日期时间戳
            $timeStampRange=[$this->getThisMondy(),($this->getThisSunday()+86400-1)];
        }
        if($flag==2){
            //获取本月所有日期时间戳
            $timeStampRange=$this->getMonthRangeByTime(time());
            $timeStampRange=[strtotime($timeStampRange[0]),strtotime($timeStampRange[1])];
        }
        //获取自定义日期时间戳
        if($flag==4) $timeStampRange=[strtotime($startDate),strtotime($endDate.' 23:59:59')];
        //获取日期范围，并分段获取
        $dateRange=$this->getDateLists($timeStampRange[0],$timeStampRange[1]);
        $range=array_slice($dateRange,$offset,$length);
        //计算总页数
        $total=ceil(count($dateRange)/$length);
        if($flag==3){
            //获取本年所有月份
            $total=1;
            $range=$this->getYearRangeByTime(time());
        }
        //获取所有日期，并创建一个以日期为键名的数组，键值默认0
        $tmps=[];
        foreach ($range as $r) {
            $tmps[$r][]=0;
        }
        $where=[
            'start_time'=>date('Y-m-d H:i:s',strtotime(reset($range))),
            'end_time'=>date('Y-m-d 23:59:59',strtotime(end($range))),
        ];
        if($flag==3) $where['end_time']=date(end($range).'-31 23:59:59',strtotime($range));
        if($compType==C('COMPANY_TYPE.PROPERTY')) $where['f.cm_id']=$compid;
        if($compType==C('COMPANY_TYPE.REPAIR')) $where['f.rc_id']=$compid;
        if($ccId) $where['f.cc_id']=$ccId;
        return ['where'=>$where,'total'=>$total,'tmps'=>$tmps];
    }

    /**
     * 根据开始时间戳和结束时间戳得出所有日期
     * @param $beginTimeStamp
     * @param $endTimeStamp
     * @return array|string
     */
    public function getDateLists($beginTimeStamp,$endTimeStamp)
    {
        if((!is_numeric($beginTimeStamp)) || (!is_numeric($endTimeStamp)) || ($endTimeStamp<=$beginTimeStamp)){
            return '';
        }
        $tmp=[];
        $i=$beginTimeStamp;
        for(;;){
            if($i>$endTimeStamp) break;
            $tmp[]=date("Y-m-d",$i);
            $i+=(24*3600);
        }
        return $tmp;
    }

    /**
     * 获取本周周一
     * @param number $timeStamp     某个星期某一时间戳，默认为当前时间
     * @param string $isReturnTimeStamp     是否返回时间戳，是-时间戳，否-日期格式，默认时间戳
     * @return Ambigous <>
     */
    public function getThisMondy($timeStamp=0,$isReturnTimeStamp=true){
        static $cache ;
        $id = $timeStamp.$isReturnTimeStamp;
        if(!isset($cache[$id])){
            if(!$timeStamp) $timeStamp = time();
            $mondy = date('Y-m-d', $timeStamp-86400*date('w',$timeStamp)+(date('w',$timeStamp)>0?86400:-/*6*86400*/518400));
            if($isReturnTimeStamp){
                $cache[$id] = strtotime($mondy);
            }else{
                $cache[$id] = $mondy;
            }
        }
        return $cache[$id];

    }

    /**
     * 获取下周周一
     * @param number $timeStamp     某个星期某一时间戳，默认为当前时间
     * @param string $isReturnTimeStamp     是否返回时间戳，是-时间戳，否-日期格式，默认时间戳
     * @return Ambigous <>
     */
    public function getThisSunday($timeStamp=0,$isReturnTimeStamp=true){
        static $cache ;
        $id = $timeStamp.$isReturnTimeStamp;
        if(!isset($cache[$id])){
            if(!$timeStamp) $timeStamp = time();
            $sunday = $this->getThisMondy($timeStamp) + /*6*86400*/518400;
            if($isReturnTimeStamp){
                $cache[$id] = $sunday;
            }else{
                $cache[$id] = date('Y-m-d',$sunday);
            }
        }
        return $cache[$id];
    }

    /**
     * 获取指定时间戳所属月份的日期范围
     * @param $timestamp
     * @return array
     */
    public function getMonthRangeByTime($timestamp)
    {
        $days=date('t',$timestamp);
        $range=[date('Y-m-01',$timestamp),date("Y-m-{$days}",$timestamp)];
        return $range;
    }

    /**
     * 获取指定时间戳所属年份的月份范围
     * @param $timestamp
     * @return array
     */
    public function getYearRangeByTime($timestamp)
    {
        $range=[];
        for($i=1;$i<13;$i++){
            $month=str_pad($i,2,0,STR_PAD_LEFT);
            $range[]=date("Y-{$month}",$timestamp);
        }
        return $range;
    }

    /**
     * 去重二维数组
     * @param array $array  要去重的数组
     * @param string $key   去重的根据
     * @return multitype:unknown
     */
    public function unique_multidim_array(array $array, $key){
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $val){
            if(!in_array($val[$key],$key_array)){
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    /**
     * 获取日期范围
     * @param $startDay     开始日期
     * @param $endDay      结束日期
     * @return array
     */
    public function getDateRange($startDay,$endDay)
    {
        $dRange=range($startDay,$endDay,86400);
        $dRange=array_map(function($timestamp){
            return date('Y-m-d',$timestamp);
        },$dRange);
        return$dRange;
    }

    /**
     * 获取月份范围
     * @param $startMonth   开始月份
     * @param $endMonth    结束月份
     * @return array
     */
    public function getMonthRange($startMonth, $endMonth)
    {
        $dRange = [];
        while($startMonth<$endMonth) {
            $dRange[] = date('Y-m',$startMonth);
            $startMonth = strtotime('next month',$startMonth);
        }
        return $dRange;
    }
    // TODO 故障统计
    public function count(){
        $compid = I('get.compid','');
        //物业
        $propMod = D('Property');
        //报障
        $faultMod = D('Fault');
        //公司
        $compMod = D('Company');
        $day = getdate()['wday']-1;
        $week = date('Y-m-d 00:00:00',strtotime("-{$day} day"));
        //查询公司类型
        $cm_type = $compMod->where("id={$compid}")
                           ->getField('cm_type');
        //获取所有楼盘
        if($cm_type == C('COMPANY_TYPE.PROPERTY')){
            $proLlist = $propMod->getCommunityToBindRepair($compid);
        }elseif($cm_type == C('COMPANY_TYPE.REPAIR')){
            $proLlist = $faultMod->repairProperty($compid);
        }
        //本周本企业所有维修订单
        if($cm_type == C('COMPANY_TYPE.PROPERTY')){
            $order = $faultMod->ord_quantity($compid, $week);
        }elseif($cm_type == C('COMPANY_TYPE.REPAIR')){
            $order = $faultMod->repairQuantity($compid, $week);
        }
        //计算页数
        $proCount = ceil(count($proLlist)/10);
        //初始化页面数据数组
        $proArr = $this->__synthetise($proLlist);
        //计算百分比
        $proArr = $this->__calculate($order,$proArr);
        //截取10
        $proArr = array_slice($proArr,0,10,true);
        $this->assign('list',$proLlist);
        $this->assign('repair',$proArr);
        $this->assign('compid',$compid);
        $this->assign('proCount',$proCount);
        $this->assign('cm_type',$cm_type);
        $this->display();
    }
    //按条件查询所有订单（AJAX）
    public function purview(){
        //查询时间条件类型
        $type = I('post.type',2);
        $compid = I('post.compid','');
        //房产ID -1为全部房产
        $prop = I('post.prop',-1);
        //开始时间
        $star = I('post.star',null);
        //结束时间
        $end = I('post.end',null);
        //公司类型
        $cm_type = I('post.cm_type','');
        //开始截取位置
        $startLimit = I('post.limit',0);
        $endTime = $end?$end:date('Y-m-d H:i:s',time());
        $range = getdate();
        switch($type){
            case 2:
                //本周
                $day = $range['wday']-1;
                $week = date('Y-m-d 00:00:00',strtotime("-{$day} day"));
                $data = $week;
                break;
            case 3:
                //本月
                $day = $range['mday'];
                $month = date('Y-m-d 00:00:00',strtotime("-{$day} day"));
                $data = $month;
                break;
            case 4:
                //本年
                $day = $range['yday'];
                $year = date('Y-m-d 00:00:00',strtotime("-{$day} day"));
                $data = $year;
                break;
            default:
                $data = '';
                break;
        }
        $starTime = $star?$star:$data;
        //查询所有楼盘
        $faultMod = D('Fault');
        $propMod = D('Property');
        if($prop==-1){
            if($cm_type == C('COMPANY_TYPE.PROPERTY')){
                $property = $propMod->getCommunityToBindRepair($compid);
            }elseif($cm_type == C('COMPANY_TYPE.REPAIR')){
                $property = $faultMod->repairProperty($compid);
            }
        }else{
            if($cm_type == C('COMPANY_TYPE.PROPERTY')){
                $property = $propMod->getCommunityToBindRepair($compid, $prop);
            }elseif($cm_type == C('COMPANY_TYPE.REPAIR')){
                $property = $faultMod->repairProperty($compid, $prop);
            }
        }
        //订单
        if($cm_type == C('COMPANY_TYPE.PROPERTY')){
            $order = $faultMod->ord_quantity($compid,$starTime,$endTime,$prop);
        }elseif($cm_type == C('COMPANY_TYPE.REPAIR')){
            $order = $faultMod->repairQuantity($compid,$starTime,$endTime,$prop);
        }
        //初始化页面数据数组
        $proArr = $this->__synthetise($property);

        //计算百分比
        $proArr = $this->__calculate($order,$proArr);
        //截取10
        $proArr = array_slice($proArr,$startLimit,10,true);
        exit(json_encode($proArr));
    }
    //组成页面数组
    protected function __synthetise($index){
        $proArr = array();
        foreach ($index as $pro) {
            $proArr[$pro['id']] = array(
                'id' => $pro['id'],
                'name' => $pro['name'],
                'count' => 0,
                //待接单
                'notYet' => array(
                    'amount' => 0,
                    'percent' => 0
                ),
                //已接单待修复
                'catched' => array(
                    'amount' => 0,
                    'percent' => 0
                ),
                //已修复
                'repaired' => array(
                    'amount' => 0,
                    'percent' => 0
                ),
                //已评价
                'finish' => array(
                    'amount' => 0,
                    'percent' => 0
                ),
                //评价统计
                'evaluat' => array(
                    'work' => 0,
                    'serve' => 0,
                    'count' => 0
                ),
                //已转单
                'shifted' => array(
                    'amount' => 0,
                    'percent' => 0
                ),
                //接单超时
                'catchOut' => array(
                    'amount' => 0,
                    'percent' => 0
                ),
                //修复超时
                'repairOut' => array(
                    'amount' => 0,
                    'percent' => 0
                )
            );
        }
        return $proArr;
    }
    //订单百分比计算
    protected function __calculate($order,$proArr){
        foreach($order as $or){
            $index = $or['cc_id'];
            $proArr[$index]['count'] += 1;
            switch($or['status']){
                case C('FAULT_STATUS.NOT_YET'):
                    $proArr[$index]['notYet']['amount'] += 1;
                    break;
                case C('FAULT_STATUS.CATCHED'):
                    $proArr[$index]['catched']['amount'] += 1;
                    break;
                case C('FAULT_STATUS.REPAIRED'):
                    $proArr[$index]['repaired']['amount'] += 1;
                    break;
                case C('FAULT_STATUS.EVALUATED'):
                    $proArr[$index]['evaluat']['work'] += $or['work'];
                    $proArr[$index]['evaluat']['serve'] += $or['serve'];
                    $proArr[$index]['evaluat']['count'] += 1;
                    break;
                case C('FAULT_STATUS.SHIFTED'):
                    $proArr[$index]['shifted']['amount'] += 1;
                    break;
                case C('FAULT_STATUS.FINISH'):
                    $proArr[$index]['finish ']['amount'] += 1;
                    break;
            }
            //超时接单
            if($or['outType'] == 1 || $or['outType'] == 2){
                $proArr[$index]['catchOut']['amount'] += 1;
            }
            //超时修复
            if($or['outType'] == 3){
                $proArr[$index]['repairOut']['amount'] += 1;
            }
        }
        //开始计算百分比
        foreach($proArr as $key=>$pr){
            $count = $pr['count'];
            $n_amount = $pr['notYet']['amount'];
            $c_amount = $pr['catched']['amount'];
            $r_amount = $pr['repaired']['amount'];
            $s_amount = $pr['shifted']['amount'];
            $f_amount = $pr['finish']['amount'];
            $ca_amount = $pr['catchOut']['amount'];
            $re_amount = $pr['repairOut']['amount'];
            $proArr[$key]['notYet']['percent'] = round($n_amount/$count*100, 2);
            $proArr[$key]['catched']['percent'] = round($c_amount/$count*100, 2);
            $proArr[$key]['repaired']['percent'] = round($r_amount/$count*100, 2);
            $proArr[$key]['shifted']['percent'] = round($s_amount/$count*100, 2);
            $proArr[$key]['finish']['percent'] = round($f_amount/$count*100, 2);
            $proArr[$key]['catchOut']['percent'] = round($ca_amount/$count*100, 2);
            $proArr[$key]['repairOut']['percent'] = round($re_amount/$count*100, 2);
            //评价的平均数
            $e_count = $pr['evaluat']['count'];
            $w_eval = $pr['evaluat']['work']/$e_count;
            $s_eval = $pr['evaluat']['serve']/$e_count;
            $proArr[$key]['evaluat']['work'] = $w_eval?$w_eval:0;
            $proArr[$key]['evaluat']['serve'] = $s_eval?$s_eval:0;
        }
        return $proArr;
    }
    

    //组装查询条件
    public function getWhere(){
        foreach (I('get.') as $k => $v) {
            if ($v) {
                if($v == 'year'){
                    $where['evaluate_time'] = date('Y');
                    unset($where['start_time'],$where['end_time']);
                    continue; 
                }elseif ($v == 'month') {
                    $where['evaluate_time'] = date('Y-m');
                    unset($where['start_time'],$where['end_time']);
                    continue;
                }elseif ($v == 'week') {
                    $where['start_time'] = date('Y-m-d H:i:s',$this->getThisMondy());
                    $where['end_time'] = date('Y-m-d H:i:s',$this->getThisSunday());
                    continue;
                }
                if(array_key_exists($k , $this->query_map)){
                    $where[$this->query_map[$k]] = $v;
                }
            }
        }
        return $where;
    }
    //评价统计
    public function evaluation(){
        $where = $this->getWhere();
        $faultModel = D('Fault');
        $communityModel = D('Property');
        $deviceCompModel = D('Compdevice');
        $datas = $faultModel->getEvaluationData($where);
        foreach ($datas as $data) {
            $infos[$data['group_id']][] = $data;
            $sum['work'] += $data['work_eva'];
            $sum['service'] += $data['service_eva'];
        }
        $avg['work'] = round($sum['work']/count($datas) , 1);
        $avg['service'] = round($sum['service']/count($datas) , 1);
        foreach ($infos as $info) {
            $work_sum = 0;
            $service_sum = 0;
            foreach ($info as $key => $record){
                $work_sum += $record['work_eva'];
                $service_sum += $record['service_eva'];
            }
            $evaluations[$info[$key]['group_id']]['work_eva'] = round($work_sum/count($info) , 1);
            $evaluations[$info[$key]['group_id']]['service_eva'] = round($service_sum/count($info) , 1);
            $evaluations[$info[$key]['group_id']]['name'] = $info[$key]['name'];
        }
        if ($where['flag'] == 'search') {
            $this->assign('s_time',$where['start_time']);
            $this->assign('e_time',$where['end_time']);
        }
        $community = ($where['type'] ==  'property') ? $communityModel->getCommunityByCompid($where['compid']) : $deviceCompModel->getCommunityByRCid($where['compid']) ;
        $this->assign('cc_id',$where['cc_id']);
        $this->assign('compid',$where['compid']);
        $this->assign('community',$community);
        $this->assign('count',count($evaluations));
        $this->assign('avg',$avg);
        $this->assign('data',$evaluations);
        $this->display();
    }

    //考勤统计
    public function sign(){
        $signModel = D('WXSign');
        $where = $this->getWhere();
        //dump($where);exit;
        if($where['evaluate_time']){
            $where['sign_time'] = $where['evaluate_time'];
            unset($where['evaluate_time']);
        }
        $allRepairs = $signModel->getAllRepairers($where['compid']);
        foreach ($allRepairs as $key => $value) {
            $allRepairsData[$value['id']] = $value;
        }
        $datas = $signModel->getSignData($where);
        foreach ($datas as $key => $data) {
            $repairers[$data['id']]['name'] = $data['name'];
            unset($allRepairsData[$data['id']]);
        }
        foreach ($datas as $key => $data) {
            foreach ($repairers as $k => $repairer) {
                if ($k == $data['id'] && $data['sign_time']){
                    $repairers[$k]['count'] = $repairers[$k]['count']+1;
                }
            }
        }
        if (I('get.time') == 'week'){
            $total = 7;
        }elseif (I('get.time') == 'month'){
            $total = date('t',$timestamp);
        }elseif (I('get.time') == 'year'){
            $year = date('Y'); 
            if(($year%4 == 0 && $year%100 != 0) || ($year%400 == 0 )){
                $total = 366;
            }else{
                $total = 365;
            }
        }elseif($where['start_time'] && $where['end_time']){
            $dateList = $this->getDateLists(strtotime($where['start_time']), strtotime($where['end_time']));
            $total = count($dateList);
        }
        $this->assign('compid',$where['compid']);
        $this->assign('not_sign',$allRepairsData);
        $this->assign('count',count($repairers)+count($allRepairsData));
        $this->assign('data',$repairers);
        $this->assign('total',$total);
        $this->display();
    }
}
