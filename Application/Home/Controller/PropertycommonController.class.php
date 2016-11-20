<?php
namespace Home\Controller;

use Think\Controller;

class PropertycommonController extends Controller
{
    protected $propertyModel;

    protected $buildingModel;

    protected $companyModel;

    /**
     * 初始化
     */
    public function _initialize(){
        $this->propertyModel=D('property');
    }

    /**
     * 获取楼盘或楼栋或房间列表
     * @param string $isAjax 是否为ajax请求   true-是，false-否，默认否
     * @param string $id 查询的ID
     * @param string $type ID的类型   community-楼盘，build-楼栋，house-房间
     * @param boolean $isDelBind 是否筛选掉已绑定的房产
     * @param string $openid 微信用户openid，用于筛选已绑定的房产
     * 返回数据都是retMessage函数返回，如果不是ajax请求，请先json_decode解析返回的数据再进行处理
     */
    public function getListsById($isAjax = false, $id = '', $type = '', $isDelBind = false, $openid='', $isRoom=false)
    {
        $isAjax = I('post.isAjax', $isAjax);
        if ($isAjax) {
            //接收数据
            $id = I('post.id', '');
            $type = I('post.type', '');
        }
        if (!$id || !$type) {
            retMessage(false, null, '参数错误，请检查参数', '参数错误，请检查参数', 4001);
            exit;
        }
        $function = new \ReflectionMethod(get_called_class(), 'get' . $type . 'Lists');
        $result = $function->invoke($this, $id, $isDelBind, $openid, $isRoom);
        if (!$result) {
            retMessage(false, null, '查询不到记录', '查询不到记录', 4002);
            exit;
        }
        retMessage(true, $result);
        exit;
    }

    /**
     * 根据企业ID查询楼盘列表
     * @param $compid   企业ID
     * @return mixed
     */
    public function getCommunityLists($compid)
    {
        $result = $this->propertyModel->getCommunityByCompid($compid);
        return $result;
    }

    /**
     * 根据楼盘ID获取楼栋列表
     * @param $ccId     楼盘ID
     * @return mixed
     */
    public function getBuildLists($ccId)
    {
        $this->buildingModel = D('building');
        $result = $this->buildingModel->selectAllBuild($ccId, 0, 0, 1,true);
        return $result;
    }

    /**
     * 根据楼栋ID获取房间列表
     * @param $bmId
     * @return mixed
     */
    public function getHouseLists($bmId, $isDelBind=false, $openid='', $isRoom=false)
    {
        $result = $this->propertyModel->get_house_list($bmId, '', 0, '', '', 1)['list'];
        if ($isDelBind) {
            $wxbindModel=D('wxbind');
            $bindLists=$wxbindModel->getHouseBindList($openid,1);
            $result=$this->delBindHouses($result,$bindLists);
            $result=array_values($result);
        }
        if($isRoom){
            $roomModel=D('room');
            $hmIds=array_map(function($hm){return $hm['id'];},$result);
            $result=$roomModel->getRoomByHmids($hmIds,'eq',1);
        }
        return $result;
    }

    /**
     * 获取未绑定缴费的房间
     * @param array $houseLists 房间列表
     * @param array $bindLists 已绑定房间列表
     * @return mixed
     */
    public function delBindHouses(array $houseLists, array $bindLists)
    {
        foreach ($houseLists as $hm => $house) {
            foreach ($bindLists as $b => $bindList) {
                if ($bindList['hm_id'] == $house['id']) {
                    unset($houseLists[$hm]);
                }
            }
        }
        return $houseLists;
    }

    /**
     * 获取所有房间ID
     * @param $id       查询的ID
     * @param $type   ID的类型     company-企业，community-楼盘，build-楼栋，house-房间
     * @return array
     */
    public function getPropertyIds($id, $type)
    {
        //存在企业ID，查询所有楼盘ID
        if($type=='company') $ccIds=array_map(function($ccList){return $ccList['id'];},$this->getCommunityLists($id));
        //存在楼盘ID，直接获取楼盘ID
        if($type=='community') $ccIds=[$id];
        //存在企业ID或者楼盘ID，查询所有楼栋ID
        if(in_array($type,['company','community'])) {
            $this->buildingModel=D('building');
            $bmIds=array_map(function($bmList){return $bmList['id'];},$this->buildingModel->getBuildingListsByCommunityIds($ccIds));
        }
        //存在楼栋ID，直接获取楼栋ID
        if($type=='build') $bmIds=[$id];
        //存在企业ID或者楼盘ID或者楼栋ID，查询所有房间ID
        if(in_array($type,['company','community','build'])) $hmIds=array_map(function($hmList){return $hmList['id'];},$this->propertyModel->getHouseListsByBuildingIds($bmIds));
        //存在房间ID，直接获取房间ID
        if($type=='house') $hmIds=[$id];
        return $hmIds;
    }

