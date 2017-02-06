<?php
use Think\Image;

/**
 * 获取图片的绝对路径
 * @param string $pic <p>
 * 从数据库中获取的图片路径 相对于存放图片的根目录
 * </p>
 * @return string 获取到的图片的绝对路径
 */
function picUrl($pic)
{
    if(empty($pic)) {
        return '';
    }
    $uploadpath = C('UPLOAD_PATH');
    $uploadpath = rtrim($uploadpath, '/').'/';
    $impath = BASE.$uploadpath.$pic;
    return $impath;
}

/**
 * 生成img标签
 * @param string $pic <p>
 * 从数据库中获取的图片路径 相对于存放图片的根目录
 * </p>
 * @param string $cls [optional] <p>
 * img标签中指定的class属性值
 * </p>
 * @param string $alt [optional] <p>
 * 占位图
 * </p>
 * @return string img标签
 */
function showPic($pic, $cls = '', $alt = '')
{
    if(empty($pic)) {
        return '';
    }
    $impath = picUrl($pic);
    return "<img src='{$impath}' class='{$cls}' alt='{$alt}'>";
}

/**
 * 使用coreseek进行关键词搜索
 * @param string $key <p>
 * 要搜索的关键词
 * </p>
 * @param string $index <p>
 * coreseek数据源名称
 * </p>
 * @param array $ucfg [optional] <p>
 * coreseek搜索选项
 * $ucfg['host']指定要连接的coreseek服务器的ip地址或主机名
 * $ucfg['port']指定要连接的coreseek服务器的端口
 * </p>
 * @return array 所有符合搜索条件的记录的id
 */
function keySearch($key, $index, $ucfg = array())
{
    $cfg = array(
        'host' => val($ucfg['host'], 'localhost'),
        'port' => val($ucfg['port'], 9312)
    );

    require_once 'Common/Common/sphinxapi.php';
    $client = new SphinxClient();
    $client->SetServer($cfg['host'], $cfg['port']);
    $client->SetFilter('is_update', array(0, 1));
    $re = $client->Query($key, $index);
    if(empty($re['matches'])) {
        return array('');
    } else {
        return array_keys($re['matches']);
    }
}

/**
 * 对数据库进行update操作后更新coreseek数据源
 * @param string $index <p>
 * coreseek数据源名称
 * </p>
 * @param int $id <p>
 * 进行update操作的记录的id
 * </p>
 * @param array $ucfg [optional] <p>
 * coreseek搜索选项
 * $ucfg['host']指定要连接的coreseek服务器的ip地址或主机名
 * $ucfg['port']指定要连接的coreseek服务器的端口
 * </p>
 */
function updateKeySearch($index, $id, $ucfg = array())
{
    $cfg = array(
        'host' => val($ucfg['host'], 'localhost'),
        'port' => val($ucfg['port'], 9312)
    );

    require_once 'Common/Common/sphinxapi.php';
    $client = new SphinxClient();
    $client->SetServer($cfg['host'], $cfg['port']);
    $client->UpdateAttributes($index, array('is_update'), array($id => array(2)));
}

/**
 * 上传图片
 * @param string $name <p>
 * 表单中上传图片的字段名
 *</p>
 * @param string $dir <p>
 * 上传后图片存在的路径 相对于上传图片存放的根目录
 * </p>
 * @param array $thumb <p>
 * 生成缩略图的信息
 * 每个元素是个一维数组 包含三个元素
 * 第一个元素指定缩略图的长
 * 第二个元素指定缩略图的宽
 * 第三个元素指定缩略图的裁剪类型 可缺省 默认为Image::IMAGE_THUMB_CENTER
 * 例如
 * array(array(300, 200, Image::IMAGE_THUMB_SCALE), array(30, 20))
 * 表示生成两张缩略图 第一张300*200 等比例缩放类型 第二张30*20 居中裁剪类型
 * </p>
 * @param int $maxSize [optional] <p>
 * 上传图片限制的大小 字节为单位 默认最大为400KB
 * </p>
 * @param array $exts [optional] <p>
 * 上传图片允许接受的后缀名 默认为jpg,jpeg,png,gif
 * </p>
 * @return array <p> 若返回数组$info
 * $info['status']是上传的状态信息 true表示上传成功 false表示上传失败
 * $info['images']是个数组 存放上传成功后原图和所有缩略图存放的路径 相对于上传图片根目录
 * $info['error']是上传图片失败时的错误信息
 * </p>
 */
