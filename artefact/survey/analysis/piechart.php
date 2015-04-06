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
//define('SECTION_PAGE', 'chart');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'survey');

$data = param_integer('data', 0);
/*
$surveyname = param_alphanumext('survey', null); // in case we are dealing with individual survey history...
$userid = param_integer('userid', 0); // in case we are dealing with individual survey history...
$WIDTH = param_integer('width', 400);
$HEIGHT = param_integer('height', 250);
$PALETTE = param_alpha('palette', 'default');
$LEGEND = param_alpha('legend', 'key'); // possible values are 'label' and 'key'
$FONT['type'] = param_alpha('fonttype', 'sans'); // possible values are 'sans' and 'serif'
$FONT['size'] = param_integer('fontsize', 10);
$SAMEGRAPH = param_integer('samegraph', 0); // multiple survey results drawn on same graph?
*/

/*
function is_survey_history($surveyid, $surveyname, $userid) {
	if ($surveyid == 0) {
		return (count_records('artefact', 'artefacttype', 'survey', 'title', $surveyname, 'owner', $userid) > 0 ? true : false); 
	} else {
		return false;
	}
}
*/

/*
$is_survey  = (get_field('artefact', 'artefacttype', 'id', $id) == 'survey' ? true : false);
$is_history = is_survey_history($id, $surveyname, $userid);
$user_is_owner   = ($USER->get('id') == get_field('artefact', 'owner', 'id', $id) ? true : false);
$user_has_access = record_exists('artefact_access_usr', 'usr', $USER->get('id'), 'artefact', $id);

if (!$is_survey && !$is_history) {
    throw new ArtefactNotFoundException(get_string('artefactnotsurvey', 'artefact.survey'));
}

if (!$user_is_owner && !$user_has_access) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}
*/

/*
if ($id == 0) {
	$surveyids = array($id);
} else {
	$surveyids = get_column('artefact', 'id', 'artefacttype', 'survey', 'title', $surveyname, 'owner', $userid);
}

foreach ($surveyids as $surveyid) {
	$survey = null;
	try {
		$survey = artefact_instance_from_id($surveyid);
	}
	catch (Exception $e) { }

	// Get survey filename and return empty responses
	$filename = $survey->get('title');
	$responses = unserialize($survey->get('description'));

	$CONFIG[] = ArtefactTypeSurvey::get_chart_config($filename);
	//log_debug($CONFIG);
	$DATA[]   = ArtefactTypeSurvey::get_chart_data_from_responses($filename, $responses);
	//log_debug($DATA);

	$xmlDoc = new DOMDocument('1.0', 'UTF-8');
	// 'title' field in 'artefact' table contains the survey xml filename...
	$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $filename);
	# Return http response in string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$loaded = $xmlDoc->load(curl_exec($ch));
	if ($loaded) {
		// Determine, which type of graph sould be rendered
		$TYPE = $xmlDoc->getElementsByTagName('chart')->item(0)->getAttribute('type');
	} else {
		$message = get_string('surveyerror', 'artefact.survey', $filename);
		$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
	}
}

$COUNT  = count($DATA);
*/


function draw_3dpie_chart($WIDTH, $HEIGHT, $DATA, $CONFIG, $LEGEND, $FONT, $PALETTE='default') {
	/* Include all the pChart 2.0 classes */
	include('../lib/pchart2/class/pDraw.class');
	include('../lib/pchart2/class/pImage.class');
	include('../lib/pchart2/class/pData.class');
	include('../lib/pchart2/class/pPie.class');

	$COLORS = ArtefactTypeSurvey::get_palette_colors($PALETTE);

	/* Create your dataset object */
	$myData = new pData();

	/* Add data in your dataset */
	if ($CONFIG['type'] == 'percent') {
		foreach ($DATA[0] as $value) {
			if ($value['percent'] != 0) {
				$POINTS[] = $value['percent'];
				if ($LEGEND == 'key') { $LABELS[] = $value['key']; }
				if ($LEGEND == 'label') { $LABELS[] = $value['label']; }
			}
		}
		$myData->addPoints($POINTS, $CONFIG['title']);
		$myData->setAxisUnit(0,'%');
	} else {
		foreach ($DATA[0] as $value) {
			if ($value['value'] != 0) {
				$POINTS[] = $value['value'];
				if ($LEGEND == 'key') { $LABELS[] = $value['key']; }
				if ($LEGEND == 'label') { $LABELS[] = $value['label']; }
			}
		}
		$myData->addPoints($POINTS, $CONFIG['title']);
		$myData->setAxisUnit(0,'');
	}

	/* Labels definition */
	/*
	$myData->addPoints($LABELS,'Legend');
	$myData->setSerieDescription('Legend','');
	$myData->setAbscissa('Legend');
	*/

	/* Will replace the whole color scheme by the selected palette */
	$myData->loadPalette('lib/pchart2/palettes/' . $PALETTE	. '.color', TRUE);

	/* Create a pChart object and associate your dataset */
	$myPicture = new pImage($WIDTH,$HEIGHT,$myData);

	/* Draw border around the chart */
	$myPicture->drawRectangle(0,0,$WIDTH-1,$HEIGHT-1,array("R"=>0,"G"=>0,"B"=>0));

	/* Draw chart background */
	//$myPicture->drawFilledRectangle(0,0,$WIDTH,$HEIGHT,array("R"=>$COLORS[1]['R'],"G"=>$COLORS[1]['G'],"B"=>$COLORS[1]['B'],"Alpha"=>10));

	/* Define the boundaries of the graph area */
	//$myPicture->setGraphArea(20,20,$WIDTH-20,$HEIGHT-40);

	/* Choose a nice font */
	switch ($FONT['type']) {
		case 'serif': $fontname = 'lib/pchart2/fonts/LiberationSerif-Regular.ttf'; break;
		case 'sans': $fontname = 'lib/pchart2/fonts/LiberationSans-Regular.ttf'; break;
	}
	$myPicture->setFontProperties(array('FontName'=>$fontname,'FontSize'=>$FONT['size']));

	/* Create label with survey name */
	//$myPicture->drawText(20,$HEIGHT-30,$CONFIG['title'],array("DrawBox"=>true,"BoxRounded"=>true,"BoxR"=>$COLORS[1]['R'],"BoxG"=>$COLORS[1]['G'],"BoxB"=>$COLORS[1]['B'],"BoxAlpha"=>20,"Align"=>TEXT_ALIGN_MIDDLELEFT));

	/* Create the pPie object */
	$PieChart = new pPie($myPicture,$myData);

	/* Draw a simple pie chart */
	$PIE_WIDTH  = $WIDTH-40; // 20px margin on left and right
	$PIE_HEIGHT = $HEIGHT-60; // 20px margin on top and 40px on bottom (space for legend)
	if ($PIE_WIDTH >= $PIE_HEIGHT) {
		// Landscape orientation of the graph...
		$PieRadius = round($PIE_HEIGHT/2);
	} else {
		// Portrait orientation of the graph...
		$PieRadius = round($PIE_WIDTH/2);
	}
	$PieX = round($WIDTH/2);
	$PieY = round($HEIGHT/2);
	$PieChart->draw3DPie($PieX,$PieY,array("Radius"=>$PieRadius,"SecondPass"=>false,"DrawLabels"=>false));

	/* Build the PNG file and send it to the web browser */
	$myPicture->Stroke();
}

?>
