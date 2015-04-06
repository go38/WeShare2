{include file="header.tpl"}
<div class="rbuttons">
	<a class="btn btn-add" href="{$WWWROOT}artefact/survey/">{str tag="backtosurveys" section="artefact.survey"}</a>
</div>
{if $RESULTS}
<h3>{str tag="results" section="artefact.survey"}</h3>
<div>{$html|safe}</div>
{if $CHART}
<h3>{str tag="chart" section="artefact.survey"}</h3>
<div><img src="{$WWWROOT}artefact/survey/chart.php?id={$id}"></div>
{/if}
{else}
	<div class="message">{str tag="noresults" section="artefact.survey"}</div>
{/if}
{include file="footer.tpl"}
