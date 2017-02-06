{extends file="Admin/View/Public/layout.html"}
{block name="main"}
<div class="crumb-wrap">
    <div class="crumb-list">
        <i class="icon-font"></i>
        <a href="{$smarty.const.__MODULE__}">首页</a>
        <span class="crumb-step">&gt;</span>
        <a class="crumb-name" href="{$smarty.const.__CONTROLLER__}/index">{$tcname}管理</a>
        <span class="crumb-step">&gt;</span>
        <span class="crumb-name">回收站</span>
    </div>
</div>
<div class="result-wrap">
    <form name="myform" id="myform" method="post">
        <input id="_csrf" type="hidden" name="_csrf" value="{$smarty.session.csrf}">
        <div class="result-title">
            <div class="result-list">
                <a href="{val(I('get.back'), '{$smarty.const.__CONTROLLER__}/index')}">返回</a>
            </div>
            <div class="result-list">
                <input type="checkbox" id="all">全选
                <a href="javascript:" onclick="recoverGroup()">批量还原</a>
                <a href="javascript:" onclick="delGroup()">批量删除</a>
            </div>
        </div>
        <div class="result-content">
            <table class="result-tab" width="100%">
                <tr>
                    <th width="15"></th>
                    <?php foreach ($info as $v):?>
                    <?php if($v['field'] == 'name'):?>
                    <?="\r"?>
                    <th <?php if(!empty($rec)):?>align="left"<?php endif;?>><?=$v['comment']?></th>
                    <?php break;endif;?>
                    <?php endforeach;?>
                    <?="\r"?>
                    <th width="20%">操作</th>
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
                        <?php if(!empty($rec)):?>{if $v.level eq 0}<?php endif;?>
                        <?="\r"?>
                        <a class="link-del" href="javascript:" onclick="recover({$v.id}, this)">还原</a>
                        <?php if(!empty($rec)):?>{/if}<?php endif;?>
                        <?="\r"?>
                        <a class="link-del" href="javascript:" onclick="del({$v.id}, this)">删除</a>
                    </td>
                </tr>
                {/foreach}
            </table>
            <div class="list-page">{$page}</div>
        </div>
    </form>
</div>
<script>
    function recover(id, obj)
    {
        $.ajax({
            method: "POST",
            url: "{$smarty.const.__CONTROLLER__}/recover",
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
                    window.location.reload(true);
                    <?php else:?>
                    <?="\r"?>
                    $(obj).parent().parent().remove();
                    <?php endif;?>
                <?="\r"?>
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

    function del(id, obj)
    {
        if(confirm('确定要删除吗')) {
            $.ajax({
                method: "POST",
                url: "{$smarty.const.__CONTROLLER__}/del",
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
            url: "{$smarty.const.__CONTROLLER__}/recovergroup",
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
                url: "{$smarty.const.__CONTROLLER__}/delgroup",
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
    $("tr[level=0]").show();
    <?php endif;?>
<?="\r"?>
</script>
{/block}