    /**
     * 获取物业相关地址
     * @param $id
     * @param $type
     * @return mixed
     */
    public function getPorpertyAddress($id, $type)
    {
        $function=new \ReflectionMethod(get_called_class(),'get'.ucwords($type).'Address');
        $result=$function->invoke($this,$id);
        return $result;
    }

    /**
     * 获取企业地址
     * @param $id
     * @return mixed
     */
    public function getCompanyAddress($id)
    {
        $this->companyModel=D('company');
        $result=$this->companyModel->selectCompanyDetail($id)['name'];
        return $result;
    }

    /**
     * 获取楼盘地址
     * @param $id
     * @return string
     */
    public function getCommunityAddress($id)
    {
        $result=$this->propertyModel->getPropertyBelog($id,'community');
        return "{$result['cm_name']} {$result['cc_name']}";
    }

    /**
     * 获取楼栋地址
     * @param $id
     * @return string
     */
    public function getBuildAddress($id)
    {
        $result=$this->propertyModel->getPropertyBelog($id,'build');
        return "{$result['cm_name']} {$result['cc_name']} {$result['bm_name']}";
    }

    /**
     * 获取房间地址
     * @param $id
     * @return string
     */
    public function getHouseAddress($id)
    {
        $result=$this->propertyModel->getPropertyBelog($id,'house');
        return "{$result['cm_name']} {$result['cc_name']} {$result['bm_name']} {$result['hm_name']}";
    }

    /**
     * 查询房产相关信息
     * @param int $id ID
     * @param $type    ID类型     company-企业，community-企业，build-楼栋，house-房间
     * @param bool|false $isAjax 是否ajax访问，默认false
     * @return mixed
     */
    public function getPropertyInfo($id, $type, $isAjax = false)
    {
        $function = new \ReflectionMethod(get_called_class(), 'get' . ucwords($type) . 'Info');
        $result = $function->invoke($this, $id);
        if ($isAjax) {
            if (!$id || !$type) {
                retMessage(false, null, '参数错误，请检查参数！', '参数错误，请检查参数！', 4001);
                exit;
            }
            $result ? retMessage(true, $result) : retMessage(false, null, '查找不到相关记录', '查找不到相关记录', 4002);
            exit;
        }
        return $result;
    }

    /**
     * 查询企业信息
     * @param int $id 企业ID
     * @return mixed
     */
    public function getCompanyInfo($id)
    {
        $this->companyModel = D('company');
        $result = $this->companyModel->selectCompanyAll($id);
        return $result;
    }

    /**
     * 查询楼盘信息
     * @param int $id 楼盘ID
     * @return mixed
     */
    public function getCommunityInfo($id)
    {
        $result = $this->propertyModel->getCommunityInfo($id);
        return $result;
    }

    /**
     * 查询楼栋信息
     * @param int $id 楼栋ID
     * @return mixed
     */
    public function getBuildInfo($id)
    {
        $this->buildingModel = D('building');
        $result = $this->buildingModel->selectBuild($id);
        return $result;
    }

    /**
     * 查询房间信息
     * @param int $id   房间ID
     * @return mixed
     */
    public function getHouseInfo($id)
    {
        $result=$this->propertyModel->find_house_info($id);
        if(!$result['building_area']) $result['building_area']=0;
        if(!$result['inside_area']) $result['inside_area']=0;
        if(!$result['flat_area']) $result['flat_area']=0;
        return $result;
    }

    /************************************脚本分割线************************************************/

    /*
    * 为费项上线前的公司添加32费项
    */
    public function addCharges(){
        $chargeModel = D('Charge');
        $result = $chargeModel->addItems();
        dump($result);exit;
        $this->display('/Authrule/index');
    }
}