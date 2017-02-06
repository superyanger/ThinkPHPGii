<?php
namespace Admin\Controller;


use Think\Controller;

class BaseController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        header('Content-type:text/html;charset=utf-8');

        $aid = session('aid');
        $controller = CONTROLLER_NAME;
        $action = ACTION_NAME;
        if(empty($aid)) {
            if($controller != 'Admin' || !in_array($action, array('login', 'verify'))) {
                redirect(__MODULE__.'/Admin/login');
            }
            return;
        }

        if($controller == 'Admin' && in_array($action, array('login', 'verify'))) {
            redirect($_SERVER['HTTP_REFERER'], 3, '您已登陆');
            return;
        }

        $aModel = new \Model\AuthModel();
        $aData = $aModel->treeWithChildren();
        $this->assign('aData', $aData);

        $aname = session('aname');
        if($aname != 'admin') {
            $arModel = new \Model\AdminRoleModel();
            $_arData = $arModel->alias('a')->field('c.controller,c.action')
                ->join('LEFT JOIN __ROLE_AUTH__ b ON a.role_id=b.role_id LEFT JOIN __AUTH__ c ON b.auth_id=c.id')
                ->where('a.admin_id='.$aid)->select();
            $arData = array();
            foreach($_arData as $v) {
                $actions = str_replace('，', ',', $v['action']);
                $actions = explode(',', $actions);
                foreach($actions as $v2) {
                    $arData[$v['controller']][] = $v2;
                }
            }
            if($controller != 'Index' && ($controller != 'Admin' || !in_array($action, array('logout', 'changePassword')))
                && !in_array($action, $arData[$controller])) {
                echo  '没有权限';
                exit();
            }
        }
    }
}