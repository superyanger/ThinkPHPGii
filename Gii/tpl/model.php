namespace Model;
use Think\Page;

class <?=$cls?>Model extends DYCModel
{
    protected $insertFields = array(<?=$insertFields?>/**自定义insertFields start*/<?=$sif?>/**自定义insertFields end*/);
    protected $updateFields = array(<?=$updateFields?>/**自定义updateFields start*/<?=$suf?>/**自定义updateFields end*/);

    protected $_validate = array(
    <?php foreach($info as $v):?>
    <?php if(in_array($v['field'], array('id', 'is_trash', 'is_update')))continue;?>
    <?php if(preg_match('/^.*(logo|pic|img)$/', $v['field']) > 0)continue;?>
    <?php if($v['null'] == 'NO' && $v['default'] === NULL):?>
    <?="\r"?>
        array('<?=$v['field']?>', 'require', '<?=$v['comment']?>不能为空', 1<?php if($v['field'] == 'password'):?>, 'regex', 1<?php endif;?>),
    <?php endif;?>
    <?php if($v['key'] == 'UNI'):?>
    <?="\r"?>
        array('<?=$v['field']?>', '', '<?=$v['comment']?>已存在', 2, 'unique'),
    <?php endif;?>
    <?php if(preg_match('/^.*char\(([0-9]+)\)$/', $v['type'], $matches) > 0 && $v['field'] != 'password'):?>
    <?="\r"?>
        array('<?=$v['field']?>', '1,<?=$matches[1]?>', '<?=$v['comment']?>不能超过<?=$matches[1]?>个字符', 2, 'length'),
    <?php endif;?>
    <?php if(contain($v['type'], 'int')):?>
    <?="\r"?>
        array('<?=$v['field']?>', 'number', '<?=$v['comment']?>必须是数字',2),
    <?php endif;?>
    <?php if(preg_match('/^(decimal|float|double)\([0-9]+,2\).*$/', $v['type'])):?>
    <?="\r"?>
        array('<?=$v['field']?>', 'currency', '<?=$v['comment']?>非法输入',2),
    <?php endif;?>
    <?php if(preg_match('/^(decimal|float|double).*$/', $v['type']) > 0):?>
    <?="\r"?>
        array('<?=$v['field']?>', 'checkDecimal', '<?=$v['comment']?>必须是整数或小数',2,'callback'),
    <?php endif;?>
    <?php if(preg_match('/^.*unsigned$/', $v['type'])):?>
    <?="\r"?>
        array('<?=$v['field']?>', 'checkUnsigned', '<?=$v['comment']?>不能小于0', 2, 'callback'),
    <?php endif;?>
    <?php if($v['field'] == 'email'):?>
    <?="\r"?>
        array('<?=$v['field']?>', 'email', '<?=$v['comment']?>格式不正确', 2),
    <?php endif;?>
    <?php if($v['field'] == 'url'):?>
    <?="\r"?>
        array('<?=$v['field']?>', 'checkUrl', '<?=$v['comment']?>格式不正确', 2, 'callback'),
    <?php endif;?>
    <?php if($v['field'] == 'mobile'):?>
    <?="\r"?>
        array('<?=$v['field']?>', 'checkMobile', '<?=$v['comment']?>格式不正确', 2, 'callback'),
    <?php endif;?>
    <?php if($v['field'] == 'tel'):?>
    <?="\r"?>
        array('<?=$v['field']?>', 'checkTel', '<?=$v['comment']?>格式不正确,参照0XXX-XXXXXXXX,400XXXXXXX,800XXXXXXX', 2, 'callback'),
    <?php endif;?>
    <?php if($v['field'] == 'phone'):?>
    <?="\r"?>
        array('<?=$v['field']?>', 'checkPhone', '<?=$v['comment']?>格式不正确,可输入手机或固话,固话参照0XXX-XXXXXXXX,400XXXXXXX,800XXXXXXX', 2, 'callback'),
    <?php endif;?>
    <?php if($v['field'] == 'idcard'):?>
    <?="\r"?>
        array('<?=$v['field']?>', 'checkIdcard', '<?=$v['comment']?>格式不正确', 2, 'callback'),
    <?php endif;?>
    <?php endforeach;?>
    <?php if(in_array('repassword', $_insertFields)):?>
    <?="\r"?>
        array('repassword', 'password', '两次输入的密码不一致', 1, 'confirm', 1),
    <?php endif;?>
    <?php if(in_array('repassword', $_updateFields)):?>
    <?="\r"?>
        array('repassword', 'password', '两次输入的密码不一致', 1, 'confirm', 2),
    <?php endif;?>
    <?php if(in_array('captcha', $_insertFields)):?>
    <?="\r"?>
        array('captcha', 'checkCaptcha', '验证码错误', 1, 'callback', 1),
    <?php endif;?>
    <?php if(in_array('captcha', $_updateFields)):?>
    <?="\r"?>
        array('captcha', 'checkCaptcha', '验证码错误', 1, 'callback', 2),
    <?php endif;?>
        <?="\r"?>
        /**自定义验证start*/<?=val($srule)?>/**自定义验证end*/
    <?="\r"?>
    );
    protected $patchValidate = true;

