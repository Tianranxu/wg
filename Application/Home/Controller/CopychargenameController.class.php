<?php
namespace Home\Controller;
use Think\Controller;
class CopychargenameController extends Controller
{
//增加费项字段运行的脚本（只需运行一次）
    public function index()
    {
        $billMod = D('Accounts');
        $result = $billMod->copyChargesName();
        if ($result) {
            $this->success('运行成功', '/home/user/login');
        } else {
            $this->error('运行失败,请重新运行', '/home/user/login', 3);
        }

    }
}