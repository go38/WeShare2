{include file="header.tpl"}
            <div class="rbuttons">
                <a class="btn btn-add" href="{$WWWROOT}artefact/survey/settings.php?new=1">{str tag="addsurvey" section="artefact.survey"}</a>
                <a class="btn" href="{$WWWROOT}artefact/survey/analysis/">{str tag="surveyanalysis" section="artefact.survey"}</a>
            </div>
		<div id="mysurveys rel">
{if !$surveys}
        <div class="message">{$strnosurveysaddone|safe}</div>
{else}
        <script type="text/javascript">
            function confirmdelete(id) {
                if(confirm("{str tag=deletesurvey section=artefact.survey}")) {
                    window.location = "{$WWWROOT}artefact/survey/index.php?delete=" + id;
                }
            }
        </script>
        <table id="surveylist" class="tablerenderer fullwidth">
			<tr>
				<th style="width:20px; !important;"></th>
				<th style="padding-left:5px">{str section=artefact.survey tag=surveytitle}</th>
				<th style="padding-left:5px">{str section=artefact.survey tag=surveycreated}</th>
				<th style="padding-left:5px">{str section=artefact.survey tag=surveymodified}</th>
				<th style="width:80px; !important;"></th>
				<th style="width:80px; !important;"></th>
				<th style="width:80px; !important;"></th>
			</tr>
            {foreach from=$surveys item=survey}
            <tr class="{cycle values='r0,r1'}">
                <td width="20">
					{if $base64images}
						<div style="{$survey->flagicon}">&nbsp;</div>
					{else}
						{$survey->note|truncate:2:''}
					{/if}
                </td>
                <td>
                    <strong><a href="{$WWWROOT}artefact/survey/edit.php?id={$survey->id}">{$survey->title}</a></strong>
                </td>
                <td>
					{$survey->ctime|strtotime|format_date:'strfdaymonthyearshort'}
                </td>
                <td>
					{$survey->mtime|strtotime|format_date:'strfdaymonthyearshort'}
                </td>
                <td class="right">
                    <a href="{$WWWROOT}artefact/survey/settings.php?id={$survey->id}" class="btn-manage">{str section=artefact.survey tag=settings}</a>
                </td>
                <td class="right">
                    <a href="{$WWWROOT}artefact/survey/results.php?id={$survey->id}" class="btn-request">{str section=artefact.survey tag=results}</a>
                </td>
                <td class="right">
                    <a href="#" onClick="confirmdelete({$survey->id});" class="btn-del">{str tag=delete}</a>
                </td>
            </tr>
            {/foreach}
        </table>
        {$pagination|safe}
{/if}
        </div>
{include file="footer.tpl"}
