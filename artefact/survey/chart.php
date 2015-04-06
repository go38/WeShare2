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

define('INTERNAL', 1);
define('PUBLIC', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'survey');

// Initialisation of eZ Components...
require(dirname(__FILE__) . '/lib/ezc/Base/src/ezc_bootstrap.php');


$id = param_integer('id');
$WIDTH = param_integer('width', 400);
$HEIGHT = param_integer('height', 400);
$PALETTE = param_alpha('palette', 'default');
$LEGEND = param_alpha('legend', 'key'); // possible values are 'label' and 'key'
$FONT['type'] = param_alpha('fonttype', 'chinese'); // possible values are 'sans' and 'serif' and 'chinese'
$FONT['size'] = param_integer('fontsize', 10);
//$FONT['titlesize'] = $FONT['size'] + 4;
$FONT['titlesize'] = 16;

$is_survey  = (get_field('artefact', 'artefacttype', 'id', $id) == 'survey' ? true : false);

if (!$is_survey) {
    throw new ArtefactNotFoundException(get_string('artefactnotsurvey', 'artefact.survey'));
}


$survey = null;
try {
    $survey = artefact_instance_from_id($id);
}
catch (Exception $e) { }


// Get survey filename and return empty responses
$filename = $survey->get('title');
$responses = unserialize($survey->get('description'));

$CONFIG = ArtefactTypeSurvey::get_chart_config($filename);
$CONFIG['ctime'] = strftime(get_string('strfdaymonthyearshort'), $survey->get('ctime')); // Created
$CONFIG['mtime'] = strftime(get_string('strfdaymonthyearshort'), $survey->get('mtime')); // Modified
// If language survey, add the language abbreviation at the end of survey title...
if ($survey->get('note') != null) {
	$CONFIG['title'] = $CONFIG['title'] . ' (' . substr($survey->get('note'), 0, -5) . ')';
}
//log_debug($CONFIG);
$DATA   = ArtefactTypeSurvey::get_chart_data_from_responses($filename, $responses);
//log_debug($DATA);


if (is_null($CONFIG['charttype'])) {
	$message = get_string('surveyerror', 'artefact.survey', $filename);
	$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
}


switch (strtolower($CONFIG['charttype'])) {
	case 'bar': draw_bar_chart($WIDTH, $HEIGHT, $DATA, $CONFIG, $LEGEND, $FONT, $PALETTE); break;
	case 'stacked': draw_stacked_chart($WIDTH, $HEIGHT, $DATA, $CONFIG, $LEGEND, $FONT, $PALETTE); break;
	case 'area':
	case 'line':
	case 'plot':
	case 'plotline':
	case 'spline': draw_line_chart($WIDTH, $HEIGHT, $DATA, $CONFIG, $LEGEND, $FONT, $PALETTE); break;
	case 'pie': draw_pie_chart($WIDTH, $HEIGHT, $DATA, $CONFIG, $LEGEND, $FONT, $PALETTE); break;
	case 'polar':
	case 'radar': draw_radar_chart($WIDTH, $HEIGHT, $DATA, $CONFIG, $LEGEND, $FONT, $PALETTE); break;
	case 'odometer': draw_odometer_chart($WIDTH, $HEIGHT, $DATA, $CONFIG, $LEGEND, $FONT, $PALETTE); break;
}



function return_palette_name($name) {
	$palettefile = dirname(__FILE__) . '/lib/ezc/Graph/mahara/palettes/' . $name . '.php';
	if (file_exists($palettefile)) {
		return strtolower($name);
	} else {
		return 'default';
	}
}

function generate_palette_class_name($name) {
	return 'ezcMahara' . ucfirst($name) . 'Theme';
}


