{include file="header.tpl"}
<div class="rbuttons">
	<a class="btn btn-add" href="{$WWWROOT}artefact/survey/">{str tag="backtosurveys" section="artefact.survey"}</a>
</div>
{if $RESPONSES}
<h3>{str tag="responses" section="artefact.survey"}</h3>
<div>{$html|safe}</div>
{else}
	<div class="message">{str tag="noresponses" section="artefact.survey"}</div>
{/if}
{include file="footer.tpl"}
