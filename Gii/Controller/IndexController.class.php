<?php
namespace Gii\Controller;
use Model\AdminModel;
use Think\Controller;
use Think\Model;

class IndexController extends Controller
{
    public function index()
    {
        $this->display();
    }

    public function generate()
    {
        $csrf = session('csrf');
        session('csrf', md5(microtime().rand()));
        if(empty(I('post.tname')) || empty(I('post._csrf')) || I('post._csrf') != $csrf) {
            echo json_encode(array('status' => 0, 'csrf' => session('csrf')));
            return;
        }

        $tname = I('post.tname');
        $tcname = getTableChineseName($tname);
        $rec = I('post.rec');

        $model = new Model();
        $re = $model->query("SHOW FULL FIELDS FROM {$tname} WHERE field='id'");
        if(empty($re)) {
            $model->execute("ALTER TABLE {$tname} ADD COLUMN `id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST");
        }

        $na = I('post.na');
        $ti = I('post.ti');
        $ex = I('post.ex');
        if(!empty($na) && empty($ti) && empty($ex)) {
            $re = $model->query("SHOW FULL FIELDS FROM {$tname} WHERE field='name'");
            if(empty($re)) {
                $model->execute("ALTER TABLE {$tname} ADD COLUMN `name` VARCHAR(20) NOT NULL COMMENT '名称' AFTER `id`");
            }
        }

        $re = $model->query("SHOW FULL FIELDS FROM {$tname} WHERE field='rank'");
        if(empty($re)) {
            $model->execute("ALTER TABLE {$tname} ADD COLUMN `rank` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序'");
        }

        $re = $model->query("SHOW FULL FIELDS FROM {$tname} WHERE field='is_trash'");
        if(empty($re)) {
            $re = $model->execute("ALTER TABLE {$tname} ADD COLUMN `is_trash` ENUM('0','1') NOT NULL DEFAULT '0' COMMENT '在回收站'");
            if($re !== false) {
                $model->execute("ALTER TABLE {$tname} ADD INDEX is_trash(is_trash)");
            }
        }

        if(!empty(I('post.ks'))) {
            $re = $model->query("SHOW FULL FIELDS FROM {$tname} WHERE field='is_update'");
            if(empty($re)) {
                $model->execute("ALTER TABLE {$tname} ADD COLUMN `is_update` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新'");
            }
        }

        $info = $model->query('SHOW FULL FIELDS FROM '.$tname);
        $_insertFields = array();
        $_updateFields = array();
        $picAttrs = array();
        $opics = array();
        foreach ($info as $v) {
            if(preg_match('/^.*(logo|pic|img)$/', $v['field']) > 0) {
                $picAttrs[] = $v['field'];
                if(empty(preg_match('/^t[0-9]+_[0-9]+_.*$/', $v))) {
                    $opics[] = $v;
                }
                continue;
            }

            if($v['field'] != 'id') {
                $_insertFields[] = $v['field'];
            }

            $_updateFields[] = $v['field'];
        }

        if(in_array('password', $_insertFields)) {
            $_insertFields[] = 'repassword';
        }
        if(in_array('password', $_updateFields)) {
            $_updateFields[] = 'repassword';
        }

        $acap = I('post.acap');
        if(!empty($acap)) {
            $_insertFields[] = 'captcha';
        }
        $ecap = I('post.ecap');
        if(!empty($ecap)) {
            $_updateFields[] = 'captcha';
        }

        $insertFields = implode("','", $_insertFields);
        $insertFields = "'{$insertFields}'";
        $updateFields = implode("','", $_updateFields);
        $updateFields = "'{$updateFields}'";
        $picAttrs = implode("','", $picAttrs);
        $picAttrs = "'{$picAttrs}'";
        $opics = implode("','", $opics);
        $opics = "'{$opics}'";

        $cls = getClassNameFromTable($tname);

        $fk = I('post.fk');
        $fk = explode('&amp;', $fk);

        $tfk = I('post.tfk');
        $tfk = explode('&amp;', $tfk);

        $tcfk = array();
        foreach ($tfk as $v) {
            if(!empty($v)) {
                $re = $model->query("SHOW TABLE STATUS WHERE name='{$v}'");
                $v = trimPrefix($v, C('DB_PREFIX'));
                $tcfk[$v] = $re[0]['comment'];
            }
        }

        $n = I('post.n');

        $tiname = I('post.tiname');
        if(!empty($tiname)) {
            $idat = $model->query('SHOW FULL FIELDS FROM '.$tiname);
            $icls = getClassNameFromTable($tiname);
        }

        $ext = I('post.ext');
        $extc = array();
        if(!empty($ext)) {
            $ext = explode('&amp;', $ext);

            foreach ($ext as $v) {
                $re = $model->query("SHOW TABLE STATUS WHERE `name`='{$v}'");
                $extc[] = $re[0]['comment'];
            }
        }

        $at = I('post.at');
        if(!empty($at)) {
            $at = explode('&amp;', $at);
        }

        $av = I('post.av');
        if(!empty($av)) {
            $av = explode('&amp;', $av);
        }

        $mo = I('post.module');
        $module = val($mo, 'Admin');
        $cdir = $module.'/Controller/';
        if(!is_dir($cdir)) {
            mkdir($cdir, 0777, true);
        }

        $vdir = $module.'/View/'.$cls.'/';
        if(!is_dir($vdir)) {
            mkdir($vdir, 0777, true);
        }

        $controllerPath = $cdir.$cls.'Controller.class.php';
        $modelPath = 'Model/'.$cls.'Model.class.php';
        $indexPath = $vdir.'index.html';
        $trashcanPath = $vdir.'trashcan.html';
        $detailPath = $vdir.'detail.html';
        $addPath = $vdir.'add.html';
        $editPath = $vdir.'edit.html';
        $galleryPath = $vdir.'gallery.html';
        $gallerytrashcanPath = $vdir.'gallerytrashcan.html';

        if(file_exists($modelPath)) {
            $str = file_get_contents($modelPath);

            $sif = $this->customStr($str, '/**自定义insertFields start*/');
            $suf = $this->customStr($str, '/**自定义updateFields start*/');
            $srule = $this->customStr($str, '/**自定义验证start*/');
            $sfun = $this->customStr($str, '/**自定义验证方法start*/');
            $ssmodel = $this->customStr($str, '/**自定义搜索start*/');
            $sres = $this->customStr($str, '/**自定义搜索结果处理start*/');
            $smet = $this->customStr($str, '/**自定义方法start*/');
            $sbi = $this->customStr($str, '/**自定义before_insert start*/');
            $sai = $this->customStr($str, '/**自定义after_insert start*/');
            $sbu = $this->customStr($str, '/**自定义before_update start*/');
            $sau = $this->customStr($str, '/**自定义after_update start*/');
            $sbd = $this->customStr($str, '/**自定义before_delete start*/');
            $sad = $this->customStr($str, '/**自定义after_delete start*/');
            $sew = $this->customStr($str, '/**自定义扩展属性条件start*/');
            $siew = $this->customStr($str, '<!--自定义action start-->');
            $sse = $this->customStr($str, '/**自定义saveExtend start*/');
        }

        if(file_exists($controllerPath)) {
            $str = file_get_contents($controllerPath);
            $sact = $this->customStr($str, '/**自定义action start*/');
        }

        if(file_exists($indexPath)) {
            $str = file_get_contents($indexPath);
            $ssindex = $this->customStr($str, '<!--自定义搜索start-->');
        }

        if(!empty(I('post.controller'))) {
            ob_start();
            require_once 'Gii/tpl/controller.php';
            $str = ob_get_clean();
            file_put_contents($controllerPath, "<?php\n".$str);
        }

        if(!empty(I('post.model'))) {
            ob_start();
            require_once 'Gii/tpl/model.php';
            $str = ob_get_clean();
            file_put_contents($modelPath, "<?php\n".$str);
        }

        if(!empty(I('post.index'))) {
            ob_start();
            require_once 'Gii/tpl/index.php';
            $str = ob_get_clean();
            file_put_contents($indexPath, $str);
        }

        if(!empty(I('post.trashcan'))) {
            ob_start();
            require_once 'Gii/tpl/trashcan.php';
            $str = ob_get_clean();
            file_put_contents($trashcanPath, $str);
        }

        if(!empty(I('post.detail'))) {
            ob_start();
            require_once 'Gii/tpl/detail.php';
            $str = ob_get_clean();
            file_put_contents($detailPath, $str);
        }

        if(!empty(I('post.add'))) {
            ob_start();
            require_once 'Gii/tpl/add.php';
            $str = ob_get_clean();
            file_put_contents($addPath, $str);
        }

        if(!empty(I('post.edit'))) {
            ob_start();
            require_once 'Gii/tpl/edit.php';
            $str = ob_get_clean();
            file_put_contents($editPath, $str);
        }

        if(!empty($tiname) && !empty(I('post.gallery'))) {
            ob_start();
            require_once 'Gii/tpl/gallery.php';
            $str = ob_get_clean();
            file_put_contents($galleryPath, $str);
        }

        if(!empty($tiname) && !empty(I('post.gallerytrashcan'))) {
            ob_start();
            require_once 'Gii/tpl/gallerytrashcan.php';
            $str = ob_get_clean();
            file_put_contents($gallerytrashcanPath, $str);
        }

        if(!empty($ext) && !empty(I('post.extend'))) {
            for ($i = 0; $i < count($ext); $i++) {
                $tab = $ext[$i];
                $tabc = $extc[$i];
                $atv = $at[$i];
                $avv = $av[$i];
                $v2 = strtolower(getClassNameFromTable($tab));
                $path = $vdir.$v2;
                ob_start();
                require 'Gii/tpl/extend.php';  //相同文件要引入多次  只引入一次的话文件输出就会被清空  下次不能再引入相同文件
                $str = ob_get_clean();
                file_put_contents($path, $str);
            }
        }

        echo json_encode(array('status' => 1, 'csrf' => session('csrf')));
    }

    public function rabc()
    {
        ob_start();
        require_once 'Gii/tpl/rabcsql.php';
        $sql = ob_get_clean();
        $model = new Model();
        $model->execute($sql);

        $aModel = new AdminModel();
        $re = $aModel->where(array('name' => 'admin'))->count();
        if(empty($re)) {
            $password = 'admin';
            $password = md5($password);
            $re = $aModel->add(array(
                'name' => 'admin',
                'password' => "{$password}"
            ));
            if($re === false) {
                echo json_encode(array('status' => 0));
                return;
            }
        }

        echo json_encode(array('status' => 1));
    }

    private function customStr($str, $sstr)
    {
        if(!contain($str, $sstr))return '';
        $estr = str_replace('start', 'end', $sstr);
        if(!contain($str, $estr))return '';
        $start = strpos($str,$sstr) + strlen($sstr);
        return substr($str, $start, strpos($str, $estr) - $start);
    }
}