function draw_bar_chart($WIDTH, $HEIGHT, $DATA, $CONFIG, $LEGEND, $FONT, $PALETTE='default') {
	$palettename = return_palette_name($PALETTE);
	require(dirname(__FILE__) . '/lib/ezc/Graph/mahara/palettes/' . $palettename . '.php');
	
	$graph = new ezcGraphBarChart();
	$paletteclass = generate_palette_class_name($palettename);
	$graph->palette = new $paletteclass();
	
	$graph->title = $CONFIG['title'];
	$graph->subtitle = '建立： ' . $CONFIG['ctime'] . ', 修改: ' . $CONFIG['mtime'];
	$graph->subtitle->position = ezcGraph::BOTTOM; 
	$graph->legend = false;

	$graph->background->padding = 10;
	
	$graph->driver = new ezcGraphGdDriver();
	$graph->driver->options->imageFormat = IMG_PNG;
	
	switch ($FONT['type']) {
		case 'chinese':  $fontname = 'lib/ezc/Graph/mahara/fonts/wt006.ttf'; break;
		case 'serif': $fontname = 'lib/ezc/Graph/mahara/fonts/LiberationSerif-Regular.ttf'; break;
		case 'sans':  $fontname = 'lib/ezc/Graph/mahara/fonts/LiberationSans-Regular.ttf'; break;
	}
	$graph->options->font = $fontname;
	
	// Set the maximum font size for all chart elements
	$graph->options->font->maxFontSize = $FONT['size'];
	
	// Set the font size for the title independently to 14
	$graph->title->font->maxFontSize = $FONT['titlesize'];

	$graph->yAxis->min = 0;
	$graph->yAxis->max = 100;
	$graph->yAxis->majorStep = 20;
	$graph->yAxis->minorStep = 10;
	if ($CONFIG['type'] == 'percent') {
		$graph->yAxis->formatString = '%d%%';
	}

	$EZCDATA = array();
	foreach ($DATA as $value) {
		if ($CONFIG['type'] == 'percent') {
			$EZCDATA = array_merge($EZCDATA, array($value['key'] => $value['percent']));
		} else {
			$EZCDATA = array_merge($EZCDATA, array($value['key'] => $value['value']));
		}
	}

    $graph->data[$CONFIG['title']] = new ezcGraphArrayDataSet($EZCDATA);
	
	//$CONFIG['charttype'] = 'bar3d';
	if (strtolower($CONFIG['chartspace']) == '3d') {
		$graph->renderer = new ezcGraphRenderer3d();
	}
	//$graph->renderer->options->barMargin = .2;
	$graph->renderer->options->barPadding = .2; 
	
	/* Build the PNG file and send it to the web browser */
	$graph->renderToOutput($WIDTH, $HEIGHT);
}


function draw_line_chart($WIDTH, $HEIGHT, $DATA, $CONFIG, $LEGEND, $FONT, $PALETTE='default') {
	$palettename = return_palette_name($PALETTE);
	require(dirname(__FILE__) . '/lib/ezc/Graph/mahara/palettes/' . $palettename . '.php');
	
	$graph = new ezcGraphLineChart();
	$paletteclass = generate_palette_class_name($palettename);
	$graph->palette = new $paletteclass();
	
	$graph->title = $CONFIG['title'];
	$graph->subtitle = '建立: ' . $CONFIG['ctime'] . ', 修改: ' . $CONFIG['mtime'];
	$graph->subtitle->position = ezcGraph::BOTTOM; 
	$graph->legend = false;

	$graph->background->padding = 10;
	
	$graph->driver = new ezcGraphGdDriver();
	$graph->driver->options->imageFormat = IMG_PNG;
	
	switch ($FONT['type']) {
		case 'chinese':  $fontname = 'lib/ezc/Graph/mahara/fonts/wt006.ttf'; break;
		case 'serif': $fontname = 'lib/ezc/Graph/mahara/fonts/LiberationSerif-Regular.ttf'; break;
		case 'sans':  $fontname = 'lib/ezc/Graph/mahara/fonts/LiberationSans-Regular.ttf'; break;
	}
	$graph->options->font = $fontname;
	
	// IF $CONFIG['charttype'] == 'area' !!!
	$graph->options->fillLines = 128; // Alpha (0 <= Alpha <= 255)
	
	// Set the maximum font size for all chart elements
	$graph->options->font->maxFontSize = $FONT['size'];
	
	// Set the font size for the title independently to 14
	$graph->title->font->maxFontSize = $FONT['titlesize'];

	$graph->yAxis->min = 0;
	$graph->yAxis->max = 100;
	$graph->yAxis->majorStep = 20;
	$graph->yAxis->minorStep = 10;
	if ($CONFIG['type'] == 'percent') {
		$graph->yAxis->formatString = '%d%%';
	}

	$EZCDATA = array();
	foreach ($DATA as $value) {
		if ($CONFIG['type'] == 'percent') {
			$EZCDATA = array_merge($EZCDATA, array($value['key'] => $value['percent']));
		} else {
			$EZCDATA = array_merge($EZCDATA, array($value['key'] => $value['value']));
		}
	}

    $graph->data[$CONFIG['title']] = new ezcGraphArrayDataSet($EZCDATA);
	// IF $CONFIG['charttype'] == 'plotline' !!!, IF $CONFIG['charttype'] == 'line' than ezcGraph::NO_SYMBOL!
	$graph->data[$CONFIG['title']]->symbol = ezcGraph::BULLET; 
	
	if (strtolower($CONFIG['chartspace']) == '3d') {
		$graph->renderer = new ezcGraphRenderer3d();
	}
	//$graph->renderer->options->barMargin = .2;
	$graph->renderer->options->barPadding = .2; 
	
	/* Build the PNG file and send it to the web browser */
	$graph->renderToOutput($WIDTH, $HEIGHT);
}


