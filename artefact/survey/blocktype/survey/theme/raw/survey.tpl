<div id="surveywrap">
{if $RESPONSES}<div>{$responseshtml|safe}</div>{/if}
{if $RESULTS}<br><div>{$resultshtml|safe}</div>{/if}
{if $CHART}<br><div><img src="{$charturl}"></div>{/if}
</div>
