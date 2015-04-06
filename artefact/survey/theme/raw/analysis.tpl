{include file="header.tpl"}

{if $RESULTS > 1}
{if $pagedescription}
  <p>{$pagedescription}</p>
{elseif $pagedescriptionhtml}
  {$pagedescriptionhtml|safe}
{/if}
{$form|safe}
<br><div id="exportgeneration">
<iframe src="{$WWWROOT}artefact/survey/analysis/exportcsv.php" scrolling="no" frameborder="none" style="border:0px; width:100%; height:50px;"></iframe>
</div>
{else}
	<div class="message">{str tag="noaccessiblesurveys" section="artefact.survey"}</div>
{/if}

{include file="footer.tpl"}
