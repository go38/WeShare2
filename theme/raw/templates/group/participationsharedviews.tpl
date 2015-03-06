{if count($items) > 0}
  {foreach from=$items item=view}
    <tr class="{cycle values='r0,r1'}">
      <td class="sv"><h3 class="title"><a href="{$view->url}">{$view->title}</a></h3></td>
      <td class="sb"><label class="hidden">{str tag=sharedby section=view}: </label>
    {if $view->owner}
            <a href="{$WWWROOT}user/view.php?id={$view->owner}">{$view->owner|display_name:null:true}</a>
    {elseif $view->group}
            <a href="{$WWWROOT}group/view.php?id={$view->group}">{$view->groupname}</a>
    {elseif $view->institution}
            <a href="{$WWWROOT}institution/view.php?id={$view->institution}">{$view->institution}</a>
    {/if}
      </td>
      <td class="mc"><label class="hidden">{str tag=membercommenters section=group}: </label>
        <ul>
    {foreach from=$view->comments key=commenter item=info}
        {if $info.member}<li><a href="{$WWWROOT}user/view.php?id={$info.commenter}">{$info.commenter|display_name:null:true}</a><span> ({$info.count})</span></li>{/if}
    {/foreach}
        </ul>
    {if $view->mcomments > 0}<div class="detail">{$view->mcomments} {str tag=comments section=artefact.comment}</div>{/if}
      </td>
      <td class="ec"><label class="hidden">{str tag=extcommenters section=group}: </label>
        <ul>
    {foreach from=$view->comments key=commenter item=info}
        {if $info.commenter|is_string}
          <li>{$info.commenter}<span> ({$info.count})</span></li>
        {elseif ! $info.member}
          <li><a href="{$WWWROOT}user/view.php?id={$info.commenter}">{$info.commenter|display_name:null:true}</a><span> ({$info.count})</span></li>
        {/if}
    {/foreach}
        </ul>
    {if $view->ecomments > 0}<div class="detail">{$view->ecomments} {str tag=comments section=artefact.comment}</div>{/if}
      </td>
    </tr>
  {/foreach}
{else}
    <tr class="{cycle values='r0,r1'}"><td colspan="4" class="message">{str tag=noviewssharedwithgroupyet section=group}</td></tr>
{/if}