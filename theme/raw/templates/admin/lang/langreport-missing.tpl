<div id="adminlangreport-missing">
    <dl>
    {foreach name=files from=$lang_strings key=filepath item=missingstrings}
        <dt>{$lang_files.$filepath}</dt>
        {foreach name=strings from=$missingstrings key=stringid item=stringoriginal}
            <dd>$string['<a href="lang.php?file={$filepath}#translated_{$stringid}">{$stringid}</a>'] = '{$stringoriginal}';</dd>
        {/foreach}
    {foreachelse}
        {str section=admin tag=langnomissingstrings}
    {/foreach}
    </dl>
</div>
