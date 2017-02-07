{extends file="Admin/View/Public/layout.html"}
{block name="main"}
<div class="crumb-wrap">
    <div class="crumb-list">
        <i class="icon-font"></i>
        <a href="{$smarty.const.__MODULE__}">首页</a>
        <span class="crumb-step">&gt;</span>
        <a class="crumb-name" href="{$smarty.const.__CONTROLLER__}/index">{$tcname}管理</a>
        <span class="crumb-step">&gt;</span>
        <span class="crumb-name">修改</span>
    </div>
</div>
<div class="result-wrap">
    <div class="result-content">
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="{setValue('id')}">
            <input type="hidden" name="_csrf" value="{$smarty.session.csrf}">
            <table class="insert-tab" width="100%">
                <tbody>
                    <?php foreach ($info as $v):?>
                    <?php if(in_array($v['field'], array('id', 'is_trash', 'is_update')))continue;?>
                    <?php if(preg_match('/^t[0-9]+_[0-9]+_.*(logo|pic|img)$/', $v['field']) > 0)continue;?>
                    <?="\r"?>
                    <tr>
                        <th width="20%" valign="top">
                            <?php if($v['null'] == 'NO' && $v['default'] === NULL):?>
                            <?="\r"?>
                            <i class="require-red">*</i>
                            <?php endif;?>
                            <?php if(preg_match('/^enum\(\'\',\'([^\',]+)\'\)$/', $v['type']) > 0):else:?>
                            <?="\r"?>
                            <?=$v['comment']?>：
                            <?php endif;?>
                        <?="\r"?>
                        </th>
                        <td>
                            <?php if(preg_match('/^.*(logo|pic|img)$/', $v['field']) > 0):?>
                            <?="\r"?>
                            <input type="file" name="<?=$v['field']?>">
                            <div>再次上传将覆盖原图</div>
                            <?php elseif(preg_match('/^enum\(\'(.*)\'\)$/', $v['type'], $matches) > 0):
                                    $ma = $matches[1];
                                    $ma = explode("','", $ma);
                                    if(count($ma) < 2)continue;
                                    if(preg_match('/^enum\(\'\',\'([^\',]+)\'\)$/', $v['type']) > 0):?>
                            <?="\r"?>
                            <input type="checkbox" name="<?=$v['field']?>" value="<?=$ma[1]?>" {setCheckValue('<?=$ma[1]?>', '<?=$v['field']?>', $data.<?=$v['field']?>)}><?=$ma[1]?>
                            <?php else:?>
                            <?="\r"?>
                            <select name="<?=$v['field']?>">
                                <option value="">请选择</option>
                                <?php foreach ($ma as $v2):
                                    if(!empty($v2)):?>
                                <?="\r"?>
                                <option value="<?=$v2?>" {setSelectValue('<?=$v2?>','<?=$v['field']?>', $data.<?=$v['field']?>)}><?=$v2?></option>
                                <?php endif;endforeach;?>
                            <?="\r"?>
                            </select>
                            <?php endif;?>
                            <?php elseif(hasSuffix($v['type'], 'text')):?>
                            <?="\r"?>
                            <textarea id="<?=$v['field']?>" name="<?=$v['field']?>">{setValue('<?=$v['field']?>', $data.<?=$v['field']?>)}</textarea>
                            <?php elseif(in_array($v['field'], $fk)):?>
                            <?="\r"?>
                            <select name="<?=$v['field']?>">
                                <option value="">请选择</option>
                                {foreach $<?=strtolower(getClassNameFromTable(trimSuffix($v['field'], '_id')))?>Data as $v2}
                                <option value="{$v2.id}" {setSelectValue($v2.id,'<?=$v['field']?>', $data.<?=$v['field']?>)}>{str_repeat('-', $v2.level * 8)}{$v2.name}</option>
                                {/foreach}
                            </select>
                            <?php elseif($v['field'] == 'parent_id'):?>
                            <?="\r"?>
                            <select name="<?=$v['field']?>">
                                <option value="">请选择</option>
                                {foreach $cData as $v2}
                                <option value="{$v2.id}" {setSelectValue($v2.id,'<?=$v['field']?>', $data.<?=$v['field']?>)}>{str_repeat('-', $v2.level * 8)}{$v2.name}</option>
                                {/foreach}
                            </select>
                            <?php elseif($v['field'] == 'password'):?>
                            <?="\r"?>
                            <input type="password" name="<?=$v['field']?>" id="<?=$v['field']?>">
                            <div>请再次输入<?=$v['comment']?></div>
                            <input type="password" name="re<?=$v['field']?>" id="re<?=$v['field']?>">
                            <?php else:?>
                            <?="\r"?>
                            <input type="text" name="<?=$v['field']?>" id="<?=$v['field']?>" class="common-text" value="{setValue('<?=$v['field']?>', $data.<?=$v['field']?>)}">
                            <?php endif;?>
                            <?="\r"?>
                            <div style="color: red">{$error.<?=$v['field']?>}</div>
                        </td>
                    </tr>
                    <?php endforeach;?>
                    <?php if(!empty($ecap)):?>
                    <?="\r"?>
                    <tr>
                        <th width="20%" valign="top">验证码</th>
                        <td>
                            <input type="text" name="captcha" maxlength="4">
                            <img src="{$smarty.const.COMMON}/captcha.php" onclick="this.src='{$smarty.const.COMMON}/captcha.php?'+Math.random();">
                        </td>
                    </tr>
                    <?php endif;?>
                    <?="\r"?>
                    <tr>
                        <th></th>
                        <td>
                            <input class="btn btn-primary btn6 mr10" value="提交" type="submit">
                            <input class="btn btn6" onclick='window.location.href = "{val(I('get.back'), '{$smarty.const.__CONTROLLER__}/index')}"' value="返回" type="button">
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>
</div>
<?php foreach ($info as $v):?>
<?php if(in_array($v['type'], array('date', 'time', 'datetime'))):?>
<?="\r"?>
<link href="{$smarty.const.PUB}datetimepicker/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" charset="utf-8" src="{$smarty.const.PUB}datetimepicker/jquery-ui-1.9.2.custom.min.js"></script>
<script type="text/javascript" charset="utf-8" src="{$smarty.const.PUB}datetimepicker/datepicker-zh_cn.js"></script>
<link rel="stylesheet" media="all" type="text/css" href="{$smarty.const.PUB}datetimepicker/time/jquery-ui-timepicker-addon.min.css" />
<script type="text/javascript" src="{$smarty.const.PUB}datetimepicker/time/jquery-ui-timepicker-addon.min.js"></script>
<script type="text/javascript" src="{$smarty.const.PUB}datetimepicker/time/i18n/jquery-ui-timepicker-addon-i18n.min.js"></script>
<?php break;endif;?>
<?php endforeach;?>

