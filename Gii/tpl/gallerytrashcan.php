{extends file="Admin/View/Public/layout.html"}
{block name="main"}
<div class="crumb-wrap">
    <div class="crumb-list">
        <i class="icon-font"></i>
        <a href="{$smarty.const.__MODULE__}">首页</a>
        <span class="crumb-step">&gt;</span>
        <a class="crumb-name" href="{$smarty.const.__CONTROLLER__}/index">{$tcname}管理</a>
        <span class="crumb-step">&gt;</span>
        <span class="crumb-name">图集回收站</span>
    </div>
</div>
<div class="result-wrap">
    <div class="result-content">
        <div class="result-title">
            <div class="result-list">
                <a href="{$smarty.const.__CONTROLLER__}/gallery?<?=trimPrefix($tname, C('DB_PREFIX'))?>_id={I('get.<?=trimPrefix($tname, C('DB_PREFIX'))?>_id')}&p={I('get.p')}">返回</a>
            </div>
            <div class="result-list">
                <input type="checkbox" id="all">全选
                <a href="javascript:" onclick="recoverGroup()">批量还原</a>
                <a href="javascript:" onclick="delGroup()">批量删除</a>
            </div>
        </div>
        <form name="myform" id="myform" method="post">
            <input id="_csrf" type="hidden" name="_csrf" value="{$smarty.session.csrf}">
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
                    <td style="text-align: center;padding: 0">
                        <a href="javascript:" onclick="recover(this, {$v.id})">还原</a>
                        <a href="javascript:" onclick="del(this, {$v.id})">删除</a>
                    </td>
                </tr>
                {/foreach}
            </table>
        </form>
        <div class="list-page">{$page}</div>
    </div>
</div>
<script>
    $("input[type=file]").change(function() {
        $("form").submit();
    });

    function recover(obj, id)
    {
        $.ajax({
            method: "POST",
            url: "{$smarty.const.__CONTROLLER__}/recoverpic",
            data: {
                id: id,
                _csrf: $("#_csrf").val()
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
                $("#_csrf").val(data.csrf);
            },
            error: function(o, s, e) {
                alert("服务器开了小差,稍后再试");
            }
        })
    }

    function del(obj, id)
    {
        $.ajax({
            method: "POST",
            url: "{$smarty.const.__CONTROLLER__}/rmpic",
            data: {
                id: id,
                _csrf: $("#_csrf").val()
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
                $("#_csrf").val(data.csrf);
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

    function recoverGroup()
    {
        if(!selectAny()) {
            alert("没有选择任何行");
            return;
        }

        var v = $(".check-id");
        for(var i = 0; i < $(v).length; i++) {
            var ck = $(v).eq(i);
            var tr = $(ck).parent().parent();
            var lev = $(tr).attr("level");
            if($(ck).prop("checked") && lev > 0) {
                $(tr).prevAll("tr").each(function(k, vv) {
                    var vlev = $(vv).attr("level");
                    if(vlev < lev) {
                        $(vv).find(".check-id").prop("checked", true);
                        lev = vlev;
                    }
                })
            }
        }

        $.ajax({
            method: "POST",
            url: "{$smarty.const.__CONTROLLER__}/recoverpicgroup",
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

    function delGroup()
    {
        if(!selectAny()) {
            alert("没有选择任何行");
            return;
        }
        if(confirm("确定要删除吗")) {
            $.ajax({
                method: "POST",
                url: "{$smarty.const.__CONTROLLER__}/rmpicgroup",
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
</script>
{/block}