function uploadPic($name, $dir, $thumb, $maxSize = 409600, $exts = array('jpg', 'jpeg', 'png', 'gif'))
{
    $uploadpath = C('UPLOAD_PATH');
    $uploadpath = rtrim($uploadpath, '/').'/';
    $dir = rtrim($dir, '/').'/';
    $upload = new \Think\Upload(array(
        'maxSize'       =>  $maxSize, //上传的文件大小限制 (0-不做限制)
        'exts'          =>  $exts, //允许上传的文件后缀
        'rootPath'      =>  $uploadpath, //保存根路径
        'savePath'      =>  $dir
    ));
    $re = $upload->uploadOne($_FILES[$name]);
    if($re === false) {
        return array(
            'status' => false,
            'error' => $upload->getError()
        );
    }

    $info['status'] = true;
    $info['images'][0] = $re['savepath'].$re['savename'];
    $impath = $uploadpath.$info['images'][0];
    $im = new Image();
    for($i = 1; $i <= count($thumb); $i++) {
        $v = $thumb[$i - 1];
        $im->open($impath);
        $im->thumb($v[0], $v[1], empty($v[2])? Image::IMAGE_THUMB_CENTER: $v[2]);
        $info['images'][$i] = $re['savepath'].$i.'_'.$re['savename'];
        $im->save($uploadpath.$info['images'][$i]);
    }
    return $info;
}

/**
 * 如果值为空 返回原值 否则返回默认值
 * @param string $val <p>
 * 原值
 * </p>
 * @param string $default [optional] <p>
 * 默认值
 * </p>
 * @return string
 */
function val($val, $default = '')
{
    return isset($val) && !empty($val)? $val: $default;
}

/**
 * 用于表单字段值的设置
 * 如果$_REQUEST[$key]不为空 返回$_REQUEST[$key] 否则返回指定的值
 * 用法举例
 * <input type="text" name="name" value="<?=setValue('name', $data['name'])?>">
 * <textarea name="goods_desc"><?=setValue('goods_desc', $data['goods_desc']?></textarea>
 * @param string $key
 * @param string $data [optional] <p>
 * $_REQUEST[$key]为空时指定的值
 * </p>
 * @return mixed|string
 */
function setValue($key, $data = '')
{
    return empty(I('request.'.$key))? $data: I('request.'.$key);
}

/**
 * 用于设置<select>标签中当前的<option>是否被选中
 * 如果$_REQUEST[$key]不为空 将<select>字段值设置为$_REQUEST[$key] 否则设置为指定的值
 * 用法举例
 * <select name="cat_id">
 * <option value="">请选择</option>
 * <?php foreach($catData as $v2):?>
 * <option value="<?=$v2['id']?>" <?=setSelectValue($v2['id'],'cat_id', $data['cat_id'])?>><?=$v2['name']?></option>
 * <?php endforeach;?>
 * </select>
 * @param string $cur 当前<option>选项的值
 * @param $key
 * @param string $data [optional] <p>
 * $_REQUEST[$key]为空时指定的值
 * </p>
 * @return string
 */
function setSelectValue($cur, $key, $data = '')
{
    $val = setValue($key, $data);
    return $val == $cur? 'selected': '';
}

/**
 * 用于设置当前的单选按钮或复选框是否被选中
 * 如果$_REQUEST[$key]不为空 将所在的字段值设置为$_REQUEST[$key] 否则设置为指定的值
 * 用法举例
 * <input type="radio" name="is_best" value="精品" <?=setCheckValue('精品', 'is_best', $data['is_best'])?>>
 * <input type="checkbox" name="is_best" value="精品" <?=setCheckValue('精品', 'is_best', $data['is_best'])?>>
 * @param string $cur 当前单选按钮或复选框的值
 * @param $key
 * @param string $data [optional] <p>
 * $_REQUEST[$key]为空时指定的值
 * </p>
 * @return string
 */
function setCheckValue($cur, $key, $data = '')
{
    $val = setValue($key, $data);
    return $val == $cur? 'checked': '';
}

/**
 * UEditor编辑器XSS过滤
 * @param string $data UEditor编辑器的文本内容
 * @return string 过滤后的文本内容
 */
