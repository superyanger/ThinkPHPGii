<?php
namespace Model;
use Think\Page;

class AdminRoleModel extends DYCModel
{
    protected $insertFields = array('role_id','admin_id','rank','is_trash'/**自定义insertFields start*//**自定义insertFields end*/);
    protected $updateFields = array('id','role_id','admin_id','rank','is_trash'/**自定义updateFields start*//**自定义updateFields end*/);

    protected $_validate = array(
                        
        array('role_id', 'require', '角色不能为空', 1),
                    
        array('role_id', 'number', '角色必须是数字',2),
                
        array('role_id', 'checkUnsigned', '角色不能小于0', 2, 'callback'),
                                                    
        array('admin_id', 'require', '管理员不能为空', 1),
                    
        array('admin_id', 'number', '管理员必须是数字',2),
                
        array('admin_id', 'checkUnsigned', '管理员不能小于0', 2, 'callback'),
                                                                
        array('rank', 'number', '排序必须是数字',2),
                
        array('rank', 'checkUnsigned', '排序不能小于0', 2, 'callback'),
                                                                
        /**自定义验证start*//**自定义验证end*/
    
    );
    protected $patchValidate = true;

    protected $picAttrs = array('');

    /**自定义验证方法start*//**自定义验证方法end*/

            
    public function search($fields = '', $where = array(), $order = '', $listRows = 0, $trash = false)
    {
        
                                        
        $role_id = I('request.role_id');
        if(!empty($role_id)) {
                        
            $where['role_id'] = array('eq', "{$role_id}");
                    
        }
                                
        $admin_id = I('request.admin_id');
        if(!empty($admin_id)) {
                        
            $where['admin_id'] = array('eq', "{$admin_id}");
                    
        }
                                                
        if(empty($trash)) {
            $where['is_trash'] = array('eq', '0');
        } else {
            $where['is_trash'] = array('eq', '1');
        }

        if(empty($order)) {
            $order = 'rank asc';
        }

        /**自定义搜索start*//**自定义搜索end*/
        
        if(empty($listRows)) {
            $listRows = $this->count();
        }

        $totalRows = $this->where($where)->count();
        $page = new Page($totalRows, $listRows);
        $data = $this->field($fields)->where($where)->limit($page->firstRow, $listRows)->order($order)->select();

        /**自定义搜索结果处理start*//**自定义搜索结果处理end*/

        return array(
            'data' => $data,
            'pageCount' => ceil($totalRows/$listRows),
            'page' => $page->show()
        );
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
                
        $adminModel = new AdminModel();
                
        $adminData = $adminModel->search('id,name', array(), '', 0);
        $fk['adminData'] = $adminData['data'];
                
                
        $roleModel = new RoleModel();
                
        $roleData = $roleModel->search('id,name', array(), '', 0);
        $fk['roleData'] = $roleData['data'];
                
                
        return $fk;
    }

        
    /**自定义方法start*/
    public function saveRole($adminID)
    {
        $this->startTrans();
        $re = $this->where(array('admin_id' => $adminID))->delete();
        if($re === false) {
            $this->rollback();
            return false;
        }

        $role = I('post.role');
        foreach($role as $v) {
            $re = $this->add(array(
                'role_id' => $v,
                'admin_id' => $adminID
            ));
            if($re === false) {
                $this->rollback();
                return false;
            }
        }

        $this->commit();
        return true;
    }
    /**自定义方法end*/

}