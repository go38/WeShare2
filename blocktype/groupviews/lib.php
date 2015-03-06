<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-groupviews
 * @author     Liip AG
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2010 Liip AG, http://www.liip.ch
 *
 */

defined('INTERNAL') || die();

require_once('group.php');
class PluginBlocktypeGroupViews extends SystemBlocktype {

    const SORTBY_TITLE = 0;
    const SORTBY_LASTUPDATE = 1;
    const SORTBY_TIMESUBMITTED = 2;

    public static function get_title() {
        return get_string('title', 'blocktype.groupviews');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.groupviews');
    }

    public static function single_only() {
        return true;
    }

    public static function get_categories() {
        return array('general' => 18000);
    }

    public static function get_viewtypes() {
        return array('grouphomepage');
    }

    public static function hide_title_on_empty_content() {
        return true;
    }

    /**
     * This function renders a list of items (views/collections) as html
     *
     * @param array items
     * @param string template
     * @param array options
     * @param array pagination
     */
    public function render_items(&$items, $template, $options, $pagination) {
        $smarty = smarty_core();
        $smarty->assign('options', $options);
        $smarty->assign('items', $items['data']);

        $items['tablerows'] = $smarty->fetch($template);

        if ($items['limit'] && $pagination) {
            $pagination = build_pagination(array(
                'id' => $pagination['id'],
                'class' => 'center',
                'datatable' => $pagination['datatable'],
                'url' => $pagination['baseurl'],
                'jsonscript' => $pagination['jsonscript'],
                'count' => $items['count'],
                'limit' => $items['limit'],
                'offset' => $items['offset'],
                'numbersincludefirstlast' => false,
                'resultcounttextsingular' => $pagination['resultcounttextsingular'] ? $pagination['resultcounttextsingular'] : get_string('result'),
                'resultcounttextplural' => $pagination['resultcounttextplural'] ? $pagination['resultcounttextplural'] :get_string('results'),
            ));
            $items['pagination'] = $pagination['html'];
            $items['pagination_js'] = $pagination['javascript'];
        }
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');
        if (!isset($configdata['showgroupviews'])) {
            // If not set, use default
            $configdata['showgroupviews'] = 1;
        }
        if (!isset($configdata['showsharedviews'])) {
            $configdata['showsharedviews'] = 1;
        }
        if (!isset($configdata['showsharedcollections'])) {
            $configdata['showsharedcollections'] = 1;
        }
        if (!isset($configdata['showsubmitted'])) {
            $configdata['showsubmitted'] = 1;
        }
        $groupid = $instance->get_view()->get('group');
        if (!$groupid) {
            return '';
        }

        $data = self::get_data($groupid, $editing);

        $dwoo = smarty_core();
        $dwoo->assign('group', $data['group']);
        $dwoo->assign('groupid', $data['group']->id);
        $baseurl = $instance->get_view()->get_url();
        $baseurl .= (strpos($baseurl, '?') === false ? '?' : '&') . 'group=' . $groupid;

        if (!empty($configdata['showgroupviews']) && isset($data['groupviews'])) {
            $groupviews = (array)$data['groupviews'];
            $pagination = array(
                'baseurl'    => $baseurl,
                'id'         => 'groupviews_pagination',
                'datatable'  => 'groupviewlist',
                'jsonscript' => 'blocktype/groupviews/groupviews.json.php',
                'resultcounttextsingular' => get_string('view', 'view'),
                'resultcounttextplural'   => get_string('views', 'view'),
            );
            self::render_items($groupviews, 'blocktype:groupviews:groupviewssection.tpl', $configdata, $pagination);
            $dwoo->assign('groupviews', $groupviews);
        }

        if (!empty($configdata['showsharedviews']) && isset($data['sharedviews'])) {
            $sharedviews = (array)$data['sharedviews'];
            $pagination = array(
                'baseurl'    => $baseurl,
                'id'         => 'sharedviews_pagination',
                'datatable'  => 'sharedviewlist',
                'jsonscript' => 'blocktype/groupviews/sharedviews.json.php',
                'resultcounttextsingular' => get_string('view', 'view'),
                'resultcounttextplural'   => get_string('views', 'view'),
            );
            self::render_items($sharedviews, 'blocktype:groupviews:sharedviews.tpl', $configdata, $pagination);
            $dwoo->assign('sharedviews', $sharedviews);
        }
        if (!empty($configdata['showsharedcollections']) && isset($data['sharedcollections'])) {
            $sharedcollections = (array)$data['sharedcollections'];
            $pagination = array(
                'baseurl'    => $baseurl,
                'id'         => 'sharedcollections_pagination',
                'datatable'  => 'sharedcollectionlist',
                'jsonscript' => 'blocktype/groupviews/sharedcollections.json.php',
                'resultcounttextsingular' => get_string('collection', 'collection'),
                'resultcounttextplural'   => get_string('collections', 'collection'),
            );
            self::render_items($sharedcollections, 'blocktype:groupviews:sharedcollections.tpl', $configdata, $pagination);
            $dwoo->assign('sharedcollections', $sharedcollections);
        }
        if (!empty($configdata['showsubmitted']) && isset($data['allsubmitted'])) {
            $allsubmitted = $data['allsubmitted'];
            $pagination = array(
                'baseurl'    => $baseurl,
                'id'         => 'allsubmitted_pagination',
                'datatable'  => 'allsubmissionlist',
                'jsonscript' => 'blocktype/groupviews/allsubmissions.json.php',
                'resultcounttextsingular' => get_string('vieworcollection', 'view'),
                'resultcounttextplural'   => get_string('viewsandcollections', 'view'),
            );
            self::render_items($allsubmitted, 'blocktype:groupviews:allsubmissions.tpl', $configdata, $pagination);
            $dwoo->assign('allsubmitted', $allsubmitted);
        }
        if (isset($data['mysubmitted'])) {
            $dwoo->assign('mysubmitted', $data['mysubmitted']);
        }
        if (!$editing && isset($data['group_view_submission_form'])) {
            $dwoo->assign('group_view_submission_form', $data['group_view_submission_form']);
        }

        return $dwoo->fetch('blocktype:groupviews:groupviews.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        return array(
            'showgroupviews' => array(
                'type' => 'select',
                'description' => get_string('displaygroupviewsdesc', 'blocktype.groupviews'),
                'title' => get_string('displaygroupviews', 'blocktype.groupviews'),
                'options' => array(
                    1 => get_string('yes'),
                    0 => get_string('no'),
                ),
                'defaultvalue' => isset($configdata['showgroupviews']) ? $configdata['showgroupviews'] : 1,
            ),
            'sortgroupviewsby' => array(
                'type' => 'select',
                'title' => get_string('sortgroupviewstitle', 'blocktype.groupviews'),
                'options' => array(
                    PluginBlocktypeGroupViews::SORTBY_TITLE => get_string('sortviewsbyalphabetical', 'blocktype.groupviews'),
                    PluginBlocktypeGroupViews::SORTBY_LASTUPDATE => get_string('sortviewsbylastupdate', 'blocktype.groupviews'),
                ),
                'defaultvalue' => isset($configdata['sortgroupviewsby']) ? (int) $configdata['sortgroupviewsby'] : 0
            ),
            'showsharedviews' => array(
                'type' => 'select',
                'title' => get_string('displaysharedviews', 'blocktype.groupviews'),
                'description' => get_string('displaysharedviewsdesc1', 'blocktype.groupviews'),
                'options' => array(
                    0 => get_string('shownone', 'blocktype.groupviews'),
                    1 => get_string('showbygroupmembers', 'blocktype.groupviews'),
                    2 => get_string('showbyanybody', 'blocktype.groupviews'),
                ),
                'defaultvalue' => isset($configdata['showsharedviews']) ? $configdata['showsharedviews'] : 1,
            ),
            'showsharedcollections' => array(
                'type' => 'select',
                'title' => get_string('displaysharedcollections', 'blocktype.groupviews'),
                'description' => get_string('displaysharedcollectionsdesc', 'blocktype.groupviews'),
                'options' => array(
                    0 => get_string('shownone', 'blocktype.groupviews'),
                    1 => get_string('showbygroupmembers', 'blocktype.groupviews'),
                    2 => get_string('showbyanybody', 'blocktype.groupviews'),
                ),
                'defaultvalue' => isset($configdata['showsharedcollections']) ? $configdata['showsharedcollections'] : 1,
            ),
            'sortsharedviewsby' => array(
                'type' => 'select',
                'title' => get_string('sortsharedviewstitle', 'blocktype.groupviews'),
                'options' => array(
                    PluginBlocktypeGroupViews::SORTBY_TITLE => get_string('sortviewsbyalphabetical', 'blocktype.groupviews'),
                    PluginBlocktypeGroupViews::SORTBY_LASTUPDATE => get_string('sortviewsbylastupdate', 'blocktype.groupviews'),
                ),
                'defaultvalue' => isset($configdata['sortsharedviewsby']) ? (int) $configdata['sortsharedviewsby'] : 0
            ),
            'showsubmitted' => array(
                'type' => 'select',
                'title' => get_string('displaysubmissions', 'blocktype.groupviews'),
                'description' => get_string('displaysubmissionsdesc', 'blocktype.groupviews'),
                'options' => array(
                    1 => get_string('yes'),
                    0 => get_string('no'),
                ),
                'defaultvalue' => isset($configdata['showsubmitted']) ? $configdata['showsubmitted'] : 1,
            ),
            'sortsubmittedby' => array(
                'type' => 'select',
                'title' => get_string('sortsubmittedtitle', 'blocktype.groupviews'),
                'options' => array(
                    PluginBlocktypeGroupViews::SORTBY_TITLE => get_string('sortviewsbyalphabetical', 'blocktype.groupviews'),
                    PluginBlocktypeGroupViews::SORTBY_TIMESUBMITTED => get_string('sortviewsbytimesubmitted', 'blocktype.groupviews'),
                ),
                'defaultvalue' => isset($configdata['sortsubmittedby']) ? (int) $configdata['sortsubmittedby'] : 0
            ),
            'count' => array(
                'type' => 'text',
                'title' => get_string('itemstoshow', 'blocktype.groupviews'),
                'description'  => get_string('itemstoshowdesc', 'blocktype.groupviews'),
                'defaultvalue' => isset($configdata['count']) ? $configdata['count'] : 5,
                'size'         => 3,
                'rules'        => array('integer' => true, 'minvalue' => 1, 'maxvalue' => 100),
            ),
        );
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    protected static function get_data($groupid, $editing=false) {
        global $USER;

        if(!defined('GROUP')) {
            define('GROUP', $groupid);
        }
        // get the currently requested group
        $group = group_current_group();
        $role = group_user_access($group->id);
        $bi = group_get_homepage_view_groupview_block($group->id);
        $configdata = $bi->get('configdata');
        if (!isset($configdata['sortsubmittedby']) || $configdata['sortsubmittedby'] == PluginBlocktypeGroupViews::SORTBY_TITLE) {
            $sortsubmittedby = 'c.name, v.title';
        }
        else {
            $sortsubmittedby = 'c.submittedtime DESC, v.submittedtime DESC';
        }
        if ($role) {
            $limit = isset($configdata['count']) ? intval($configdata['count']) : 5;
            $limit = ($limit > 0) ? $limit : 5;

            // Get all views created in the group
            // Sortorder: Group homepage should be first, then sort by sortorder
            $sort = array(
                    array(
                            'column' => "type='grouphomepage'",
                            'desc' => true
                    )
            );
            // Find out what order to sort them by (default is titles)
            if (!isset($configdata['sortgroupviewsby']) || $configdata['sortgroupviewsby'] == PluginBlocktypeGroupViews::SORTBY_TITLE) {
                $sort[] = array('column' => 'title');
            }
            else {
                $sort[] = array('column' => 'mtime', 'desc' => true);
            }
            $data['groupviews'] = View::view_search(null, null, (object) array('group' => $group->id), null, $limit, 0, true, $sort);
            foreach ($data['groupviews']->data as &$view) {
                if (!$editing && isset($view['template']) && $view['template']) {
                    $view['form'] = pieform(create_view_form(null, null, $view['id']));
                }
            }

            // Find out what order to sort them by (default is titles)
            if (!isset($configdata['sortsharedviewsby']) || $configdata['sortsharedviewsby'] == PluginBlocktypeGroupViews::SORTBY_TITLE) {
                $sortsharedviewsby = 'v.title';
                $sortsharedcollectionsby = array(array('column'=>'c.name'));
            }
            else {
                $sortsharedviewsby = 'v.mtime DESC';
                $sortsharedcollectionsby = array(
                        array(
                                'column'=>'GREATEST(c.mtime, (SELECT MAX(v.mtime) FROM {view} v INNER JOIN {collection_view} cv ON v.id=cv.view WHERE cv.collection=c.id))',
                                'desc' => true
                        )
                );
            }

            // For group members, display a list of views that others have
            // shared to the group
            if (empty($configdata['showsharedviews'])) {
                $data['sharedviews'] = (object) array(
                    'data'   => array(),
                    'count'  => 0,
                    'limit'  => $limit,
                    'offset' => 0
                );
            }
            else {
                $data['sharedviews'] = View::get_sharedviews_data(
                        $limit,
                        0,
                        $group->id,
                        ($configdata['showsharedviews'] == 2 ? false : true),
                        $sortsharedviewsby
                );
                foreach ($data['sharedviews']->data as &$view) {
                    if (!$editing && isset($view['template']) && $view['template']) {
                        $view['form'] = pieform(create_view_form($group, null, $view->id));
                    }
                }
            }

            if (empty($configdata['showsharedcollections'])) {
                $data['sharedcollections'] = (object) array(
                    'data'   => array(),
                    'count'  => 0,
                    'limit'  => $limit,
                    'offset' => 0
                );
            }
            else {
                $data['sharedcollections'] = View::get_sharedcollections_data(
                        $limit,
                        0,
                        $group->id,
                        ($configdata['showsharedcollections'] == 2 ? false : true),
                        $sortsharedcollectionsby
                );
            }

            if (group_user_can_assess_submitted_views($group->id, $USER->get('id'))) {
                // Display a list of views submitted to the group
                list($collections, $views) = View::get_views_and_collections(null, null, null, null, false, $group->id, $sortsubmittedby);
                $allsubmitted = array_merge(array_values($collections), array_values($views));
                $data['allsubmitted'] = array(
                    'data'   => array_slice($allsubmitted, 0, $limit),
                    'count'  => count($allsubmitted),
                    'limit'  => $limit,
                    'offset' => 0,
                );
            }

        }

        if ($group->submittableto) {
            require_once('pieforms/pieform.php');
            // A user can submit more than one view to the same group, but no view can be
            // submitted to more than one group.

            // Display a list of views this user has submitted to this group, and a submission
            // form containing drop-down of their unsubmitted views.

            list($collections, $views) = View::get_views_and_collections($USER->get('id'), null, null, null, false, $group->id, $sortsubmittedby);
            $data['mysubmitted'] = array_merge(array_values($collections), array_values($views));

            // Only render the submission form in viewing mode
            if (!$editing) {
                $data['group_view_submission_form'] = group_view_submission_form($group->id);
            }
        }
        $data['group'] = $group;
        return $data;
    }

    public static function get_instance_title() {
        return get_string('title', 'blocktype.groupviews');
    }
}