function draw_pie_chart($WIDTH, $HEIGHT, $DATA, $CONFIG, $LEGEND, $FONT, $PALETTE='default') {
	$palettename = return_palette_name($PALETTE);
	require(dirname(__FILE__) . '/lib/ezc/Graph/mahara/palettes/' . $palettename . '.php');
	
	$graph = new ezcGraphPieChart();
	$paletteclass = generate_palette_class_name($palettename);
	$graph->palette = new $paletteclass();
	
	$graph->title = $CONFIG['title'];
	$graph->subtitle = '建立: ' . $CONFIG['ctime'] . ', 修改: ' . $CONFIG['mtime'];
	$graph->subtitle->position = ezcGraph::BOTTOM; 
	$graph->legend = false;

	$graph->background->padding = 10;
	
	$graph->driver = new ezcGraphGdDriver();
	$graph->driver->options->imageFormat = IMG_PNG;
	
	switch ($FONT['type']) {
		case 'chinese':  $fontname = 'lib/ezc/Graph/mahara/fonts/wt006.ttf'; break;
		case 'serif': $fontname = 'lib/ezc/Graph/mahara/fonts/LiberationSerif-Regular.ttf'; break;
		case 'sans':  $fontname = 'lib/ezc/Graph/mahara/fonts/LiberationSans-Regular.ttf'; break;
	}
	$graph->options->font = $fontname;
	
	switch ($CONFIG['labeltype']) {
		case 'labelonly':   $label = '%1$s'; break;
		case 'valueonly':   $label = '%2$d'; break;
		case 'value':       $label = '%1$s: %2$d'; break;
		case 'percentonly': $label = '%3$.1f%%'; break;
		case 'percent':     $label = '%1$s: %3$.1f%%'; break;
		default:            $label = '%1$s: %2$d (%3$.1f%%)';
	}
	$graph->options->label = $label;

	// Set the maximum font size for all chart elements
	$graph->options->font->maxFontSize = $FONT['size'];
	
	// Set the font size for the title independently to 14
	$graph->title->font->maxFontSize = $FONT['titlesize'];

	$EZCDATA = array();
	foreach ($DATA as $value) {
		$EZCDATA = array_merge($EZCDATA, array($value['key'] => $value['value']));
		/*
		if ($LEGEND == 'key') { $EZCDATA = array_merge($EZCDATA, array($value['key'] => $value['value'])); }
		if ($LEGEND == 'label') { $EZCDATA = array_merge($EZCDATA, array($value['label'] => $value['value'])); }
		*/
	}

	$graph->data[$CONFIG['title']] = new ezcGraphArrayDataSet($EZCDATA);
	
	if (strtolower($CONFIG['chartspace']) == '3d') {
		$graph->renderer = new ezcGraphRenderer3d();
		$graph->renderer->options->pieChartOffset = 60;
		$graph->renderer->options->pieChartShadowSize = 8;
		$graph->renderer->options->pieChartShadowColor = '#000000'; //#BABDB6
		$graph->renderer->options->pieChartHeight = 20;
	}

	/* Build the PNG file and send it to the web browser */
	$graph->renderToOutput($WIDTH, $HEIGHT);
}


