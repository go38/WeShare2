{include file="header.tpl"}

        <div id="adminlang">
            <form id="helpfileselectionform" method="get">
                <select name="lang" onchange="$('helpfileselectionform').submit()">
                    {foreach from=$lang_options key=code item=name}
                        <option value="{$code}"{if $code eq $current_language} selected="selected"{/if}>{$name}</option>
                    {/foreach}
                </select>
                <select name="helpfile" onchange="$('helpfileselectionform').submit()">
                    {foreach from=$help_files key=path item=name}
                        <option value="{$path}"{if $path eq $current_help_file} selected="selected"{/if}>{$name}</option>
                    {/foreach}
                </select>
                <input type="submit" id="loadhelpfilebutton" value="{str section=admin tag=strloadfile}" />
                <script type="text/javascript">hide_element('loadhelpfilebutton');</script>
            </form>
            {if $lang_help_strings}
            <form id="allhelpstrings" method="POST" action="{$save_all_help_handler}">
            <input type="hidden" id="current_help_file" name="helpfile" value="{$current_help_file}" />
                <table cellspacing="0" cellpadding="1" id="strings" wrap="yes">
                <thead>
                    <tr>
                        <th>{str section=admin tag=stringoriginal}</th>
                        <th>{str section=admin tag=stringtranslated}</th>
                        <th id="msgscol">{str section=admin tag=stringstatus}</th>
                    </tr>
                </thead>
                <tbody>
                {foreach from=$lang_help_strings key=stringid item=string}
                    <tr class="{cycle name=rows values=r1,r0}">
                        <td class="original">
                            <div class="text">{$string->original}</div>
                            <div class="identifier">{$string->id}</div>
                        </td>
                        <td id="translated_{$string->elementid}" class="translated{if $string->translated == ''} empty{/if}" onClick="javascript:toggle_edit('{$string->elementid}',true);">
                        <div class="static hidden" id="static_{$string->elementid}">
                            {$string->translated}
                        </div>
                        <textarea name="strings[{$string->elementid}]" rows="10" cols="40" id="text_{$string->elementid}" onChange="queue_help_string('{$string->elementid}')" onBlur="string_blur('{$string->elementid}')" onFocus="string_focus('{$string->elementid}')">{$string->translated}</textarea>
                        {if $string->translated != ''}
                            <script type="text/javascript">toggle_edit('{$string->elementid}',false);</script>
                        {/if}
                        </td>
                        <td id="msg_{$string->elementid}" class="msgscol">&nbsp;</td>
                    </tr>
                {/foreach}
                </tbody>
                </table>
            <input type="submit" value="{str section=admin tag=savechanges}" id="savechangesbutton" />
            <script type="text/javascript">hide_element('savechangesbutton');</script>
            </form>
            {/if}
        </div>

{include file="admin/upgradefooter.tpl"}
