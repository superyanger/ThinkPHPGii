<?php
namespace Model;
use Think\Model;
use Think\Verify;

class DYCModel extends Model
{
    protected $pics = array(); //已添加的和数据库原先存在的图片
    protected $picAttrs = array(); //存储图片字段

    /**
     * 验证值是否为非负数
     * @param string $arg 待验证的值
     * @return bool true非负数 false负数
     */
    public function checkUnsigned($arg)
    {
        return $arg >= 0;
    }

    /**
     * 验证手机号码格式是否正确
     * @param string $arg 待验证的手机号码
     * @return bool true正确 false不正确
     */
    public function checkMobile($arg)
    {
        return preg_match('/^(0|86|17951)?(13[0-9]|15[0-9]|17[0-9]|18[0-9]|14[57])[0-9]{8}$/', $arg) > 0;
    }

    /**
     * 验证固定电话格式是否正确
     * @param string $arg 待验证的固定电话
     * @return bool true正确 false不正确
     */
    public function checkTel($arg)
    {
        return preg_match('/^0(10|2[0-9]|[3-9][0-9]{2})\-[1-9][0-9]{6,7}$/', $arg) > 0
            || preg_match('/^(4|8)00[0-9]{7}$/', $arg) > 0;
    }

    /**
     * 验证电话号码格式是否正确
     * @param string $arg 待验证的电话号码 可以是手机号码或固定电话
     * @return bool true正确 false不正确
     */
    public function checkPhone($arg)
    {
        return $this->checkMobile($arg) || $this->checkTel($arg);
    }

    /**
     * 验证url格式是否正确
     * @param string $arg 待验证的url
     * @return bool true正确 false不正确
     */
    public function checkUrl($arg)
    {
        return preg_match('/^https?:\/\/(([a-zA-Z0-9_-])+(\.)?)*(:\d+)?(\/((\.)?(\?)?=?(&amp;)?[a-zA-Z0-9_-](\?)?)*)*$/i', $arg) > 0;
    }

    /**
     * 验证身份证号格式是否正确
     * @param string $arg 待验证的身份证号
     * @return bool true正确 false不正确
     */
    public function checkIdcard($arg)
    {
        return preg_match('/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{4}$/', $arg) > 0;
    }

    /**
     * 验证码格式是否正确
     * @param string $arg 用户输入的验证码
     * @return bool true正确 false不正确
     */
    public function checkCaptcha($arg)
    {
        $verify = new Verify();
        return $verify->check($arg);
    }

    protected function _before_insert(&$data, $option)
    {
        return $this->uploadPics($data);
    }

    protected function _before_update(&$data, $option)
    {
        $this->pics += $this->getDBPics($option['where']);

        return $this->uploadPics($data);
    }

    protected function _before_delete($option)
    {
        $this->pics += $this->getDBPics($option['where']);

        return true;
    }

    protected function _after_delete($option)
    {
        $this->deleteIms($this->pics);
    }

    public function add($data)
    {
        $re = parent::add($data);
        if($re === false) {
            $this->deleteIms($this->pics);
        }
        return $re;
    }

    public function save($data, $option = array())
    {
        $re = parent::save($data);
        $this->deleteIms($this->getNoDBPics($option['where']));
        return $re;
    }

    /**
     * 删除数组中指定的所有图片
     * @param array $ims <p>
     * 存放要删除的图片的路径 相对于上传图片根目录
     * </p>
     */
    public function deleteIms($ims)
    {
        $uploadpath = C('UPLOAD_PATH');
        $uploadpath = rtrim($uploadpath, '/').'/';
        foreach ($ims as $v) {
            $impath = $uploadpath.$v;
            if(file_exists($impath)) {
                unlink($impath);
            }
        }
    }

    /**
     * 将符合条件的记录放入回收站
     * @param $where string|array <p>
     * 条件
     * </p>
     * @return bool
     */
    public function trash($where)
    {
        return $this->where($where)->save(array(
            'is_trash' => '1'
        ));
    }

    /**
     * 将符合条件的记录从回收站恢复
     * @param $where string|array <p>
     * 条件
     * </p>
     * @return bool
     */
    public function recover($where)
    {
        return $this->where($where)->save(array(
            'is_trash' => '0'
        ));
    }

    /**
     * 获取数据库中已经存在的图片
     * @param string|array $where <p>
     * 查询条件
     * </p>
     * @return array
     */
    protected function getDBPics($where)
    {
        $field = implode(',', $this->picAttrs);
        $re = $this->where($where)->field($field)->select();
        $_pics = array();
        foreach ($re as $v) {
            foreach ($v as $v2) {
                $_pics[] = $v2;
            }
        }
        return $_pics;
    }

