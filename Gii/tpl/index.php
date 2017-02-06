{extends file="Admin/View/Public/layout.html"}
{block name="main"}
<div class="crumb-wrap">
    <div class="crumb-list">
        <i class="icon-font"></i>
        <a href="{$smarty.const.__MODULE__}">首页</a>
        <span class="crumb-step">&gt;</span>
        <span class="crumb-name">{$tcname}管理</span>
    </div>
</div>
<?php if(empty($rec)):?>
<div class="search-wrap">
    <div class="search-content">
        <form action="" method="get">
            <table class="search-tab" width="30%">
                <?php $f = false;?>
                <?php if(!empty(I('post.ks'))):
                    $f = true;?>
                <?="\r"?>
                <tr>
                    <th width="70">关键字:</th>
                    <td><input class="common-text" name="key" value="{setValue('key')}" type="text"></td>
                </tr>
                <?php endif;?>
                <?php $ck = array();?>
                <?php foreach ($info as $v):?>
                <?php if(empty($v['key']) || in_array($v['field'], array('id', 'is_trash', 'is_update')))continue;?>
                <?php if(preg_match('/^enum\(\'(.*)\'\)$/', $v['type'], $matches) > 0):
                        $ma = $matches[1];
                        $ma = explode("','", $ma);
                        if(count($ma) < 2)continue;
                        $f = true;
                        if(preg_match('/^enum\(\'\',\'([^\',]+)\'\)$/', $v['type']) > 0) {
                            $ck[$v['field']] = $ma[1];
                            continue;
                        }?>
                <?="\r"?>
                <tr>
                    <th width="70"><?=$v['comment']?>:</th>
                    <td>
                        <select name="<?=$v['field']?>">
                            <option value="">全部</option>
                            <?php foreach ($ma as $v2):
                                if(!empty($v2)):?>
                            <?="\r"?>
                            <option value="<?=$v2?>" {setSelectValue('<?=$v2?>','<?=$v['field']?>')}><?=$v2?></option>
                            <?php endif;endforeach;?>
                        <?="\r"?>
                        </select>
                    </td>
                </tr>
                <?php elseif(in_array($v['field'], $fk)):$f = true;?>
                <?="\r"?>
                <tr>
                    <th width="70"><?=$v['comment']?>:</th>
                    <td>
                        <select name="<?=$v['field']?>">
                            <option value="">请选择</option>
                            {foreach $<?=strtolower(getClassNameFromTable(trimSuffix($v['field'], '_id')))?>Data as $v2}
                            <option value="{$v2.id}" {setSelectValue($v2.id,'<?=$v['field']?>')}>{str_repeat('-', $v2.level * 8)}{$v2.name}</option>
                            {/foreach}
                        </select>
                    </td>
                </tr>
                <?php endif;endforeach;?>
                <?php if(!empty($ck)):?>
                <?="\r"?>
                <tr>
                    <th width="70"></th>
                    <td>
                        <?php foreach($ck as $k => $v):?>
                        <?="\r"?>
                        <input type="checkbox" name="<?=$k?>" value="<?=$v?>" {setCheckValue('<?=$v?>', '<?=$k?>')}><?=$v?>
                        <?php endforeach;?>
                    <?="\r"?>
                    </td>
                </tr>
                <?php endif;?>
                <?="\r"?>
                <!--自定义搜索start--><?=val($ssindex)?><!--自定义搜索end-->
                <?php if(!empty($f)):?>
                <?="\r"?>
                <tr>
                    <th></th>
                    <td><input class="btn btn-primary btn2" value="搜索" type="submit"></td>
                </tr>
                <?php endif;?>
            <?="\r"?>
            </table>
        </form>
    </div>
