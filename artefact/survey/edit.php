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
define('SECTION_PAGE', 'edit');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'survey');

$id = param_integer('id');

$fieldset = param_alphanum('fs', 'tab1');

$is_survey  = (get_field('artefact', 'artefacttype', 'id', $id) == 'survey' ? true : false);
$user_is_owner   = ($USER->get('id') == get_field('artefact', 'owner', 'id', $id) ? true : false);

if (!$is_survey) {
    throw new ArtefactNotFoundException(get_string('artefactnotsurvey', 'artefact.survey'));
}

if (!$user_is_owner) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}


$survey = null;
try {
    $survey = artefact_instance_from_id($id);
}
catch (Exception $e) { }

if ($USER->get('id') <> $survey->get('owner')) {
    $SESSION->add_error_msg(get_string('canteditdontown'));
    redirect('/artefact/survey/');
}

define('TITLE', $survey->get_survey_title_from_xml());


$defaultvalues = unserialize($survey->get('description'));
$pieform_array = $survey->create_pieform_array_from_xml($defaultvalues, $fieldset);
$pieform_array['elements']['id'] = array('type' => 'hidden', 'value' => $id);
$form = pieform($pieform_array);

$rank_elements_js = $survey->get_rank_elements_js($defaultvalues);
//log_debug($rank_elements_js);

$description =  $survey->get_survey_info_from_xml();

$section_type =  $survey->get_survey_section_type();
if ($section_type == 'tabbed') {
	// If the survey's sections are tabbed...
	$smarty = smarty(array('artefact/survey/js/help.js', 'artefact/survey/js/edit.js'), array($rank_elements_js), array());
} else {
	// If the survey's sections are joined or separated...
	$smarty = smarty(array('artefact/survey/js/help.js'), array($rank_elements_js), array());
}
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('pagedescriptionhtml', $description);
$smarty->assign('form', $form);
$smarty->display('form.tpl');


function questionnaire_validate(Pieform $form, $values) {
	// Currently not needed...
}

function questionnaire_submit(Pieform $form, $values) {
    safe_require('notification', 'internal');
    global $SESSION, $USER, $survey;
	$id = $values['id'];
	$show_results = $values['show_results'];

	// Keep user responses only!
	// Strip out: section_type, show_results, sesskey, submit and fs (if exists)
	$responses = array();
	foreach ($values as $key => $value) {
		if (!in_array($key, array('section_type', 'show_results', 'fs', 'sesskey', 'submit'))) {
			$responses = array_merge($responses, array($key => $value));
		}
	}

    try {
		$survey->set('description', serialize($responses));
		$survey->set('mtime', time());
		$survey->commit();

		$ownername = $USER->get('firstname') . ' ' . $USER->get('lastname') . ' (' . $USER->get('username') . ')';
		
		// Get users who are recipients - can get the results of user's survey
		$recipients = get_column('artefact_access_usr', 'usr', 'artefact', $id);
		
		// Get correct activity type
		$type = get_field('activity_type', 'id', 'name', 'feedback', 'plugintype', 'artefact', 'pluginname', 'survey');
		
        if (!empty($recipients)) {
			foreach($recipients as $recipient) {
				$user = new StdClass;
				$user->id = $recipient;
				$data = new StdClass;
				$data->type = $type;
				$data->parent = null;
				$data->message = get_string('surveyaccessmessage', 'artefact.survey', $ownername);
				$data->subject = get_string('surveyaccesssubject', 'artefact.survey', $ownername);
				$data->url = get_config('wwwroot') . 'artefact/survey/results.php?id=' . $id;
				$data->urltext = get_string('surveyaccessurltext', 'artefact.survey');
				$data->fromuser = $USER->get('id');
				PluginNotificationInternal::notify_user($user, $data);
			}
		}
	}
    catch (Exception $e) {
        $SESSION->add_error_msg(get_string('surveysavefailed', 'artefact.survey'), false);
    }   

	$SESSION->add_ok_msg(get_string('surveysaved', 'artefact.survey'));
	if ($show_results) {
		redirect('/artefact/survey/results.php?id=' . $survey->get('id'));
	} else {
		redirect('/artefact/survey/');
	}
}

function questionnaire_cancel_submit() {
    redirect('/artefact/survey/');
}

?>
