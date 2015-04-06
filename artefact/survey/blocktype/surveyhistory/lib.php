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
 * @subpackage blocktype-surveyhistory
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2010-2011 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeSurveyHistory extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.survey/surveyhistory');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.survey/surveyhistory');
    }

    public static function get_categories() {
        return array('survey');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        safe_require('artefact', 'survey');
		//require_once(dirname(dirname(dirname(__FILE__))) . '/dwoo/function.survey_name.php');
        $configdata = $instance->get('configdata'); // this will make sure to unserialize it for us
		
        $survey = (isset($configdata['survey']) ? $configdata['survey'] : '');
        $userid = (isset($configdata['userid']) ? $configdata['userid'] : '');

        //$showresponses = (isset($configdata['showresponses']) ? $configdata['showresponses'] : false);
        //$showresults = (isset($configdata['showresults']) ? $configdata['showresults'] : true);
        $showchart = (isset($configdata['showchart']) ? $configdata['showchart'] : true);

        $palette = (isset($configdata['palette']) ? $configdata['palette'] : 'default');
        $legend = (isset($configdata['legend']) ? $configdata['legend'] : 'key');
        $fonttype = (isset($configdata['fonttype']) ? $configdata['fonttype'] : 'chinese');
        $fontsize = (isset($configdata['fontsize']) ? $configdata['fontsize'] : 10);
        $height = (isset($configdata['height']) ? $configdata['height'] : 350);
        $width = (isset($configdata['width']) ? $configdata['width'] : 400);

		$artefactids = get_records_sql_array(
			"SELECT	a.id
			FROM {artefact} a
			WHERE a.artefacttype = 'survey' AND a.title = ? AND a.owner = ?
			ORDER BY a.ctime DESC, a.mtime DESC", array($survey, $userid)
			);

		$data = array();
		$first = true;
		foreach ($artefactids as $artefactid) {
			if ($first) { $alpha = 30; }
			else { $alpha = 10; }
			$data[] = array(
				'id' => $artefactid->id,
				'palette' => $palette,
				'legend' => $legend,
				'fonttype' => $fonttype,
				'fontsize' => $fontsize,
				'height' => $height,
				'width' => $width,
				'alpha' => $alpha,
			);
			$first = false;
		}

        $smarty = smarty_core();
        //$smarty->addPlugin('survey_name', 'Dwoo_Plugin_survey_name');
		$smarty->assign('CHART', ($showchart ? true : false));
		$smarty->assign('data', $data);
        return $smarty->fetch('blocktype:surveyhistory:surveyhistory.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
		global $USER;
        safe_require('artefact', 'survey');
        $configdata = $instance->get('configdata');
		log_debug($configdata);
		$options = getoptions_available_surveys();
        return array(
			'userid' => array(
				'type' => 'hidden',
				'value' => $USER->get('id'),
			),
			/*
            'includetitle' => array(
                'type'  => 'checkbox',
                'title' => get_string('includetitle','blocktype.survey/surveyhistory'),
                'defaultvalue' => (!empty($configdata['includetitle']) ? $configdata['includetitle'] : 1),
            ),
			*/
            'survey' => array(
				'type' => ($options ? 'select' : 'html'),
				'labelhtml' => get_string('surveytitle', 'artefact.survey'),
				'defaultvalue' => (isset($configdata['survey'])) ? $configdata['survey'] : null,
				'value' => ($options ? null : '<div id="artefactchooser-body"><p class="noartefacts">' . get_string('noartefactstochoosefrom', 'view') . '</p></div>'),
				'options' => $options,
			),
			'steps' => array(
				'type' => 'select',
				'labelhtml' => get_string('surveyhistorysteps', 'blocktype.survey/surveyhistory'),
				'defaultvalue' => (isset($configdata['steps'])) ? $configdata['steps'] : 5,
				'options' => array(
					2 => 2,
					3 => 3,
					4 => 4,
					5 => 5,
					6 => 6,
					7 => 7,
					8 => 8,
					9 => 9,
				),
			),
			/*
            'showresponses' => array(
				'type' => 'checkbox',
				'title' => get_string('showresponses', 'blocktype.survey/surveyhistory'),
				'defaultvalue' => (isset($configdata['showresponses'])) ? $configdata['showresponses'] : false,
			),
            'showresults' => array(
				'type' => 'checkbox',
				'title' => get_string('showresults', 'blocktype.survey/surveyhistory'),
				'defaultvalue' => (isset($configdata['showresults'])) ? $configdata['showresults'] : true,
			),
			*/
            'showchart' => array(
				'type' => 'checkbox',
				'title' => get_string('showchart', 'blocktype.survey/surveyhistory'),
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
		log_debug($values);
        if (!empty($values['includetitle'])) {
			$titles = getoptions_available_surveys();
			$values['title'] = get_string('title', 'blocktype.survey/surveyhistory') . ': ' . $titles[$values['survey']];
		}
		return $values;
	}
	*/
	
    public static function artefactchooser_element($default=null) {
		//
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


function getoptions_available_surveys() {
	// Get names of all the survey responses (from other users) that are accessible to our user...
	global $USER;
	$results = get_records_sql_array(
		"SELECT	a.title
		FROM {artefact} a
		WHERE a.artefacttype = 'survey' AND a.owner = ?", array($USER->get('id'))
	);
	if (!$results) return false;

	$available_surveys = array();
	foreach ($results as $result) {
		if (!in_array($result->title, $available_surveys)) {
			$available_surveys[] = $result->title;
		}
	}
	
	// Prepare our available surveys array so we can use it as options in drop-down select box...
	$survey_types = array();
	foreach ($available_surveys as $filename) {
		$LANGUAGE = ArtefactTypeSurvey::get_default_lang($filename);
		$xmlDoc = new DOMDocument('1.0', 'UTF-8');
		$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $filename);
		# Return http response in string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$loaded = $xmlDoc->loadXML(curl_exec($ch));
		if ($loaded) {
			$surveyname = $xmlDoc->getElementsByTagName('survey')->item(0)->getAttribute('name');
			if (isset($surveyname) && $surveyname == substr($filename, 0, -4)) {
				$charttype = $xmlDoc->getElementsByTagName('chart')->item(0);
				if (!empty($charttype)) {
					$title = $xmlDoc->getElementsByTagName('title')->item(0)->getAttribute($LANGUAGE);
					$survey_types = array_merge($survey_types, array($filename => $title));
				}
			} else {
				$message = get_string('surveynameerror', 'artefact.survey', $filename);
				$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
			}
		} else {
			$message = get_string('surveyerror', 'artefact.survey', $filename);
			$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
		}
	}
	return $survey_types;		
}

