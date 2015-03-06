<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
    <meta property="og:title" content="{$PAGETITLE}" />
    <meta property="og:description" content="{$sitedescription4facebook}" />
    <meta property="og:image" content="{$sitelogo4facebook}" />
    {if isset($PAGEAUTHOR)}<meta name="author" content="{$PAGEAUTHOR}">{/if}
    <title>{$PAGETITLE}</title>
    <script type="text/javascript">
    var config = {literal}{{/literal}
        'theme': {$THEMELIST|safe},
        'sesskey' : '{$SESSKEY}',
        'wwwroot': '{$WWWROOT}',
        'loggedin': {$USER->is_logged_in()|intval},
        'userid': {$USER->get('id')},
        'mobile': {if $MOBILE}1{else}0{/if},
        'handheld_device': {if $HANDHELD_DEVICE}1{else}0{/if}
    {literal}}{/literal};
    </script>
    {$STRINGJS|safe}
{foreach from=$JAVASCRIPT item=script}
    <script type="text/javascript" src="{$script}"></script>
{/foreach}
{foreach from=$HEADERS item=header}
    {$header|safe}
{/foreach}
{if isset($INLINEJAVASCRIPT)}
    <script type="text/javascript">
{$INLINEJAVASCRIPT|safe}
    </script>
{/if}
{foreach from=$STYLESHEETLIST item=cssurl}
    <link rel="stylesheet" type="text/css" href="{$cssurl}">
{/foreach}
    <link rel="stylesheet" type="text/css" href="{theme_url filename='style/print.css'}?v={$RELEASE}" media="print">
	<link href='//fonts.googleapis.com/css?family=Oswald:400,700' rel='stylesheet' type='text/css'>
	<link href='//fonts.googleapis.com/css?family=PT+Sans:400,700,400italic' rel='stylesheet' type='text/css'>
    <script type="text/javascript" src="{$WWWROOT}js/css.js?v={$RELEASE}"></script>
    <link rel="shortcut icon" href="{$WWWROOT}favicon.ico?v={$RELEASE}" type="image/vnd.microsoft.icon">
    <link rel="image_src" href="{$sitelogo}?v={$RELEASE}">
  <!-- Responsive design -->
        <script type="text/javascript" src="{$WWWROOT}js/rd-nav.js?v={$RELEASE}"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
{if $ADDITIONALHTMLHEAD}{$ADDITIONALHTMLHEAD|safe}{/if}
</head>
{dynamic}{flush}{/dynamic}
