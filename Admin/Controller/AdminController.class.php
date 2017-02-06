<?php
namespace Admin\Controller;
use Model\AdminModel;
use Think\Controller;
use Think\Verify;

class AdminController extends BaseController
{
    public function add()
    {
        $model = new AdminModel();
        if(!empty(I('post.')) && !empty(I('post._csrf')) && I('post._csrf') == session('csrf')) {
            session('csrf', null);
            if($model->create(I('post.')) !== false && $model->add() !== false) {
                redirect(val(I('get.back'), __CONTROLLER__.'/index'));
                return;
            }
            $this->assign('error', $model->getError());
        }

        session('csrf', md5(microtime().rand()));

        
        $fk = $model->getForeignKeys();
        $this->assign($fk);

        $this->assign('tcname', '管理员');

        $this->display();
    }

    public function index()
    {
        session('csrf', md5(microtime().rand()));

        $model = new AdminModel();
                
        $data = $model->search('id,name');
        $this->assign($data);
        
        $fk = $model->getForeignKeys();
        $this->assign($fk);

        $this->assign('tcname', '管理员');

        session('back_url', __SELF__);

        $this->display();
    }

    public function trashcan()
    {
        session('csrf', md5(microtime().rand()));

        $model = new AdminModel();
                
        $data = $model->search('id,name', array(), '', 0, true);
        $this->assign($data);
        
        $this->assign('tcname', '管理员');

        $this->display();
    }

    public function detail()
    {
        $id = (int)I('get.id');
        $model = new AdminModel();
        $data = $model->find($id);

                        
        $this->assign('data', $data);

        $this->assign('tcname', '管理员');

        $this->display();
    }

    public function edit()
    {
        $id = (int)I('get.id');
        $model = new AdminModel();
        if(!empty(I('post.')) && !empty(I('post._csrf')) && I('post._csrf') == session('csrf')) {
            session('csrf', null);
            if($model->create(I('post.')) !== false && $model->save() !== false) {
                redirect(val(I('get.back'), __CONTROLLER__.'/index'));
                return;
            }
            $this->assign('error', $model->getError());
        }

        session('csrf', md5(microtime().rand()));

        
        $data = $model->find($id);
        $this->assign('data', $data);

        $fk = $model->getForeignKeys();
        $this->assign($fk);

        $this->assign('tcname', '管理员');

        $this->display();
    }

    public function trash()
    {
        $csrf = session('csrf');
        session('csrf', md5(microtime().rand()));
        if(empty(I('post.')) || empty(I('post._csrf')) || I('post._csrf') != $csrf) {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
            return;
        }

        $id = I('post.id');
        $model = new AdminModel();
                
        if($model->trash('id='.$id) !== false) {
            echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
        } else {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
        }
            
    }

    public function trashgroup()
    {
        if(empty(I('post.')) || empty(I('post._csrf')) || I('post._csrf') != session('csrf')) {
            echo json_encode(array('status' => 0));
        }

        session('csrf', null);

        $model = new AdminModel();
        $ids = I('post.ids');
        $ids = implode("','", $ids);
        if($model->trash("id in ('{$ids}')") !== false) {
            echo json_encode(array('status' => 1));
        } else {
            echo json_encode(array('status' => 0));
        }
    }

    public function recover()
    {
        $csrf = session('csrf');
        session('csrf', md5(microtime().rand()));
        if(empty(I('post.')) || empty(I('post._csrf')) || I('post._csrf') != $csrf) {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
            return;
        }

        $id = I('post.id');
        $model = new AdminModel();
        if($model->recover('id='.$id) !== false) {
            echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
        } else {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
        }
    }

    public function recovergroup()
    {
        $csrf = session('csrf');
        session('csrf', md5(microtime().rand()));
        if(empty(I('post.')) || empty(I('post._csrf')) || I('post._csrf') != $csrf) {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
            return;
        }

        $model = new AdminModel();
        $ids = I('post.ids');
        $ids = implode("','", $ids);
        if($model->recover("id in ('{$ids}')") !== false) {
            echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
        } else {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
        }
    }

    public function del()
    {
        $csrf = session('csrf');
        session('csrf', md5(microtime().rand()));
        if(empty(I('post.')) || empty(I('post._csrf')) || I('post._csrf') != $csrf) {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
            return;
        }

        $id = I('post.id');
        $model = new AdminModel();
                
        if($model->delete($id) !== false) {
            echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
        } else {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
        }
            
    }

    public function delgroup()
    {
        $csrf = session('csrf');
        session('csrf', md5(microtime().rand()));
        if(empty(I('post.')) || empty(I('post._csrf')) || I('post._csrf') != $csrf) {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
            return;
        }

        $model = new AdminModel();
        $ids = I('post.ids');
        $ids = implode("','", $ids);
        if($model->where("id in ('{$ids}')")->delete() !== false) {
            echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
        } else {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
        }
    }

    
        
    /**自定义action start*/
    public function role()
    {
        $adminID = (int)I('get.admin_id');
        $model = new \Model\AdminRoleModel();
        if(!empty(I('post.')) && !empty(I('post._csrf')) && I('post._csrf') == session('csrf')) {
            session('csrf', null);
            if($model->saveRole($adminID) !== false) {
                echo "<script>alert('成功');</script>";
            }
        }

        session('csrf', md5(microtime().rand()));
        $rModel = new \Model\RoleModel();
        $rData = $rModel->search('id,name');
        $rData = $rData['data'];
        $this->assign('rData', $rData);

        $arData = $model->getFromWhere(array('admin_id' => $adminID), 'role_id');
        $this->assign('arData', $arData);

        $this->assign('tcname', '管理员');
        $this->display();
    }

    public function verify()
    {
        $cfg = val(C('CAPTCHA_OPTION'), array());
        $verify = new Verify($cfg);
        $verify->entry();
    }

    public function login()
    {
        if(!empty(I('post.')) && !empty(I('post._csrf')) && I('post._csrf') == session('csrf')) {
            session('csrf', null);
            $mModel = new \Model\AdminModel();
            if($mModel->login() !== false) {
                redirect(__MODULE__.'/Index');
                return;
            }
            $this->assign('error', $mModel->getError());
        }

        session('csrf', md5(microtime().rand()));

        $this->display();
    }

    public function logout()
    {
        $mModel = new \Model\AdminModel();
        $mModel->logout();
        redirect(__CONTROLLER__.'/login');
    }

    public function changePassword()
    {
        if(!empty(I('post.')) && !empty(I('post._csrf')) && I('post._csrf') == session('csrf')) {
            session('csrf', null);
            $mModel = new \Model\AdminModel();
            if($mModel->changePassword() !== false) {
                redirect(val(I('get.back'), __MODULE__.'/Index'));
                return;
            }
            $this->assign('error', $mModel->getError());
        }

        session('csrf', md5(microtime().rand()));

        $this->display();
    }
    /**自定义action end*/
}