<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage artefact-survey
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2010-2011 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

define('INTERNAL', true);
define('MENUITEM', 'content/surveys');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'survey');
define('SECTION_PAGE', 'analysis');

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
define('TITLE', get_string('surveyanalysis', 'artefact.survey'));
require_once('pieforms/pieform.php');
safe_require('artefact', 'survey');


function get_accessible_surveys() {
	// Get names of all the survey responses (from other users) that are accessible to our user...
	global $USER;
	$results = get_records_sql_array(
		"SELECT	a.title
		FROM {artefact} a
		LEFT JOIN {artefact_access_usr} aau ON (aau.artefact = a.id)
		WHERE aau.usr = ?", array($USER->get('id'))
	);

	// Prepare our accessible surveys array so we can use it as options in drop-down select box...
	$survey_types = array('empty' => get_string('selectsurvey', 'artefact.survey'));
	
	if ($results) {
		// There can be (and often will be) duplicates of survey names:
		// two/more users can complete the same survey, but the responses will appear as different artefacts.
		//
		// We use/simulate mathematical concept of Set, which can contain only one instance of the same thing (survey in our case).
		//
		// We achieve that by checking if the current survey name is already contained in our accessible surveys array.
		// If not, we add the name of taht survey to our accessible surveys array
		$accessible_surveys = array();
		foreach ($results as $result) {
			if (!in_array($result->title, $accessible_surveys)) {
				$accessible_surveys[] = $result->title;
			}
		}
	
		foreach ($accessible_surveys as $filename) {
			$LANGUAGE = ArtefactTypeSurvey::get_default_lang($filename);
			$xmlDoc = new DOMDocument('1.0', 'UTF-8');
			$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $filename);
			# Return http response in string
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$loaded = $xmlDoc->loadXML(curl_exec($ch));
			if ($loaded) {
				$surveyname = $xmlDoc->getElementsByTagName('survey')->item(0)->getAttribute('name');
				if (isset($surveyname) && $surveyname == substr($filename, 0, -4)) {
					$title = $xmlDoc->getElementsByTagName('title')->item(0)->getAttribute($LANGUAGE);
					$survey_types = array_merge($survey_types, array($filename => $title));
				} else {
					$message = get_string('surveynameerror', 'artefact.survey', $filename);
					$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
				}
			} else {
				$message = get_string('surveyerror', 'artefact.survey', $filename);
				$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
			}
		}
	}
	
	return $survey_types;
}


$results = get_accessible_surveys();
$form = pieform(array(
    'name' => 'surveyanalysisform',
    'jsform' => false,
	'method' => 'post',
    'plugintype' => 'artefact',
    'pluginname' => 'survey',
    'elements' => array(
		'survey' => array(
			'type' => 'select',
			'labelhtml' => get_string('surveytitle', 'artefact.survey'),
			'defaultvalue' => null,
			'options' => $results,
		),
		'analysistype' => array(
			'type' => 'radio',
			'labelhtml' => get_string('analysistype', 'artefact.survey'),
			'defaultvalue' => 'exportcsv',
			'options' => array(
				'online' => get_string('analysisonline', 'artefact.survey'),
				'exportcsv' => get_string('analysisexportcsv', 'artefact.survey'),
			),
			'disabled' => true,
			'separator' => '<br />',
		),
		'submit' => array(
			'type' => 'submit',
			'value' => get_string('generate', 'artefact.survey'),
		),
	),
));


function surveyanalysisform_validate(Pieform $form, $values) {
    global $SESSION;

	if ($values['survey'] == 'empty') {
		$SESSION->add_error_msg(get_string('emptysurveyname', 'artefact.survey'));
		redirect('/artefact/survey/analysis/index.php');
	}
}

function surveyanalysisform_submit(Pieform $form, $values) {
    global $SESSION;

	if ($values['analysistype'] == 'online') {
		$SESSION->set('survey', $values['survey']);
		$SESSION->add_ok_msg(get_string('surveyanalysisgenerated', 'artefact.survey'));
		redirect('/artefact/survey/analysis/generate.php');
	}
	if ($values['analysistype'] == 'exportcsv') {
		$SESSION->set('survey', $values['survey']);
		$SESSION->add_ok_msg(get_string('surveyresponsesexported', 'artefact.survey'));
		redirect('/artefact/survey/analysis/index.php');
	}
}


$smarty = smarty();
$smarty->assign('pagedescription', get_string('surveyanalysisdesc', 'artefact.survey'));
$smarty->assign('form', $form);
$smarty->assign('RESULTS', count($results));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('artefact:survey:analysis.tpl');

?>
