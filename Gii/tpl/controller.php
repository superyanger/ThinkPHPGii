namespace <?=$module?>\Controller;
use Model\<?=$cls?>Model;

class <?=$cls?>Controller extends BaseController
{
    public function add()
    {
        $model = new <?=$cls?>Model();
        if(!empty(I('post.')) && !empty(I('post._csrf')) && I('post._csrf') == session('csrf')) {
            session('csrf', null);
            if($model->create(I('post.')) !== false && $model->add() !== false) {
                redirect(val(I('get.back'), __CONTROLLER__.'/index'));
                return;
            }
            $this->assign('error', $model->getError());
        }

        session('csrf', md5(microtime().rand()));

        <?php if(!empty($rec)):?>
        <?="\r"?>
        $cData = $model->tree();
        $this->assign('cData', $cData);
        <?php endif;?>

        $fk = $model->getForeignKeys();
        $this->assign($fk);

        $this->assign('tcname', '<?=$tcname?>');

        $this->display();
    }

    public function index()
    {
        session('csrf', md5(microtime().rand()));

        $model = new <?=$cls?>Model();
        <?php if(!empty($rec)):?>
        <?="\r"?>
        $data = $model->tree();
        $this->assign('data', $data);
        <?php else:?>
        <?="\r"?>
        $data = $model->search('id,name');
        $this->assign($data);
        <?php endif;?>

        $fk = $model->getForeignKeys();
        $this->assign($fk);

        $this->assign('tcname', '<?=$tcname?>');

        session('back_url', __SELF__);

        $this->display();
    }

    public function trashcan()
    {
        session('csrf', md5(microtime().rand()));

        $model = new <?=$cls?>Model();
        <?php if(!empty($rec)):?>
        <?="\r"?>
        $data = $model->treeInTrash();
        $this->assign('data', $data);
        <?php else:?>
        <?="\r"?>
        $data = $model->search('id,name', array(), '', <?=val($n, 0)?>, true);
        $this->assign($data);
        <?php endif;?>

        $this->assign('tcname', '<?=$tcname?>');

        $this->display();
    }

    public function detail()
    {
        $id = (int)I('get.id');
        $model = new <?=$cls?>Model();
        $data = $model->find($id);

        <?php foreach ($fk as $v):
            $v = trimSuffix($v, '_id');
            if(!empty($v)):$v2 = getClassNameFromTable($v);?>
        <?="\r"?>
        $<?=strtolower($v2)?>Model = new \Model\<?=$v2?>Model();
        $data['<?=$v?>_name'] = $<?=strtolower($v2)?>Model->field('name')->find($data['<?=$v?>_id'])['name'];
        <?php endif;endforeach;?>
        <?php if(!empty($rec)):?>
        <?="\r"?>
        $data['parent_name'] = $model->getName($data['parent_id']);
        <?php endif;?>
        <?="\r"?>
        $this->assign('data', $data);

        $this->assign('tcname', '<?=$tcname?>');

        $this->display();
    }

    public function edit()
    {
        $id = (int)I('get.id');
        $model = new <?=$cls?>Model();
        if(!empty(I('post.')) && !empty(I('post._csrf')) && I('post._csrf') == session('csrf')) {
            session('csrf', null);
            if($model->create(I('post.')) !== false && $model->save() !== false) {
                redirect(val(I('get.back'), __CONTROLLER__.'/index'));
                return;
            }
            $this->assign('error', $model->getError());
        }

        session('csrf', md5(microtime().rand()));

        <?php if(!empty($rec)):?>
        <?="\r"?>
        $cData = $model->treeWithoutSubTree($id);
        $this->assign('cData', $cData);
        <?php endif;?>

        $data = $model->find($id);
        $this->assign('data', $data);

        $fk = $model->getForeignKeys();
        $this->assign($fk);

        $this->assign('tcname', '<?=$tcname?>');

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
        $model = new <?=$cls?>Model();
        <?php if(empty($rec)):?>
        <?="\r"?>
        if($model->trash('id='.$id) !== false) {
            echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
        } else {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
        }
        <?php else:?>
        <?="\r"?>
        $ids = $model->treeIDs($id);
        $ids[] = $id;
        $ids = implode("','", $ids);
        if($model->trash("id in ('{$ids}')") !== false) {
            echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
        } else {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
        }
        <?php endif;?>
    <?="\r"?>
    }

    public function trashgroup()
    {
        if(empty(I('post.')) || empty(I('post._csrf')) || I('post._csrf') != session('csrf')) {
            echo json_encode(array('status' => 0));
        }

        session('csrf', null);

        $model = new <?=$cls?>Model();
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
        $model = new <?=$cls?>Model();
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

        $model = new <?=$cls?>Model();
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
        $model = new <?=$cls?>Model();
        <?php if(empty($rec)):?>
        <?="\r"?>
        if($model->delete($id) !== false) {
            echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
        } else {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
        }
        <?php else:?>
        <?="\r"?>
        $ids = $model->treeIDs($id, '', true);
        $ids[] = $id;
        $ids = implode("','", $ids);
        if($model->where("id in ('{$ids}')")->delete() !== false) {
            echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
        } else {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
        }
        <?php endif;?>
    <?="\r"?>
    }

    public function delgroup()
    {
        $csrf = session('csrf');
        session('csrf', md5(microtime().rand()));
        if(empty(I('post.')) || empty(I('post._csrf')) || I('post._csrf') != $csrf) {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
            return;
        }

        $model = new <?=$cls?>Model();
        $ids = I('post.ids');
        $ids = implode("','", $ids);
        if($model->where("id in ('{$ids}')")->delete() !== false) {
            echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
        } else {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
        }
    }

