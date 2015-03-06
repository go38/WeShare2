<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Melissa Draper <melissa@catalyst.net.nz>, Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

define('INTERNAL', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('view.php');
require_once('group.php');
safe_require('artefact', 'comment');
define('TITLE', get_string('report', 'group'));
define('MENUITEM', 'groups/report');
define('GROUP', param_integer('group'));

$wwwroot = get_config('wwwroot');
$needsubdomain = get_config('cleanurlusersubdomains');

$limit = param_integer('limit', 0);
$userlimit = get_account_preference($USER->get('id'), 'viewsperpage');
if ($limit > 0 && $limit != $userlimit) {
    $USER->set_account_preference('viewsperpage', $limit);
}
else {
    $limit = $userlimit;
}
$offset = param_integer('offset', 0);
$sort = param_variable('sort', 'title');
$direction = param_variable('direction', 'asc');
$group = group_current_group();
$role = group_user_access($group->id);
if (!group_role_can_access_report($group, $role)) {
    throw new AccessDeniedException();
}

$sharedviews = View::get_participation_sharedviews_data($group->id, $sort, $direction, $limit, $offset);

$pagination = array(
    'baseurl'    => $wwwroot . 'group/report.php?group=' . $group->id . '&sort=' . $sort . '&direction=' . $direction,
    'id'         => 'sharedviews_pagination',
    'datatable'  => 'sharedviewsreport',
    'jsonscript' => 'group/participationsharedviews.json.php',
    'setlimit'   => true,
    'resultcounttextsingular' => get_string('view', 'view'),
    'resultcounttextplural'   => get_string('views', 'view'),
);

View::render_participation_views($sharedviews, 'group/participationsharedviews.tpl', $pagination);

$groupviews = View::get_participation_groupviews_data($group->id, $sort, $direction, $limit, $offset);

$pagination = array(
    'baseurl'    => $wwwroot . 'group/report.php?group=' . $group->id . '&sort=' . $sort . '&direction=' . $direction,
    'id'         => 'groupviews_pagination',
    'datatable'  => 'groupviewsreport',
    'jsonscript' => 'group/participationgroupviews.json.php',
    'setlimit'   => true,
    'resultcounttextsingular' => get_string('view', 'view'),
    'resultcounttextplural'   => get_string('views', 'view'),
);

View::render_participation_views($groupviews, 'group/participationgroupviews.tpl', $pagination);

$smarty = smarty(array('paginator'));
$smarty->assign('baseurl', get_config('wwwroot') . 'group/report.php?group=' . $group->id);
$smarty->assign('heading', $group->name);
$smarty->assign('sharedviews', $sharedviews);
$smarty->assign('groupviews', $groupviews);
$smarty->assign('sort', $sort);
$smarty->assign('direction', $direction);
$smarty->display('group/report.tpl');