    protected $picAttrs = array(<?=$picAttrs?>);

    /**自定义验证方法start*/<?=val($sfun)?>/**自定义验证方法end*/

    <?php if(empty($ex)):?>
    <?php if(!empty($rec)):?>
    <?="\r"?>
    /**
     * 获取无限分类树(没放到回收站的)
     * 非递归包含 也就是说子分类和父分类是同维度的数组元素
     * @param int $parent_id [optional] 父分类id
     * @param string $fields [optional] 查询字段 默认全部字段
     * @param string $order [optional] 排序 默认'rank asc'
     * @return array 无限分类树
     */
    public function tree($parent_id = 0, $fields = '', $order = '')
    {
        $data = $this->filterData($fields, $order);
        return $this->_tree($data, $parent_id);
    }

    /**
     * 获取无限分类树(已放到回收站的)
     * 非递归包含 也就是说子分类和父分类是同维度的数组元素
     * @param string $fields [optional] 查询字段 默认全部字段
     * @param string $order [optional] 排序 默认'rank asc'
     * @return array 无限分类树
     */
    public function treeInTrash($fields = '', $order = '')
    {
        $data = $this->filterData($fields, $order, true);
        return $this->_treeInTrash($data);
    }

    /**
     * 获取递归包含的无限分类树(没放到回收站的)
     * 递归包含指的是子分类是父分类的一个数组元素
     * @param int $parent_id [optional] 父分类id
     * @param string $fields [optional] 查询字段 默认全部字段
     * @param string $order [optional] 排序 默认'rank asc'
     * @return array 无限分类树
     */
    public function treeWithChildren($parent_id = 0, $fields = '', $order = '')
    {
        $data = $this->filterData($fields, $order);
        return $this->_treeWithChildren($data, $parent_id);
    }

    /**
      * 获取所有子分类id
      * @param int $parent_id [optional] 父分类id
      * @param string $order [optional] 排序 默认'rank asc'
      * @param bool $trash [optional] 是否已放入回收站
      * @return array
      */
    public function treeIDs($parent_id = 0, $order = '', $trash = false)
    {
        $data = $this->filterData('id,parent_id', $order, $trash);
        $tree = $this->_tree($data, $parent_id);
        $ids = array();
        foreach($tree as $v) {
            $ids[] = $v['id'];
        }
        return $ids;
    }

    /**
     * 获取不包含指定子树的分类树(没放到回收站的)
     * @param int $subid 指定子树根元素id
     * @param int $parent_id [optional] 要获取的分类树的父分类id
     * @param string $fields [optional] 查询字段 默认所有字段
     * @param string $order [optional] 排序 默认'rank asc'
     * @return array 分类树
     */
    public function treeWithoutSubTree($subid, $parent_id = 0, $fields = '', $order = '')
    {
        $_tree = $this->tree($parent_id, $fields, $order);
        $subIDs = $this->treeIDs($subid, $order);
        $subIDs[] = $subid;
        $tree = array();
        foreach($_tree as $v) {
            if(!in_array($v['id'], $subIDs)) {
                $tree[] = $v;
            }
        }
        return $tree;
    }

    /**
     * 数据库查询操作
     * @param string $fields [optional] 查询字段 默认所有字段
     * @param string $order [optional] 排序 如'id asc'
     * @param bool $trash [optional] 查询的数据是否已放入回收站
     * @return mixed 查询结果
     */
    public function filterData($fields = '', $order = '', $trash = false)
    {
        $order = empty($order)? 'rank asc': $order;
        $where = array(
            'is_trash'  =>  empty($trash)? '0': '1'
        );
        $data = $this->field($fields)->where($where)->order($order)->select();
        return $data;
    }

