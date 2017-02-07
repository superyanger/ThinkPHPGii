<?php
namespace Model;
use Think\Page;
use Think\Verify;

class AdminModel extends DYCModel
{
    protected $insertFields = array('name','password','rank','is_trash','repassword'/**自定义insertFields start*//**自定义insertFields end*/);
    protected $updateFields = array('id','name','password','rank','is_trash','repassword'/**自定义updateFields start*//**自定义updateFields end*/);

    protected $_validate = array(
                        
        array('name', 'require', '名称不能为空', 1),
                
        array('name', '1,20', '名称不能超过20个字符', 2, 'length'),
                                                                
        array('password', 'require', '密码不能为空', 1, 'regex', 1),
                                                                                    
        array('rank', 'number', '排序必须是数字',2),
                    
        array('rank', 'checkUnsigned', '排序不能小于0', 2, 'callback'),
                                            
        array('repassword', 'password', '两次输入的密码不一致', 1, 'confirm', 1),
            
        array('repassword', 'password', '两次输入的密码不一致', 1, 'confirm', 2),
                    
        /**自定义验证start*/
        array('password', '6,20', '密码6-20位', 2, 'length'),
        /**自定义验证end*/
    
    );
    protected $patchValidate = true;

    protected $picAttrs = array('');

    /**自定义验证方法start*//**自定义验证方法end*/

            
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
                                                                                                                                
        if(!empty($_POST['password'])) {
            $data['password'] = md5($_POST['password']);
        }
                                                                                                        
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
                                                                                                                                
        if(!empty($data['password'])) {
            $data['password'] = md5($_POST['password']);
        } else {
            unset($data['password']);
        }
                                                                                                
                
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
                        
        $adminroleModel = new AdminRoleModel();
        $re = $adminroleModel->where("admin_id in ('{$ids}')")->delete();
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
                        
        $adminroleModel = new AdminRoleModel();
        $re = $adminroleModel->trash("admin_id in ('{$ids}')");
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
                        
        $adminroleModel = new AdminRoleModel();
        $re = $adminroleModel->recover("admin_id in ('{$ids}')");
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

    /**
     * 获得外键关联表所有记录的id和name(无限分类表可以获得所有字段)
     * @return array
     */
    public function getForeignKeys()
    {
        $fk = array();
                
        return $fk;
    }

        
    /**自定义方法start*/
    public function login()
    {
        $verify = new Verify();
        if($verify->check($_POST['captcha']) === false) {
            $this->error['captcha'] = '验证码错误';
            return false;
        }

        $data = $this->where(array('name' => I('post.name')))->find();
        if(empty($data)) {
            $this->error['name'] = '用户不存在';
            return false;
        }
        if($data['password'] != md5($_POST['password'])) {
            $this->error['password'] = '密码错误';
            return false;
        }
        if($data['login_status'] == '1' && time() - strtotime($data['last_login']) < getSessionExpire()) {
            $this->error['name'] = '用户已登陆';
            return false;
        }

        $this->afterLogin($data['id']);
        return true;
    }

    public function afterLogin($aid)
    {
        $re = $this->where('id='.$aid)->save(array(
            'login_status' => '1',
            'last_login' => date('Y-m-d H:i:s')
        ));
        if($re !== false) {
            session('aid', $aid);
            $aname = $this->getName($aid);
            session('aname', $aname);
        }
    }

    public function logout()
    {
        $aid = session('aid');
        if(!empty($aid)) {
            $re = $this->where('id='.$aid)->setField('login_status', '0');
            if($re !== false) {
                session('aid', null);
                session('aname', null);
            }
        }
    }

    public function changePassword()
    {
        if(empty($_POST['password'])) {
            $this->error['password'] = '新密码不能为空';
            return false;
        }
        $len = strlen($_POST['password']);
        if($len < 6 || $len > 20) {
            $this->error['password'] = '新密码6-20位';
            return false;
        }

        if($_POST['password'] != $_POST['repassword']) {
            $this->error['password'] = '确认新密码输入错误';
            return false;
        }

        $aid = session('aid');
        $re = $this->field('password')->find($aid);
        if(md5($_POST['old_password']) != $re['password']) {
            $this->error['old_password'] = '原密码输入错误';
            return false;
        }

        return $this->where(array('id' => $aid))->setField('password', md5($_POST['password']));
    }
    /**自定义方法end*/

}