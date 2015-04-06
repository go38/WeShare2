<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2011 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage blocktype-survey
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2010-2011 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeSurvey extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.survey/survey');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.survey/survey');
    }

    public static function get_categories() {
        return array('survey');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        safe_require('artefact', 'survey');
		//require_once(dirname(dirname(dirname(__FILE__))) . '/dwoo/function.survey_name.php');
        $configdata = $instance->get('configdata'); // this will make sure to unserialize it for us

        if (!isset($configdata['artefactid'])) {
            return '';
        }
        $id = $configdata['artefactid'];
        $survey = $instance->get_artefact_instance($id);
		
        $showresponses = (isset($configdata['showresponses']) ? $configdata['showresponses'] : false);
        $showresults = (isset($configdata['showresults']) ? $configdata['showresults'] : true);
        $showchart = (isset($configdata['showchart']) ? $configdata['showchart'] : true);

        $palette = (isset($configdata['palette']) ? $configdata['palette'] : 'default');
        $legend = (isset($configdata['legend']) ? $configdata['legend'] : 'key');
        $fonttype = (isset($configdata['fonttype']) ? $configdata['fonttype'] : 'sans');
        $fontsize = (isset($configdata['fontsize']) ? $configdata['fontsize'] : 10);
        $height = (isset($configdata['height']) ? $configdata['height'] : 250);
        $width = (isset($configdata['width']) ? $configdata['width'] : 400);

        $smarty = smarty_core();
        //$smarty->addPlugin('survey_name', 'Dwoo_Plugin_survey_name');
		$smarty->assign('RESPONSES', ($showresponses ? true : false));
		$smarty->assign('responseshtml', ArtefactTypeSurvey::build_user_responses_output_html($survey->get('title'), unserialize($survey->get('description'))));
		$smarty->assign('RESULTS', ($showresults ? true : false));
		$smarty->assign('resultshtml', ArtefactTypeSurvey::build_user_responses_summary_html($survey->get('title'), unserialize($survey->get('description'))));
		$smarty->assign('CHART', ($showchart ? true : false));
		$smarty->assign('charturl', get_config('wwwroot') . 'artefact/survey/chart.php?id=' . $id . '&width=' . $width . '&height=' . $height . '&palette=' . $palette . '&legend=' . $legend . '&fonttype=' . $fonttype . '&fontsize=' . $fontsize);
        return $smarty->fetch('blocktype:survey:survey.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        safe_require('artefact', 'survey');
        $configdata = $instance->get('configdata');
        return array(
			/*
            'includetitle' => array(
                'type'  => 'checkbox',
                'title' => get_string('includetitle','blocktype.survey/survey'),
                'defaultvalue' => (!empty($configdata['includetitle']) ? $configdata['includetitle'] : 1),
            ),
			*/
            'artefactid' => self::artefactchooser_element((isset($configdata['artefactid'])) ? $configdata['artefactid'] : null),
            'showresponses' => array(
				'type' => 'checkbox',
				'title' => get_string('showresponses', 'blocktype.survey/survey'),
				'defaultvalue' => (isset($configdata['showresponses'])) ? $configdata['showresponses'] : false,
			),
            'showresults' => array(
				'type' => 'checkbox',
				'title' => get_string('showresults', 'blocktype.survey/survey'),
				'defaultvalue' => (isset($configdata['showresults'])) ? $configdata['showresults'] : true,
			),
            'showchart' => array(
				'type' => 'checkbox',
				'title' => get_string('showchart', 'blocktype.survey/survey'),
				'defaultvalue' => (isset($configdata['showchart'])) ? $configdata['showchart'] : true,
			),
            'chartoptions' => array(
				'type' => 'fieldset',
				'legend' => get_string('chartoptions', 'artefact.survey'),
				'collapsible' => true,
				'collapsed' => true,
				'elements' => ArtefactTypeSurvey::get_chart_options_elements($configdata),
			),
        );
    }

	/*
    public static function instance_config_save($values) {
        if (!empty($values['includetitle'])) {
			$survey = artefact_instance_from_id($values['artefactid']);
			$values['title'] = get_string('Survey', 'artefact.survey') . ': ' . ArtefactTypeSurvey::get_survey_title_from_xml($survey->get('title'));
		}
		return $values;
	}
	*/
	
    public static function artefactchooser_element($default=null) {
        return array(
            'name'  => 'artefactid',
            'type'  => 'artefactchooser',
            'title' => get_string('title', 'blocktype.survey/survey'),
            'defaultvalue' => $default,
            'blocktype' => 'survey',
            'limit'     => 10,
            'selectone' => true,
            'search'    => false,
            'artefacttypes' => array('survey'),
            'template'  => 'artefact:survey:artefactchooser-element.tpl',
        );
    }

    public static function default_copy_type() {
        return 'full';
    }

    /**
     * Survey blocktype is only allowed in personal views, because currently 
     * there's no such thing as group/site surveys
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

}

?>