</div>
<?php endif;?>
<div class="result-wrap">
    <form name="myform" id="myform" method="post">
        <input id="_csrf" type="hidden" name="_csrf" value="{$smarty.session.csrf}">
        <div class="result-title">
            <div class="result-list">
                <a href="{$smarty.const.__CONTROLLER__}/add?back={$smarty.const.__SELF__}&<?php foreach($fk as $v):if(!empty($v)):?><?=$v?>={I('get.<?=$v?>')}&<?php endif;endforeach;?>"><i class="icon-font"></i>新增</a>
                <a href="{$smarty.const.__CONTROLLER__}/trashcan?back={$smarty.const.__SELF__}">回收站</a>
            </div>
            <div class="result-list">
                <input type="checkbox" id="all">全选
                <a href="javascript:" onclick="trashGroup()">批量移到回收站</a>
            </div>
        </div>
        <div class="result-content">
            <table class="result-tab" width="100%">
                <tr>
                    <th width="15"></th>
                    <?php foreach ($info as $v):?>
                        <?php if($v['field'] == 'name'):?>
                            <?="\r"?>
                            <th><?=$v['comment']?></th>
                            <?php break;endif;?>
                    <?php endforeach;?>
                    <?="\r"?>
                    <th width="30%">操作</th>
                </tr>
                {foreach $data as $v}
                <tr id="row_{$v.id}" <?php if(!empty($rec)):?>class="parent parent_{$v.parent_id}" level="{$v.level}"<?php endif;?>>
                    <td><input type="checkbox" name="ids[]" value="{$v.id}" class="check-id" onclick="choose({$v.id}, this)"></td>
                    <td <?php if(!empty($rec)):?>align="left"<?php endif;?>>
                        <?php if(!empty($rec)):?>
                            <?="\r"?>
                            {str_repeat('-', $v.level * 8)}
                            <span class="flip-cat" onclick="flipCat({$v.id}, this)">+</span>
                        <?php endif;?>
                        <?="\r"?>
                        {$v.name}
                    </td>
                    <td>
                        <a class="link-update" href="{$smarty.const.__CONTROLLER__}/detail?id={$v.id}&back={$smarty.const.__SELF__}">详情</a>
                        <?php foreach($tfk as $v):
                            $v2 = trimPrefix($v, C('DB_PREFIX'));
                            $v3 = getClassNameFromTable($v);
                            if(!empty($v2)):?>
                        <?php if($v == $tiname):?>
                        <?="\r"?>
                        <a class="link-update" href="{$smarty.const.__CONTROLLER__}/gallery?<?=trimPrefix($tname, C('DB_PREFIX'))?>_id={$v.id}&back={$smarty.const.__SELF__}"><?=$tcfk[$v2]?></a>
                        <?php elseif(in_array($v, $ext)):?>
                        <?="\r"?>
                        <a class="link-update" href="{$smarty.const.__CONTROLLER__}/<?=strtolower($v3)?>?<?=trimPrefix($tname, C('DB_PREFIX'))?>_id={$v.id}&back={$smarty.const.__SELF__}"><?=$tcfk[$v2]?></a>
                        <?php else:?>
                        <?="\r"?>
                        <a class="link-update" href="{$smarty.const.__MODULE__}/<?=$v3?>/index?<?=trimPrefix($tname, C('DB_PREFIX'))?>_id={$v.id}"><?=$tcfk[$v2]?></a>
                        <?php endif;endif;endforeach;?>
                        <?="\r"?>
                        <!--自定义action start--><?=$siew?><!--自定义action end-->
                        <a class="link-update" href="{$smarty.const.__CONTROLLER__}/edit?id={$v.id}&back={$smarty.const.__SELF__}">修改</a>
                        <a class="link-del" href="javascript:" onclick="trash({$v.id}, this)">移到回收站</a>
                    </td>
                </tr>
                {/foreach}
            </table>
            <div class="list-page">{$page}</div>
        </div>
    </form>