    /**
     * 获取$pics数组中数据库不存在的图片
     * @param string|array $where <p>
     * 查询条件
     * </p>
     * @return array
     */
    protected function getNoDBPics($where)
    {
        $dbPics = $this->getDBPics($where);
        $_pics = array();
        foreach ($this->pics as $v) {
            if(!in_array($v, $dbPics)) {
                $_pics[] = $v;
            }
        }
        return $_pics;
    }

    /**
     * 获取原图和缩略图字段的名称 以及待生成的缩略图尺寸
     * @return array
     */
    protected function getOpicsAndTpics()
    {
        $opics = array();
        $tpics = array();
        $thumb = array();
        foreach ($this->picAttrs as $v) {
            if(preg_match('/^t([0-9]+)_([0-9]+)_(.*)$/', $v, $matches) > 0) {
                $thumb[$matches[3]][] = array($matches[1], $matches[2]);
                $tpics[$matches[3]][] = $v;
            } else {
                $opics[] = $v;
            }
        }
        return array(
            'opics' => $opics,
            'tpics' => $tpics,
            'thumb' => $thumb
        );
    }

    /**
     * 上传图片 将上传图片的路径保存到即将写入数据库的数据中
     * @param array $data <p>
     * 即将写入数据库的数据
     * </p>
     * @return bool
     */
    protected function uploadPics(&$data)
    {
        $pics = $this->getOpicsAndTpics();
        foreach ($pics['opics'] as $v) {
            if(!empty($_FILES[$v]) && $_FILES[$v]['error'] < 1) {
                $thumb = $pics['thumb'][$v];
                $re = uploadPic($v, trimPrefix($this->getTableName(), C('DB_PREFIX')), $thumb);
                if($re['status'] !== false) {
                    $i = 0;
                    $data[$v] = $re['images'][$i];
                    $this->pics[] = $data[$v];
                    foreach($pics['tpics'][$v] as $v2) {
                        ++$i;
                        $data[$v2] = $re['images'][$i];
                        $this->pics[] = $data[$v2];
                    }
                } else {
                    $this->error[$v] = $re['error'];
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 数据库查询操作
     * @param string|array $where [optional] <p>
     * 查询条件
     * </p>
     * @param string $fields [optional] <p>
     * 查询字段 默认所有字段
     * </p>
     * @param string $order [optional] <p>
     * 排序 如'id asc'
     * </p>
     * @return array 结果集如果只有一个字段 返回的是一维数组 如果有多个字段 返回的是二维数组
     */
    public function getFromWhere($where = '', $fields = '', $order = '')
    {
        $_data = $this->field($fields)->where($where)->order($order)->select();
        if(contain($fields, ',') || in_array($fields, array('', '*'))) {
            $data = $_data;
        } else {
            $fields = trimPrefix($fields, 'DISTINCT');
            $fields = trimPrefix($fields, 'distinct');
            $fields = trim($fields);
            $data = array();
            foreach ($_data as $v) {
                $data[] = $v[$fields];
            }
        }
        return $data;
    }

    /**
     * 按指定的顺序获取符合条件的所有记录的id
     * @param string|array $where [optional] <p>
     * 查询条件
     * </p>
     * @param string $order [optional] <p>
     * 排序 如'id asc'
     * </p>
     * @return array
     */
    public function getIDs($where = '', $order = 'id desc')
    {
        return $this->getFromWhere($where, 'DISTINCT id', $order);
    }

    /**
     * 按指定的顺序获取符合条件的所有记录的name
     * @param string|array $where [optional] <p>
     * 查询条件 默认没有任何条件
     * </p>
     * @param string $order [optional] <p>
     * 排序 如'id asc'
     * </p>
     * @return array
     */
    public function getNamesFromWhere($where = '', $order = '')
    {
        return $this->getFromWhere($where, 'DISTINCT name', $order);
    }

    /**
     * 获取指定id的记录中指定字段的值
     * @param $id <p>
     * 指定id
     * </p>
     * @param $field <p>
     * 指定字段
     * </p>
     * @return string|null 如果字段值不为空 返回字段值 否则返回null
     */
    public function getFieldByID($id, $field)
    {
        $re = $this->field($field)->find($id);
        if(!empty($re[$field])) {
            return $re[$field];
        } else {
            return null;
        }
    }

    /**
     * 获取指定id记录的name
     * @param $id <p>
     * 指定id
     * </p>
     * @return string|null 如果name不为空 返回name 否则返回null
     */
    public function getName($id)
    {
        return $this->getFieldByID($id, 'name');
    }

    /**
     * 数据表加锁/解锁
     * @param bool $lock [optional] <p>
     * true加锁 false解锁
     * </p>
     * @return bool true成功 false失败
     */
    public function lock($lock = true)
    {
        $lockpath = C('LOCK_PATH');
        $lockpath = rtrim($lockpath, '/').'/';
        $lockpath = $lockpath.trimPrefix($this->getTableName(), C('DB_PREFIX')).'.lock';
        $fp = fopen($lockpath, 'w+');
        return flock($fp, $lock? LOCK_EX: LOCK_UN);
    }
}