{extends file="Admin/View/Public/layout.html"}
{block name="main"}
<div class="crumb-wrap">
    <div class="crumb-list">
        <i class="icon-font"></i>
        <a href="{$smarty.const.__MODULE__}">首页</a>
        <span class="crumb-step">&gt;</span>
        <a class="crumb-name" href="{$smarty.const.__CONTROLLER__}/index">{$tcname}管理</a>
        <span class="crumb-step">&gt;</span>
        <span class="crumb-name">详情</span>
    </div>
</div>
<div class="result-wrap">
    <div class="result-title">
        <div class="result-list">
            <a href="{val(I('get.back'), '{$smarty.const.__CONTROLLER__}/index')}">返回</a>
        </div>
    </div>
    <table class="result-tab" width="100%">
        <?php foreach ($info as $v):?>
        <?php if(in_array($v['field'], array('id', 'is_trash', 'is_update')))continue;?>
        <?php if(preg_match('/^.*(logo|pic|img)$/', $v['field']) > 0 && !hasSuffix($v['comment'], '#b'))continue;?>
        <?="\r"?>
        <tr>
            <td valign="top" width="20%"><?=preg_match('/^.*(logo|pic|img)$/', $v['field']) > 0? trimSuffix($v['comment'], '#b'): $v['comment']?></td>
            <td>
                {if $data.<?=$v['field']?> !== NULL}
                <?php if(preg_match('/^.*(logo|pic|img)$/', $v['field']) > 0):?>
                <?="\r"?>
                {showPic($data.<?=$v['field']?>)}
                <?php elseif(in_array($v['field'], $fk)):?>
                <?="\r"?>
                {$data.<?=trimSuffix($v['field'], '_id')?>_name}
                <?php elseif(preg_match('/^enum\(\'\',\'([^\',]+)\'\)$/', $v['type']) > 0):?>
                <?="\r"?>
                {if !empty($data.<?=$v['field']?>)}是{else}否{/if}
                <?php elseif($v['field'] == 'parent_id'):?>
                <?="\r"?>
                {$data.parent_name}
                <?php elseif($v['field'] == 'url'):?>
                <?="\r"?>
                <a href="{$data.<?=$v['field']?>}">{$data.<?=$v['field']?>}</a>
                <?php else:?>
                <?="\r"?>
                {$data.<?=$v['field']?>}
                <?php endif;?>
                <?="\r"?>
                {/if}
            </td>
        </tr>
        <?php endforeach;?>
    <?="\r"?>
    </table>
</div>
{/block}