</div>
<script>
    function trash(id, obj)
    {
        if(confirm('确定要移到回收站吗')) {
            $.ajax({
                method: "POST",
                url: "{$smarty.const.__CONTROLLER__}/trash",
                data: {
                    id: id,
                    _csrf: $("#_csrf").val()
                },
                dataType: "json",
                success: function(data) {
                    if(data.status > 0) {
                        alert("成功");
                        <?php if(!empty($rec)):?>
                        <?="\r"?>
                        var v = $("#row_" + id).find(".flip-cat");
                        if($(v).text() == "-") {
                            flipCat(id, v);
                        }
                        <?php endif;?>
                        <?="\r"?>
                        $(obj).parent().parent().remove();
                    } else {
                        alert("网络正在睡觉,请稍后再试");
                    }
                    $("#_csrf").val(data.csrf);
                },
                error: function(o, s, e) {
                    alert("网络正在睡觉,请稍后再试");
                }
            })
        }
    }

    function choose(id, obj)
    {
        <?php if(!empty($rec)):?>
        <?="\r"?>
        chooseSubCat(id, obj);
        <?php endif;?>
        <?="\r"?>
        $("#all").prop("checked", canChooseAll());
    }

    <?php if(!empty($rec)):?>
    <?="\r"?>
    function chooseSubCat(id, obj)
    {
        var v = $(".parent_" + id);
        if($(obj).prop("checked")) {
            $(v).find(".check-id").prop("checked", true);
            $(v).each(function(k, vv) {
                var sid = $(vv).attr("id").substr(4);
                chooseSubCat(sid, $(vv).find(".check-id"));
            })
        } else {
            $(v).find(".check-id").prop("checked", false);
            $(v).each(function(k, vv) {
                var sid = $(vv).attr("id").substr(4);
                chooseSubCat(sid, $(vv).find(".check-id"));
            });
            var tr = $(obj).parent().parent();
            var level = $(tr).attr("level");
            $(tr).prevAll("tr").each(function(k, vv) {
                var lev = $(vv).attr("level");
                if(lev < level) {
                    $(vv).find(".check-id").prop("checked", false);
                    level = lev;
                }
            })
        }
    }
    <?php endif;?>

    function canChooseAll()
    {
        var v = $(".check-id");
        for(var i = 0; i < $(v).length; i++) {
            if(!$(v).eq(i).prop("checked")) {
                return false;
            }
        }
        return true;
    }

    $("#all").click(function() {
        if($(this).prop("checked")) {
            $(".check-id").prop("checked", true);
        } else {
            $(".check-id").prop("checked", false);
        }
    });

    function trashGroup()
    {
        if(!selectAny()) {
            alert("没有选择任何行");
            return;
        }
        if(confirm("确定要移到回收站吗")) {
            $.ajax({
                method: "POST",
                url: "{$smarty.const.__CONTROLLER__}/trashgroup",
                data: $("#myform").serialize(),
                dataType: "json",
                success: function(data) {
                    if(data.status > 0) {
                        alert("成功");
                        window.location.reload(true);
                    } else {
                        alert("网络正在睡觉,请稍后再试");
                    }
                    $("#_csrf").val(data.csrf);
                },
                error: function(o, s, e) {
                    alert("网络正在睡觉,请稍后再试");
                }
            })
        }
    }

    function selectAny()
    {
        var v = $(".check-id");
        for (var i = 0; i < $(v).length; i++) {
            if($(v).eq(i).prop("checked")) {
                return true;
            }
        }
        return false;
    }

    <?php if(!empty($rec)):?>
    <?="\r"?>
    function flipCat(id, obj)
    {
        var v = $(".parent_" + id);
        if($(obj).text() == "+") {
            $(v).find(".flip-cat").text("+");
            $(obj).text("-");
            $(v).show();
        } else {
            $(v).find(".flip-cat").text("-");
            $(v).each(function(k, vv) {
                var sid = $(vv).attr("id").substr(4);
                flipCat(sid, $(vv).find(".flip-cat"));
            });
            $(obj).text("+");
            $(v).hide();
        }
    }

    $(".parent").hide();
    $(".parent_0").show();
    <?php endif;?>
<?="\r"?>
</script>
{/block}