<div id="surveywrap">
{if $SURVEYS}
<br><div>{$resultshtml|safe}</div>
<br><div><img src="{$charturl}"></div>
<div class="description">{$chartdesc|safe}</div>
{else}
{str tag="nocompletedsurveys" section="blocktype.survey/competenceskadis"}
{/if}
</div>