    <?php if(!empty($tiname)):?>
    <?="\r"?>
    public function gallery()
    {
        $model = new \Model\<?=$icls?>Model();
        if(!empty($_POST)) {
            if($model->create(I('post.')) === false || $model->add() === false) {
                session('csrf', null);
                $this->assign('error', $model->getError());
            }
        }

        session('csrf', md5(microtime().rand()));

        $data = $model->search();
        $this->assign($data);

        $this->assign('tcname', '<?=$tcname?>');

        $this->display();
    }

    public function gallerytrashcan()
    {
        session('csrf', md5(microtime().rand()));

        $model = new \Model\<?=$icls?>Model();
        $data = $model->search('', array(), '', <?=val($n, 0)?>, true);
        $this->assign($data);

        $this->assign('tcname', '<?=$tcname?>');

        $this->display();
    }

    public function trashpic()
    {
        $csrf = session('csrf');
        session('csrf', md5(microtime().rand()));
        if(empty(I('post.')) || empty(I('post._csrf')) || I('post._csrf') != $csrf) {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
            return;
        }

        $id = I('post.id');
        $model = new \Model\<?=$icls?>Model();
        if($model->trash('id='.$id) !== false) {
            echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
        } else {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
        }
    }

    public function trashpicgroup()
    {
        $csrf = session('csrf');
        session('csrf', md5(microtime().rand()));
        if(empty(I('post.')) || empty(I('post._csrf')) || I('post._csrf') != $csrf) {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
            return;
        }

        $ids = I('post.ids');
        $ids = implode("','", $ids);
        $model = new \Model\<?=$icls?>Model();
        if($model->trash("id in ('{$ids}')") !== false) {
            echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
        } else {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
        }
    }

    public function recoverpic()
    {
        $csrf = session('csrf');
        session('csrf', md5(microtime().rand()));
        if(empty(I('post.')) || empty(I('post._csrf')) || I('post._csrf') != $csrf) {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
            return;
        }

        session('csrf', null);

        $id = I('post.id');
        $model = new \Model\<?=$icls?>Model();
        if($model->recover('id='.$id) !== false) {
            echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
        } else {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
        }
    }

    public function recoverpicgroup()
    {
        $csrf = session('csrf');
        session('csrf', md5(microtime().rand()));
        if(empty(I('post.')) || empty(I('post._csrf')) || I('post._csrf') != $csrf) {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
            return;
        }

        $ids = I('post.ids');
        $ids = implode("','", $ids);
        $model = new \Model\<?=$icls?>Model();
        if($model->recover("id in ('{$ids}')") !== false) {
            echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
        } else {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
        }
    }

    public function rmpic()
    {
        $csrf = session('csrf');
        session('csrf', md5(microtime().rand()));
        if(empty(I('post.')) || empty(I('post._csrf')) || I('post._csrf') != $csrf) {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
            return;
        }

        $id = I('post.id');
        $model = new \Model\<?=$icls?>Model();
        if($model->delete($id) !== false) {
            echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
        } else {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
        }
    }

    public function rmpicgroup()
    {
        $csrf = session('csrf');
        session('csrf', md5(microtime().rand()));
        if(empty(I('post.')) || empty(I('post._csrf')) || I('post._csrf') != $csrf) {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
            return;
        }

        $ids = I('post.ids');
        $ids = implode("','", $ids);
        $model = new \Model\<?=$icls?>Model();
        if($model->where("id in ('{$ids}')")->delete() !== false) {
            echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
        } else {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
        }
    }
    <?php endif;?>

    <?php if(!empty($ext)):
        for($i = 0; $i < count($ext); $i++):
        if(!empty($ext[$i]) && !empty($at[$i]) && !empty($av[$i])):
        $v = $ext[$i];
        $v2 = getClassNameFromTable($v);
        $atv = $at[$i];
        $atv2 = trimPrefix($atv, C('DB_PREFIX'));
        $atv3 = getClassNameFromTable($atv);
        $avv = $av[$i];?>
    <?="\r"?>
    public function <?=strtolower($v2)?>()
    {
        $<?=strtolower($cls)?>ID = I('get.<?=trimPrefix($tname, C('DB_PREFIX'))?>_id');
        $<?=strtolower($v2)?>Model = new \Model\<?=$v2?>Model();
        if(!empty(I('post.')) && !empty(I('post._csrf')) && I('post._csrf') == session('csrf')) {
            session('csrf', null);
            $<?=strtolower($v2)?>Model->saveExtend($<?=strtolower($cls)?>ID);
        }

        session('csrf', md5(microtime().rand()));
        $data = $<?=strtolower($v2)?>Model->search($<?=strtolower($cls)?>ID);
        $this->assign('data', $data);
        $this->assign('tcname', '<?=$tcname?>');
        $this->display();
    }

    <?php $re = $model->query("SHOW FULL FIELDS FROM {$atv} WHERE `field`='value_type'");if(!empty($re)):?>
    <?="\r"?>
    public function <?=strtolower($v2)?>del()
    {
        $csrf = session('csrf');
        session('csrf', md5(microtime().rand()));
        if(empty(I('post.')) || empty(I('post._csrf')) || I('post._csrf') != $csrf) {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
            return;
        }

        $id = I('post.id');
        $<?=strtolower($v2)?>Model = new \Model\<?=$v2?>Model();
        if($<?=strtolower($v2)?>Model->delete($id) !== false) {
            echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
        } else {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
        }
    }
    <?php endif;?>
    <?php endif;endfor;endif;?>
    <?="\r"?>

    /**自定义action start*/<?=$sact?>/**自定义action end*/
}