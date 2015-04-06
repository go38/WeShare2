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
define('SECTION_PAGE', 'settings');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'survey');
define('TITLE', get_string('surveysettings', 'artefact.survey'));

$id = param_integer('id', 0);
$new = param_integer('new', 0);

$is_survey  = (get_field('artefact', 'artefacttype', 'id', $id) == 'survey' ? true : false);
$user_is_owner   = ($USER->get('id') == get_field('artefact', 'owner', 'id', $id) ? true : false);

if (!$is_survey && !$new) {
    throw new ArtefactNotFoundException(get_string('artefactnotsurvey', 'artefact.survey'));
}

if (!$user_is_owner && !$new) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}


$survey = null;
try {
    $survey = artefact_instance_from_id($id);
}
catch (Exception $e) { }

if (empty($survey)) {
	$title_defaultvalue = null;
} else {
	$title_defaultvalue = $survey->get('title');
}

if ($id == 0) {
	$submitstr = array('cancel' => get_string('cancel'), 'submit' => get_string('next') . ': ' . get_string('completesurvey', 'artefact.survey'));
	$confirm   = array('cancel' => get_string('confirmcancelcreatingsurvey','artefact.survey'));
}
else {
	$submitstr = array(get_string('save'), get_string('cancel'));
	$confirm   = null;
}

$surveys = getoptions_surveys();

// Extract language related surveys...
function language($var) {
	return (substr($var, 0, 8) == 'language');
}
$language_surveys = array_filter(array_flip($surveys), "language");
$language_surveys = array_flip($language_surveys);
asort($language_surveys);

// Extract person related surveys...
function personal($var) {
	return (substr($var, 0, 8) == 'personal');
}
$personal_surveys = array_filter(array_flip($surveys), "personal");
$personal_surveys = array_flip($personal_surveys);
asort($personal_surveys);

// Extract career related surveys...
function career($var) {
	return (substr($var, 0, 6) == 'career');
}
$career_surveys = array_filter(array_flip($surveys), "career");
$career_surveys = array_flip($career_surveys);
asort($career_surveys);

// Extract staff related surveys...
function staff($var) {
	return (substr($var, 0, 5) == 'staff');
}
$staff_surveys = array_filter(array_flip($surveys), "staff");
$staff_surveys = array_flip($staff_surveys);
asort($staff_surveys);

// Extract all other surveys...
function other($var) {
	return (
		substr($var, 0, 8) != 'language' &&
		substr($var, 0, 8) != 'personal' &&
		substr($var, 0, 6) != 'career' &&
		substr($var, 0, 5) != 'staff'
	);
}
$other_surveys = array_filter(array_flip($surveys), "other");
$other_surveys = array_flip($other_surveys);
asort($other_surveys);


// Get users who are recipients - can get the results of user's survey
$recipients = get_column('artefact_access_usr', 'usr', 'artefact', $id);

