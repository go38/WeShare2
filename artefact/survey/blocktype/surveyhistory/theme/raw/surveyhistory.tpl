<div id="surveywrap">
{if $CHART}
{foreach from=$data item=survey}
<br><div><img src="{$WWWROOT}artefact/survey/chart.php?id={$survey.id}&width={$survey.width}&height={$survey.height}&palette={$survey.palette}&legend={$survey.legend}&fonttype={$survey.fonttype}&fontsize={$survey.fontsize}&alpha={$survey.alpha}"></div>
{/foreach}
{/if}
</div>
