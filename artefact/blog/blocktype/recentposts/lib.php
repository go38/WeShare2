<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-recentposts
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeRecentposts extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.blog/recentposts');
    }


    public static function get_description() {
        return get_string('description', 'blocktype.blog/recentposts');
    }

    public static function get_categories() {
        return array('blog' => 12000);
    }

    public static function get_instance_javascript(BlockInstance $bi) {
        $blockid = $bi->get('id');
        return array(
            array(
                'file'   => 'js/recentposts.js',
                'initjs' => "addNewPostShortcut($blockid);",
            )
        );
    }

    /**
     * Returns a list of artefact IDs that are "in" this blockinstance.
     *
     * {@internal{Because links to artefacts within blogposts don't count
     * as making those artefacts 'children' of the blog post, we have to add
     * them directly to the blog.}}
     *
     * @return array List of artefact IDs that are 'in' this blog - all
     *               blogposts in it plus all links to other artefacts that are
     *               part of the blogpost text. Note that proper artefact
     *               children, such as blog post attachments, aren't included -
     *               the artefact parent cache is used for them
     * @see PluginBlocktypeBlogPost::get_artefacts()
     */
    public static function get_artefacts(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $artefacts = array();
        if (isset($configdata['artefactids'])) {
            if (isset($configdata['artefactids']) && is_array($configdata['artefactids'])) {
                $artefacts = array_merge($artefacts, $configdata['artefactids']);
            }
            $blogposts = self::get_blog_posts_in_block($instance);
            foreach ($blogposts as $blogpost) {
                $artefacts[] = $blogpost->id;
                $blogpostobj = $instance->get_artefact_instance($blogpost->id);
                $artefacts = array_merge($artefacts, $blogpostobj->get_referenced_artefacts_from_postbody());
            }
        }
        return $artefacts;
    }

    /**
     * Get the blog entries that will be displayed by this block.
     * (This list will change depending when new blog entries are created, published, etc
     *
     * @param BlockInstance $instance
     * @return array of objects
     */
    public static function get_blog_posts_in_block(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $limit = isset($configdata['count']) ? (int) $configdata['count'] : 10;
        $mostrecent = array();
        if (!empty($configdata['artefactids'])) {
            $before = 'TRUE';
            if ($instance->get_view()->is_submitted()) {
                if ($submittedtime = $instance->get_view()->get('submittedtime')) {
                    // Don't display posts added after the submitted date.
                    $before = "a.ctime < '$submittedtime'";
                }
            }

            $blogids = $configdata['artefactids'];
            $artefactids = implode(', ', array_map('db_quote', $blogids));
            $mostrecent = get_records_sql_array(
                'SELECT a.title, ' . db_format_tsfield('a.ctime', 'ctime') . ', p.title AS parenttitle, a.id, a.parent
                    FROM {artefact} a
                    JOIN {artefact} p ON a.parent = p.id
                    JOIN {artefact_blog_blogpost} ab ON (ab.blogpost = a.id AND ab.published = 1)
                    WHERE a.artefacttype = \'blogpost\'
                    AND a.parent IN ( ' . $artefactids . ' )
                    AND a.owner = (SELECT "owner" from {view} WHERE id = ?)
                    AND ' . $before . '
                    ORDER BY a.ctime DESC, a.id DESC
                    LIMIT ' . $limit, array($instance->get('view')));
            if (!$mostrecent) {
                $mostrecent = array();
            }
        }
        return $mostrecent;
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {

        $result = '';
        $mostrecent = self::get_blog_posts_in_block($instance);
        if ($mostrecent) {
            // format the dates
            foreach ($mostrecent as &$data) {
                $data->displaydate = format_date($data->ctime);
            }
            $smarty = smarty_core();
            $smarty->assign('mostrecent', $mostrecent);
            $smarty->assign('view', $instance->get('view'));
            $smarty->assign('blockid', $instance->get('id'));
            $smarty->assign('editing', $editing);
            if ($editing) {
                // Get id and title of configued blogs
                $recentpostconfigdata = $instance->get('configdata');
                $wherestm = ' WHERE id IN (' . join(',', array_fill(0, count($recentpostconfigdata['artefactids']), '?')) . ')';
                if (!$selectedblogs = get_records_sql_array('SELECT id, title FROM {artefact}'. $wherestm, $recentpostconfigdata['artefactids'])) {
                    $selectedblogs = array();
                }
                $smarty->assign('blogs', $selectedblogs);
            }
            $result = $smarty->fetch('blocktype:recentposts:recentposts.tpl');
        }

        return $result;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        safe_require('artefact', 'blog');
        $configdata = $instance->get('configdata');
        $elements = array(self::artefactchooser_element((isset($configdata['artefactids'])) ? $configdata['artefactids'] : null),
            'count' => array(
                'type' => 'text',
                'title' => get_string('itemstoshow', 'blocktype.blog/recentposts'),
                'description'   => get_string('betweenxandy', 'mahara', 1, 100),
                'defaultvalue' => isset($configdata['count']) ? $configdata['count'] : 10,
                'size' => 3,
                'rules' => array('integer' => true, 'minvalue' => 1, 'maxvalue' => 100),
            ),
        );
	
        return $elements;
    }

    public static function artefactchooser_element($default=null) {
        return array(
            'name'  => 'artefactids',
            'type'  => 'artefactchooser',
            'title' => get_string('blogs', 'artefact.blog'),
            'defaultvalue' => $default,
            'blocktype' => 'recentposts',
            'limit'     => 10,
            'selectone' => false,
            'artefacttypes' => array('blog'),
            'template'  => 'artefact:blog:artefactchooser-element.tpl',
        );
    }

    public static function default_copy_type() {
        return 'nocopy';
    }

    /**
     * Recentposts blocktype is only allowed in personal views, because
     * currently there's no such thing as group/site blogs
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

}
