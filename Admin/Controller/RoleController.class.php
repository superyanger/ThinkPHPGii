<?php
namespace Admin\Controller;
use Model\RoleModel;
use Think\Controller;

class RoleController extends BaseController
{
    public function add()
    {
        $model = new RoleModel();
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

        $this->assign('tcname', '角色');

        $this->display();
    }

    public function index()
    {
        session('csrf', md5(microtime().rand()));

        $model = new RoleModel();
                
        $data = $model->search('id,name');
        $this->assign($data);
        
        $fk = $model->getForeignKeys();
        $this->assign($fk);

        $this->assign('tcname', '角色');

        session('back_url', __SELF__);

        $this->display();
    }

    public function trashcan()
    {
        session('csrf', md5(microtime().rand()));

        $model = new RoleModel();
                
        $data = $model->search('id,name', array(), '', 0, true);
        $this->assign($data);
        
        $this->assign('tcname', '角色');

        $this->display();
    }

    public function detail()
    {
        $id = (int)I('get.id');
        $model = new RoleModel();
        $data = $model->find($id);

                        
        $this->assign('data', $data);

        $this->assign('tcname', '角色');

        $this->display();
    }

    public function edit()
    {
        $id = (int)I('get.id');
        $model = new RoleModel();
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

        $this->assign('tcname', '角色');

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
        $model = new RoleModel();
                
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

        $model = new RoleModel();
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
        $model = new RoleModel();
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

        $model = new RoleModel();
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
        $model = new RoleModel();
                
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

        $model = new RoleModel();
        $ids = I('post.ids');
        $ids = implode("','", $ids);
        if($model->where("id in ('{$ids}')")->delete() !== false) {
            echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
        } else {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
        }
    }

    
        
    /**自定义action start*/
    public function auth()
    {
        $roleID = (int)I('get.role_id');
        $model = new \Model\RoleAuthModel();
        if(!empty(I('post.')) && !empty(I('post._csrf')) && I('post._csrf') == session('csrf')) {
            session('csrf', null);
            if($model->saveAuth($roleID) !== false) {
                echo "<script>alert('成功');</script>";
            }
        }

        session('csrf', md5(microtime().rand()));
        $aModel = new \Model\AuthModel();
        $aData = $aModel->tree();
        $this->assign('aData', $aData);

        $raData = $model->getFromWhere(array('role_id' => $roleID), 'auth_id');
        $this->assign('raData', $raData);

        $this->assign('tcname', '角色');
        $this->display();
    }
    /**自定义action end*/
}