    /**
     * 由数据库查询结果集获得无限分类树
     * 非递归包含 也就是说子分类和父分类是同维度的数组元素
     * @param mixed $data 数据库查询结果集
     * @param int $parent_id [optional] 父分类id
     * @param int $level [optional] 父分类层级
     * @param bool $clear [optional] 是否清空之前定义的无限分类树$tree(无限分类树$tree定义为static)
     * @return array 无限分类树
     */
    public function _tree($data, $parent_id = 0, $level = 0, $clear = true)
    {
        static $tree = array();
        if($clear) {
            $tree = array();
        }
        if(!empty($data)) {
            foreach($data as $v) {
                if($v['parent_id'] == $parent_id) {
                    $v['level'] = $level;
                    $tree[] = $v;
                    $this->_tree($data, $v['id'], $level + 1, false);
                }
            }
        }
        return $tree;
    }

    /**
     * 由数据库查询结果集获得已放到回收站的无限分类树
     * @param mixed $data 数据库查询结果集
     * @return array 无限分类树
     */
    public function _treeInTrash($data)
    {
        $ids = array();
        foreach($data as $v) {
            $ids[] = $v['id'];
        }

        $parent_ids = array();
        foreach($data as $v) {
            if(!in_array($v['parent_id'], $ids)) {
                $parent_ids[] = $v['parent_id'];
            }
        }
        $parent_ids = array_unique($parent_ids);

        $tree = array();
        foreach($parent_ids as $v) {
            $_tree = $this->_tree($data, $v);
            $tree = array_merge($tree, $_tree);
        }

        return $tree;
    }

    /**
     * 由数据库查询结果集获得递归包含的无限分类树
     * 递归包含指的是子分类是父分类的一个数组元素
     * @param mixed $data 数据库查询结果集
     * @param int $parent_id [optional] 父分类id
     * @param int $level [optional] 父分类层级
     * @return array 无限分类树
     */
    public function _treeWithChildren($data, $parent_id = 0, $level = 0)
    {
        $tree = array();
        foreach($data as $v) {
            if($v['parent_id'] == $parent_id) {
                $v['level'] = $level;
                $v['children'] = $this->_treeWithChildren($data, $v['id'], $level + 1);
                $tree[] = $v;
            }
        }
        return $tree;
    }

    /**
     * 获得所有父分类信息
     * @param int $id 子分类的id
     * @param bool $self [optional] 返回结果是否包含子分类本身
     * @param string $fields [optional] 要返回的字段 默认全部字段
     * @return array
     */
    public function getParents($id, $self = false, $fields = '')
    {
        $ids = $this->getParentIDs($id, $self);
        return $this->getFromWhere(array('id' => array('in', $ids)), $fields);
    }

