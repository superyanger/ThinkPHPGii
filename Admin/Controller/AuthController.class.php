<?php
namespace Admin\Controller;
use Model\AuthModel;
use Think\Controller;

class AuthController extends BaseController
{
    public function add()
    {
        $model = new AuthModel();
        if(!empty(I('post.')) && !empty(I('post._csrf')) && I('post._csrf') == session('csrf')) {
            session('csrf', null);
            if($model->create(I('post.')) !== false && $model->add() !== false) {
                redirect(val(I('get.back'), __CONTROLLER__.'/index'));
                return;
            }
            $this->assign('error', $model->getError());
        }

        session('csrf', md5(microtime().rand()));

                
        $cData = $model->tree();
        $this->assign('cData', $cData);
        
        $fk = $model->getForeignKeys();
        $this->assign($fk);

        $this->assign('tcname', '权限');

        $this->display();
    }

    public function index()
    {
        session('csrf', md5(microtime().rand()));

        $model = new AuthModel();
                
        $data = $model->tree();
        $this->assign('data', $data);
        
        $fk = $model->getForeignKeys();
        $this->assign($fk);

        $this->assign('tcname', '权限');

        session('back_url', __SELF__);

        $this->display();
    }

    public function trashcan()
    {
        session('csrf', md5(microtime().rand()));

        $model = new AuthModel();
                
        $data = $model->treeInTrash();
        $this->assign('data', $data);
        
        $this->assign('tcname', '权限');

        $this->display();
    }

    public function detail()
    {
        $id = (int)I('get.id');
        $model = new AuthModel();
        $data = $model->find($id);

                        
        $data['parent_name'] = $model->getName($data['parent_id']);
                
        $this->assign('data', $data);

        $this->assign('tcname', '权限');

        $this->display();
    }

    public function edit()
    {
        $id = (int)I('get.id');
        $model = new AuthModel();
        if(!empty(I('post.')) && !empty(I('post._csrf')) && I('post._csrf') == session('csrf')) {
            session('csrf', null);
            if($model->create(I('post.')) !== false && $model->save() !== false) {
                redirect(val(I('get.back'), __CONTROLLER__.'/index'));
                return;
            }
            $this->assign('error', $model->getError());
        }

        session('csrf', md5(microtime().rand()));

                
        $cData = $model->treeWithoutSubTree($id);
        $this->assign('cData', $cData);
        
        $data = $model->find($id);
        $this->assign('data', $data);

        $fk = $model->getForeignKeys();
        $this->assign($fk);

        $this->assign('tcname', '权限');

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
        $model = new AuthModel();
                
        $ids = $model->treeIDs($id);
        $ids[] = $id;
        $ids = implode("','", $ids);
        if($model->trash("id in ('{$ids}')") !== false) {
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

        $model = new AuthModel();
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
        $model = new AuthModel();
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

        $model = new AuthModel();
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
        $model = new AuthModel();
                
        $ids = $model->treeIDs($id, '', true);
        $ids[] = $id;
        $ids = implode("','", $ids);
        if($model->where("id in ('{$ids}')")->delete() !== false) {
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

        $model = new AuthModel();
        $ids = I('post.ids');
        $ids = implode("','", $ids);
        if($model->where("id in ('{$ids}')")->delete() !== false) {
            echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
        } else {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
        }
    }

    
        
    /**自定义action start*//**自定义action end*/
}