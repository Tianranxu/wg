<?php
namespace Home\Controller;

use Think\Controller;

class CompleteController extends Controller
{
    public function index(){

        $CompleteMod = M('completed_work');
        $allData = $CompleteMod->select();
        foreach($allData as $data){
            if(empty($data['form_id'])){
                $compids[$data['id']] = '';
            }else{
                $compids[$data['id']] = substr($data['form_id'], 0, strpos($data['form_id'], '_'));
            }

        }

        $sql_1 = 'UPDATE fx_completed_work SET cm_id = CASE id';
        $sql_2 = '';
        $ids = '';
        $sql_3 = ' END WHERE id IN (';
        foreach($compids as $key=>$cid){
            $sql_2 .= " WHEN {$key} THEN '{$cid}'";
            $ids .= $key.',';
        }
        $ids = rtrim($ids, ',');
        $sql = $sql_1.$sql_2.$sql_3.$ids.')';
        $result = $CompleteMod->execute($sql);
        $result = $result===0?true:$result;
        if($result){

            echo 'success'.PHP_EOL;
            echo $CompleteMod->getLastSql();
        }else{
            echo 'fail'.PHP_EOL;
            echo $CompleteMod->getLastSql();
        }


       // $this->display('index');
    }
}