    /**
     * 获得所有父分类的id
     * @param int $id 子分类的id
     * @param bool $self [optional] 返回结果是否包含子分类本身
     * @return array
     */
    public function getParentIDs($id, $self = false)
    {
        $ids[] = 0;
        if(!empty($self)) {
            $ids[] = $id;
        }
        $parent_id = $this->getFieldByID($id, 'parent_id');
        while(!empty($parent_id)) {
            $ids[] = $parent_id;
            $parent_id = $this->getFieldByID($parent_id, 'parent_id');
        }
        return array_reverse($ids);
    }
    <?php else:?>
    <?="\r"?>
    /**
     * 分页搜索
     * @param string $fields [optional] 查询字段 默认全部字段
     * @param array $where [optional] 查询条件
     * @param string $order [optional] 排序 默认'rank asc'
     * @param int $listRows [optional] 每页显示的记录数
     * @param bool $trash [optional] 是否已放入回收站
     * @return array 返回数组$data
     * $data['data']存储搜索得到的所有记录
     * $data['pageCount']存储总页数
     * $data['page']存储分页html代码
     */
    public function search($fields = '', $where = array(), $order = '', $listRows = <?=val($n, 0)?>, $trash = false)
    {
        <?php if(!empty(I('post.ks'))):?>
        <?="\r"?>
        $key = I('request.key');
        if(!empty($key)) {
            $ids = keySearch($key, '<?=strtolower($cls)?>', array(
                'host' => C('SPHINX_HOST'),
                'port' => C('SPHINX_PORT')
            ));
            $where['id'] = array('in', $ids);
        }
        <?php endif;?>

        <?php foreach ($info as $v):?>
        <?php if(!empty($v['key']) && !in_array($v['field'], array('id', 'is_update', 'is_trash'))):?>
        <?="\r"?>
        $<?=$v['field']?> = I('request.<?=$v['field']?>');
        if(!empty($<?=$v['field']?>)) {
            <?php $fcls = getClassNameFromTable(trimSuffix($v['field'], '_id'));
                if(method_exists('\\Model\\'.$fcls.'Model', 'treeIDs')):?>
            <?="\r"?>
            $<?=strtolower($fcls)?>Model = new <?=$fcls?>Model();
            $ids = $<?=strtolower($fcls)?>Model->treeIDs($<?=$v['field']?>);
            $ids[] = $<?=$v['field']?>;
            $ids = val($ids, array(''));
            $where['<?=$v['field']?>'] = array('in', $ids);
            <?php else:?>
            <?="\r"?>
            $where['<?=$v['field']?>'] = array('eq', "{$<?=$v['field']?>}");
            <?php endif;?>
        <?="\r"?>
        }
        <?php endif;?>
        <?php endforeach;?>

        if(empty($trash)) {
            $where['is_trash'] = array('eq', '0');
        } else {
            $where['is_trash'] = array('eq', '1');
        }

        if(empty($order)) {
            $order = 'rank asc';
        }

        /**自定义搜索start*/<?=val($ssmodel)?>/**自定义搜索end*/
        <?="\r"?>

        if(empty($listRows)) {
            $listRows = $this->count();
        }

        $totalRows = $this->where($where)->count();
        $page = new Page($totalRows, $listRows);
        $data = $this->field($fields)->where($where)->limit($page->firstRow, $listRows)->order($order)->select();

        /**自定义搜索结果处理start*/<?=$sres?>/**自定义搜索结果处理end*/

        return array(
            'data' => $data,
            'pageCount' => ceil($totalRows/$listRows),
            'page' => $page->show()
        );
    }
    <?php endif;?>
    <?php endif;?>

    protected function _before_insert(&$data, $option)
    {
        <?php foreach($info as $v):?>
        <?php if(preg_match('/^.*(logo|pic|img)$/', $v['field']) > 0):?>
        <?php if(preg_match('/^t[0-9]+_[0-9]+.*$/', $v['field']) > 0)continue;?>
        <?php if($v['null'] == 'NO' && $v['default'] === NULL):?>
        <?="\r"?>
        if(empty($_FILES['<?=$v['field']?>']) || $_FILES['<?=$v['field']?>']['error'] > 0) {
            $this->error['<?=$v['field']?>'] = '请上传图片';
            return false;
        }
        <?php endif;?>
        <?php endif;?>
        <?php if(preg_match('/^.*text$/', $v['type']) > 0):?>
        <?="\r"?>
        $data['<?=$v['field']?>'] = removeXSS($_POST['<?=$v['field']?>']);
        <?php endif;?>
        <?php if($v['null'] != 'NO' && $v['default'] == NULL):?>
        <?="\r"?>
        if(empty($data['<?=$v['field']?>'])) {
            unset($data['<?=$v['field']?>']);
        }
        <?php endif;?>
        <?php if($v['field'] == 'password'):?>
        <?="\r"?>
        if(!empty($_POST['<?=$v['field']?>'])) {
            $data['<?=$v['field']?>'] = md5($_POST['<?=$v['field']?>']);
        }
        <?php endif;?>
        <?php endforeach;?>
        <?="\r"?>

        if(parent::_before_insert($data, $option) === false) {
            return false;
        }

        /**自定义before_insert start*/<?=$sbi?>/**自定义before_insert end*/

        return true;
    }

    protected function _after_insert($data, $option)
    {
        parent::_after_insert($data, $option);
        /**自定义after_insert start*/<?=$sai?>/**自定义after_insert end*/
    }

