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
define('SECTION_PAGE', 'index');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('Surveys', 'artefact.survey'));
require_once(get_config('docroot') . 'artefact/lib.php');
safe_require('artefact', 'survey');

// Unset SESSION values
// So when we call htdocs/artefact/survey/analysis/index.php script, it would not automatically generate CSV export file
$SESSION->set('survey', '');

// Delete selected survey...
if ($delete = param_integer('delete', 0)) {
    $survey = artefact_instance_from_id($delete);
    $survey->delete();
    $SESSION->add_ok_msg(get_string('surveydeleted', 'artefact.survey'));
}


$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);

list($count, $data) = ArtefactTypeSurvey::get_survey_list($limit, $offset);

foreach ($data as $survey) {
	$survey->title = ArtefactTypeSurvey::get_survey_title_from_xml($survey->title);

	$flagicons = getlanguageportfolio_languages();
	$flagicon = $survey->note;
	if (isset($flagicon) && !empty($flagicon)) {
		$survey->flagicon = $flagicons[$survey->note]['style'];
	} else {
		$survey->flagicon = 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAHElEQVQ4jWP8//8/AyWAiSLdowaMGjBqwCAyAABjmgMdtjw0ugAAAABJRU5ErkJggiANCg==) no-repeat left center; padding-left: 20px;';
	}
}


// Function for comparing, used by usort...
function cmp($a, $b) {
    return strcmp($a->title, $b->title);
}
usort($data, 'cmp');

// Web browser supports base64 images?
function browser_supports_base64_images() {
	$browser = $_SERVER['HTTP_USER_AGENT'];
	//log_debug($browser);
	if (strpos($browser, 'MSIE') !== false) {
		return false;
	}
	return true;
}


$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'artefact/survey',
    'count' => $count,
    'limit' => $limit,
    'offset' => $offset,
    'resultcounttextsingular' => get_string('survey', 'artefact.survey'),
    'resultcounttextplural' => get_string('surveys', 'artefact.survey'),
));

$smarty = smarty(array('artefact/survey/js/help.js'), array(), array());
//$smarty = smarty();
$smarty->assign('surveys', $data);
$smarty->assign('strnosurveysaddone', get_string('nosurveysaddone','artefact.survey','<a href="' . get_config('wwwroot') . 'artefact/survey/settings.php?new=1">', '</a>'));
$smarty->assign('base64images', browser_supports_base64_images());
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('artefact:survey:index.tpl');

?>
