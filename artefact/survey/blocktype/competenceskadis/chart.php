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
 * @subpackage blocktype-kadiscompetences
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2010-2011 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);

require(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('blocktype', 'competenceskadis');

$WIDTH = param_integer('width', 400);
$HEIGHT = param_integer('height', 250);
$PALETTE = param_alpha('palette', 'default');
$FONT['type'] = param_alpha('fonttype', 'sans'); // possible values are 'sans' and 'serif'
$FONT['size'] = param_integer('fontsize', 10);
$OWNER = param_integer('id');

$DATA   = PluginBlocktypeCompetencesKadis::prepare_chart_data($OWNER);
//log_debug($DATA);
$CONFIG = PluginBlocktypeCompetencesKadis::prepare_chart_config();

/* Include all the pChart 2.0 classes */
include(dirname(dirname(dirname(__FILE__))) . '/lib/pchart2/class/pDraw.class');
include(dirname(dirname(dirname(__FILE__))) . '/lib/pchart2/class/pImage.class');
include(dirname(dirname(dirname(__FILE__))) . '/lib/pchart2/class/pData.class');
include(dirname(dirname(dirname(__FILE__))) . '/lib/pchart2/class/pRadar.class');

$COLORS = ArtefactTypeSurvey::get_palette_colors($PALETTE);

/* Create your dataset object */
$myData = new pData();

/* Add data in your dataset */
$POINTS = array();
$LABELS = array();
foreach ($DATA as $value) {
	$POINTS[] = $value['value'];
	$LABELS[] = $value['key'];
}
$myData->addPoints($POINTS, $CONFIG['title']);
$myData->setAxisUnit(0,'');

/* Labels definition */
$myData->addPoints($LABELS,'Legend');
$myData->setSerieDescription('Legend','');
$myData->setAbscissa('Legend');
	
/* Will replace the whole color scheme by the selected palette */
$myData->loadPalette(dirname(dirname(dirname(__FILE__))) . '/lib/pchart2/palettes/' . $PALETTE	. '.color', TRUE);

/* Create a pChart object and associate your dataset */
$myPicture = new pImage($WIDTH,$HEIGHT,$myData);

/* Draw border around the chart */
$myPicture->drawRectangle(0,0,$WIDTH-1,$HEIGHT-1,array("R"=>0,"G"=>0,"B"=>0));

/* Draw chart background */
$myPicture->drawFilledRectangle(0,0,$WIDTH,$HEIGHT,array("R"=>$COLORS[1]['R'],"G"=>$COLORS[1]['G'],"B"=>$COLORS[1]['B'],"Alpha"=>10));

/* Define the boundaries of the graph area */
$myPicture->setGraphArea(20,20,$WIDTH-20,$HEIGHT-40);

/* Choose a nice font */
switch ($FONT['type']) {
	case 'serif': $fontname = dirname(dirname(dirname(__FILE__))) . '/lib/pchart2/fonts/LiberationSerif-Regular.ttf'; break;
	case 'sans': $fontname = dirname(dirname(dirname(__FILE__))) . '/lib/pchart2/fonts/LiberationSans-Regular.ttf'; break;
}
$myPicture->setFontProperties(array('FontName'=>$fontname,'FontSize'=>$FONT['size']));

/* Draw chart legend */
$myPicture->drawLegend(20,$HEIGHT-30,array("R"=>$COLORS[1]['R'],"G"=>$COLORS[1]['G'],"B"=>$COLORS[1]['B'],"Alpha"=>20));

/* Create the radar object */
$SplitChart = new pRadar();

/* Draw a simple radar chart */
$mySettings = array(
	"Layout"=>RADAR_LAYOUT_STAR,
	"LabelPos"=>RADAR_LABELS_HORIZONTAL,
	"BackgroundGradient"=>array("StartR"=>$COLORS[3]['R'],"StartG"=>$COLORS[3]['G'],"StartB"=>$COLORS[3]['B'],"StartAlpha"=>30,"EndR"=>$COLORS[5]['R'],"EndG"=>$COLORS[5]['G'],"EndB"=>$COLORS[5]['B'],"EndAlpha"=>30),
	// to draw segments even if they are not achieved by survey results...
	"SegmentHeight"=>1,
	"Segments"=>3,
	// to also color the polygon (slightly darker than background), defined by result points
	"DrawPoly"=>true
);
$SplitChart->drawRadar($myPicture, $myData, $mySettings);

/* Build the PNG file and send it to the web browser */
$myPicture->Stroke();

?>