$form = pieform(array(
    'name'       => 'editsurvey',
    'method'     => 'post',
    'action'     => '',
    'plugintype' => 'artefact',
    'pluginname' => 'survey',
	'template'   => 'settingsform.php',
    'configdirs' => array(get_config('libroot') . 'form/', get_config('docroot') . 'artefact/survey/form/'),
    'elements'   => array(
        'id' => array(
            'type'  => 'text', // This is set to 'text' because we are using template for rendering form (and will be set 'hidden' there), otherwise it must be set to 'hidden'!
            'value' => $id
        ),
        'title' => array(
            'type'         => 'select',
            'rules'        => array('required' => true),
            'title'        => get_string('surveytitle', 'artefact.survey'),
            'description'  => get_string('surveytitledesc', 'artefact.survey'),
			'options'      => $surveys,
            'defaultvalue' => $title_defaultvalue,
			'disabled'     => ($id == 0 ? false : true),
			// For this javascript event to work, 'onchange' option must be added to $attributes array in function element_attributes (htdocs/lib/pieforms/pieform.php, line 1085 or so).
			'onchange'     => 'selectSurveyLanguage(document.editsurvey.title[document.editsurvey.title.selectedIndex].value, "language")', // if foreign language related survey is selected...
        ),
		'language_surveys' => array(
			'type' => (!empty($language_surveys) ? 'select' : 'hidden'),
			'value' => null,
			'options' => $language_surveys,
		),
		'personal_surveys' => array(
			'type' => (!empty($personal_surveys) ? 'select' : 'hidden'),
			'value' => null,
			'options' => $personal_surveys,
		),
		'career_surveys' => array(
			'type' => (!empty($career_surveys) ? 'select' : 'hidden'),
			'value' => null,
			'options' => $career_surveys,
		),
		'other_surveys' => array(
			'type' => (!empty($other_surveys) ? 'select' : 'hidden'),
			'value' => null,
			'options' => $other_surveys,
		),
		// If exist, these surveys are visible only to staff and admin users
		'staff_surveys' => array(
			'type' => (!empty($staff_surveys) && ($USER->get('admin') || $USER->get('staff')) ? 'select' : 'hidden'),
			'value' => null,
			'options' => $staff_surveys,
		),
        'language' => array(
            'type'         => 'css_select',
            'title'        => get_string('foreignlanguage', 'artefact.survey'),
            'description'  => get_string('foreignlanguagedesc', 'artefact.survey'),
			'options'      => getlanguageportfolio_languages(),
			'disabled'     => (isset($title_defaultvalue) && !empty($title_defaultvalue) && substr($title_defaultvalue, 0, 8) == 'language' && $id == 0 ? false : true),
            'defaultvalue' => (isset($survey) ? $survey->get('note') : null),
        ),
		'recipients' => array(
			'type' => 'userlist',
			'title' => get_string('recipients', 'artefact.survey'),
            'filter' => false,
            'lefttitle' => get_string('allusers', 'artefact.survey'),
            'righttitle' => get_string('surveyrecipients', 'artefact.survey'),
			'defaultvalue' => $recipients,
            'searchparams' => array(
                'query' => '',
                'limit' => 250,
                'orderby' => 'lastname',
            ),
		),
        'submit' => array(
            'type'    => 'submitcancel',
            'value'   => $submitstr,
	        'confirm' => $confirm,
        )
    )
));

$inlinejs = <<<EOF

function selectSurveyLanguage(value, condition) {
	// if foreign language related survey is selected, than we enable drop-down box to select that foreign language...
	if (value.substring(0,condition.length) == condition) {
		document.editsurvey.language.disabled = false;
	}
	// if foreign language related survey is not selected, than we disable that drop-down box...
	else {
		document.editsurvey.language.disabled = true;
	}
    return false;
}

EOF;


$smarty = smarty();
$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('form', $form);
$smarty->display('form.tpl');


function editsurvey_submit(Pieform $form, $values) {
    global $SESSION, $USER;

    $userid = $USER->get('id');
	$errors = false;
	$artefactid = $values['id'];
	$recipients = $values['recipients'];

    try {
        if ($artefactid == 0) {
            $survey = new ArtefactTypeSurvey(); 
            $survey->set('owner', $userid);
        }
        else {
            $survey = new ArtefactTypeSurvey($artefactid); 
        }
		
        $survey->set('title', $values['title']);
        $survey->set('note', $values['language']);
        $survey->commit();
		
        //if (!empty($recipients)) {
		db_begin();
		execute_sql("DELETE FROM {artefact_access_usr} WHERE artefact = ?", array($survey->get('id')));
		foreach($recipients as $recipient) {
			execute_sql("INSERT INTO {artefact_access_usr} (usr, artefact) VALUES (?,?)", array($recipient, $survey->get('id')));
		}
		db_commit();
		//}
    }
    catch (Exception $e) {
        $errors = true;
    }   

    if (!$errors) {
        $SESSION->add_ok_msg(get_string('surveysaved', 'artefact.survey'));
    }

    if ($artefactid == 0) {
        $redirecturl = '/artefact/survey/edit.php?id=' . $survey->get('id');
    } else {
        $redirecturl = '/artefact/survey/';
    }
    redirect($redirecturl);
}

function editsurvey_cancel_submit() {
    redirect('/artefact/survey/');
}

?>