    protected function _before_update(&$data, $option)
    {
        <?php foreach($info as $v):?>
        <?php if(preg_match('/^.*text$/', $v['type']) > 0):?>
        <?="\r"?>
        $data['<?=$v['field']?>'] = removeXSS($_POST['<?=$v['field']?>']);
        <?php endif;?>
        <?php if($v['null'] != 'NO' && $v['default'] == NULL):?>
        <?="\r"?>
        if(empty($data['<?=$v['field']?>'])) {
            unset($data['<?=$v['field']?>']);
        }
        <?php endif;?>
        <?php if(preg_match('/^enum\(\'\',\'([^\',]+)\'\)$/', $v['type']) > 0):?>
        <?="\r"?>
        if(empty($data['<?=$v['field']?>'])) {
            $data['<?=$v['field']?>'] = '';
        }
        <?php endif;?>
        <?php if($v['field'] == 'password'):?>
        <?="\r"?>
        if(!empty($data['<?=$v['field']?>'])) {
            $data['<?=$v['field']?>'] = md5($_POST['<?=$v['field']?>']);
        } else {
            unset($data['<?=$v['field']?>']);
        }
        <?php endif;?>
        <?php endforeach;?>

        <?php if(!empty(I('post.ks'))):?>
        <?="\r"?>
        $data['is_update'] = 2;
        <?php endif;?>
        <?="\r"?>

        if(parent::_before_update($data, $option) === false) {
            return false;
        }

        /**自定义before_update start*/<?=$sbu?>/**自定义before_update end*/

        return true;
    }

    protected function _after_update($data, $option)
    {
        parent::_after_update($data, $option);

        <?php if(!empty(I('post.ks'))):?>
        <?="\r"?>
        if(!empty($data['id'])) {
            $id = $data['id'];
            if(!is_array($id)) {
                updateKeySearch('goods', $id, array(
                    'host' => C('SPHINX_HOST'),
                    'port' => C('SPHINX_PORT')
                ));
            } else {
                if($id[0] == 'in') {
                    $ids = $id[1];
                    foreach($ids as $v) {
                        updateKeySearch('goods', $v, array(
                            'host' => C('SPHINX_HOST'),
                            'port' => C('SPHINX_PORT')
                        ));
                    }
                }
            }
        }
        <?php endif;?>
        <?="\r"?>

        /**自定义after_update start*/<?=$sau?>/**自定义after_update end*/
    }

    protected function _before_delete($option)
    {
        if($this->lock() === false) {
            return false;
        }
        $this->startTrans();

        <?php if(!empty($tfk[0])):?>
        <?="\r"?>
        $where = $option['where'];
        if(empty($where['id'])) {
            $ids = $this->getIDs($where);
            $ids = implode("','", $ids);
        } else {
            $ids = $where['id'];
        }
        <?php endif;?>
        <?php foreach ($tfk as $v):
            $v = trimPrefix($v, C('DB_PREFIX'));
            if(!empty($v)):$v2 = getClassNameFromTable($v);?>
        <?="\r"?>
        $<?=strtolower($v2)?>Model = new <?=$v2?>Model();
        $re = $<?=strtolower($v2)?>Model->where("<?=trimPrefix($tname, C('DB_PREFIX'))?>_id in ('{$ids}')")->delete();
        if($re === false) {
            $this->rollback();
            $this->lock(false);
            return false;
        }
        <?php endif;endforeach;?>
        <?="\r"?>

        if(parent::_before_delete($option) === false) {
            return false;
        }

        /**自定义before_delete start*/<?=$sbd?>/**自定义before_delete end*/

        return true;
    }

    protected function _after_delete($option)
    {
        $this->commit();
        $this->lock(false);

        parent::_after_delete($option);

        /**自定义after_delete start*/<?=$sad?>/**自定义after_delete end*/
    }

    public function trash($where)
    {
        if($this->lock() === false) {
            return false;
        }
        $this->startTrans();

        <?php if(!empty($tfk[0])):?>
        <?="\r"?>
        $ids = $this->getIDs($where);
        $ids = implode("','", $ids);
        <?php endif;?>
        <?php foreach ($tfk as $v):
            $v = trimPrefix($v, C('DB_PREFIX'));
            if(!empty($v)):$v2 = getClassNameFromTable($v);?>
        <?="\r"?>
        $<?=strtolower($v2)?>Model = new <?=$v2?>Model();
        $re = $<?=strtolower($v2)?>Model->trash("<?=trimPrefix($tname, C('DB_PREFIX'))?>_id in ('{$ids}')");
        if($re === false) {
            $this->rollback();
            $this->lock(false);
            return false;
        }
        <?php endif;endforeach;?>
        <?="\r"?>
        $re = parent::trash($where);
        if($re === false) {
            $this->rollback();
            $this->lock(false);
            return false;
        }
        $this->commit();
        $this->lock(false);
        return true;
    }

