<?php
namespace Model;
use Think\Page;

class AuthModel extends DYCModel
{
    protected $insertFields = array('name','parent_id','rank','is_trash','controller','action'/**自定义insertFields start*//**自定义insertFields end*/);
    protected $updateFields = array('id','name','parent_id','rank','is_trash','controller','action'/**自定义updateFields start*//**自定义updateFields end*/);

    protected $_validate = array(
                        
        array('name', 'require', '名称不能为空', 1),
                
        array('name', '1,20', '名称不能超过20个字符', 2, 'length'),
                                                                            
        array('parent_id', 'number', '父权限必须是数字',2),
                
        array('parent_id', 'checkUnsigned', '父权限不能小于0', 2, 'callback'),
                                                                
        array('rank', 'number', '排序必须是数字',2),
                
        array('rank', 'checkUnsigned', '排序不能小于0', 2, 'callback'),
                                                                
        array('controller', '1,150', '控制器不能超过60个字符', 2, 'length'),
                                                                        
        array('action', '1,150', '方法不能超过60个字符', 2, 'length'),
                                                                        
        /**自定义验证start*//**自定义验证end*/
    
    );
    protected $patchValidate = true;

    protected $picAttrs = array('');

    /**自定义验证方法start*//**自定义验证方法end*/

            
    public function tree($parent_id = 0, $fields = '', $order = '')
    {
        $data = $this->filterData($fields, $order);
        return $this->_tree($data, $parent_id);
    }

    public function treeInTrash($fields = '', $order = '')
    {
        $data = $this->filterData($fields, $order, true);
        return $this->_treeInTrash($data);
    }

    public function treeWithChildren($parent_id = 0, $fields = '', $order = '')
    {
        $data = $this->filterData($fields, $order);
        return $this->_treeWithChildren($data, $parent_id);
    }

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

    public function filterData($fields = '', $order = '', $trash = false)
    {
        $order = empty($order)? 'rank asc': $order;
        $where = array(
            'is_trash'  =>  empty($trash)? '0': '1'
        );
        $data = $this->field($fields)->where($where)->order($order)->select();
        return $data;
    }

    public function _tree($data, $parent_id = 0, $level = 0, $clear = true)
    {
        static $tree = array();
        if($clear) {
            $tree = array();
        }
        foreach($data as $v) {
            if($v['parent_id'] == $parent_id) {
                $v['level'] = $level;
                $tree[] = $v;
                $this->_tree($data, $v['id'], $level + 1, false);
            }
        }
        return $tree;
    }

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

    public function getParents($id, $self = false, $fields = '')
    {
        $ids = $this->getParentIDs($id, $self);
        return $this->getFromWhere(array('id' => array('in', $ids)), $fields);
    }

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
        
    protected function _before_insert(&$data, $option)
    {
                                                                                                                                                                                                                                                                                                        
        if(parent::_before_insert($data, $option) === false) {
            return false;
        }

        /**自定义before_insert start*//**自定义before_insert end*/

        return true;
    }

    protected function _after_insert($data, $option)
    {
        parent::_after_insert($data, $option);
        /**自定义after_insert start*//**自定义after_insert end*/
    }

    protected function _before_update(&$data, $option)
    {
                                                                                                                                                                                                                                                                                                
                
        if(parent::_before_update($data, $option) === false) {
            return false;
        }

        /**自定义before_update start*//**自定义before_update end*/

        return true;
    }

    protected function _after_update($data, $option)
    {
        parent::_after_update($data, $option);

                
        /**自定义after_update start*//**自定义after_update end*/
    }

    protected function _before_delete($option)
    {
        if($this->lock() === false) {
            return false;
        }
        $this->startTrans();

                
        $where = $option['where'];
        if(empty($where['id'])) {
            $ids = $this->getIDs($where);
            $ids = implode("','", $ids);
        } else {
            $ids = $where['id'];
        }
                        
        $roleauthModel = new RoleAuthModel();
        $re = $roleauthModel->where("auth_id in ('{$ids}')")->delete();
        if($re === false) {
            $this->rollback();
            $this->lock(false);
            return false;
        }
                
        if(parent::_before_delete($option) === false) {
            return false;
        }

        /**自定义before_delete start*//**自定义before_delete end*/

        return true;
    }

    protected function _after_delete($option)
    {
        $this->commit();
        $this->lock(false);

        parent::_after_delete($option);

        /**自定义after_delete start*//**自定义after_delete end*/
    }

    public function trash($where)
    {
        if($this->lock() === false) {
            return false;
        }
        $this->startTrans();

                
        $ids = $this->getIDs($where);
        $ids = implode("','", $ids);
                        
        $roleauthModel = new RoleAuthModel();
        $re = $roleauthModel->trash("auth_id in ('{$ids}')");
        if($re === false) {
            $this->rollback();
            $this->lock(false);
            return false;
        }
                
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

                
        $ids = $this->getIDs($where);
        $ids = implode("','", $ids);
                        
        $roleauthModel = new RoleAuthModel();
        $re = $roleauthModel->recover("auth_id in ('{$ids}')");
        if($re === false) {
            $this->rollback();
            $this->lock(false);
            return false;
        }
                
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

    public function getForeignKeys()
    {
        $fk = array();
                
        return $fk;
    }

        
    /**自定义方法start*//**自定义方法end*/

}