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

defined('INTERNAL') || die();

class PluginBlocktypeCompetencesKadis extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.survey/competenceskadis');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.survey/competenceskadis');
    }

    public static function get_categories() {
        return array('survey');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $USER;
        safe_require('artefact', 'survey');
        $configdata = $instance->get('configdata'); // this will make sure to unserialize it for us

        $palette = (isset($configdata['palette']) ? $configdata['palette'] : 'default');
        $fonttype = (isset($configdata['fonttype']) ? $configdata['fonttype'] : 'sans');
        $fontsize = (isset($configdata['fontsize']) ? $configdata['fontsize'] : 10);
        $height = (isset($configdata['height']) ? $configdata['height'] : 250);
        $width = (isset($configdata['width']) ? $configdata['width'] : 400);
        $owner = (isset($configdata['userid']) ? $configdata['userid'] : $USER->get('id'));

        $smarty = smarty_core();
		$smarty->assign('SURVEYS', self::surveys_exist($owner));
		$smarty->assign('resultshtml', self::build_results_summary_html($owner));
		$smarty->assign('charturl', get_config('wwwroot') . 'artefact/survey/blocktype/competenceskadis/chart.php?width=' . $width . '&height=' . $height . '&palette=' . $palette . /*'&legend=' . $legend . */'&fonttype=' . $fonttype . '&fontsize=' . $fontsize . '&id=' . $owner);
		$smarty->assign('chartdesc', get_string('competenceleveldesc', 'blocktype.survey/competenceskadis'));
        return $smarty->fetch('blocktype:competenceskadis:chart.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        global $USER;
        safe_require('artefact', 'survey');
        $configdata = $instance->get('configdata');
        return array(
			'userid' => array(
				'type' => 'hidden',
				'value' => $USER->get('id'),
			),
			/*
            'includetitle' => array(
                'type'  => 'checkbox',
                'title' => get_string('includetitle','blocktype.survey/competenceskadis'),
                'defaultvalue' => (!empty($configdata['includetitle']) ? $configdata['includetitle'] : 1),
            ),
			*/
            'chartoptions' => array(
				'type' => 'fieldset',
				'legend' => get_string('chartoptions', 'artefact.survey'),
				'elements' => ArtefactTypeSurvey::get_chart_options_elements($configdata, true, false, true, true, true, true),
			),
        );
    }

	/*
    public static function instance_config_save($values) {
        if (!empty($values['includetitle'])) {
			$values['title'] = self::get_title();
		}
		return $values;
	}
	*/
	
    public static function artefactchooser_element($default=null) {
		//
	}

    public static function default_copy_type() {
        return 'full';
    }

    /**
     * KadisCompetences blocktype is only allowed in personal views, because currently 
     * there's no such thing as group/site surveys
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }
	
	public static function prepare_chart_config() {
		return array(
			'title' => self::get_title(),
			'type' => 'value',
			'coloroverride' => null,
		);
	}
	
	public static function surveys_exist($owner) {
		global $USER;
		$count = count_records_sql("
			SELECT COUNT(*)
			FROM {artefact} a
			WHERE a.owner = ? AND a.title LIKE 'competence.kadis%'
			",
			array($owner));
		if ($count > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function prepare_chart_data($owner) {
		safe_require('artefact', 'survey');
		
		$surveys = array(
			'competence.kadis.interpersonal.xml',
			'competence.kadis.organisational.xml',
			'competence.kadis.flexibility.xml',
			'competence.kadis.problemsolving.xml',
			'competence.kadis.leadership.xml',
			'competence.kadis.reliability.xml',
		);
		$surveysdata = array();
		foreach ($surveys as $survey) {
			if (record_exists('artefact', 'title', $survey, 'owner', $owner)) {
				$surveysdata[] = get_records_sql_array("
					SELECT a.id, a.title, a.description, a.mtime
					FROM {artefact} a
					WHERE a.owner = ? AND a.title = ?
					ORDER BY a.ctime DESC, a.mtime DESC
					LIMIT 1;",
					array($owner, $survey));
			}
		}

		$DATA = array();
		if (!empty($surveysdata)) {
			foreach ($surveysdata as $surveydata) {
				$filename = $surveydata[0]->title;
				$responses = unserialize($surveydata[0]->description);
				$SINGLE = ArtefactTypeSurvey::get_chart_data_from_responses($filename, $responses);
				$SINGLE[0]['value'] = self::set_achieved_level($SINGLE[0]['value'], $SINGLE[0]['key']);
				$DATA = array_merge($DATA, $SINGLE);
			}
		}
		return $DATA;
	}
	
	public static function build_results_summary_html($owner) {
		$DATA = self::prepare_chart_data($owner);

		$result = '<table border="0">';
		foreach ($DATA as $value) {
			$result .= '<tr>';
			$result .= '<td><strong>' . $value['key'] . '. ' . $value['label'] . ':</strong></td>';
			$result .= '<td>';
			switch ($value['value']) {
				case 1:  $result .= get_string('competencelevel1', 'blocktype.survey/competenceskadis'); break;
				case 2:  $result .= get_string('competencelevel2', 'blocktype.survey/competenceskadis'); break;
				case 3:  $result .= get_string('competencelevel3', 'blocktype.survey/competenceskadis'); break;
				default: $result .= get_string('competencelevel0', 'blocktype.survey/competenceskadis');
			}
			$result .= '</td>';
			$result .= '</tr>';
		}
		$result .= '</table>';
		return $result;
	}
	
	private static function set_achieved_level($number, $key=null) {
		if (isset($number) && isset($key)) {
			switch ($key) {
				case 'M':
					if (0 <= $number && $number <= 64)   return 1; // low level
					if (65 <= $number && $number <= 98)  return 2; //medium level
					if (99 <= $number && $number <= 120) return 3; // high level
					break;
				case 'O':
					if (0 <= $number && $number <= 57)   return 1; // low level
					if (58 <= $number && $number <= 87)  return 2; //medium level
					if (88 <= $number && $number <= 108) return 3; // high level
					break;
				case 'P':
					if (0 <= $number && $number <= 32)  return 1; // low level
					if (33 <= $number && $number <= 49) return 2; //medium level
					if (50 <= $number && $number <= 64) return 3; // high level
					break;
				case 'R':
					if (0 <= $number && $number <= 62)    return 1; // low level
					if (63 <= $number && $number <= 109)  return 2; //medium level
					if (110 <= $number && $number <= 140) return 3; // high level
					break;
				case 'V':
					if (0 <= $number && $number <= 53)   return 1; // low level
					if (54 <= $number && $number <= 83)  return 2; //medium level
					if (84 <= $number && $number <= 104) return 3; // high level
					break;
				case 'Z':
					if (0 <= $number && $number <= 38)  return 1; // low level
					if (39 <= $number && $number <= 61) return 2; //medium level
					if (62 <= $number && $number <= 80) return 3; // high level
					break;
				default: return 0;
			}
		} else {
			return 0;
		}
	}

}

?>