function draw_radar_chart($WIDTH, $HEIGHT, $DATA, $CONFIG, $LEGEND, $FONT, $PALETTE='default') {
	$palettename = return_palette_name($PALETTE);
	require(dirname(__FILE__) . '/lib/ezc/Graph/mahara/palettes/' . $palettename . '.php');
	
	$graph = new ezcGraphRadarChart();
	$paletteclass = generate_palette_class_name($palettename);
	$graph->palette = new $paletteclass();
	
	$graph->title = $CONFIG['title'];
	$graph->subtitle = '建立: ' . $CONFIG['ctime'] . ', 修改: ' . $CONFIG['mtime'];
	$graph->subtitle->position = ezcGraph::BOTTOM; 
	$graph->legend = false;

	$graph->background->padding = 10;
	
	$graph->driver = new ezcGraphGdDriver();
	$graph->driver->options->imageFormat = IMG_PNG;
	
	switch ($FONT['type']) {
		case 'chinese':  $fontname = 'lib/ezc/Graph/mahara/fonts/wt006.ttf'; break;
		case 'serif': $fontname = 'lib/ezc/Graph/mahara/fonts/LiberationSerif-Regular.ttf'; break;
		case 'sans':  $fontname = 'lib/ezc/Graph/mahara/fonts/LiberationSans-Regular.ttf'; break;
	}
	$graph->options->font = $fontname;
	
	$graph->options->fillLines = 148; // Alpha (0 <= Alpha <= 255)

	/*
	switch ($CONFIG['labeltype']) {
		case 'labelonly':   $label = '%1$s'; break;
		case 'valueonly':   $label = '%2$d'; break;
		case 'value':       $label = '%1$s: %2$d'; break;
		case 'percentonly': $label = '%3$.1f%%'; break;
		case 'percent':     $label = '%1$s: %3$.1f%%'; break;
		default:            $label = '%1$s: %2$d (%3$.1f%%)';
	}
	$graph->options->label = $label;
	*/

	// Set the maximum font size for all chart elements
	$graph->options->font->maxFontSize = $FONT['size'];
	
	// Set the font size for the title independently to 14
	$graph->title->font->maxFontSize = $FONT['titlesize'];

	//$graph->axis->min = 0; // ???
	//$graph->axis->max = 4; // ???
	$graph->axis->majorStep = 5;
	//$graph->axis->minorStep = 1;
	

	$EZCDATA = array();
	foreach ($DATA as $value) {
		$EZCDATA = array_merge($EZCDATA, array($value['key'] => $value['value']));
		/*
		if ($LEGEND == 'key') { $EZCDATA = array_merge($EZCDATA, array($value['key'] => $value['value'])); }
		if ($LEGEND == 'label') { $EZCDATA = array_merge($EZCDATA, array($value['label'] => $value['value'])); }
		*/
	}

	$graph->data[$CONFIG['title']] = new ezcGraphArrayDataSet($EZCDATA);
	$graph->data[$CONFIG['title']][] = reset($EZCDATA);
 
	/*
	if (strtolower($CONFIG['chartspace']) == '3d') {
		$graph->renderer = new ezcGraphRenderer3d();
		$graph->renderer->options->pieChartOffset = 60;
		$graph->renderer->options->pieChartShadowSize = 8;
		$graph->renderer->options->pieChartShadowColor = '#000000'; //#BABDB6
		$graph->renderer->options->pieChartHeight = 20;
	}
	*/

	/* Build the PNG file and send it to the web browser */
	$graph->renderToOutput($WIDTH, $HEIGHT);
}


