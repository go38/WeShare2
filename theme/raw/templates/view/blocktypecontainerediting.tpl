    <div class="blockinstance cb{if $configure} configure{elseif $retractable} retractable{/if}" id="blockinstance_{$id}{if $configure}_configure{/if}">
        <div class="blockinstance-controls">
        <input type="image" src="{theme_url filename=images/btn_move.png}" class="keyboardmovebutton nojs-hidden-inline" name="action_moveblockinstance_id_{$id}" alt="{$strmovetitletext}">
        {if $retractable && !$configure}
            <img src="{theme_url filename=images/retractable.png}" alt="{str tag='retractable' section='view'}" title="{str tag='retractable' section='view'}" class="retractablebtn">
        {/if}
        {if $configurable && !$configure}    <input type="image" src="{theme_url filename=images/btn_configure.png}" class="configurebutton" name="action_configureblockinstance_id_{$id}" alt="{$strconfigtitletext}">{/if}
            {if $configure}<input type="image" src="{theme_url filename=images/btn_close.png}" class="deletebutton" name="action_removeblockinstance_id_{$id}" alt="{str tag=Close}">{else}<input type="image" src="{theme_url filename=images/btn_deleteremove.png}" class="deletebutton" name="action_removeblockinstance_id_{$id}" alt="{$strremovetitletext}">{/if}
        </div>
        <div class="blockinstance-header">
            <h2 class="title">{if $configure}{$configtitle}: {str tag=Configure section=view}{else}{$title|default:"[$strnotitle]"}{/if}</h2>
        </div>
        <div class="blockinstance-content">
            {$content|safe}
        </div>
    </div>
