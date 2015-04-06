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
define('SECTION_PAGE', 'results');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'survey');

$id = param_integer('id', 0);

$is_survey  = (get_field('artefact', 'artefacttype', 'id', $id) == 'survey' ? true : false);
$user_is_owner   = ($USER->get('id') == get_field('artefact', 'owner', 'id', $id) ? true : false);
$user_has_access = record_exists('artefact_access_usr', 'usr', $USER->get('id'), 'artefact', $id);

if (!$is_survey) {
    throw new ArtefactNotFoundException(get_string('artefactnotsurvey', 'artefact.survey'));
}

if (!$user_is_owner && !$user_has_access) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}


$survey = null;
try {
    $survey = artefact_instance_from_id($id);
}
catch (Exception $e) { }


define('TITLE', $survey->get_survey_title_from_xml());

// Get survey filename and return empty responses
$filename = $survey->get('title');
$responses = unserialize($survey->get('description'));
if (!$responses) {
    throw new NotFoundException(get_string('responsesnotfound', 'artefact.survey', ArtefactTypeSurvey::get_survey_title_from_xml($filename)));
}

$html = ArtefactTypeSurvey::build_user_responses_summary_html($filename, $responses);

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('RESULTS', $survey->survey_returns_results($filename));
$smarty->assign('RESULTSHEADNIG', get_string('results', 'artefact.survey'));
$smarty->assign('CHART', $survey->survey_returns_chart($filename));
$smarty->assign('CHARTHEADNIG', get_string('chart', 'artefact.survey'));
$smarty->assign('id', $id);
$smarty->assign('html', $html);
$smarty->display('artefact:survey:results.tpl');

?>