    public function recover($where)
    {
        if($this->lock() === false) {
            return false;
        }
        $this->startTrans();

        <?php if(!empty($tfk[0])):?>
        <?="\r"?>
        $ids = $this->getIDs($where);
        $ids = implode("','", $ids);
        <?php endif;?>
        <?php foreach ($tfk as $v):
            $v = trimPrefix($v, C('DB_PREFIX'));
            if(!empty($v)):$v2 = getClassNameFromTable($v);?>
        <?="\r"?>
        $<?=strtolower($v2)?>Model = new <?=$v2?>Model();
        $re = $<?=strtolower($v2)?>Model->recover("<?=trimPrefix($tname, C('DB_PREFIX'))?>_id in ('{$ids}')");
        if($re === false) {
            $this->rollback();
            $this->lock(false);
            return false;
        }
        <?php endif;endforeach;?>
        <?="\r"?>
        $re = parent::recover($where);
        if($re === false) {
            $this->rollback();
            $this->lock(false);
            return false;
        }
        $this->commit();
        $this->lock(false);
        return true;
    }

    /**
     * 获得外键关联表所有记录的id和name(无限分类表可以获得所有字段)
     * @return array
     */
    public function getForeignKeys()
    {
        $fk = array();
        <?php foreach ($fk as $v):
            $v = trimSuffix($v, '_id');
            if(!empty($v)):$v2 = getClassNameFromTable($v);?>
        <?="\r"?>
        $<?=strtolower($v2)?>Model = new <?=$v2?>Model();
        <?php if(method_exists("\\Model\\".$v2."Model", 'search')):?>
        <?="\r"?>
        $<?=strtolower($v2)?>Data = $<?=strtolower($v2)?>Model->search('id,name', array(), '', 0);
        $fk['<?=strtolower($v2)?>Data'] = $<?=strtolower($v2)?>Data['data'];
        <?php else:?>
        <?="\r"?>
        $<?=strtolower($v2)?>Data = $<?=strtolower($v2)?>Model->tree();
        $fk['<?=strtolower($v2)?>Data'] = $<?=strtolower($v2)?>Data;
        <?php endif;?>
        <?="\r"?>
        <?php endif;endforeach;?>
        <?="\r"?>
        return $fk;
    }

    <?php if(!empty($ex) && !empty($at) && !empty($av)):
        $atv = trimPrefix($at[0], C('DB_PREFIX'));
        $atv2 = getClassNameFromTable($at[0]);
        $avv = trimPrefix($av[0], C('DB_PREFIX'));

