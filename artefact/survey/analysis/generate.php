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
define('SECTION_PAGE', 'generate');

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
define('TITLE', get_string('surveyanalysis', 'artefact.survey'));
require_once('pieforms/pieform.php');
safe_require('artefact', 'survey');


global $SESSION;
global $USER;

$survey = $SESSION->get('survey');
log_debug($survey);

if ($survey != '') {
	//$analysis1 = ArtefactTypeSurvey::get_empty_responses_from_survey($survey);
	//log_debug($analysis1);
	$analysis = ArtefactTypeSurvey::get_empty_analysis_from_survey($survey);
	//log_debug($analysis);
	
	$answers = array('V_a01' => 13, 'R_a05' => 1);
	foreach ($answers as $akey => $avalue) {
		foreach ($analysis as $qkey => $qvalue) {
			if (array_key_exists($akey, $qvalue['responses'])) {
				//$analysis[$qkey]['responses'][$akey]['count'] += $avalue;
				$analysis[$qkey]['responses'][$akey]['count']++;
			}
		}
	}
	/*
	log_debug($analysis);
	$analysis[0]['responses']['V_a01']['count']++;
	*/
	log_debug($analysis);
}

$smarty = smarty();
/*
$smarty->assign('pagedescription', get_string('surveyanalysisdesc', 'artefact.survey'));
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', TITLE);
*/
$smarty->display('artefact:survey:generate.tpl');

?>
