{extends file="Admin/View/Public/layout.html"}
{block name="main"}
<div class="crumb-wrap">
    <div class="crumb-list">
        <i class="icon-font"></i>
        <a href="{$smarty.const.__MODULE__}">首页</a>
        <span class="crumb-step">&gt;</span>
        <a class="crumb-name" href="{$smarty.const.__CONTROLLER__}/index">{$tcname}管理</a>
        <span class="crumb-step">&gt;</span>
        <span class="crumb-name"><?=$tabc?></span>
    </div>
</div>
<div class="result-wrap">
    <div class="result-content">
        <div class="result-title">
            <div class="result-list">
                <a href="{val(I('get.back'), '{$smarty.const.__CONTROLLER__}/index')}">返回</a>
            </div>
        </div>
        <form action="" name="myform" id="myform" method="post">
            <input id="_csrf" type="hidden" name="_csrf" value="{$smarty.session.csrf}">
            <table class="insert-tab" width="100%">
                <tbody>
                {foreach $data as $k => $v}
                <tr>
                    <th width="20%" valign="top">{$k}</th>
                    <td>
                        {if !empty($v[0])}
                        <ul style="list-style-type: none">
                            <li><a href="javascript:" onclick="add(this,{$v[0].id})">+</a></li>
                            {foreach $v as $k2 => $v2}
                            {if !empty($v2.<?=$avv?>)}
                            <li>
                                <a href="javascript:" onclick="del(this, {$v2.aid})">-</a>
                                <input type="text" name="attr[{$v2.id}][]" value="{$v2.<?=$avv?>}" readonly>
                            </li>
                            {/if}
                            {/foreach}
                        </ul>
                        {else}
                        <input type="text" name="attr[{$v.id}]" value="{$v.<?=$avv?>}">
                        {/if}
                    </td>
                </tr>
                {/foreach}

                <tr>
                    <th></th>
                    <td>
                        <input class="btn btn-primary btn6 mr10" value="提交" type="submit">
                        <input class="btn btn6" onclick='window.location.href = "{backUrl('{$smarty.const.__CONTROLLER__}/index')}"' value="返回" type="button">
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
        <div class="list-page">{$page}</div>
    </div>
</div>
<script>
    function del(obj, id)
    {
        if(id == 0) {
            $(obj).parent().remove();
            return;
        }

        if(confirm("确定要删除吗")) {
            $.ajax({
                method: "POST",
                url: "{$smarty.const.__CONTROLLER__}/<?=$v2?>del",
                data: {
                    id: id,
                    _csrf: $("#_csrf").val()
                },
                dataType: "json",
                success: function(data) {
                    if(data.status > 0) {
                        $(obj).parent().remove();
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

    function add(obj, id)
    {
        var ul = $(obj).parent().parent();
        var li = '<li><a href="javascript:" onclick="del(this, 0)">-</a><input type="text" name="attr[' + id + '][]"></li>';
        $(ul).append(li);
    }
</script>
{/block}