<?php foreach ($info as $v):?>
<?php if(preg_match('/^.*text$/', $v['type']) > 0):?>
<?="\r"?>
<script type="text/javascript" src="{$smarty.const.PUB}ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="{$smarty.const.PUB}ueditor/ueditor.all.js"></script>
<?php break;endif;?>
<?php endforeach;?>

<?="\r"?>
<script>
    <?php foreach ($info as $v):?>
    <?php if($v['type'] == 'date'):?>
    <?="\r"?>
    $("#<?=$v['field']?>").datepicker().prop("readonly", true);
    <?php elseif($v['type'] == 'time'):?>
    <?="\r"?>
    $("#<?=$v['field']?>").timepicker().prop("readonly", true);
    <?php elseif($v['type'] == 'datetime'):?>
    <?="\r"?>
    $("#<?=$v['field']?>").datetimepicker().prop("readonly", true);
    <?php elseif(preg_match('/^.*text$/', $v['type']) > 0):?>
    <?="\r"?>
    UE.getEditor('<?=$v['field']?>', {
        initialFrameWidth: "80%",
        initialFrameHeight: "300",
        zIndex: "0",
        autoFloatEnabled: false,
        autoWidthEnabled: false,
        autoHeightEnabled: false
    });
    <?php endif;?>
    <?php endforeach;?>
<?="\r"?>
</script>
{/block}