        $_ps = array();
        foreach ($fk as $v){
            if($v != $atv.'_id') {
                $_ps[] = '$'.trimSuffix($v, '_id').'ID';
            }
        }
        $ps = implode(', ', $_ps);?>
    <?="\r"?>
    /**
     * 保存修改后的<?=$tcname?>
     <?php foreach($_ps as $v):?>
     <?="\r"?>
     * @param int <?=$v?>
     <?php endforeach;?>
     <?="\r"?>
     * @return bool true成功 false失败
     */
    public function saveExtend(<?=$ps?>)
    {
        $this->startTrans();

        /**自定义saveExtend start*/<?=$sse?>/**自定义saveExtend end*/

        foreach(I('post.attr') as $k => $v) {
            if(is_array($v)) {
                foreach($v as $v2) {
                    if(!empty($v2)) {
                        $re = $this->where(array(
                            <?php foreach ($fk as $v):
                            if($v != $atv.'_id'):?>
                            <?="\r"?>
                            '<?=$v?>' => $<?=trimSuffix($v, '_id')?>ID,
                            <?php endif;endforeach;?>
                            <?="\r"?>
                            '<?=$atv?>_id' => $k,
                            '<?=$avv?>' => $v2
                        ))->find();
                        if(empty($re)) {
                            $re = $this->add(array(
                                <?php foreach ($fk as $v):
                                if($v != $atv.'_id'):?>
                                <?="\r"?>
                                '<?=$v?>' => $<?=trimSuffix($v, '_id')?>ID,
                                <?php endif;endforeach;?>
                                <?="\r"?>
                                '<?=$atv?>_id' => $k,
                                '<?=$avv?>' => trim($v2)
                            ));
                            if($re === false) {
                                $this->rollback();
                                return false;
                            }
                        }
                    }
                }
            } else {
                $re = $this->where(array(
                    <?php foreach ($fk as $v):
                    if($v != $atv.'_id'):?>
                    <?="\r"?>
                    '<?=$v?>' => $<?=trimSuffix($v, '_id')?>ID,
                    <?php endif;endforeach;?>
                    <?="\r"?>
                    '<?=$atv?>_id' => $k
                ))->find();
                if(empty($re)) {
                    if(!empty($v)) {
                        $re = $this->add(array(
                            <?php foreach ($fk as $v):
                            if($v != $atv.'_id'):?>
                            <?="\r"?>
                            '<?=$v?>' => $<?=trimSuffix($v, '_id')?>ID,
                            <?php endif;endforeach;?>
                            <?="\r"?>
                            '<?=$atv?>_id' => $k,
                            '<?=$avv?>' => trim($v)
                        ));
                        if($re === false) {
                            $this->rollback();
                            return false;
                        }
                    }
                } else {
                    if(!empty($v)) {
                        $re = $this->where(array(
                            <?php foreach ($fk as $v):
                            if($v != $atv.'_id'):?>
                            <?="\r"?>
                            '<?=$v?>' => $<?=trimSuffix($v, '_id')?>ID,
                            <?php endif;endforeach;?>
                            <?="\r"?>
                            '<?=$atv?>_id' => $k
                        ))->setField('<?=$avv?>', trim($v));
                        if($re === false) {
                            $this->rollback();
                            return false;
                        }
                    } else {
                        $re = $this->delete($re['id']);
                        if($re === false) {
                            $this->rollback();
                            return false;
                        }
                    }
                }
            }
        }
        $this->commit();
        return true;
    }

    <?php $re = $model->query("SHOW FULL FIELDS FROM {$at[0]} WHERE `field`='value_type'");
        $valueType = empty($re)? '': 'a.value_type,';?>
    <?="\r"?>
    /**
     * 获取所有<?=$tcname?>
     * 调用时需传入外键值 然后根据传入的外键值搜索
     <?php foreach($_ps as $v):?>
     <?="\r"?>
     * @param int <?=$v?>
     <?php endforeach;?>
     <?="\r"?>
     * @return array key为属性名称
     * 如果是唯一属性返回的是二维数组 如果是单选属性返回的是三位数组
     * 属性的id,名称,属性值id,属性值均会出现在返回数组中
     * 举例
     * array(
     *     '颜色' => array(
     *         array('id' => 1, 'name' => '颜色', 'value_type' => '单选', 'aid' => 1, 'value' => '玫瑰金'),
     *         array('id' => 1, 'name' => '颜色', 'value_type' => '单选', 'aid' => 2, 'value' => '土豪金')
     *     ),
     *     '分辨率' => array('id' => 2, 'name' => '分辨率', 'aid' => 3, 'value' => '1080*1920')
     * );
     * 上述数组中id表示属性id,name表示属性名称,aid表示属性值id,value表示属性值,value_type表示属性是否单选(缺省表示唯一属性)
     */
    public function search(<?=$ps?>)
    {
        $where = array();
        /**自定义扩展属性条件start*/<?=val($sew)?>/**自定义扩展属性条件end*/
        $<?=strtolower($atv2)?>Model = new <?=$atv2?>Model();
        $_data = $<?=strtolower($atv2)?>Model->alias('a')->field('a.id,a.name,<?=$valueType?>b.id aid,b.<?=$avv?>')
                    ->join("LEFT JOIN <?=$tname?> b ON a.id=b.<?=$atv?>_id<?php foreach ($fk as $v):if($v != $atv.'_id'):?> AND b.<?=$v?>={$<?=trimSuffix($v, '_id')?>ID}<?php endif;;endforeach;?>")
                    ->where($where)->order('a.rank')->select();
        $data = array();
        foreach($_data as $v) {
            if(!empty($v['value_type']) && $v['value_type'] == '单选') {
                $data[$v['name']][] = $v;
            } else {
                $data[$v['name']] = $v;
            }
        }
        return $data;
    }
    <?php endif;?>
    <?="\r"?>

    /**自定义方法start*/<?=val($smet)?>/**自定义方法end*/
<?="\r"?>
}