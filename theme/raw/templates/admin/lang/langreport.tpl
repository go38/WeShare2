{include file='header.tpl' nosearch='true'}

    <div id="adminlangreport">

        <div id="reportselectionwrapper">
            <form id="reportselectionform" method="get">
                <select name="lang" onchange="$('reportselectionform').submit()">
                    {foreach from=$lang_options key=code item=name}
                        <option value="{$code}"{if $code eq $current_language} selected="selected"{/if}>{$name}</option>
                    {/foreach}
                </select>
                <select name="report" onchange="$('reportselectionform').submit()">
                    {foreach from=$available_reports key=id item=name}
                    <option value="{$id}"{if $id eq $reportid} selected="selected"{/if}>{$name}</option>
                    {/foreach}
                </select>
                <input type="submit" id="choosereportbutton" value="{str section=admin tag=langchoosereport}" />
                <script type="text/javascript">hide_element('choosereportbutton');</script>
            </form>
        </div>
 
        {if $reportid eq "missing"}
            {include file="admin/lang/langreport-missing.tpl"}
        {/if}

    </div>

{include file='admin/upgradefooter.tpl'}
