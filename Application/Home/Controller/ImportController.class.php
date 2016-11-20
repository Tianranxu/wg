<?php
/*************************************************
 * 文件名：ProtertyController.class.php
 * 功能：     导入管理控制器
 * 日期：     2015.11.23
 * 作者：     DA mimi,XU,fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;

use Org\Util\RabbitMQ;
class ImportController extends AccessController{
    protected $type;
    protected $id;
    protected $name;
    protected $companyModel;
    protected $propertyModel;
    protected $importModel;
    
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
        
        $this->companyModel=D('company');
        $this->importModel=D('import');
    }
    
    /**
     * 导入管理页面
     */
    public function index(){
        $this->type=I('get.type','');
        $this->id=I('get.id','');
        if (!$this->type || !$this->id){
            $this->error('参数错误！',U('company/index'));
        }
        $typeName=$this->getTypeName($this->type)['cName'];
        
        //查询企业信息
        $compid=$this->formatCompid($this->type, $this->id);
        $compInfo=$this->companyModel->selectCompanyDetail($compid);
        
        //查询日志信息
        $logList=$this->importModel->getImportLog($compid,$this->type);
        $this->assign('typeName',$typeName);
        $this->assign('compid',$compid);
        $this->assign('compInfo',$compInfo);
        $this->assign('logList',$logList);
        $this->assign('type',$this->type);
        $this->assign('id',$this->id);
        $this->display();
    }
    
    /**
     * 导入错误报告
     */
    public function wrong(){
        //接收数据
        $type=I('get.type','');
        $typeName=$this->getTypeName($type)['cName'];
        $logId=I('get.logid','');
        //查询导入错误日志
        $logInfo=$this->importModel->getImportById($logId);
        $logInfo['remark']=json_decode($logInfo['remark'],true);

        $this->assign('typeName',$typeName);
        $this->assign('logInfo',$logInfo);
        $this->display();
    }
    
    /**
     * 上传excel
     */
    public function upload(){
        header("Content-Type:text/html;charset=utf-8");
        $type=I('post.type','');
        $id=I('post.id','');
        $uploadName=I('post.uploadName','');
        if (!$type || !$id || !$uploadName){
            $this->error('对不起，你无此权限进入！！', U('User/login'));
        }
        
        $compid=$this->formatCompid($type, $id);
        // 实例化上传类
        $upload = new \Think\Upload();
        // 设置附件上传大小
        $upload->maxSize = 3145728;
        // 设置附件上传类型
        $upload->exts = array(
            'xls',
            'xlsx'
        );
        // 设置附件上传目录（根目录/Uploads/导入类型/企业ID/年/月/日/）
        $typeInfo=$this->getTypeName($type);
        $typeName=$typeInfo['eName'];
        $upload->savePath = DIRECTORY_SEPARATOR.$typeName.DIRECTORY_SEPARATOR.$compid.DIRECTORY_SEPARATOR;
        
        //上传文件
        $info=$upload->uploadOne($_FILES['excelData']);
        $filename ='.'.DIRECTORY_SEPARATOR.'Uploads'.$info['savepath'].$info['savename'];
        $exts = $info['ext'];
        if (!$info){
            $this->error($upload->getError());
        }
        
        //TODO 写入导入日志表
        //组装数据
        $data=array(
            'user_id'=>$this->userID,
            'file_name'=>$uploadName,
            'file_path'=>$filename,
            'status'=>-1,
            'cm_id'=>$compid,
            'il_type'=>$type,
            'success'=>0,
            'failures'=>0,
            'create_time'=>date('Y-m-d H:i:s')
        );
        if ($type==C('IMPORT_TYPE')['METER'] || $type==C('IMPORT_TYPE')['CAR']) $data['cc_id']=$id;
        
        $result=$this->importModel->addImportLog($data);
        if (!$result){
            $this->error('导入失败！',U('company/index'));
        }
        $data['id']=$result;
        //推送到队列中
        $queue=$typeName.'_import_queue';
        RabbitMQ::publish($queue, json_encode($data));
        $this->success('操作成功！请等待导入完成。',U('index',array('type'=>$type,'id'=>$id)));
    }
    
    /**
     * 获取导入类型所属名称
     * @param integer $type     导入类型
     * @return string
     */
    public function getTypeName($type){
        if ($type==C('IMPORT_TYPE')['METER']) return array('eName'=>'meter','cName'=>'仪表');
        if ($type==C('IMPORT_TYPE')['CAR']) return array('eName'=>'car','cName'=>'车位');
        if ($type==C('IMPORT_TYPE')['CUSTOMER']) return array('eName'=>'customer','cName'=>'用户');
        if ($type==C('IMPORT_TYPE')['HOUSE']) return array('eName'=>'house','cName'=>'房产');
        if ($type==C('IMPORT_TYPE')['BILLS']) return array('eName'=>'bills','cName'=>'账单');
    }
    
    /**
     * 格式化企业ID
     * @param integer $type     导入类型
     * @param integer $id         ID
     * @return unknown
     */
    public function formatCompid($type,$id){
        $compid=$id;
        if ($type==C('IMPORT_TYPE')['METER'] || $type==C('IMPORT_TYPE')['CAR']){
            //根据楼盘ID查询企业ID
            $this->propertyModel=D('property');
            $compid=$this->propertyModel->getCommunityInfo($id)['cm_id'];
        }
        return $compid;
    }
    
}


