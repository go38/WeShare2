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
define('JSON', 1);
define('PUBLIC', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'survey');

json_headers();

function get_audio_player_code($wwwroot, $plugintype, $pluginname, $surveyname, $language, $filename) {
    $return  = '<object width="290" height="30"';
    $return .= '<param name="movie" value="' . $wwwroot . '/' . $plugintype . '/' . $pluginname . '/lib/wpaudioplayer/player.swf">';
    $return .= '<param name="quality" value="high">';
    $return .= '<param name="flashvars" value="playerID=1&soundFile=' . $wwwroot . '/' . $plugintype . '/' . $pluginname . '/surveys/' . $surveyname . '/' . $language . '/' . $filename . '">';
    $return .= '<param name="wmode" value="transparent">';
	$return .= '<p>Requires Flash Player 9 or better.</p>';
    $return .= '<embed src="' . $wwwroot . '/' . $plugintype . '/' . $pluginname . '/lib/wpaudioplayer/player.swf" quality="high" wmode="transparent" flashvars="playerID=1&soundFile=' . $wwwroot . '/' . $plugintype . '/' . $pluginname . '/surveys/' . $surveyname . '/' . $language . '/' . $filename . '" width="290" height="30">';
    $return .= '</embed>';
    $return .= '</object>';
	return $return;
}

$plugintype = param_alpha('plugintype');
$pluginname = param_alpha('pluginname');
$surveyname = param_alphanumext('survey');
$question   = param_alphanumext('question');
$language   = param_alphanumext('language', 'en.utf8');

$data = get_surveyhelpfile($plugintype, $pluginname, $surveyname, $question, $language);
// Replace <audio:filename.mp3> tag with HTML code for rendering flash wpaudioplayer...
$data = preg_replace('#<audio:([a-zA-Z0-9\_\-\.]+)>#', get_audio_player_code(get_config('wwwroot'), $plugintype, $pluginname, $surveyname, $language, '$1'), $data);

if (empty($data)) {
    json_reply('local', get_string('nohelpfound'));
}

$json = array('error' => false, 'content' => $data);
echo json_encode($json);
exit;

?>
