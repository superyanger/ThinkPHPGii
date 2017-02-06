{extends file="Admin/View/Public/layout.html"}
{block name="main"}
<div class="crumb-wrap">
    <div class="crumb-list">
        <i class="icon-font"></i>
        <a href="{$smarty.const.__MODULE__}">首页</a>
        <span class="crumb-step">&gt;</span>
        <a class="crumb-name" href="{$smarty.const.__CONTROLLER__}/index">{$tcname}管理</a>
        <span class="crumb-step">&gt;</span>
        <span class="crumb-name">图集</span>
    </div>
</div>
<div class="result-wrap">
    <div class="result-content">
        <form id="upload" action="" method="post" enctype="multipart/form-data">
            <?php foreach ($idat as $v):?>
            <?php if(preg_match('/^.*(logo|img|pic)$/', $v['field']) > 0):?>
            <?php if(preg_match('/^t[0-9]+_[0-9]+_.*$/', $v['field']) > 0)continue;?>
            <?="\r"?>
            <div style="width: 100px; height: 30px; background: dodgerblue;position: relative;">
                <input class="_csrf" type="hidden" name="_csrf" value="{$smarty.session.csrf}">
                <input type="hidden" name="<?=trimPrefix($tname, C('DB_PREFIX'))?>_id" value="{I('get.<?=trimPrefix($tname, C('DB_PREFIX'))?>_id')}">
                <div style="position: absolute;left: 0; top: 0;width: 100px;height: 30px;text-align: center;line-height: 30px; color: white">添加图片</div>
                <input type="file" name="<?=$v['field']?>" style="position: absolute;left: 0; top: 0;width: 100px;height: 30px;opacity: 0;-moz-opacity: 0;">
            </div>
            <div style="color: red">{$error.<?=$v['field']?>}</div>
            <?php break;endif;endforeach;?>
            <?="\r"?>
        </form>
        <div style="height: 20px"></div>
        <div class="result-title">
            <div class="result-list">
                <a href="{$smarty.const.__CONTROLLER__}/gallerytrashcan?<?=trimPrefix($tname, C('DB_PREFIX'))?>_id={I('get.<?=trimPrefix($tname, C('DB_PREFIX'))?>_id')}&p={I('get.p')}">回收站</a>
                <a href="{val(I('get.back'), '{$smarty.const.__CONTROLLER__}/index')}">返回</a>
            </div>
            <div class="result-list">
                <input type="checkbox" id="all">全选
                <a href="javascript:" onclick="trashGroup()">批量移到回收站</a>
            </div>
        </div>
        <form name="myform" id="myform" method="post">
            <input class="_csrf" type="hidden" name="_csrf" value="{$smarty.session.csrf}">
            <table class="insert-tab" cellpadding="0">
                <tr>
                    <th width="15"></th>
                    <th width="120" style="text-align: center;padding: 0">图片</th>
                    <th width="120" style="text-align: center;padding: 0">操作</th>
                </tr>
                {foreach $data as $v}
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{$v.id}" class="check-id" onclick="choose()"></td>
                    <td style="padding: 10px">
                        <?php foreach ($idat as $v):?>
                        <?php if(preg_match('/^.*(logo|img|pic)$/', $v['field']) > 0 && hasSuffix($v['comment'], '#b')):?>
                        <?="\r"?>
                        {showPic($v.<?=$v['field']?>)}
                        <?php break;endif;endforeach;?>
                    <?="\r"?>
                    </td>
                    <td style="text-align: center;padding: 0"><a href="javascript:" onclick="trash(this, {$v.id})">移到回收站</a></td>
                </tr>
                {/foreach}
            </table>
        </form>
        <div class="list-page">{$page}</div>
    </div>
</div>
<script>
    $("input[type=file]").change(function() {
        $("#upload").submit();
    });

    function trash(obj, id)
    {
        $.ajax({
            method: "POST",
            url: "{$smarty.const.__CONTROLLER__}/trashpic",
            data: {
                id: id,
                _csrf: $("._csrf").val()
            },
            dataType: "json",
            success: function(data) {
                if(data.status > 0) {
                    alert("成功");
                    var tr = $(obj).parent().parent();
                    $(tr).hide();
                } else {
                    alert("服务器开了小差,稍后再试");
                }
                $("._csrf").val(data.csrf);
            },
            error: function(o, s, e) {
                alert("服务器开了小差,稍后再试");
            }
        })
    }

    function choose()
    {
        $("#all").prop("checked", canChooseAll());
    }

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
                url: "{$smarty.const.__CONTROLLER__}/trashpicgroup",
                data: $("#myform").serialize(),
                dataType: "json",
                success: function(data) {
                    if(data.status > 0) {
                        alert("成功");
                        window.location.reload(true);
                    } else {
                        alert("网络正在睡觉,请稍后再试");
                    }
                    $("._csrf").val(data.csrf);
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
</script>
{/block}