function draw_odometer_chart($WIDTH, $HEIGHT, $DATA, $CONFIG, $LEGEND, $FONT, $PALETTE='default') {
	$palettename = return_palette_name($PALETTE);
	require(dirname(__FILE__) . '/lib/ezc/Graph/mahara/palettes/' . $palettename . '.php');
	
	$graph = new ezcGraphOdometerChart();
	$paletteclass = generate_palette_class_name($palettename);
	$graph->palette = new $paletteclass();
	
	$graph->title = $CONFIG['title'];
	$graph->subtitle = '建立: ' . $CONFIG['ctime'] . ', 修改: ' . $CONFIG['mtime'];
	$graph->subtitle->position = ezcGraph::BOTTOM; 
	$graph->legend = false;

	$graph->background->padding = 10;
	
	$graph->driver = new ezcGraphGdDriver();
	$graph->driver->options->imageFormat = IMG_PNG;
	
	switch ($FONT['type']) {
		case 'serif': $fontname = 'lib/ezc/Graph/mahara/fonts/LiberationSerif-Regular.ttf'; break;
		case 'sans':  $fontname = 'lib/ezc/Graph/mahara/fonts/LiberationSans-Regular.ttf'; break;
	}
	$graph->options->font = $fontname;
	
	// Set the maximum font size for all chart elements
	$graph->options->font->maxFontSize = $FONT['size'];
	
	// Set the font size for the title independently to 14
	$graph->title->font->maxFontSize = $FONT['titlesize'];

	// Set colors for the background gradient
	//$graph->options->startColor = '#808080';
	//$graph->options->endColor = '#C0C0C0';
	/*
	$graph->options->startColor = '#2E3436';
	$graph->options->endColor = '#EEEEEC';
	*/
	
	// Set marker width
	$graph->options->markerWidth = 3; 

	// Set axis span
	$graph->axis->min = 0;
	$graph->axis->max = 100;
	$graph->axis->label = $DATA['label'];
	/*
	$graph->yAxis->majorStep = 20;
	$graph->yAxis->minorStep = 10;
	if ($CONFIG['type'] == 'percent') {
		$graph->yAxis->formatString = '%d%%';
	}
	*/

	if ($CONFIG['type'] == 'percent') {
		$EZCDATA = array($DATA['percent']);
	} else {
		$EZCDATA = array($DATA['value']);
	}

    $graph->data[$CONFIG['title']] = new ezcGraphArrayDataSet($EZCDATA);
	
	/* Build the PNG file and send it to the web browser */
	$graph->renderToOutput($WIDTH, $HEIGHT);
}


function draw_stacked_chart($WIDTH, $HEIGHT, $DATA, $CONFIG, $LEGEND, $FONT, $PALETTE='default') {
	$palettename = return_palette_name($PALETTE);
	require(dirname(__FILE__) . '/lib/ezc/Graph/mahara/palettes/' . $palettename . '.php');
	
	$graph = new ezcGraphBarChart();
	$paletteclass = generate_palette_class_name($palettename);
	$graph->palette = new $paletteclass();
	
	$graph->title = $CONFIG['title'];
	$graph->subtitle = '建立: ' . $CONFIG['ctime'] . ', 修改: ' . $CONFIG['mtime'];
	$graph->subtitle->position = ezcGraph::BOTTOM; 

	$graph->legend = true;
	$graph->legend->position = ezcGraph::BOTTOM;
	$graph->legend->symbolSize = $FONT['size']; 

	$graph->background->padding = 10;
	
	$graph->driver = new ezcGraphGdDriver();
	$graph->driver->options->imageFormat = IMG_PNG;
	
	switch ($FONT['type']) {
		case 'serif': $fontname = 'lib/ezc/Graph/mahara/fonts/LiberationSerif-Regular.ttf'; break;
		case 'sans':  $fontname = 'lib/ezc/Graph/mahara/fonts/LiberationSans-Regular.ttf'; break;
	}
	$graph->options->font = $fontname;
	
	// Stack bars
	$graph->options->stackBars = true;

	$graph->options->fillLines = 128; // Alpha (0 <= Alpha <= 255)

	// Set the maximum font size for all chart elements
	$graph->options->font->maxFontSize = $FONT['size'];
	
	// Set the font size for the title independently to 14
	$graph->title->font->maxFontSize = $FONT['titlesize'];

	$graph->yAxis->min = 0;
	$graph->yAxis->max = 100;
	$graph->yAxis->majorStep = 50;
	$graph->yAxis->minorStep = 10;
	if ($CONFIG['type'] == 'percent') {
		$graph->yAxis->formatString = '%d%%';
	}

	if (isset($DATA['marker']) && !empty($DATA['marker'])) {
		$graph->additionalAxis['border'] = $marker = new ezcGraphChartElementNumericAxis( );
		$marker->position = ezcGraph::LEFT;
		//$marker->border = $graph->palette->majorGridColor;
		$marker->chartPosition = $DATA['marker'];
		$marker->label = $DATA['percent'].'% ';
	}

	//$EZCDATA = array();
	foreach ($DATA['data'] as $key => $value) {
		$graph->data[$key] = new ezcGraphArrayDataSet($value);
	}
	
	//$CONFIG['charttype'] = 'bar3d';
	if (strtolower($CONFIG['chartspace']) == '3d') {
		$graph->renderer = new ezcGraphRenderer3d();
	}
	//$graph->renderer->options->barMargin = .2;
	$graph->renderer->options->barPadding = .2; 
	
	/* Build the PNG file and send it to the web browser */
	$graph->renderToOutput($WIDTH, $HEIGHT);
}

?>
