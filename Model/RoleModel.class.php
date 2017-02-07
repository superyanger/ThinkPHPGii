<?php
namespace Model;
use Think\Page;

class RoleModel extends DYCModel
{
    protected $insertFields = array('name','rank','is_trash'/**自定义insertFields start*//**自定义insertFields end*/);
    protected $updateFields = array('id','name','rank','is_trash'/**自定义updateFields start*//**自定义updateFields end*/);

    protected $_validate = array(
                                array('name', 'require', '名称不能为空', 1),
                        array('name', '1,20', '名称不能超过20个字符', 2, 'length'),
                                                                                    array('rank', 'number', '排序必须是数字',2),
                        array('rank', 'checkUnsigned', '排序不能小于0', 2, 'callback'),
                                                                        /**自定义验证start*//**自定义验证end*/
        );
    protected $patchValidate = true;

    protected $picAttrs = array('');

    /**自定义验证方法start*//**自定义验证方法end*/

                public function search($fields = '', $where = array(), $order = '', $listRows = 0, $trash = false)
    {
        
                                                                        
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

                        $where = $option['where'];
        if(empty($where['id'])) {
            $ids = $this->getIDs($where);
            $ids = implode("','", $ids);
        } else {
            $ids = $where['id'];
        }
                                $roleauthModel = new RoleAuthModel();
        $re = $roleauthModel->where("role_id in ('{$ids}')")->delete();
        if($re === false) {
            $this->rollback();
            $this->lock(false);
            return false;
        }
                        $adminroleModel = new AdminRoleModel();
        $re = $adminroleModel->where("role_id in ('{$ids}')")->delete();
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
        $re = $roleauthModel->trash("role_id in ('{$ids}')");
        if($re === false) {
            $this->rollback();
            $this->lock(false);
            return false;
        }
                        $adminroleModel = new AdminRoleModel();
        $re = $adminroleModel->trash("role_id in ('{$ids}')");
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
        $re = $roleauthModel->recover("role_id in ('{$ids}')");
        if($re === false) {
            $this->rollback();
            $this->lock(false);
            return false;
        }
                        $adminroleModel = new AdminRoleModel();
        $re = $adminroleModel->recover("role_id in ('{$ids}')");
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