function removeXSS($data)
{
    require_once "Public/HtmlPurifier/HTMLPurifier.auto.php";
    $_clean_xss_config = HTMLPurifier_Config::createDefault();
    $_clean_xss_config->set('Core.Encoding', 'UTF-8');
    // 设置保留的标签
    $_clean_xss_config->set('HTML.Allowed','div,b,strong,i,em,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src]');
    $_clean_xss_config->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align');
    $_clean_xss_config->set('HTML.TargetBlank', TRUE);
    $_clean_xss_obj = new HTMLPurifier($_clean_xss_config);
    // 执行过滤
    return $_clean_xss_obj->purify($data);
}

/**
 * 判断字符串$str是否包含指定的子字符串$needle
 * @param string $str
 * @param string $needle
 * @return bool true表示包含 false表示不包含
 */
function contain($str, $needle)
{
    return strrpos($str, $needle) !== false;
}

/**
 * 判断字符串$str是否包含指定的前缀$needle
 * @param string $str
 * @param string $needle
 * @return bool true表示包含 false表示不包含
 */
function hasPrefix($str, $needle)
{
    return strpos($str, $needle) === 0;
}

/**
 * 去除字符串$str中指定的前缀$needle
 * @param string $str
 * @param string $needle
 * @return string 去除前缀后的字符串
 */
function trimPrefix($str, $needle)
{
    if(hasPrefix($str, $needle)) {
        $len = strlen($needle);
        $str = substr($str, $len);
    }
    return $str;
}

/**
 * 判断字符串$str是否包含指定的后缀$needle
 * @param string
 * @param string
 * @return bool true表示包含 false表示不包含
 */
function hasSuffix($str, $needle)
{
    $i = strrpos($str, $needle);
    if($i === false) {
        return false;
    }
    return $i + strlen($needle) == strlen($str);
}

/**
 * 去除字符串$str中指定的后缀$needle
 * @param string $str
 * @param string $needle
 * @return string 去除后缀后的字符串
 */
function trimSuffix($str, $needle)
{
    if(hasSuffix($str, $needle)) {
        $i = strrpos($str, $needle);
        $str = substr($str, 0, $i);
    }
    return $str;
}

/**
 * 获得要返回的url 要返回的url存放在session中 如果为空 返回网站首页
 * @param string $default
 * @return string [optional]
 */
function backUrl($default = BASE)
{
    return val(session('back_url'), $default);
}

/**
 * 由数据表名获得相应的类名
 * @param string $table <p>
 * 数据表名
 * </p>
 * @return string 类名
 */
function getClassNameFromTable($table)
{
    $cls = trimPrefix($table, C('DB_PREFIX'));
    $cls = str_replace(' ', '', ucwords(str_replace('_', ' ', $cls)));
    return $cls;
}

/**
 * 获得数据表中文名
 * @param string $table 数据表名
 * @return string 表中文名
 */
function getTableChineseName($table)
{
    $model = new \Think\Model();
    $re = $model->query("SHOW TABLE STATUS WHERE `name`='{$table}'");
    if(!empty($re[0]['comment'])) {
        $tcname = $re[0]['comment'];
        return $tcname;
    } else {
        return '';
    }
}

/**
 * 发送Email
 * @param string $address <p>
 * 收件人
 * </p>
 * @param string $subject <p>
 * 主题
 * </p>
 * @param string $body <p>
 * 内容
 * </p>
 * @return bool true表示发送成功 false表示发送失败
 */
function sendMail($address, $subject, $body)
{
    require_once 'Public/PHPMailer/PHPMailerAutoload.php';
    $mailer = new PHPMailer();
    $mailer->isHTML();
    $mailer->isSMTP();
    $mailer->CharSet = 'utf-8';
    $mailer->SMTPAuth = true;
    $mailer->From = C('MAIL_USER').'@163.com';
    $mailer->FromName = C('MAIL_USER');
    $mailer->Username = C('MAIL_USER');
    $mailer->Password = C('MAIL_PWD');
    $mailer->Host = C('MAIL_HOST');
    $mailer->Port = 25;
    $mailer->addAddress($address);
    $mailer->Subject = $subject;
    $mailer->Body = $body;
    return $mailer->send();
}

/**
 * 获取session的有效期限
 * @return string session的有效期限
 */
function getSessionExpire()
{
    return ini_get('session.gc_maxlifetime');
}

/**
 * 过滤url中指定的请求参数
 * @param string $key <p>
 * 要过滤的请求参数的key
 * </p>
 * @param string $url [optional]
 * @return string 过滤后的url
 */
function filterUrl($key, $url = __SELF__)
{
    return preg_replace("/(\?|&){$key}=[^&]*/", '', $url);
}