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

defined('INTERNAL') || die();
require_once('activity.php');


class PluginArtefactSurvey extends PluginArtefact {
    
    public static function get_artefact_types() {
		return array(
			'survey'
		);
    }
    
    public static function get_block_types() {
        return array(); 
    }

    public static function get_plugin_name() {
        return 'survey';
    }

    public static function menu_items() {
        return array(
            array(
                'path' => 'content/surveys',
                'title' => get_string('pluginname', 'artefact.survey'),
                'url' => 'artefact/survey/',
                'weight' => 90,
            )
        );
    }

    public static function get_activity_types() {
        return array(
            (object)array(
                'name' => 'feedback',
                'admin' => 0,
                'delay' => 0,
            )
        );
    }

    public static function postinst($prevversion) {
		// Add blocktype category called 'survey' if it doesn't exists...
		ensure_record_exists('blocktype_category', (object)array('name' => 'survey'), (object)array('name' => 'survey'));
			
        if ($prevversion == 0) {
			// 1. Convert MyLearning artefacts to Survey artefacts (mhr_artefact table)...
			convert_artefacts_to_survey('multipleintelligences');
			convert_artefacts_to_survey('learningstyles');
			// 2. Install survey artefact blocktype named survey and later correctly convert block instances used in views...
			install_survey_blocktype();
			// 3. Convert block instances used in views (mhr_block_instance table)...
			convert_blocks_used_in_views('multipleintelligences');
			convert_blocks_used_in_views('learningstyles');
			// 4. Delete multipleintelligences and learningstyles blocks from mhr_blocktype_installed* tables...
			delete_records('blocktype_installed_viewtype', 'blocktype', 'multipleintelligences');
			delete_records('blocktype_installed_category', 'blocktype', 'multipleintelligences');
			delete_records('blocktype_installed_viewtype', 'blocktype', 'learningstyles');
			delete_records('blocktype_installed_category', 'blocktype', 'learningstyles');
			delete_records('blocktype_installed', 'artefactplugin', 'learning');
			// 5. Delete learning artefact from mhr_artefact_installed* tables...
			delete_records('artefact_installed_type', 'plugin', 'learning');
			delete_records('artefact_installed', 'name', 'learning');
			// 6. Recursive delete learning folder from htdocs/artefact/learning...
			recursive_folder_delete(get_config('docroot') . 'artefact/learning/');
        }
    }

}

class ArtefactTypeSurvey extends ArtefactType {

    public static function get_icon($options=null) {}

    public function __construct($id=0, $data=array()) {
        parent::__construct($id, $data);
    }
    
    public static function is_singular() {
        return false;
    }

    public static function format_child_data($artefact, $pluginname) {
        $a = new StdClass;
        $a->id         = $artefact->id;
        $a->isartefact = true;
        $a->title      = '';
        $a->text       = get_string($artefact->artefacttype, 'artefact.survey'); // $artefact->title;
        $a->container  = (bool) $artefact->container;
        $a->parent     = $artefact->id;
        return $a;
    }

    public static function get_links($id) {
        //
    }

    public function render_self($options) {
        //
    }

    /**
     * This function returns a list of the given user's surveys.
     *
     * @param User
     * @return array (count: integer, data: array)
     */
    public static function get_survey_list($limit, $offset) {
        global $USER;
        ($result = get_records_sql_array("
         SELECT q.id, q.title, q.ctime, q.mtime, q.note
         FROM {artefact} q
         WHERE q.owner = ? AND q.artefacttype = 'survey'
         ORDER BY q.title", array($USER->get('id')), $offset, $limit))
            || ($result = array());

        $count = (int)get_field('artefact', 'COUNT(*)', 'owner', $USER->get('id'), 'artefacttype', 'survey');

        return array($count, $result);
    }


	// Constructs and returns Pieform array of selected survey.
 	// $values		user entered values (answers/responses to questions)
   public function create_pieform_array_from_xml($values=null, $fieldset='tab1') {
		$xmlDoc = new DOMDocument('1.0', 'UTF-8');
		// 'title' field in 'artefact' table contains the survey xml filename...
		try {
			$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $this->get('title'));
			# Return http response in string
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$xmlDoc->loadXML(curl_exec($ch));
		}
		catch (Exception $e) {
			$message = get_string('surveyerror', 'artefact.survey', $this->get('title'));
			$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
		}
		
		$pieform_array = array();
		$pieform_array['name'] = 'questionnaire';
		$pieform_array['renderer'] = get_survey_renderer(get_config('lang'));
		$pieform_array['method'] = 'post';
		$pieform_array['plugintype'] = 'artefact';
		$pieform_array['pluginname'] = 'survey';
		$pieform_array['configdirs'] = array(get_config('libroot') . 'form/', get_config('docroot') . 'artefact/survey/form/');
		$pieform_array['elements'] = array();

		if ($xmlDoc->getElementsByTagName('survey')->item(0) != null) {
			$help_language = $xmlDoc->getElementsByTagName('survey')->item(0)->getAttribute('helpLanguage');
		} else { $help_language = ''; }
		$survey_name = $xmlDoc->getElementsByTagName('survey')->item(0)->getAttribute('name');
		$required_responses = return_bool($xmlDoc->getElementsByTagName('survey')->item(0)->getAttribute('requiredResponses'));
		
		$question_order = $xmlDoc->getElementsByTagName('questions')->item(0)->getAttribute('order');
		$question_order_exists = true;
		if (empty($question_order) || !isset($question_order)) {
			$question_order = null;
			$question_order_exists = false;
		}
		// Read all question IDs from XML survey file
		// and create random order of that IDs (questions)
		// The questions will be printed randomly if sections are joined
		// e.g.: <questions sections="joined" order="random">
		if ($question_order == 'random') {
			$questionIDs = $xmlDoc->getElementsByTagName('question');
			$question_ids = array();
			foreach ($questionIDs as $questionID) {
				array_push($question_ids, $questionID->getAttribute('id'));
			}
			shuffle($question_ids);
			$question_order = implode(',', $question_ids);
		}
		
		$section_type = $xmlDoc->getElementsByTagName('questions')->item(0)->getAttribute('sections');
		if (empty($section_type) || !isset($section_type)) {
			$section_type = 'separated';
		}

		$show_results = $xmlDoc->getElementsByTagName('results')->item(0);
		if (empty($show_results) || !isset($show_results)) {
			$show_results = false;
		} else {
			$show_results = return_bool($show_results->getAttribute('showResults'));
		}

			
		$responses = $xmlDoc->getElementsByTagName('response');
		$possible_responses = array();
		foreach ($responses as $response) {
			$children = $response->cloneNode(true);
			$options = $children->getElementsByTagName('option');
			$response_array = array();
			foreach ($options as $option) {
				// Response label
				$optionlabel = $option->getAttribute(get_config('lang'));
				if (empty($optionlabel)) {
					$optionlabel = $option->getAttribute(self::get_default_lang($this->get('title')));
				}
				// Replace {image:pict.jpg} in responses with proper <img src="pict.jpg"> tag...
				$optionlabel = preg_replace('#{image:([a-zA-Z0-9\.\_\-\~]+)}#', '<img src="' . get_config('wwwroot') . 'artefact/survey/surveys/' . substr($this->get('title'), 0, -4) . '/$1" style="border:1px solid black">', $optionlabel);
				$optionvalue = $option->getAttribute('value');

				//if (isset($optionvalue) && !empty($optionvalue)) {
				if (isset($optionvalue)) {
					$response_array = array_merge($response_array, array($optionvalue => $optionlabel));
				} else {
					$optionsection = $option->getAttribute('section');
					$optionid = $option->getAttribute('id');
					//if (isset($optionsection) && !empty($optionsection) && isset($optionid) && !empty($optionid)) {
					if (isset($optionsection) && isset($optionid)) {
						$response_array = array_merge($response_array, array($optionsection . '_' . $optionid => $optionlabel));
					}
				}
			}
			// If set, than shuffle/randomize response order...
			if (return_bool($response->getAttribute('shuffle'))) {
				$response_array = shuffle_assoc($response_array);
			}
			$possible_responses = array_merge($possible_responses, array($response->getAttribute('id') => $response_array));
		}
		
		//---------------------------
		// Sections are separated...
		//---------------------------
		if ($section_type == 'separated') {
			$pieform_array['elements'] = array_merge($pieform_array['elements'],
			array('section_type' => array(
					'type' => 'hidden',
					'value' => $section_type,
				)
			));
			$pieform_array['elements'] = array_merge($pieform_array['elements'],
			array('show_results' => array(
					'type' => 'hidden',
					'value' => $show_results,
				)
			));

			$sections = $xmlDoc->getElementsByTagName('section');
			foreach ($sections as $section) {
				$question_elements_array = array();
				// Add section help, if it is enabled...
				$section_intro = return_bool($section->getAttribute('help'));
				if ($section_intro) {
					$introhtml  = '<a href="#" onClick="surveyHelp(\'artefact\',\'survey\',\'' . $survey_name . '\',\'section_' . $section->getAttribute('name') . '\',\'' . self::get_default_lang($this->get('title')) . '\',this); return false;">';
					$introhtml .= '<img src="' . get_config('wwwroot') . 'theme/raw/static/images/icon_help.png" width="16" height="16" alt="Help" title="Help"></a>';
					$intro_array = array(
						'type'  => 'markup',
						'value' => $introhtml,
					);
					$question_elements_array = array_merge($question_elements_array,
					array('section_' . $section->getAttribute('name') => $intro_array));
				}

				$children = $section->cloneNode(true);
				$questions = $children->getElementsByTagName('question');
				foreach ($questions as $question) {
					// Get question type
					$type = $question->getAttribute('type');
					if (empty($type) || !isset($type)) {
						$texttype = 'checkbox';
					}
					// Get question text type
					$texttype = $question->getAttribute('textType');
					if (empty($texttype) || !isset($texttype)) {
						$texttype = 'label';
					}
					
					// Get label in Mahara site defined language or -
					// if the translation doesn't exists - in English
					$labelhtml = $question->getAttribute(get_config('lang'));
					if (empty($labelhtml)) {
						$labelhtml = $question->getAttribute(self::get_default_lang($this->get('title')));
					}
					// Add help icon with link to help file in primary/secondary language...
					$help_enabled  = return_bool($question->getAttribute('help'));
					if ($help_enabled) {
						if ($help_language == 'secondary') {
							$lang = $this->get('note');
						}
						else {
							$lang = get_config('lang');
						}				
						$labelhtml .= ' <a href="#" onClick="surveyHelp(\'artefact\',\'survey\',\'' . $survey_name . '\',\'' . $question->getAttribute('id') . '\',\'' . $lang . '\',this); return false;">';
						$labelhtml .= '<img src="' . get_config('wwwroot') . 'theme/raw/static/images/icon_help.png" width="16" height="16" alt="Help" title="Help"></a>';
					}

					// Get description in Survey foreign language or -
					// if the translation doesn't exists - in English
					$description = $question->getAttribute($this->get('note'));
					if (!isset($description) || empty($description)) {
						$description = $question->getAttribute(self::get_default_lang($this->get('title')));
					}

					$question_array = array();
					$question_array = array_merge($question_array, array('type' => $question->getAttribute('type')));
					switch ($type) {
						case 'html':
							$question_array = array_merge($question_array, array('value' => '&nbsp;'));
							break;
						default:
							$defaultvalue = strval( $question->getAttribute('defaultValue'));
							if (strlen($defaultvalue) == 0) {
								$name = $question->getAttribute('section');
								if (!isset($name) || empty($name)) {
									$name = $section->getAttribute('name');
								}
								$defaultvalue = $values[$name . '_' . $question->getAttribute('id')];
							}
							$question_array = array_merge($question_array, array('defaultvalue' => $defaultvalue));
							break;
					}
					if ($texttype == 'title') {
						$question_array = array_merge($question_array, array('title' => $labelhtml));
					} else {
						$question_array = array_merge($question_array, array('labelhtml' => $labelhtml));
					}
					if ($this->get('note') != null) {
						$question_array = array_merge($question_array, array('description' => $description));
					}
					
					// For Pieform elements other than scale or trafficlights, that require options array...
					$response = $question->getAttribute('response');
					if (isset($response) && !empty($response) && $type != 'scale' && $type != 'trafficlights') {
						$question_array = array_merge($question_array, array('options' => $possible_responses[$response]));
					}
					
					// For Pieform element scale (set steps and left/right title or label)
					if (isset($response) && !empty($response) && $type == 'scale') {
						// If left label exists...
						if (isset($possible_responses[$response]['left']) && !empty($possible_responses[$response]['left'])) {
							$labelleft = $possible_responses[$response]['left'];
						} else {
							$labelleft = null;
						}
						// If right label exists...
						if (isset($possible_responses[$response]['right']) && !empty($possible_responses[$response]['right'])) {
							$labelright = $possible_responses[$response]['right'];
						} else {
							$labelright = null;
						}
						
						$question_array = array_merge($question_array, array('titleleft' => $labelleft));
						$question_array = array_merge($question_array, array('titleright' => $labelright));
					}
					// For Pieform element textarea or wysiwyg
					if ($type == 'textarea' || $type == 'wysiwyg') {
						$cols = $question->getAttribute('cols');
						if (!isset($cols) || empty($cols)) {
							$cols = 60;
						}
						$rows = $question->getAttribute('rows');
						if (!isset($rows) || empty($rows)) {
							$rows = 6;
						}
						$question_array = array_merge($question_array, array('cols' => $cols));
						$question_array = array_merge($question_array, array('rows' => $rows));
						$question_array = array_merge($question_array, array('resizable' => false));
					}
					$steps = $question->getAttribute('scaleSteps');
					if (isset($steps) && !empty($steps)) {
						$question_array = array_merge($question_array, array('steps' => $steps));
					}
					$reverse = $question->getAttribute('reverseResponse');
					if (isset($reverse) && !empty($reverse) && return_bool($reverse) == true) {
						$question_array = array_merge($question_array, array('reverse' => 'true'));
					}
					
					$br2rows = return_bool($question->getAttribute('br2rows'));
					$separator = '&nbsp;';
					if ($type == 'radio' || $type == 'checks') $separator = '<br>';
					if (isset($br2rows) && $br2rows) {
						$question_array = array_merge($question_array, array('br2rows' => true));
						$question_array = array_merge($question_array, array('separator' => $separator));
					}

					// If answers to all questions are required...
					if ($required_responses) {
						$question_array = array_merge($question_array, array('rules' => array('required' => true)));
					}
					
					// Create elements array for questions in each fieldset
					$name = $question->getAttribute('section');
					if (!isset($name) || empty($name)) {
						$name = $section->getAttribute('name');
					}
					$question_elements_array = array_merge($question_elements_array,
					array($name . '_' . $question->getAttribute('id') => $question_array));
					
					// If allowed, add wysiwyg/textarea for individual question/response comments...
					$comments = return_bool($question->getAttribute('allowComments'));
					if (isset($comments) && $comments) {
						$comments_array = array();
						$comments_array = array_merge($comments_array, array(
							'type' => 'wysiwyg', // Maybe textarea would be better?
							'rows' => 6,
							'cols' => 50,
							'title' => get_string('addresponsecomments', 'artefact.survey'),
							'defaultvalue' => $values['comment_' . $question->getAttribute('id')]
						));
						if (isset($br2rows) && $br2rows) {
							$comments_array = array_merge($comments_array, array('br2rows' => true));
						}
						$question_elements_array = array_merge($question_elements_array,
						array('comment_' . $question->getAttribute('id') => $comments_array));
					}
				}
				
				$legend = $section->getAttribute(self::get_default_lang($this->get('title')));
				if ($section->getAttribute('legend') == 'false' || $section->getAttribute('legend') == '0') {
				//if (return_bool($section->getAttribute('legend')) == false) {
					$legend = null;
				}
				$pieform_array['elements'] = array_merge($pieform_array['elements'],
				array($section->getAttribute('name') => array(
						'type' => 'fieldset',
						'legend' => $legend,
						'collapsible' => $section->getAttribute('collapsible'),
						'collapsed' => $section->getAttribute('collapsed'),
						'elements' => $question_elements_array
					)
				));
			}
				
			$pieform_array['elements'] = array_merge($pieform_array['elements'],
			array('submit' => array(
					'type' => 'submit',
					'value' => get_string('save'),
				)
			));
		}

		//------------------------
		// Sections are joined...
		//------------------------
		if ($section_type == 'joined') {
			$pieform_array['elements'] = array_merge($pieform_array['elements'],
			array('section_type' => array(
					'type' => 'hidden',
					'value' => $section_type,
				)
			));
			$pieform_array['elements'] = array_merge($pieform_array['elements'],
			array('show_results' => array(
					'type' => 'hidden',
					'value' => $show_results,
				)
			));

			$question_elements_array = array();
			$sections = $xmlDoc->getElementsByTagName('section');
			foreach ($sections as $section) {
				$children = $section->cloneNode(true);
				$questions = $children->getElementsByTagName('question');
				foreach ($questions as $question) {
					if ($question_order_exists) {
						$question_order = str_replace($question->getAttribute('id'), $section->getAttribute('name') . '_' . $question->getAttribute('id'), $question_order);
					} else {
						if ($question_order == null) {
							$question_order .= $section->getAttribute('name') . '_' . $question->getAttribute('id');
						} else {
							$question_order .= ',' . $section->getAttribute('name') . '_' . $question->getAttribute('id');
						}
					}

					// Get question type
					$type = $question->getAttribute('type');
					if (empty($type) || !isset($type)) {
						$texttype = 'checkbox';
					}
					// Get question text type
					$texttype = $question->getAttribute('textType');
					if (empty($texttype) || !isset($texttype)) {
						$texttype = 'label';
					}
					// Get label in Mahara site defined language or -
					// if the translation doesn't exists - in English
					$labelhtml = $question->getAttribute(get_config('lang'));
					if (empty($labelhtml)) {
						$labelhtml = $question->getAttribute(self::get_default_lang($this->get('title')));
					}
					// Add help icon with link to help file in primary/secondary language...
					$help_enabled = return_bool($question->getAttribute('help'));
					if ($help_enabled) {
						if ($help_language == 'secondary') {
							$lang = $this->get('note');
						}
						else {
							$lang = get_config('lang');
						}				
						$labelhtml .= ' <a href="#" onClick="surveyHelp(\'artefact\',\'survey\',\'' . $survey_name . '\',\'' . $question->getAttribute('id') . '\',\'' . $lang . '\',this); return false;">';
						$labelhtml .= '<img src="' . get_config('wwwroot') . 'theme/raw/static/images/icon_help.png" width="16" height="16" alt="Help" title="Help"></a>';
					}

					// Get description in Survey foreign language or -
					// if the translation doesn't exists - in English
					$description = $question->getAttribute($this->get('note'));
					if (!isset($description) || empty($description)) {
						$description = $question->getAttribute(self::get_default_lang($this->get('title')));
					}

					$question_array = array();
					$question_array = array_merge($question_array, array('type' => $question->getAttribute('type')));
					switch ($type) {
						case 'html':
							$question_array = array_merge($question_array, array('value' => '&nbsp;'));
							break;
						default:
							$defaultvalue = strval( $question->getAttribute('defaultValue'));
							if (strlen($defaultvalue) == 0) {
								$name = $question->getAttribute('section');
								if (!isset($name) || empty($name)) {
									$name = $section->getAttribute('name');
								}
								$defaultvalue = $values[$name . '_' . $question->getAttribute('id')];
							}
							$question_array = array_merge($question_array, array('defaultvalue' => $defaultvalue));
							break;
					}
					if ($texttype == 'title') {
						$question_array = array_merge($question_array, array('title' => $labelhtml));
					} else {
						$question_array = array_merge($question_array, array('labelhtml' => $labelhtml));
					}
					if ($this->get('note') != null) {
						$question_array = array_merge($question_array, array('description' => $description));
					}

					// For Pieform elements other than scale or trafficlights, that require options array...
					$response = $question->getAttribute('response');
					if (isset($response) && !empty($response) && $type != 'scale' && $type != 'trafficlights') {
						$question_array = array_merge($question_array, array('options' => $possible_responses[$response]));
					}
					
					// For Pieform element scale (set steps and left/right title or label)
					if (isset($response) && !empty($response) && $type == 'scale') {
						// If left label exists...
						if (isset($possible_responses[$response]['left']) && !empty($possible_responses[$response]['left'])) {
							$labelleft = $possible_responses[$response]['left'];
						} else {
							$labelleft = null;
						}
						// If right label exists...
						if (isset($possible_responses[$response]['right']) && !empty($possible_responses[$response]['right'])) {
							$labelright = $possible_responses[$response]['right'];
						} else {
							$labelright = null;
						}
						
						$question_array = array_merge($question_array, array('titleleft' => $labelleft));
						$question_array = array_merge($question_array, array('titleright' => $labelright));
					}
					// For Pieform element textarea or wysiwyg
					if ($type == 'textarea' || $type == 'wysiwyg') {
						$cols = $question->getAttribute('cols');
						if (!isset($cols) || empty($cols)) {
							$cols = 60;
						}
						$rows = $question->getAttribute('rows');
						if (!isset($rows) || empty($rows)) {
							$rows = 6;
						}
						$question_array = array_merge($question_array, array('cols' => $cols));
						$question_array = array_merge($question_array, array('rows' => $rows));
						$question_array = array_merge($question_array, array('resizable' => false));
					}
					$steps = $question->getAttribute('scaleSteps');
					if (isset($steps) && !empty($steps)) {
						$question_array = array_merge($question_array, array('steps' => $steps));
					}
					$reverse = $question->getAttribute('reverseResponse');
					if (isset($reverse) && !empty($reverse) && return_bool($reverse) == true) {
						$question_array = array_merge($question_array, array('reverse' => 'true'));
					}

					$br2rows = return_bool($question->getAttribute('br2rows'));
					$separator = '&nbsp;';
					if ($type == 'radio' || $type == 'checks') $separator = '<br>';
					if (isset($br2rows) && $br2rows) {
						$question_array = array_merge($question_array, array('br2rows' => true));
						$question_array = array_merge($question_array, array('separator' => $separator));
					}

					// If answers to all questions are required...
					if ($required_responses) {
						$question_array = array_merge($question_array, array('rules' => array('required' => true)));
					}
					
					// Create elements array for questions in each fieldset
					$name = $question->getAttribute('section');
					if (!isset($name) || empty($name)) {
						$name = $section->getAttribute('name');
					}
					$question_elements_array = array_merge($question_elements_array,
					array($name . '_' . $question->getAttribute('id') => $question_array));			
				}
					
			}
			
			// Order questions in survey according to order rule, specified in XML file
			$question_elements_ordered = array();
			$items = explode(',', $question_order);
			foreach ($items as $item) {
				$question_elements_ordered = array_merge($question_elements_ordered, array($item => $question_elements_array[$item]));
				// If allowed, add wysiwyg/textarea for individual question/response comments...
				$name = $xmlDoc->getElementsByTagName('section')->item(0)->getAttribute('name');
				$questions = $xmlDoc->getElementsByTagName('question');
				foreach ($questions as $question) {
					$id = $question->getAttribute('id');
					$comments = return_bool($question->getAttribute('allowComments'));
					if ($name . '_' . $id == $item && $comments) {
						$comments_array = array();
						$comments_array = array_merge($comments_array, array(
							'type' => 'wysiwyg', // Maybe textarea would be better?
							'rows' => 6,
							'cols' => 50,
							'title' => get_string('addresponsecomments', 'artefact.survey'),
							'defaultvalue' => $values['comment_' . $id]
						));
						if (isset($br2rows) && $br2rows) {
							$comments_array = array_merge($comments_array, array('br2rows' => true));
						}
						$question_elements_ordered = array_merge($question_elements_ordered,
						array('comment_' . $id => $comments_array));
					}
				}
			}
			$pieform_array['elements'] = array_merge($pieform_array['elements'],
			array($section->getAttribute('name') => array(
					'type' => 'fieldset',
					'legend' => null,
					//'collapsible' => $section->getAttribute('collapsible'),
					//'collapsed' => $section->getAttribute('collapsed'),
					'elements' => $question_elements_ordered
				)
			));

			$pieform_array['elements'] = array_merge($pieform_array['elements'],
			array('submit' => array(
					'type' => 'submit',
					'value' => get_string('save'),
				)
			));
		}
		
		//------------------------
		// Sections are tabbed...
		//------------------------
		if ($section_type == 'tabbed') {
			$pieform_array['elements'] = array_merge($pieform_array['elements'],
			array('section_type' => array(
					'type' => 'hidden',
					'value' => $section_type,
				)
			));
			$pieform_array['elements'] = array_merge($pieform_array['elements'],
			array('show_results' => array(
					'type' => 'hidden',
					'value' => $show_results,
				)
			));

			$sections = $xmlDoc->getElementsByTagName('section');
			$i = 1;
			foreach ($sections as $section) {
				$sectiontab = 'tab' . $i;
				$question_elements_array = array();
				$children = $section->cloneNode(true);
				$questions = $children->getElementsByTagName('question');
				foreach ($questions as $question) {
					// Get question type
					$type = $question->getAttribute('type');
					if (empty($type) || !isset($type)) {
						$texttype = 'checkbox';
					}
					// Get question text type
					$texttype = $question->getAttribute('textType');
					if (empty($texttype) || !isset($texttype)) {
						$texttype = 'label';
					}
					// Get label in Mahara site defined language or -
					// if the translation doesn't exists - in English
					$labelhtml = $question->getAttribute(get_config('lang'));
					if (empty($labelhtml)) {
						$labelhtml = $question->getAttribute(self::get_default_lang($this->get('title')));
					}
					// Add help icon with link to help file in primary/secondary language...
					$help_enabled  = return_bool($question->getAttribute('help'));
					if ($help_enabled) {
						if ($help_language == 'secondary') {
							$lang = $this->get('note');
						}
						else {
							$lang = get_config('lang');
						}				
						$labelhtml .= ' <a href="#" onClick="surveyHelp(\'artefact\',\'survey\',\'' . $survey_name . '\',\'' . $question->getAttribute('id') . '\',\'' . $lang . '\',this); return false;">';
						$labelhtml .= '<img src="' . get_config('wwwroot') . 'theme/raw/static/images/icon_help.png" width="16" height="16" alt="Help" title="Help"></a>';
					}

					// Get description in Survey foreign language or -
					// if the translation doesn't exists - in English
					$description = $question->getAttribute($this->get('note'));
					if (!isset($description) || empty($description)) {
						$description = $question->getAttribute(self::get_default_lang($this->get('title')));
					}

					$question_array = array();
					$question_array = array_merge($question_array, array('type' => $question->getAttribute('type')));
					switch ($type) {
						case 'html':
							$question_array = array_merge($question_array, array('value' => '&nbsp;'));
							break;
						default:
							$defaultvalue = strval( $question->getAttribute('defaultValue'));
							if (strlen($defaultvalue) == 0) {
								$name = $question->getAttribute('section');
								if (!isset($name) || empty($name)) {
									$name = $section->getAttribute('name');
								}
								$defaultvalue = $values[$name . '_' . $question->getAttribute('id')];
							}
							$question_array = array_merge($question_array, array('defaultvalue' => $defaultvalue));
							break;
					}
					if ($texttype == 'title') {
						$question_array = array_merge($question_array, array('title' => $labelhtml));
					} else {
						$question_array = array_merge($question_array, array('labelhtml' => $labelhtml));
					}
					if ($this->get('note') != null) {
						$question_array = array_merge($question_array, array('description' => $description));
					}
					
					// For Pieform elements other than scale or trafficlights, that require options array...
					$response = $question->getAttribute('response');
					if (isset($response) && !empty($response) && $type != 'scale' && $type != 'trafficlights') {
						$question_array = array_merge($question_array, array('options' => $possible_responses[$response]));
					}
					
					// For Pieform element scale (set steps and left/right title or label)
					if (isset($response) && !empty($response) && $type == 'scale') {
						// If left label exists...
						if (isset($possible_responses[$response]['left']) && !empty($possible_responses[$response]['left'])) {
							$labelleft = $possible_responses[$response]['left'];
						} else {
							$labelleft = null;
						}
						// If right label exists...
						if (isset($possible_responses[$response]['right']) && !empty($possible_responses[$response]['right'])) {
							$labelright = $possible_responses[$response]['right'];
						} else {
							$labelright = null;
						}
						
						$question_array = array_merge($question_array, array('titleleft' => $labelleft));
						$question_array = array_merge($question_array, array('titleright' => $labelright));
					}
					// For Pieform element textarea or wysiwyg
					if ($type == 'textarea' || $type == 'wysiwyg') {
						$cols = $question->getAttribute('cols');
						if (!isset($cols) || empty($cols)) {
							$cols = 60;
						}
						$rows = $question->getAttribute('rows');
						if (!isset($rows) || empty($rows)) {
							$rows = 6;
						}
						$question_array = array_merge($question_array, array('cols' => $cols));
						$question_array = array_merge($question_array, array('rows' => $rows));
						$question_array = array_merge($question_array, array('resizable' => false));
					}
					$steps = $question->getAttribute('scaleSteps');
					if (isset($steps) && !empty($steps)) {
						$question_array = array_merge($question_array, array('steps' => $steps));
					}
					$reverse = $question->getAttribute('reverseResponse');
					if (isset($reverse) && !empty($reverse) && return_bool($reverse) == true) {
						$question_array = array_merge($question_array, array('reverse' => 'true'));
					}
					
					$br2rows = return_bool($question->getAttribute('br2rows'));
					$separator = '&nbsp;';
					if ($type == 'radio' || $type == 'checks') $separator = '<br>';
					if (isset($br2rows) && $br2rows) {
						$question_array = array_merge($question_array, array('br2rows' => true));
						$question_array = array_merge($question_array, array('separator' => $separator));
					}

					// If answers to all questions are required...
					if ($required_responses) {
						$question_array = array_merge($question_array, array('rules' => array('required' => true)));
					}
					
					// Create elements array for questions in each fieldset
					$name = $question->getAttribute('section');
					if (!isset($name) || empty($name)) {
						$name = $section->getAttribute('name');
					}
					$question_elements_array = array_merge($question_elements_array,
					array($name . '_' . $question->getAttribute('id') => $question_array));
					
					// If allowed, add wysiwyg/textarea for individual question/response comments...
					$comments = return_bool($question->getAttribute('allowComments'));
					if (isset($comments) && $comments) {
						$comments_array = array();
						$comments_array = array_merge($comments_array, array(
							'type' => 'wysiwyg', // Maybe textarea would be better?
							'rows' => 6,
							'cols' => 50,
							'title' => get_string('addresponsecomments', 'artefact.survey'),
							'defaultvalue' => $values['comment_' . $question->getAttribute('id')]
						));
						if (isset($br2rows) && $br2rows) {
							$comments_array = array_merge($comments_array, array('br2rows' => true));
						}
						$question_elements_array = array_merge($question_elements_array,
						array('comment_' . $question->getAttribute('id') => $comments_array));
					}
				}
				
				$legend = $section->getAttribute(self::get_default_lang($this->get('title')));
				if ($section->getAttribute('legend') == 'false' || $section->getAttribute('legend') == '0') {
				//if (return_bool($section->getAttribute('legend')) == false) {
					$legend = null;
				}
				$pieform_array['elements'] = array_merge($pieform_array['elements'],
				array($sectiontab /*$section->getAttribute('name')*/ => array(
						'type' => 'fieldset',
						'legend' => $legend,
						'class' => $fieldset != $sectiontab ? 'collapsed' : '',
						'elements' => $question_elements_array
					)
				));
				
				$i++;
			}
				
			$pieform_array['elements'] = array_merge($pieform_array['elements'],
			array('fs' => array(
					'type' => 'hidden',
					'value' => $fieldset,
				)
			));

			$pieform_array['elements'] = array_merge($pieform_array['elements'],
			array('submit' => array(
					'type' => 'submit',
					'value' => get_string('save'),
				)
			));
		}
		
		//log_debug($pieform_array);
        return $pieform_array;
    }

	// Whether selected survey returns results.
	// $filename	the name of XML survey file
    public function survey_returns_results($filename=null) {
		if ($filename == null) {
			$filename = $this->get('title');
		}
		$xmlDoc = new DOMDocument('1.0', 'UTF-8');
		// 'title' field in 'artefact' table contains the survey xml filename...
		$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $filename);
		# Return http response in string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$loaded = $xmlDoc->loadXML(curl_exec($ch));
		if ($loaded) {
			$return = $xmlDoc->getElementsByTagName('results')->item(0);
			if ($return == null) {
				$return = false;
			} else {
				$return = return_bool($xmlDoc->getElementsByTagName('results')->item(0)->getAttribute('showResults'));
			}
			return $return;
		} else {
			$message = get_string('surveyerror', 'artefact.survey', $filename);
			$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
		}
	}
	
	// Whether selected survey returns user responses.
	// $filename	the name of XML survey file
    public function survey_returns_responses($filename=null) {
		if ($filename == null) {
			$filename = $this->get('title');
		}
		$xmlDoc = new DOMDocument('1.0', 'UTF-8');
		// 'title' field in 'artefact' table contains the survey xml filename...
		$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $filename);
		# Return http response in string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$loaded = $xmlDoc->loadXML(curl_exec($ch));
		if ($loaded) {
			$return = $xmlDoc->getElementsByTagName('results')->item(0);
			if ($return == null) {
				$return = false;
			} else {
				$return = return_bool($xmlDoc->getElementsByTagName('results')->item(0)->getAttribute('showResponses'));
			}
			return $return;
		} else {
			$message = get_string('surveyerror', 'artefact.survey', $filename);
			$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
		}
	}
	
	// Whether selected survey returns chart.
	// $filename	the name of XML survey file
    public function survey_returns_chart($filename=null) {
		if ($filename == null) {
			$filename = $this->get('title');
		}
		$xmlDoc = new DOMDocument('1.0', 'UTF-8');
		// 'title' field in 'artefact' table contains the survey xml filename...
		$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $filename);
		# Return http response in string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$loaded = $xmlDoc->loadXML(curl_exec($ch));
		if ($loaded) {
			$return = $xmlDoc->getElementsByTagName('results')->item(0);
			if ($return == null) {
				$return = false;
			} else {
				$return = return_bool($xmlDoc->getElementsByTagName('results')->item(0)->getAttribute('showChart'));
			}
			return $return;
		} else {
			$message = get_string('surveyerror', 'artefact.survey', $filename);
			$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
		}
	}
	
	// Returns the title of selected survey.
	// $filename	the name of XML survey file
    public function get_survey_title_from_xml($filename=null) {
		if ($filename == null) {
			$filename = $this->get('title');
		}
		$xmlDoc = new DOMDocument('1.0', 'UTF-8');
		// 'title' field in 'artefact' table contains the survey xml filename...
		$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $filename);
		# Return http response in string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$loaded = $xmlDoc->loadXML(curl_exec($ch));
		if ($loaded) {
			$title = $xmlDoc->getElementsByTagName('title')->item(0)->getAttribute(get_config('lang'));
			if (empty($title)) {
				$title = $xmlDoc->getElementsByTagName('title')->item(0)->getAttribute(self::get_default_lang($filename));
			}
			return $title;
		} else {
			$message = get_string('surveyerror', 'artefact.survey', $filename);
			$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
		}
	}
	
	// Returns the type of sections (joined, separated, tabbed).
	// $filename	the name of XML survey file
    public function get_survey_section_type($filename=null) {
		if ($filename == null) {
			$filename = $this->get('title');
		}
		$xmlDoc = new DOMDocument('1.0', 'UTF-8');
		// 'title' field in 'artefact' table contains the survey xml filename...
		$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $filename);
		# Return http response in string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$loaded = $xmlDoc->loadXML(curl_exec($ch));
		if ($loaded) {
			$section_type = $xmlDoc->getElementsByTagName('questions')->item(0)->getAttribute('sections');
			if (empty($section_type)) {
				$section_type = 'separated'; // Maybe the default value should be joined???
			}
			return $section_type;
		} else {
			$message = get_string('surveyerror', 'artefact.survey', $filename);
			$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
		}
	}
	
	// Returns the info (description, instructions, etc) of selected survey.
	// $filename	the name of XML survey file
	public function get_survey_info_from_xml($filename=null) {
		if ($filename == null) {
			$filename = $this->get('title');
		}

		$xmlDoc = new DOMDocument('1.0', 'UTF-8');
		// 'title' field in 'artefact' table contains the survey xml filename...
		$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $filename);
		# Return http response in string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$loaded = $xmlDoc->loadXML(curl_exec($ch));
		// Get survey copyright
		if ($xmlDoc->getElementsByTagName('copyright')->item(0) != null) {
			$copyright = $xmlDoc->getElementsByTagName('copyright')->item(0)->getAttribute(self::get_default_lang($filename));
		} else { $copyright = ''; }
		// Get survey description
		if ($xmlDoc->getElementsByTagName('description')->item(0) != null) {
			$description = $xmlDoc->getElementsByTagName('description')->item(0)->getAttribute(self::get_default_lang($filename));
			// Replace {image:pict.jpg} in responses with proper <img src="pict.jpg"> tag...
			$description = preg_replace('#{image:([a-zA-Z0-9\.\_\-\~]+)}#', '<img src="' . get_config('wwwroot') . 'artefact/survey/surveys/' . substr($filename, 0, -4) . '/$1">', $description);
		} else { $description = ''; }
		// Get survey instructions
		if ($xmlDoc->getElementsByTagName('instructions')->item(0) != null) {
			$instructions = $xmlDoc->getElementsByTagName('instructions')->item(0)->getAttribute(self::get_default_lang($filename));
		} else { $instructions = ''; }
		// Get survey url
		$surveyurl = $xmlDoc->getElementsByTagName('survey')->item(0)->getAttribute('url');
		
		$instructions_title = '';
		$lang = self::get_default_lang($filename);
		if ($lang == 'en.utf8') {
			$instructions_title = get_string_from_file('instructions', get_config('docroot') . 'artefact/survey/lang/en.utf8/artefact.survey.php');
		} else {
			if (file_exists(get_config('dataroot') . 'langpacks/' . $lang . '/artefact/survey/lang/' . $lang . '/artefact.survey.php')) {
				$instructions_title = get_string_from_file('instructions', get_config('dataroot') . 'langpacks/' . $lang . '/artefact/survey/lang/' . $lang . '/artefact.survey.php');
			} else {
				$instructions_title = get_string_from_file('instructions', get_config('docroot') . 'artefact/survey/lang/' . $lang . '/artefact.survey.php');
			}
		}

		$html = '';
		if (!empty($description)) { $html .= '<div class="description">' . html_entity_decode($copyright) . '</div>'; }
		if (!empty($surveyurl)) { $html .= '<div class="description"><a href="' . $surveyurl . '" target="_blank">' . $surveyurl . '</a></div>'; }
		$html .= '<p>' . html_entity_decode($description) . '</p>';
		$html .= '<h3>' . $instructions_title . '</h3>';
		$html .= '<p>' . html_entity_decode($instructions) . '</p>';
		return $html;
	}


	// Returns the Javascript for all rank elements in selected survey.
	// $filename	the name of XML survey file
	public function get_rank_elements_js($values, $filename=null) {
		if ($filename == null) {
			$filename = $this->get('title');
		}

		$xmlDoc = new DOMDocument('1.0', 'UTF-8');
		// 'title' field in 'artefact' table contains the survey xml filename...
		$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $filename);
		# Return http response in string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$loaded = $xmlDoc->loadXML(curl_exec($ch));

		$sections = $xmlDoc->getElementsByTagName('section');
		foreach ($sections as $section) {
			$rank_elements_array = array();
			$children = $section->cloneNode(true);
			$questions = $children->getElementsByTagName('question');
			foreach ($questions as $question) {
				// Get question type
				$type = $question->getAttribute('type');
				if (empty($type) || !isset($type)) {
					$texttype = 'checkbox';
				}

				if ($type == 'rank') {
					$name = $question->getAttribute('section');
					if (!isset($name) || empty($name)) {
						$name = $section->getAttribute('name');
					}
					$defaultvalue = $values[$name . '_' . $question->getAttribute('id')];
					$rank_elements_array = array_merge($rank_elements_array, array($name . '_' . $question->getAttribute('id') => $defaultvalue));
				}
			}
		}
		
		// If there are not rank emelents in survey, return no javascript
		if (empty($rank_elements_array)) {
			return '';
		} else {
			// Maybe later (if this Pieform element is merged with master code) the path should change to:
			// $libpath = get_config('wwwroot')  . 'js/rank/';
			$libpath = get_config('wwwroot')  . 'artefact/survey/js/rank';
			$js  = "<link rel=\"stylesheet\" type=\"text/css\" href=\"$libpath/rank.css\">";
			$js .= "\n    <script type=\"text/javascript\" src=\"$libpath/core.js\"></script>";
        	$js .= "\n    <script type=\"text/javascript\" src=\"$libpath/events.js\"></script>";
        	$js .= "\n    <script type=\"text/javascript\" src=\"$libpath/css.js\"></script>";
        	$js .= "\n    <script type=\"text/javascript\" src=\"$libpath/coordinates.js\"></script>";
        	$js .= "\n    <script type=\"text/javascript\" src=\"$libpath/drag.js\"></script>";
        	$js .= "\n    <script type=\"text/javascript\" src=\"$libpath/dragsort.js\"></script>";
        	$js .= "\n    <script type=\"text/javascript\" src=\"$libpath/cookies.js\"></script>";

			$js .= <<<EOF

    <script type="text/javascript"><!--
        var dragsort = ToolMan.dragsort()
        var junkdrawer = ToolMan.junkdrawer()

        window.onload = function() {
EOF;
			foreach ($rank_elements_array as $name => $value) {
				$js .= "\n            junkdrawer.restoreListOrder(\"questionnaire_$name\")";
				$js .= "\n            dragsort.makeListSortable(document.getElementById(\"questionnaire_$name\"), verticalOnly, saveOrder)";
			}
			$js .= <<<EOF
			
        }

        function verticalOnly(item) {
            item.toolManDragGroup.verticalOnly()
        }

        function saveOrder(item) {
            var group = item.toolManDragGroup
            var list = group.element.parentNode
            var id = list.getAttribute("id")
            if (id == null) return
            group.register('dragend', function() {
                ToolMan.cookies().set("list-" + id, junkdrawer.serializeList(list), 365)
            })
        }
	
EOF;
			foreach ($rank_elements_array as $name => $value) {
				$expires = 365 * 24 * 60 * 60 * 1000; // Turn days into miliseconds
				$js .= "\n        ToolMan.cookies().set(\"list-questionnaire_$name\", \"$value\", $expires)";
			}
			$js .= <<<EOF

    </script>
EOF;
			return $js;
		}
	}


	// Returns array of all the possible responses in given survey (with empty values).
	// $filename	the name of XML survey file
	public function get_empty_responses_from_survey($filename) {
		$empty_responses = array();
		$xmlDoc = new DOMDocument('1.0', 'UTF-8');
		$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $filename);
		# Return http response in string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$loaded = $xmlDoc->loadXML(curl_exec($ch));
		if ($loaded) {
			// Get all the possible respones
			$responses = $xmlDoc->getElementsByTagName('response');
			$possible_responses = array();
			foreach ($responses as $response) {
				$children = $response->cloneNode(true);
				$options = $children->getElementsByTagName('option');
				$response_array = array();
				foreach ($options as $option) {
					$optionvalue = $option->getAttribute('value');
					if (isset($optionvalue) && !empty($optionvalue)) {
						$response_array = array_merge($response_array, array($optionvalue => 0));
					} else {
						$optionsection = $option->getAttribute('section');
						$optionid = $option->getAttribute('id');
						if (isset($optionsection) && !empty($optionsection) && isset($optionid) && !empty($optionid)) {
							$response_array = array_merge($response_array, array($optionsection . '_' . $optionid => 0));
						}
					}
				}
				$possible_responses = array_merge($possible_responses, array($response->getAttribute('id') => $response_array));
			}
			
			$sections = $xmlDoc->getElementsByTagName('section');
			foreach ($sections as $section) {
				$children = $section->cloneNode(true);
				$questions = $children->getElementsByTagName('question');
				foreach ($questions as $question) {
					// Get question type
					$type = $question->getAttribute('type');
					if (empty($type) || !isset($type)) {
						$texttype = 'checkbox';
					}
					
					// Get question name
					$name = $question->getAttribute('section');
					if (!isset($name) || empty($name)) {
						$name = $section->getAttribute('name');
					}
					
					if ($type == 'checks') {
						$response = $question->getAttribute('response');
						$empty_responses = array_merge($empty_responses, $possible_responses[$response]);
					} else {
						$empty_responses = array_merge($empty_responses, array($name . '_' . $question->getAttribute('id') => 0));
					}
				}
			}
		} else {
			$message = get_string('surveyerror', 'artefact.survey', $filename);
			$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
		}
		
		return $empty_responses;
	}

	// Returns array of all sections in given survey with highest possible values for each response.
	// $filename	the name of XML survey file
	public function get_responses_max_values_from_survey($filename) {
		$max_values = array();
		$xmlDoc = new DOMDocument('1.0', 'UTF-8');
		$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $filename);
		# Return http response in string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$loaded = $xmlDoc->loadXML(curl_exec($ch));
		if ($loaded) {
			// Get all the possible respones
			$responses = $xmlDoc->getElementsByTagName('response');
			$possible_responses = array();
			foreach ($responses as $response) {
				$children = $response->cloneNode(true);
				$options = $children->getElementsByTagName('option');
				$response_array = array();
				foreach ($options as $option) {
					$optionvalue = $option->getAttribute('value');
					if (isset($optionvalue) && !empty($optionvalue)) {
						$responseid = $response->getAttribute('id');
						$response_array = array_merge($response_array, array($responseid . '_' . $optionvalue => $optionvalue));
					} else {
						$optionid = $option->getAttribute('id');
						$optionsection = $option->getAttribute('section');
						if (isset($optionsection) && !empty($optionsection) && isset($optionid) && !empty($optionid)) {
							$response_array = array_merge($response_array, array($optionsection . '_' . $optionid => 1));
						}
					}
				}
				$possible_responses = array_merge($possible_responses, array($response->getAttribute('id') => $response_array));
			}
			
			$sections = $xmlDoc->getElementsByTagName('section');
			foreach ($sections as $section) {
				$children = $section->cloneNode(true);
				$questions = $children->getElementsByTagName('question');
				foreach ($questions as $question) {
					// Get question type
					$type = $question->getAttribute('type');
					if (empty($type) || !isset($type)) {
						$texttype = 'checkbox';
					}
					
					// Get question name
					$name = $question->getAttribute('section');
					if (!isset($name) || empty($name)) {
						$name = $section->getAttribute('name');
					}
					
					switch ($type) {
						case 'checks':
							$response = $question->getAttribute('response');
							$max_values = array_merge($max_values, $possible_responses[$response]);
							break;
						case 'checkbox':
						case 'text':		// found by "trial and error" method...
						case 'textarea':	// found by "trial and error" method...
							$max_values = array_merge($max_values, array($name . '_' . $question->getAttribute('id') => 1));
							break;
						case 'trafficlights':		// red=0, yellow=1, green=2
							$max_values = array_merge($max_values, array($name . '_' . $question->getAttribute('id') => 2));
							break;
						case 'scale':
							$response = $question->getAttribute('response');
							$maxvalue = $question->getAttribute('scaleSteps')-1;
							$max_values = array_merge($max_values, array($name . '_' . $question->getAttribute('id') => $maxvalue));
							break;
						default:
							$response = $question->getAttribute('response');
							$maxvalue = max(array_values($possible_responses[$response]));
							$max_values = array_merge($max_values, array($name . '_' . $question->getAttribute('id') => $maxvalue));
							break;
					}
					
				}
			}
		} else {
			$message = get_string('surveyerror', 'artefact.survey', $filename);
			$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
		}
		
		return $max_values;
	}

	// Returns array of empty responses, merged with user responses
	// (where user responded to a question).
	// $filename	the name of XML survey file
	// $responses	unserialized(!) array of user responses from database
	public function get_user_responses_from_survey($filename, $responses) {
		$user_responses = ArtefactTypeSurvey::get_empty_responses_from_survey($filename);

		if (!$responses) {
			throw new NotFoundException(get_string('responsesnotfound', 'artefact.survey', self::get_survey_title_from_xml($filename)));
		}
		foreach ($responses as $key => $value) {
			// if the response type is 'checkboxes'
			if (is_array($value)) {
				foreach ($value as $subkey => $subvalue) {
					if (array_key_exists($subkey, $user_responses)) {
						if ($subvalue == 'on') $subvalue = 1;
						$user_responses[$subkey] = $subvalue;
					}
				}
			}
			// for all other response types...
			else {
				if ($value != false || $value != null) {
					$user_responses[$key] = $value;
				}
			}
		}
		
		return $user_responses;
	}
	
	// Build and return HTML summary of user responses.
	// $filename		the name of XML survey file
	// $responses		unserialized(!) array of user responses from database
	// $showResults		for blocktype, to override survey setting for showing results
	// $showSummary		for blocktype, to override survey setting for showing result descriptions
	public function build_user_responses_summary_html($filename, $responses, $showResults=true, $showSummary=true) {
		$responses = ArtefactTypeSurvey::get_user_responses_from_survey($filename, $responses);
		foreach ($responses as $key => $value) {
			if (substr($key, 0, 7) != 'comment') {
				$data = explode('_', $key);
				if (!empty($data[0]) && !empty($data[1])) {
					$sections[$data[0]][$data[1]] = $value;
				}
			}
		}

		$max_values = ArtefactTypeSurvey::get_responses_max_values_from_survey($filename);
		foreach ($max_values as $key => $value) {
			if (substr($key, 0, 7) != 'comment') {
				$data = explode('_', $key);
				if (!empty($data[0]) && !empty($data[1])) {
					$maxvalues[$data[0]][$data[1]] = $value;
				}
			}
		}
		
		$xmlDoc = new DOMDocument('1.0', 'UTF-8');
		$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $filename);
		# Return http response in string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$loaded = $xmlDoc->loadXML(curl_exec($ch));
		if ($loaded) {
			$legend = $xmlDoc->getElementsByTagName('legend')->item(0);
			// If <legend> element exists in XML file, build legend from it
			// else build legend from <section> elements...
			if (isset($legend) && !empty($legend)) {
				$legend_exists = true;
			} else {
				$legend_exists = false;
				$legend = $xmlDoc->getElementsByTagName('questions')->item(0);
			}
				$children = $legend->cloneNode(true);
				if ($legend_exists) {
					$items = $children->getElementsByTagName('item');
				} else {
					$items = $children->getElementsByTagName('section');
				}
				$items_array = array();
				foreach ($items as $item) {
					// Legend label
					$itemlabel = $item->getAttribute(get_config('lang'));
					if (empty($itemlabel)) {
						$itemlabel = $item->getAttribute(self::get_default_lang($filename));
					}
					$itemname = $item->getAttribute('name');
					if (isset($itemname) && !empty($itemname)) {
						$items_array = array_merge($items_array, array($itemname => $itemlabel));
					}
				}
		} else {
			$message = get_string('surveyerror', 'artefact.survey', $filename);
			$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
		}
		
		// Prepare results summary table...
		$results_summary = array();
		$results_values = array(); // only the values!
		foreach ($sections as $key => $section) {
			if (array_sum($section) > 0)  {
				$results_summary = array_merge($results_summary, array(
					$key => array_sum($section) . '|' . (array_sum($section)/array_sum($maxvalues[$key]))*100
				));
				$results_values = array_merge($results_values, array($key => array_sum($section)));
			} else {
				$results_summary = array_merge($results_summary, array(
					$key => '0|0'
				));
				$results_values = array_merge($results_values, array($key => '0'));
			}
		}
		
		//---------------------------
		// Output results summary...
		//---------------------------
		$results_exist = $xmlDoc->getElementsByTagName('results')->item(0);
		if ($results_exist == null) {
		}
		else {
			$results_type = $xmlDoc->getElementsByTagName('results')->item(0)->getAttribute('type');
			if (empty($results_type) || !isset($results_type)) {
				$results_type = 'value';
			}

			$summary = return_bool($xmlDoc->getElementsByTagName('results')->item(0)->getAttribute('showSummary'));
			if (isset($summary) && $summary) {
				$results_html = '<table border="0">';
				foreach ($results_summary as $key => $value) {
					if (array_key_exists($key, $items_array)) {
						$results_html .= '<tr>';
						$results_html .= '<td><strong>' . $key . '. ' . $items_array[$key] . ':</strong></td>';
						list($number, $percent) = explode('|', $value);
						switch ($results_type) {
							case 'percent':
								$results_html .= '<td align="right">' . round($percent, 2) . '%</td>';
								break;
							case 'both':
								$results_html .= '<td align="right">' . $number . '</td>';
								$results_html .= '<td align="right">' . round($percent, 2) . '%</td>';
								break;
							default:
								$results_html .= '<td align="right">' . $number . '</td>';
								break;
						}
						$results_html .= '</tr>';
					}
				}
				$results_html .= '</table>';
			}
		
			//--------------------------------
			// Output results descriptions...
			//--------------------------------
			$mode = $xmlDoc->getElementsByTagName('results')->item(0)->getAttribute('mode');
			// Show MAX results descriptions
			if (isset($mode) && $mode == 'max') {
				$result_descriptions = array();
				$results = $xmlDoc->getElementsByTagName('result');
				foreach ($results as $result) {
					$section = $result->getAttribute('section');
					$description = $result->getAttribute(get_config('lang'));
					if (empty($description)) {
						$description = $result->getAttribute(self::get_default_lang($filename));
					}
					$result_descriptions = array_merge($result_descriptions, array(
						$section => $description
					));
				}
			
				$summary_html = '';
				foreach ($results_values as $key => $value) {
					if (array_key_exists($key, $items_array)) {
						if ($value == max($results_values)) {
							$summary_html .= '<h4>' . $items_array[$key] . '</h4>';
							$summary_html .= '<div>';
							// Replace {image:pict.jpg} in responses with proper <img src="pict.jpg"> tag...
							$summary_html .= preg_replace('#{image:([a-zA-Z0-9\.\_\-\~]+)}#', '<img src="' . get_config('wwwroot') . 'artefact/survey/surveys/' . substr($filename, 0, -4) . '/$1" style="border:1px solid black">', $result_descriptions[$key]);
							$summary_html .= '</div>';
						}
					}
				}
			}
		
			// Show MIN results descriptions
			if (isset($mode) && $mode == 'min') {
				$result_descriptions = array();
				$results = $xmlDoc->getElementsByTagName('result');
				foreach ($results as $result) {
					$section = $result->getAttribute('section');
					$description = $result->getAttribute(get_config('lang'));
					if (empty($description)) {
						$description = $result->getAttribute(self::get_default_lang($filename));
					}
					$result_descriptions = array_merge($result_descriptions, array(
						$section => $description
					));
				}
			
				$summary_html = '';
				foreach ($results_values as $key => $value) {
					if (array_key_exists($key, $items_array)) {
						if ($value == min($results_values)) {
							$summary_html .= '<h4>' . $items_array[$key] . '</h4>';
							$summary_html .= '<div>';
							// Replace {image:pict.jpg} in responses with proper <img src="pict.jpg"> tag...
							$summary_html .= preg_replace('#{image:([a-zA-Z0-9\.\_\-\~]+)}#', '<img src="' . get_config('wwwroot') . 'artefact/survey/surveys/' . substr($filename, 0, -4) . '/$1" style="border:1px solid black">', $result_descriptions[$key]);
							$summary_html .= '</div>';
						}
					}
				}
			}
		
			// Show RANGE results descriptions
			if (isset($mode) && $mode == 'range') {
				$result_descriptions = array();
				$results = $xmlDoc->getElementsByTagName('result');
				$possible_results = array();
				foreach ($results as $result) {
					$children = $result->cloneNode(true);
					$ranges = $children->getElementsByTagName('range');
					$range_array = array();
					foreach ($ranges as $range) {
						// Lower border of range
						$low = $range->getAttribute('low');
						if (!isset($low) || empty($low)) {
							$low = 0;
						} else {
							$low = (int) $low;
						}
						// Higher border of range
						$high = $range->getAttribute('high');
						if (!isset($high) || empty($high)) {
							$high = 100; // This is temporary. We need to find a way, to get maximum value!
						} else {
							$high = (int) $high;
						}
						// Which border of range to include (low|high|both|none)
						$include = $range->getAttribute('include');
						if (!isset($include) || empty($include)) {
							$include = 'both'; // Maybe it would be better if set to 'none'?
						}
						// Range description
						$description = $range->getAttribute(get_config('lang'));
						if (empty($description)) {
							$description = $range->getAttribute(self::get_default_lang($filename));
						}
					
						$range_array = array_merge($range_array, array(
							$low . "|" . $high . "|" . $include . "|" . $description
						));
					}
					$possible_results = array_merge($possible_results, array($result->getAttribute('section') => $range_array));
				}
			
				$summary_html = '';
				foreach ($results_summary as $key => $value) {
					list($number, $percent) = explode('|', $value);
					if ($results_type == 'percent') {
						$condition = $percent;
					} else {
						$condition = $number;
					}
				
					$results = $possible_results[$key];
					foreach ($results as $result) {
						list($low, $high, $include, $description) = explode('|', $result);
						switch ($include) {
							case 'none':
								if ($low < $condition && $condition < $high) {
									$summary_html .= '<h4>' . $items_array[$key] . '</h4>';
									$summary_html .= '<div>';
									// Replace {image:pict.jpg} in responses with proper <img src="pict.jpg"> tag...
									$summary_html .= preg_replace('#{image:([a-zA-Z0-9\.\_\-\~]+)}#', '<img src="' . get_config('wwwroot') . 'artefact/survey/surveys/' . substr($filename, 0, -4) . '/$1" style="border:1px solid black">', $description);
									$summary_html .= '</div>';
								}
								break;
							case 'low':
								if ($low <= $condition && $condition < $high) {
									$summary_html .= '<h4>' . $items_array[$key] . '</h4>';
									$summary_html .= '<div>';
									// Replace {image:pict.jpg} in responses with proper <img src="pict.jpg"> tag...
									$summary_html .= preg_replace('#{image:([a-zA-Z0-9\.\_\-\~]+)}#', '<img src="' . get_config('wwwroot') . 'artefact/survey/surveys/' . substr($filename, 0, -4) . '/$1" style="border:1px solid black">', $description);
									$summary_html .= '</div>';
								}
								break;
							case 'high':
								if ($low < $condition && $condition <= $high) {
									$summary_html .= '<h4>' . $items_array[$key] . '</h4>';
									$summary_html .= '<div>';
									// Replace {image:pict.jpg} in responses with proper <img src="pict.jpg"> tag...
									$summary_html .= preg_replace('#{image:([a-zA-Z0-9\.\_\-\~]+)}#', '<img src="' . get_config('wwwroot') . 'artefact/survey/surveys/' . substr($filename, 0, -4) . '/$1" style="border:1px solid black">', $description);
									$summary_html .= '</div>';
								}
								break;
							case 'both':
								if ($low <= $condition && $condition <= $high) {
									$summary_html .= '<h4>' . $items_array[$key] . '</h4>';
									$summary_html .= '<div>';
									// Replace {image:pict.jpg} in responses with proper <img src="pict.jpg"> tag...
									$summary_html .= preg_replace('#{image:([a-zA-Z0-9\.\_\-\~]+)}#', '<img src="' . get_config('wwwroot') . 'artefact/survey/surveys/' . substr($filename, 0, -4) . '/$1" style="border:1px solid black">', $description);
									$summary_html .= '</div>';
								}
								break;
						}
					}
				}
			}
			
			// Show MATCH results descriptions
			if (isset($mode) && $mode == 'match') {
				$result_descriptions = array();
				$results = $xmlDoc->getElementsByTagName('result');
				$possible_results = array();
				foreach ($results as $result) {
					$children = $result->cloneNode(true);
					$matches = $children->getElementsByTagName('match');
					$match_array = array();
					foreach ($matches as $match) {
						// Match exact value
						$value = $match->getAttribute('value');
						if (!isset($value) || empty($value)) {
							$value = 0;
						} else {
							$value = (int) $value;
						}
						// Value description
						$description = $match->getAttribute(get_config('lang'));
						if (empty($description)) {
							$description = $match->getAttribute(self::get_default_lang($filename));
						}
					
						$match_array = array_merge($match_array, array(
							$value . "|" . $description
						));
					}
					$possible_results = array_merge($possible_results, array($result->getAttribute('section') => $match_array));
				}
			
				$summary_html = '';
				foreach ($results_summary as $key => $value) {
					list($number, $percent) = explode('|', $value);
					if ($results_type == 'percent') {
						$condition = $percent;
					} else {
						$condition = $number;
					}
				
					$results = $possible_results[$key];
					foreach ($results as $result) {
						list($match, $description) = explode('|', $result);
						if ($match == $condition) {
							$summary_html .= '<h4>' . $items_array[$key] . '</h4>';
							$summary_html .= '<div>';
							// Replace {image:pict.jpg} in responses with proper <img src="pict.jpg"> tag...
							$summary_html .= preg_replace('#{image:([a-zA-Z0-9\.\_\-\~]+)}#', '<img src="' . get_config('wwwroot') . 'artefact/survey/surveys/' . substr($filename, 0, -4) . '/$1" style="border:1px solid black">', $description);
							$summary_html .= '</div>';
						}
					}
				}
			}

			// Include CUSTOM function for results descriptions
			if (isset($mode) && $mode == 'custom') {
				$include = get_config('docroot') . 'artefact/survey/surveys/' . substr($filename, 0, -4) . '/custom_results.php';
				require_once($include);
			}
			
			$html = '';
			// For blocktype, to override survey setting for showing results
			if ($showResults && !empty($results_html)) {
				$html .= $results_html;
			}
			// For blocktype, to override survey setting for showing result descriptions (summary)
			if ($showSummary && !empty($summary_html)) {
				$html .= $summary_html;
			}
			return $html;
		}
	}

	
	// Build and return HTML output of user responses.
	// $filename		the name of XML survey file
	// $responses		unserialized(!) array of user responses from database
	public function build_user_responses_output_html($filename, $values) {
		$xmlDoc = new DOMDocument('1.0', 'UTF-8');
		// 'title' field in 'artefact' table contains the survey xml filename...
		try {
			$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $filename);
			# Return http response in string
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$xmlDoc->loadXML(curl_exec($ch));
		}
		catch (Exception $e) {
			$message = get_string('surveyerror', 'artefact.survey', $this->get('title'));
			$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
		}
		
		$question_order = $xmlDoc->getElementsByTagName('questions')->item(0)->getAttribute('value');
		$question_order_exists = true;
		if (empty($question_order) || !isset($question_order)) {
			$question_order = null;
			$question_order_exists = false;
		}
		
		$section_type = $xmlDoc->getElementsByTagName('questions')->item(0)->getAttribute('sections');
		if (empty($section_type) || !isset($section_type)) {
			$section_type = 'separated';
		}

		/* ---------------------------
		 * Responses...
		 * --------------------------- */
		$responses = $xmlDoc->getElementsByTagName('response');
		$possible_responses = array();
		foreach ($responses as $response) {
			$children = $response->cloneNode(true);
			$options = $children->getElementsByTagName('option');
			$response_array = array();
			foreach ($options as $option) {
				// Response label
				$optionlabel = $option->getAttribute(get_config('lang'));
				if (empty($optionlabel)) {
					$optionlabel = $option->getAttribute(self::get_default_lang($filename));
				}
				// Replace {image:pict.jpg} in responses with proper <img src="pict.jpg"> tag...
				$optionlabel = preg_replace('#{image:([a-zA-Z0-9\.\_\-\~]+)}#', '<img src="' . get_config('wwwroot') . 'artefact/survey/surveys/' . substr($filename, 0, -4) . '/$1" style="border:1px solid black">', $optionlabel);

				$optionvalue = $option->getAttribute('value');
				if (isset($optionvalue) && !empty($optionvalue)) {
					$response_array = array_merge($response_array, array($optionvalue => $optionlabel));
				} else {
					$optionsection = $option->getAttribute('section');
					$optionid = $option->getAttribute('id');
					if (isset($optionsection) && !empty($optionsection) && isset($optionid) && !empty($optionid)) {
						$response_array = array_merge($response_array, array($optionsection . '_' . $optionid => $optionlabel));
					}
				}
			}
			$possible_responses = array_merge($possible_responses, array($response->getAttribute('id') => $response_array));
		}
		
		/* ---------------------------
		 * Questions...
		 * --------------------------- */
		$output = "<script>";
		$output .= "\nfunction toggleDesc(id) { (document.getElementById(id).style.display == 'none' ? document.getElementById(id).style.display = '' : document.getElementById(id).style.display = 'none'); }";
		$output .= "\nfunction toggleImg(id) { (document.getElementById(id).src == '" . get_config('wwwroot') . "theme/raw/static/images/icon_fieldset_left.gif' ? document.getElementById(id).src = '" . get_config('wwwroot') . "theme/raw/static/images/icon_fieldset_down.gif' : document.getElementById(id).src = '" . get_config('wwwroot') . "theme/raw/static/images/icon_fieldset_left.gif'); }";
		$output.= "\n</script>";
		$output .= "\n<table><tbody>";
		$sections = $xmlDoc->getElementsByTagName('section');
		foreach ($sections as $section) {
			if ($section_type != 'joined') {
				$sectionlabel = $section->getAttribute(get_config('lang'));
				if (empty($sectionlabel)) {
					$sectionlabel = $section->getAttribute(self::get_default_lang($filename));
				}
				$output .= "\n\t<tr><td><h4 style=\"padding: 1em 0 0 0\">";
				$output .= $sectionlabel;
				$output .= "</h4></td></tr>";
			}
			$question_elements_array = array();
			$children = $section->cloneNode(true);
			$questions = $children->getElementsByTagName('question');
			foreach ($questions as $question) {
				// Get question type
				$type = $question->getAttribute('type');
				if (empty($type) || !isset($type)) {
					$texttype = 'checkbox';
				}

				// Get question text type
				$texttype = $question->getAttribute('textType');
				if (empty($texttype) || !isset($texttype)) {
					$texttype = 'label';
				}
				
				// Get label in Mahara site defined language or -
				// if the translation doesn't exists - in English
				$labelhtml = $question->getAttribute(get_config('lang'));
				if (empty($labelhtml)) {
					$labelhtml = $question->getAttribute(self::get_default_lang($filename));
				}

				$name = $question->getAttribute('section');
				if (!isset($name) || empty($name)) {
					$name = $section->getAttribute('name');
				}
				$defaultvalue = $values[$name . '_' . $question->getAttribute('id')];

				$response = $question->getAttribute('response');

				$output .= "\n\t<tr>";
				$output .= "\n\t\t<td><div>";
				if ($texttype == 'title') {
					$output .= $labelhtml;
				}
				else {
					$output .= "<strong>" . $labelhtml . "</strong>";
				}
				$output .= "\n\t\t";
				switch ($type) {
					case 'checkbox':
						if ($defaultvalue) $output .= '<img src="' . get_config('wwwroot') . 'artefact/survey/theme/raw/static/images/checked.gif" border="0">';
						break;
					case 'checks':
						$output .= '<ul type="square">';
						foreach ($possible_responses[$response] as $key => $value) {
							$output .= '<li>';
							$output .= $value;
							if (array_key_exists($key, $defaultvalue)) $output .= '<img src="' . get_config('wwwroot') . 'artefact/survey/theme/raw/static/images/checked.gif" border="0">';
							//else $output .= '<img src="' . get_config('wwwroot') . 'artefact/survey/theme/raw/static/images/unchecked.gif" border="0">';
							$output .= '</li>';
						}
						$output .= '</ul>';
						break;
					case 'trafficlights':
						$output .= '<br>';
						for ($i=0; $i<=2; $i++) {
							$current = $i;
							if ($current == $defaultvalue) $output .= '<img src="' . get_config('wwwroot') . 'artefact/survey/theme/raw/static/images/checked.gif" border="0">&nbsp;';
							else $output .= '<img src="' . get_config('wwwroot') . 'artefact/survey/theme/raw/static/images/unchecked.gif" border="0">&nbsp;';
						}
						break;
					case 'scale':
						$output .= '<br>';
						$steps = $question->getAttribute('scaleSteps');
						$reverse = return_bool($question->getAttribute('reverseResponse'));
						$response = $question->getAttribute('response');
					    $output .= $possible_responses[$response]['left'] . '&nbsp;&nbsp;&nbsp;';
						for ($i=0; $i<=$steps-1; $i++) {
							$current = $i;
							if ($reverse) $current = ($steps-1)-$i; 
							if ($current == $defaultvalue) $output .= '<img src="' . get_config('wwwroot') . 'artefact/survey/theme/raw/static/images/checked.gif" border="0">&nbsp;';
							else $output .= '<img src="' . get_config('wwwroot') . 'artefact/survey/theme/raw/static/images/unchecked.gif" border="0">&nbsp;';
						}
					    $output .= '&nbsp;&nbsp;&nbsp;' . $possible_responses[$response]['right'];
						break;
					case 'text':
					case 'textarea':
					default:
						$output .= $defaultvalue;
						break;
				}
				if (isset($values['comment_' . $question->getAttribute('id')])) {
					$output .= '<a href="#" onclick="toggleDesc(\'comment_' . $question->getAttribute('id') . '\');toggleImg(\'arrow_' . $question->getAttribute('id') . '\');"><img id="arrow_' . $question->getAttribute('id') . '" src="' . get_config('wwwroot') . 'theme/raw/static/images/icon_fieldset_left.gif" border="0">&nbsp;'. get_string('comment', 'artefact.survey') .'</a>'; 
					$output .= '<div id="comment_' . $question->getAttribute('id') . '" class="description" style="display:none">' . $values['comment_' . $question->getAttribute('id')] . '</div>';
				}
				$output .= "</div></td>";
				$output .= "\n\t</tr>";
			}
		}
		$output .= "\n</tbody></table>";

		return $output;
	}
	
	
	// Build and return HTML summary of user responses.
	// $filename		the name of XML survey file
	// $responses		unserialized(!) array of user responses from database
	// $showResults		for blocktype, to override survey setting for showing results
	// $showSummary		for blocktype, to override survey setting for showing result descriptions
	public function get_chart_data_from_responses($filename, $responses, $showResults=true, $showSummary=true) {
		$responses = ArtefactTypeSurvey::get_user_responses_from_survey($filename, $responses);
		foreach ($responses as $key => $value) {
			$data = explode('_', $key);
			if (!empty($data[0]) && !empty($data[1])) {
				$sections[$data[0]][$data[1]] = $value;
			}
		}
		
		$max_values = ArtefactTypeSurvey::get_responses_max_values_from_survey($filename);
		foreach ($max_values as $key => $value) {
			if (substr($key, 0, 7) != 'comment') {
				$data = explode('_', $key);
				if (!empty($data[0]) && !empty($data[1])) {
					$maxvalues[$data[0]][$data[1]] = $value;
				}
			}
		}
		
		$xmlDoc = new DOMDocument('1.0', 'UTF-8');
		$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $filename);
		# Return http response in string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$loaded = $xmlDoc->loadXML(curl_exec($ch));
		if ($loaded) {
			$legend = $xmlDoc->getElementsByTagName('legend')->item(0);
			// If <legend> element exists in XML file, build legend from it
			// else build legend from <section> elements...
			if (isset($legend) && !empty($legend)) {
				$legend_exists = true;
			} else {
				$legend_exists = false;
				$legend = $xmlDoc->getElementsByTagName('questions')->item(0);
			}
				$children = $legend->cloneNode(true);
				if ($legend_exists) {
					$items = $children->getElementsByTagName('item');
				} else {
					$items = $children->getElementsByTagName('section');
				}
				$items_array = array();
				foreach ($items as $item) {
					// Legend label
					$itemlabel = $item->getAttribute(get_config('lang'));
					if (empty($itemlabel)) {
						$itemlabel = $item->getAttribute(self::get_default_lang($filename));
					}
					$itemname = $item->getAttribute('name');
					// Color number from palette, to override default palette color
					$itemcolor = $item->getAttribute('color');
					if (empty($itemcolor)) {
						$itemcolor = '1';
					}
					if (isset($itemname) && !empty($itemname)) {
						$items_array = array_merge($items_array, array($itemname => array('label' => $itemlabel, 'color' => $itemcolor)));
					}
				}
		} else {
			$message = get_string('surveyerror', 'artefact.survey', $filename);
			$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
		}
		
		// Prepare results summary table...
		$results_summary = array();
		foreach ($sections as $key => $section) {
			if (array_key_exists($key, $items_array)) {
				if (substr($key, 0, 7) != 'comment') {
					if (array_sum($section) > 0) {
						array_push($results_summary, array('key' => $key, 'label' => $items_array[$key]['label'], 'color' => $items_array[$key]['color'], 'value' => array_sum($section), 'percent' => (array_sum($section)/array_sum($maxvalues[$key]))*100));
					} else {
						array_push($results_summary, array('key' => $key, 'label' => $items_array[$key]['label'], 'color' => $items_array[$key]['color'], 'value' => 0, 'percent' => 0));
					}
				}
			}
		}
		
		// Include 'custom_chart_data.php' file, if it exists
		// to allow custom chart data transformations...
		$include = get_config('docroot') . 'artefact/survey/surveys/' . substr($filename, 0, -4) . '/custom_chart_data.php';
		if (file_exists($include)) require_once($include);
		
		return $results_summary;
	}
		

	// Returns array of all the possible questions, their types and user responses in given survey
	// $filename	the name of XML survey file
	public function get_empty_analysis_from_survey($filename) {
		$empty_analysis = array();
		$xmlDoc = new DOMDocument('1.0', 'UTF-8');
		$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $filename);
		# Return http response in string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$loaded = $xmlDoc->loadXML(curl_exec($ch));
		if ($loaded) {
			// Get all the possible respones
			$responses = $xmlDoc->getElementsByTagName('response');
			$possible_responses = array();
			foreach ($responses as $response) {
				$children = $response->cloneNode(true);
				$options = $children->getElementsByTagName('option');
				$response_array = array();
				foreach ($options as $option) {
					// Response label
					$optionlabel = $option->getAttribute(get_config('lang'));
					if (empty($optionlabel)) {
						$optionlabel = $option->getAttribute(self::get_default_lang($filename));
					}
					// Replace {image:pict.jpg} in responses with proper <img src="pict.jpg"> tag...
					$optionlabel = preg_replace('#{image:([a-zA-Z0-9\.\_\-\~]+)}#', '<img src="' . get_config('wwwroot') . 'artefact/survey/surveys/' . substr($filename, 0, -4) . '/$1" style="border:1px solid black">', $optionlabel);

					$optionvalue = $option->getAttribute('value');
					if (isset($optionvalue) && !empty($optionvalue)) {
						//$response_array = array_merge($response_array, array($optionvalue => 0));
						$response_array = array_merge($response_array, array($optionvalue => array('text' => $optionlabel, 'count' => 0)));
					} else {
						$optionsection = $option->getAttribute('section');
						$optionid = $option->getAttribute('id');
						if (isset($optionsection) && !empty($optionsection) && isset($optionid) && !empty($optionid)) {
							//$response_array = array_merge($response_array, array($optionsection . '_' . $optionid => 0));
							$response_array = array_merge($response_array, array($optionsection . '_' . $optionid => array('text' => $optionlabel, 'count' => 0)));
						}
					}
				}
				$possible_responses = array_merge($possible_responses, array($response->getAttribute('id') => $response_array));
			}
			
			$sections = $xmlDoc->getElementsByTagName('section');
			foreach ($sections as $section) {
				$children = $section->cloneNode(true);
				$questions = $children->getElementsByTagName('question');
				foreach ($questions as $question) {
					// Get question id
					$id = $question->getAttribute('id');				
					// Get question type
					$type = $question->getAttribute('type');
					if (empty($type) || !isset($type)) {
						$texttype = 'checkbox';
					}
					// Get question name
					$name = $question->getAttribute('section');
					if (!isset($name) || empty($name)) {
						$name = $section->getAttribute('name');
					}
					// Get question response
					$response = $question->getAttribute('response');
					if (!isset($response) || empty($response)) {
						$response = null;
					}
					// Get question label in Mahara site defined language or -
					// if the translation doesn't exists - in English
					$labelhtml = $question->getAttribute(get_config('lang'));
					if (empty($labelhtml)) {
						$labelhtml = $question->getAttribute(self::get_default_lang($filename));
					}
					
					switch ($type) {
						case 'checkbox':
							$empty_analysis = array_merge($empty_analysis, array(array(
								'id'   => $id,
								'name' => $name,
								'type' => $type,
								'text' => $labelhtml,
								'responses' => array(
									//$name . '_' . $id => array(0 => 0, 1 => 0),
									'0' => array(
										'text' => 'False',
										'count' => 0,
									),
									'1' => array(
										'text' => 'True',
										'count' => 0,
									),
								),
							)));
							break;
						case 'checks':
						case 'trafficlights':
						case 'radio':
						case 'select':
						case 'scale':
							if ($response != null) {
								$empty_analysis = array_merge($empty_analysis, array(array(
									'id'   => $id,
									'name' => $name,
									'type' => $type,
									'text' => $labelhtml,
									'responses' => $possible_responses[$response],
									//$name . '_' . $id => array(0 => 0, 1 => 0),
								)));
							}
							break;
						// text, textarea, ???
						default:
					}
					/*
					if ($type == 'checks') {
						$response = $question->getAttribute('response');
						$empty_analysis = array_merge($empty_analysis, $possible_responses[$response]);
					} else {
						$empty_analysis = array_merge($empty_analysis, array($name . '_' . $question->getAttribute('id') => 0));
					}
					*/
				}
			}
		} else {
			$message = get_string('surveyerror', 'artefact.survey', $filename);
			$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
		}
		
		return $empty_analysis;
	}


	public function get_default_lang($filename) {
		$defaultlang = 'en.utf8';
		$xmlDoc = new DOMDocument('1.0', 'UTF-8');
		$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $filename);
		# Return http response in string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$loaded = $xmlDoc->loadXML(curl_exec($ch));
		if ($loaded) {
			// Get survey default language
			$defaultlang = $xmlDoc->getElementsByTagName('survey')->item(0)->getAttribute('defaultLanguage');
			// If the survey default language isn't set, than set it to English
			if (empty($defaultlang)) {
				$defaultlang = 'en.utf8';
			}
		}
		return $defaultlang;
	}

	public function get_chart_config($filename) {
		$xmlDoc = new DOMDocument('1.0', 'UTF-8');
		$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $filename);
		# Return http response in string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$loaded = $xmlDoc->loadXML(curl_exec($ch));
		if ($loaded) {
			$CONFIG = array();
			// Get survey title
			$title = $xmlDoc->getElementsByTagName('title')->item(0)->getAttribute(get_config('lang'));
			// If the survey title doesn't exist in selected language, than set the survey title in English
			if (empty($title)) {
				$title = $xmlDoc->getElementsByTagName('title')->item(0)->getAttribute(self::get_default_lang($filename));
			}
			$CONFIG['title'] = $title;		
			// Get results type ('percent' or 'value')
			$type = $xmlDoc->getElementsByTagName('results')->item(0)->getAttribute('type');
			if (!isset($type) || empty($type)) {
				$type = 'value';
			}
			$CONFIG['type'] = $type;
			// Override default palette colors?
			/*
			$override = return_bool($xmlDoc->getElementsByTagName('chart')->item(0)->getAttribute('coloroverride'));
			$CONFIG['coloroverride'] = $override;
			*/
			// Get chart type
			$charttype = $xmlDoc->getElementsByTagName('chart')->item(0)->getAttribute('type');
			if (!isset($charttype) || empty($charttype)) {
				$charttype = null;
			}
			$CONFIG['charttype'] = strtolower($charttype);
			// Get chart space ('2d' or '3d')
			$chartspace = $xmlDoc->getElementsByTagName('chart')->item(0)->getAttribute('space');
			if (!isset($chartspace) || empty($chartspace)) {
				$chartspace = '2d';
			}
			$CONFIG['chartspace'] = strtolower($chartspace);
			// Number of segments for polar/radar charts
			$segments = $xmlDoc->getElementsByTagName('chart')->item(0)->getAttribute('segments');
			if (!isset($segments) || empty($segments)) {
				$segments = null;
			}
			$CONFIG['segments'] = $segments;
			// Formatting for graph labels
			$labeltype = $xmlDoc->getElementsByTagName('chart')->item(0)->getAttribute('labeltype');
			if (!isset($labeltype) || empty($labeltype)) {
				$labeltype = 'both';
			}
			$CONFIG['labeltype'] = $labeltype;
		} else {
			$message = get_string('surveyerror', 'artefact.survey', $filename);
			$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
		}
		return $CONFIG;
	}


    function get_palette_colors($palettename) {
	    $filename = get_config('docroot') . 'artefact/survey/lib/pchart2/palettes/' . $palettename . '.color';
        if (!file_exists($filename)) { return false; }
	    $palette = "";
	    
        $filehandle = @fopen($filename, "r");
	    if (!$filehandle) { return false; }
	    $ID = 1;
	    while (!feof($filehandle))
	    {
            $buffer = fgets($filehandle, 4096);
            if (preg_match("/,/",$buffer))
            {
                list($R,$G,$B,$Alpha) = preg_split("/,/",$buffer);
                $palette[$ID] = array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha);
            }
		    $ID++;
        }
        fclose($filehandle);
	    return $palette;
    }


	// Returns array of user responses, suitable for showing with pChart2.
	// $filename		the name of XML survey file
	// $responses		unserialized(!) array of user responses from database
	public function get_chart_labels_from_responses($filename, $responses) {
		$responses = ArtefactTypeSurvey::get_user_responses_from_survey($filename, $responses);
		
		foreach ($responses as $key => $value) {
			$data = explode('_', $key);
			if (!empty($data[0]) && !empty($data[1])) {
				$sections[$data[0]][$data[1]] = $value;
			}
		}
		
		// Prepare results summary table...
		$results_summary = array();
		$results_values = array(); // only the values!
		foreach ($sections as $key => $section) {
			$results_summary = array_merge($results_summary, array(
				$key => array_sum($section) . '|' . (array_sum($section)/count($section))*100
			));
			array_push($results_values, array_sum($section));
		}
		
		$return_array = array();
		foreach ($results_summary as $key => $value) {
			array_push($return_array, $key);
		}
		return $return_array;
	}

	// Returns array of elements, used in blocktype plugin instance config forms, for users to set chart options...
	public function get_chart_options_elements($configdata, $palette=true, $legend=true, $fonttype=true, $fontsize=true, $height=true, $width=true) {
		$elements = array();
		if ($palette) {
			$elements['palette'] = array(
				'type' => 'select',
				'title' => get_string('palette', 'artefact.survey'),
				'description' => get_string('palettedescription', 'artefact.survey'),
				'defaultvalue' => (isset($configdata['palette'])) ? $configdata['palette'] : 'default',
				'options' => getoptions_palettes(),		
			);
		}
		if ($legend) {
			$elements['legend'] = array(
				'type' => 'radio',
				'title' => get_string('legend', 'artefact.survey'),
				'defaultvalue' => (isset($configdata['legend'])) ? $configdata['legend'] : 'key',
				'options' => array(
					'key' => get_string('legendkey', 'artefact.survey'),
					'label' => get_string('legendlabel', 'artefact.survey'),
				),		
				'separator' => '&nbsp;&nbsp;&nbsp;',
			);
		}
		if ($fonttype) {
			$elements['fonttype'] = array(
				'type' => 'radio',
				'title' => get_string('fonttype', 'artefact.survey'),
				'description' => get_string('fonttypedescription', 'artefact.survey'),
				'defaultvalue' => (isset($configdata['fonttype'])) ? $configdata['fonttype'] : 'chinese',
				'options' => array(
					'chinese' => get_string('chinesefonttype', 'artefact.survey'),
					'sans' => get_string('sansseriffonttype', 'artefact.survey'),
					'serif' => get_string('seriffonttype', 'artefact.survey'),
				),
				'separator' => '&nbsp;&nbsp;&nbsp;',
			);
		}
		if ($fontsize) {
			$elements['fontsize'] = array(
				'type' => 'select',
				'title' => get_string('fontsize', 'artefact.survey'),
				'description' => get_string('fontsizedescription', 'artefact.survey'),
				'defaultvalue' => (isset($configdata['fontsize'])) ? $configdata['fontsize'] : 9,
				'options' => array(
					6 => '6',
					8 => '8',
					9 => '9',
					10 => '10',
					11 => '11',
					12 => '12',
					14 => '14',
					16 => '16',
				),
			);
		}
		if ($height) {
			$elements['height'] = array(
				'type' => 'text',
				'title' => get_string('height', 'artefact.survey'),
				'size' => 3,
				'description' => get_string('heightdescription', 'artefact.survey'),
				'rules' => array(
					'minvalue' => 100,
					'maxvalue' => 999,
				),
				'defaultvalue' => (isset($configdata['height'])) ? $configdata['height'] : 350,
			);
		}
		if ($width) {
			$elements['width'] = array(
				'type' => 'text',
				'title' => get_string('width', 'artefact.survey'),
				'size' => 3,
				'description' => get_string('widthdescription', 'artefact.survey'),
				'rules' => array(
					'minvalue' => 100,
					'maxvalue' => 999,
				),
				'defaultvalue' => (isset($configdata['width'])) ? $configdata['width'] : 400,
			);
		}
		return $elements;
	}
	
}


class ActivityTypeArtefactSurveyFeedback extends ActivityTypePlugin {

    public function __construct($data, $cron=false) {
        parent::__construct($data, $cron);
    }

    public function get_plugintype(){
        return 'artefact';
    }

    public function get_pluginname(){
        return 'survey';
    }

    public function get_required_parameters() {
        //return array('commentid', 'viewid');
    }
}


/* ========== VARIOUS OTHER FUNCTIONS ========== */
function shuffle_assoc($list) { 
	if (!is_array($list)) return $list;

	$keys = array_keys($list);
	shuffle($keys);
	$random = array();
	foreach ($keys as $key) {
		$random[$key] = $list[$key];
	}
	return $random; 
} 


function get_survey_renderer($language) {
	// Right-to-left languages
	$rtl_text = array(
		'ar.utf8',  // Arabic
		'dv.utf8',  // Dhivehi
		'fa.utf8',  // Persian/Farsi
		'he.utf8',  // Hebrew
		'syc.utf8', // Syriac (without ISO 639-1 code)
		'ur.utf8',  // Urdu
		'yi.utf8',  // Yiddish
	);
	// Return survey Pieform renderer, according to text direction...
	if (in_array($language, $rtl_text)) {
		return 'survey_rtl';  // ???
	} else {
		return 'survey';
	}
}


function getoptions_surveys() {
	$survey_types = array();
	// Read all the filenames of xml files in surveys sub-folder
	// and also read survey title from each of xml files
	if ($handle = opendir(get_config('docroot') . 'artefact/survey/surveys')) {
		// This is the correct way to loop over the directory.
		while (false !== ($filename = readdir($handle))) {
			// Only XML files...
			if (substr($filename, -3) == 'xml') {
				$LANGUAGE = get_config('lang'); //依照語言 Eri Hsin
				//$LANGUAGE = ArtefactTypeSurvey::get_default_lang($filename);
				$xmlDoc = new DOMDocument('1.0', 'UTF-8');
				$ch = curl_init(get_config('wwwroot') . 'artefact/survey/surveys/' . $filename);
				# Return http response in string
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$loaded = $xmlDoc->loadXML(curl_exec($ch));
				if ($loaded) {
					$surveyname = $xmlDoc->getElementsByTagName('survey')->item(0)->getAttribute('name');
					if (isset($surveyname) && $surveyname == substr($filename, 0, -4)) {
						$title = $xmlDoc->getElementsByTagName('title')->item(0)->getAttribute($LANGUAGE);
						// If the survey title doesn't exist in selected language, than set the survey title in English
						/*
						if (empty($title)) {
							$title = $xmlDoc->getElementsByTagName('title')->item(0)->getAttribute(self::get_default_lang($filename));
						}
						*/
						$survey_types = array_merge($survey_types, array($filename => $title));
					} else {
						$message = get_string('surveynameerror', 'artefact.survey', $filename);
						$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
					}
				} else {
					$message = get_string('surveyerror', 'artefact.survey', $filename);
					$_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
				}
			}
		}
		closedir($handle);
	}
	
	return $survey_types;		
}


function getoptions_palettes() {
	$palettes = array();
	// Read all the filenames of color files in lib/ezc/Graph/mahara/palettes folder
	if ($handle = opendir(get_config('docroot') . 'artefact/survey/lib/ezc/Graph/mahara/palettes')) {
		// This is the correct way to loop over the directory.
		while (false !== ($filename = readdir($handle))) {
			// Only COLOR files...
			if (substr($filename, -3) == 'php') {
				$palette = substr($filename, 0, -4);
				$palettes = array_merge($palettes, array($palette => ucwords($palette)));
			}
		}
		closedir($handle);
	}
	return $palettes;		
}


function getlanguageportfolio_languages() {
	return array(
		'by.utf8' => array('value' => get_string('language.by_BE', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAE/SURBVHjapJPNSsNAFIW/yYxNSmr6s7BqkYJL8aV8FJ9BwWdw4yvoEwhid26s0EItxTatOpkkM26KDVKIoWdzN5ePe8+9Rzjn2EUCqAGNda0iA6wU0Py8f3jPJhNUt8v86ubfhN7d7YECQiEl6vgI2WpTOz8rGVngcCRPzwChAqTTGpvlCATZ8G3T7QqLFiQ7bVyaAkgFoEdjvC+N7bSwcVxunJTkiQZAAdDv4wMEAXa+KHQK2HYlB0YnG4CezVBJgheGpK/D0gm8KEJLrwDwPKIoQtTrOGtLATZL+TZ2A1gNBrSmC8TpCZfXe6UAX0qWkxwu1gAThviiQX7YRfy1fIucsyS6YGIW+HhBHTodVK7KAViMMb8Ak8YrHocv2HjKOPio9M4CaAI9YL9iFpbASAByHSRZEZADRuwa558BAHmfdMyaxSnwAAAAAElFTkSuQmCC) no-repeat left center; padding-left: 20px;'),
		'ba.utf8' => array('value' => get_string('language.ba_BS', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAH5SURBVHjapJM/aFNBHMc/95K+xMbQRbsUURR1cZNAB8eKCAURC9Xi4KQQqGgRFf8M1cEK6iQObu6CoqDoUlS0gxVsk4KkSZrGpsVS0pc+9b13r3fn8AIJSIfSH3z4LXcfvnD3FcYYtjICsIHtzb2ZkcDvONB159775erPgJ2pD4xemmkd0fENbx+7cJzxN9nuOJCazi1y81ofU/k9PHv1iXMD802BFWX8L7RBKQ2QsoBYbanBy9clHKdOvryPsccJWK+ALoEqgZxvEVZAu0ipAGIWgC8luVyJzxMVTvTvJUwc5faDHoxeixB1jHYiVEQgJQAWgOcFrKz9pTCzxJOn31Ghy+xyLyN3d4N2IkQddCNC1fG8oF3gUyytUFx0ePvuK89fTDE0cBBH9XMmuwMTliP0LCacw4RlPM9vCXzfZz3UaG3QQlBdcBh7OEmy4w/VxklOX8yA0RGWBOHj++0CrylQJkIbpr/lGf9Y5OrlwyS7zzM4nAFUhAjx2xMEgY/WBiFEi202tcoq9x9N0pn0WHBPMTjcGz2jMQRBm0CGIdoYbDuGbVvR7ohhpxMUimW+TMxx40qG9K4sZ0eOABoZhgDEAWm0wm1UN/x1P1Y112+5HNjfSaF8iL6hXxitAKQAuoAeIL3JLrhATQCxZpFimxQoQIqt1vnfAOi+FEQuOSJRAAAAAElFTkSuQmCC) no-repeat left center; padding-left: 20px;'),
		'br.utf8' => array('value' => get_string('language.fr_BR', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAAXNSR0IArs4c6QAAAAZiS0dEAAAAAAAA+UO7fwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9sBAw0MNvAvidQAAADFSURBVDjL1ZOxboQwEESfAbkCOioaJP4efmkLB4k0VMi4cQx7RUSau0SCqzLSaKqZ3ZF2jaoqb6CIMbJtGzHGS0ZrLWVZUqzrStM0t6Yvy0IWQgBgHMc/1RjzxBACiIieGIbhpb6C915FRH8CfjOfCjxRRNSIiPZ9f618+uRj/iKlRHH2uwMRIQNwzqGql+ic+74DgK7r/vEGZpomPY7jVkCWZRR1XTPPM977S+aqqmjbFpNS0hgj+75fCsjzHGst5t13fgCGpgmXNZQvtgAAAABJRU5ErkJggg==) no-repeat left center; padding-left: 20px;'),
		'bg.utf8' => array('value' => get_string('language.bg_BG', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAADmSURBVHjapJM9TsQwEIW/SeIsIUCklaBBXAWuxFnoqDkOHRegQEgIpAUWlMTD2kNBoixdfl4zcvG+8dhvxMxYIgFy4KirU6TAdwZUZvY6q7vIWQKUCyYoEyBdAEgTgBDCZGfvyQCu7q45rdZsfY1hey8s/869qlXJ2+dmADSt59Fe2Go9qvtJfkjqbR9Qk7gV0eIowC7u0NYPgNubey5cTmyaUYCkKHj6US57QKuKZQ5ERgHMjFZ1uIH3Hg4KxLlxXxDjn6cDaDDj4eN9Tg5UgAo4B44nmr+AZ+mSmM9IZABUlq7z7wChM1nCssShPAAAAABJRU5ErkJggg==) no-repeat left center; padding-left: 20px;'),
		'ca.utf8' => array('value' => get_string('language.es_CA', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAACQkWg2AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAADUBpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+Cjx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDQuMi4yLWMwNjMgNTMuMzUyNjI0LCAyMDA4LzA3LzMwLTE4OjEyOjE4ICAgICAgICAiPgogPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4KICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIgogICAgeG1sbnM6eG1wUmlnaHRzPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvcmlnaHRzLyIKICAgIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyIKICAgIHhtbG5zOklwdGM0eG1wQ29yZT0iaHR0cDovL2lwdGMub3JnL3N0ZC9JcHRjNHhtcENvcmUvMS4wL3htbG5zLyIKICAgeG1wUmlnaHRzOk1hcmtlZD0iRmFsc2UiCiAgIHhtcFJpZ2h0czpXZWJTdGF0ZW1lbnQ9IiIKICAgcGhvdG9zaG9wOkF1dGhvcnNQb3NpdGlvbj0iIj4KICAgPGRjOnJpZ2h0cz4KICAgIDxyZGY6QWx0PgogICAgIDxyZGY6bGkgeG1sOmxhbmc9IngtZGVmYXVsdCIvPgogICAgPC9yZGY6QWx0PgogICA8L2RjOnJpZ2h0cz4KICAgPGRjOmNyZWF0b3I+CiAgICA8cmRmOlNlcT4KICAgICA8cmRmOmxpLz4KICAgIDwvcmRmOlNlcT4KICAgPC9kYzpjcmVhdG9yPgogICA8ZGM6dGl0bGU+CiAgICA8cmRmOkFsdD4KICAgICA8cmRmOmxpIHhtbDpsYW5nPSJ4LWRlZmF1bHQiLz4KICAgIDwvcmRmOkFsdD4KICAgPC9kYzp0aXRsZT4KICAgPHhtcFJpZ2h0czpVc2FnZVRlcm1zPgogICAgPHJkZjpBbHQ+CiAgICAgPHJkZjpsaSB4bWw6bGFuZz0ieC1kZWZhdWx0Ii8+CiAgICA8L3JkZjpBbHQ+CiAgIDwveG1wUmlnaHRzOlVzYWdlVGVybXM+CiAgIDxJcHRjNHhtcENvcmU6Q3JlYXRvckNvbnRhY3RJbmZvCiAgICBJcHRjNHhtcENvcmU6Q2lBZHJFeHRhZHI9IiIKICAgIElwdGM0eG1wQ29yZTpDaUFkckNpdHk9IiIKICAgIElwdGM0eG1wQ29yZTpDaUFkclJlZ2lvbj0iIgogICAgSXB0YzR4bXBDb3JlOkNpQWRyUGNvZGU9IiIKICAgIElwdGM0eG1wQ29yZTpDaUFkckN0cnk9IiIKICAgIElwdGM0eG1wQ29yZTpDaVRlbFdvcms9IiIKICAgIElwdGM0eG1wQ29yZTpDaUVtYWlsV29yaz0iIgogICAgSXB0YzR4bXBDb3JlOkNpVXJsV29yaz0iIi8+CiAgPC9yZGY6RGVzY3JpcHRpb24+CiA8L3JkZjpSREY+CjwveDp4bXBtZXRhPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgCjw/eHBhY2tldCBlbmQ9InciPz6wCsCdAAABK0lEQVR42pSSQUvDMBTHX9rYpq7tDtukBwWFnTyKn0JQxLMfb8rAi1e/gCcZfoAxFTxMuhbbNLSLSWNahRWE1r1DCOH/e3/+eQ8ppWCbwpzzLMv02Sm1LMt1XZwkyYBf/qt5DpG8x4yx/HqXnJ60SRECpYqnWTlhWEp5MI2RtQS5akh+gqHNgzlUPH5dS6zvbxfEPhIyKv903vyHORDrFwJ3UAF709DpGVB+NhSoqa7KyHIWfkANzM9s72tVJmlLCqMv6Y7tPdTA6DYMRgoUbc0tliEqfh3OSYFxyXCbQ895F2L/sQaGk+hwTEAVrQ68mFeCClhcOcTrd85tQZPjZ8B64MFNGkPaCQT1dmDf9+l4RintBDzP02IkhNCbp+fdCZimqR3Qtuv9LcAAy0x9ab5pvScAAAAASUVORK5CYII=) no-repeat left center; padding-left: 20px;'),
		'cy.utf8' => array('value' => get_string('language.gb_CY', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAJCSURBVHjapJPNS1RxFIaf3/2Y5sNmIhvNBG0aKVAqwlWQWVRYgtH/IEH7du1rUa3aFEEECZa0jqCMCpGCICg1xDRnzMlsbMYZ79x75869c1pkGNRGfDZncRYH3vO8SkTYCgoIAQ3rczN4gGUACRH58fcmsB1EBCMWBUBEQASlaUi9jtK039eVajKAmAQBdbeKl1vCmZiiOp8lPtCPcSBNUKlQfvYSs3UPejRMKL0PzTRAKYCYAegAztwXnOlZVsffwEKG+IkevK85cldvUlteRKFo6D3J7q5OCsOPSZw9BaBrAG5+BXsuQ+OFfmKHuqgW19BjUVaGHmJNf0RKHoYeIX7sKPlbtxHXRSIRADQAL/BRHyZxXJfS01Fcy2ZlaITyq3G0so8UCpj70/gLi3ijY2jJRjy18YX0wTuDsxdfL5O0fNrfzRB2A7SajdK34RkmpkDcq2Elwtw/nWLq3GGWSkUmLt3r0AAc1+Z63y6yq0vYmkKvBmRadjKXjBF1awz3tnBjIMXb5gihnyWm7GUc1wbAALDdKhFH50HfXqyxb2RaYnxqbyBh+Vwe+UzU8njU00amKUxntkxgGjhOZSMD2/dIZUsMPslgRU1edCdpzrscmSky2t3E3fMpjr/P0zNZIJeMYro1qp67kUHiypnZHW1JFFDVNUJBnUBX1DQNJYJRF5QIvq6h1wW9Lqwu5Clde95hAJ74AcX57wgKJULlP96KAvVvbTwFJIBWYPsmu7AG5NS6iaE/Rm6CAPDUVuv8awCBYAKOoPeaaQAAAABJRU5ErkJggg==) no-repeat left center; padding-left: 20px;'),
		'cs.utf8' => array('value' => get_string('language.cs_CZ', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAFySURBVHjapJOxSlxBFIa/uXN3Je5qQNgqASsRUukLWAhpfIDkDQKWNiFtCpGkkoCFnV2qFBaixa5KsDEpg0VEViRGQ1Bc9xrmztydOSl20cVlFy/+zTTnfJxz5v+ViPAYKaAIlDtvHjngBqDyfmVPGk0reQVUYqBU2z5EK+HV3AsmxsfyTFGKAJ0VNMcnV7z7UOPzxgEtHx4K0BGAdY6GybhMLKtr31hYqvLrT3Ngp/cegAjAGMdFw3D9z9JoBb7u1Xm9sM6X3eP+F3QOgLgNMBydeK4TC4AoDec37L95y3TrR09zNDpKsz18B5CmSBwTRBBinohj0ezw0tUR6+hxio4xhDuAtSmaYbzEVELCcrLFVOtvu7gQo+6bJ1K4zHcB0pShYpFJf8anZJPnIQGl+u4vIqTdN7AZzKR1PpoqZZ2BLgz+vBCw1t4C3Gz6k/mr75xKwKkol50V8BR4BozkzEIC/FaA7gRJ5wR4wKnHxvn/AAhx0NcPUPgiAAAAAElFTkSuQmCC) no-repeat left center; padding-left: 20px;'),
		'da.utf8' => array('value' => get_string('language.da_DK', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAEmSURBVHjapJMxT8MwEIU/104hKqiCgYFKDJQJgdhZ+A8wwE+EAf5JB2AEIVQoA0NbGpTaDs4x0KYVKUhR3+Lz+d6TfX6nRIRloIA6sDZZq8ADnwZo9k7P3+dPtq8vi/jt7OJPhdbN1ZYBGhICK0eHC4vqB/u/rqwQBHd7D9AwgJYsQ5KE0B+UBL6euz8PnYPe3ECyDEAbgOAc+ceIfDQqCeRJOae0Jjhb7NtpmkpVpGkqQLsG4Jyr/H1TTg3AWltZYMoxAA/HJ9ioTj4eA7DbfSwKn3b2SuRaHPOS+ZmA9R4xESi1wGrlnIhg/ZyAcw5WY1QUlfkLcuR50QMD+CDC3XDmgX6nU8Svw8G/dlZAE2gB6xX7mAA9BejJIOmKAgHwatlx/h4AKJemRnPSNOoAAAAASUVORK5CYII=) no-repeat left center; padding-left: 20px;'),
		'de.utf8' => array('value' => get_string('language.de_DE', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAENSURBVHjapJNNTsMwEIW/cdIKKZQIVWJT9hwgFyC3hVP0BGXDFnawQURRAsg/jW0WTSjdue1sLFt+bz7ZbyTGyDklwBy4HNdjygHfOVDWdf1xSvf1en2TA4X3nqqq0pBFiDGy2WwAihzInHP0fU/TNAeXp/cRkYPz5XKJcw4gywGstbRtS9u2SRRKKay1AOQAD1pRfGpCt4X/vyJyuJ8MouZHK+4mA6M185dXQtenEZRXmFm+JzDGsIgQQ0gyiMOA8cPe4PrRsLqdQUgMlfIMb1u4/yNwELMxVykIYaeZCKzdAhcgqWGMo2Zn4AYfeHruTgmjE6AEVsDiSPEX8C5ANg5SdqSBB5ycO86/AwCO1XbNM5YIzAAAAABJRU5ErkJggg==) no-repeat left center; padding-left: 20px;'),
		'et.utf8' => array('value' => get_string('language.et_EE', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAECSURBVHjapJMxTsQwEEWfEweKXZRAkYYKbcstOAbHoKTmKNScgCOko4zkiiYFyEuUxF4nQ0HCshJFsvsby5b/m2+NR4kIp0gBZ8B6XJfIA7UG0vun1+qY6s+Pd7kGVv0g3N5czYusQATezAfASgPxLgh1E/is3cFlQcZ3qoPzy/U5uyAAsQZw3mMbx7bxs1JE0Y8HQAOULw9s8xxrLX+7opTivy5lWUZVVXtA27aUZYm1dlaCNE1JkmQP6LoOEWEYhlmAEAJ93//uN8YYWSpjjACbaEqwVJMnAnDOLQZMHg34EAJFURzzGb0CUuAauFho/gLeFRCPgxQvBPSAV6eO8/cA4/aiQh6tFVEAAAAASUVORK5CYII=) no-repeat left center; padding-left: 20px;'),
		'el.utf8' => array('value' => get_string('language.el_GR', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAE3SURBVHjapJOxSgNBEEDfspsoiSaSIgat5CwsRCT2goqNnb9h4S+olSiWFjZWthYKaiUJaH7A6kRitJEjigiJXsjlzrGJuSJBueQ1y7Izw5vdHSUiDIIC4sBIe42CB3waIL10VHil6VPYXAVg+fAaYhq+/7YrbqxkDZAMGi0WJsY6B/PZFCqmWZ/N9VQW4K32RRGSCrBEpNxX/0pNG4AgCNBadwWsHd/0TMwk4jy/1zp7a2brVBb3r+SXue0zye9dyl+4riuAZQBaBJTrjU7FR7eJ8QNyO+co1W2QGo5hWmG85TiORMVxnNAgf3CByYzjev7/NyeQGDL4H9XQwLbtyAa2bYcGJ7d3TFWbkZ7w6eEeAAN4u6UKlCr9fAVPAWlgEhiNmFwHXhSg24OkIxYIAE8NOs4/AwC7uO3xQbABsAAAAABJRU5ErkJggg==) no-repeat left center; padding-left: 20px;'),
		'en.utf8' => array('value' => get_string('language.en_GB', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAJJSURBVHjapJNdSJNRGMd/Z3u3OTXXhx8xjRLDFWIk0jAk6WYYfaAZSUWGZkFdRNRFYAYFXhkI2gpKqISC1IsIUiKIchUEUQiuj4uiMT8m0wbNr+n2vu/p4l2K3Yn/m3Pg8Pye8/wf/kJKyWokACuQnjxXojgwowCOwPW2iS37y3k0bOfJwwHmYzFePK5n7Oxl0FRyH9zicGM3CZOFBhccypMox2tJdWZnK0Bay/AGmr+McmJvKUXbaul79hmTzYJ9x3bQdEw2C2V7XOzLUdmVZ2PWvZvm1lcAaQpg/vY9xL2NRdRMvqHUlYmzoRw9oREPjICqoic0qtIiFGVmECp20+kd4IPPD2AWQMFMcOSnxWZDavqyIcfOXARVJ7fLu9w4s4nEwgLpmzdtVQB+HW0kNcOBFo3Cv6VICWYTSEmw6hSYBOjGo7LOwdz0lHEHiMVimCciaNEpRLIWqSMUxWCpKgiBEAIJqI4M5tLtS4Cs++3kZGUlK5cUcFciEwkKBl//t3xBeHIS3KWYAEo87biruxiKWkh568N/447RWZdI3fAlMG8l5Z2PT00deOq6KfHcBjAA+YVO+p6eo8zXy/P+r9R9tCcbCQQCgIMHOuh3llFx2kNn/jjFhWuXAN6rFay/66XtZZjG9zrh0KjxVasCNgsAs9N/OHnkJq0/HLjOH+NadmjRg7jW00tnWOPKQGRxzEH/EOM7XUhNI+of4nckCEDThRaCl2qor66E/p64ABxALrBmhVmYBsYEYE4GybxCgAbExWrj/HcACIPUyGtYcDcAAAAASUVORK5CYII=) no-repeat left center; padding-left: 20px;'),
		'es.utf8' => array('value' => get_string('language.es_ES', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAFzSURBVHjapJNNS1tBFIafkxnzoSZpsI1gFoLUP9CCm65KF+79df0H/QmlIARcudGFK0GCKJhFJX7cxNyZuXdmusiNGqngxQPDOQxzXt55OEdijLwnBKgCq0UuEw6YaKA9/L77N+IRVCmFjf6frgZW8Dn1r1/e6FkgRuzRMcCKBlR0GSEZ40ejxcdzPiIL12ptjegyAKUB1n871NItxDusXWJ6JzS7OVrCKy4q+MxBBzSAy1Ia+oZpcs9tX/Fw3sH+yGhvJzRqMkP9kmCeAoWANSmN2oD8sstw/4L4aY/WfcBf/4SN5D8O2thUA1ABMCYFMiq+Reuz5vzwF+nRARX/AQggYZbnR7Kip3BgjIWguNlK+Nis8m3TkvfOmPZqLMfwBHMe3mNM/iQw2DFIfZloThkTmX96AkwWAMxqqQcGxj5j4DJiNUD1bcMYQ8A69yjgfAycjEfITK/UOAvQBnpAs2TzGLgSQBWLpEoKeMDJe9f53wBKAZ093TMjpwAAAABJRU5ErkJggg==) no-repeat left center; padding-left: 20px;'),
		'eu.utf8' => array('value' => get_string('language.es_EU', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAADUBpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+Cjx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDQuMi4yLWMwNjMgNTMuMzUyNjI0LCAyMDA4LzA3LzMwLTE4OjEyOjE4ICAgICAgICAiPgogPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4KICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIgogICAgeG1sbnM6eG1wUmlnaHRzPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvcmlnaHRzLyIKICAgIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyIKICAgIHhtbG5zOklwdGM0eG1wQ29yZT0iaHR0cDovL2lwdGMub3JnL3N0ZC9JcHRjNHhtcENvcmUvMS4wL3htbG5zLyIKICAgeG1wUmlnaHRzOk1hcmtlZD0iRmFsc2UiCiAgIHhtcFJpZ2h0czpXZWJTdGF0ZW1lbnQ9IiIKICAgcGhvdG9zaG9wOkF1dGhvcnNQb3NpdGlvbj0iIj4KICAgPGRjOnJpZ2h0cz4KICAgIDxyZGY6QWx0PgogICAgIDxyZGY6bGkgeG1sOmxhbmc9IngtZGVmYXVsdCIvPgogICAgPC9yZGY6QWx0PgogICA8L2RjOnJpZ2h0cz4KICAgPGRjOmNyZWF0b3I+CiAgICA8cmRmOlNlcT4KICAgICA8cmRmOmxpLz4KICAgIDwvcmRmOlNlcT4KICAgPC9kYzpjcmVhdG9yPgogICA8ZGM6dGl0bGU+CiAgICA8cmRmOkFsdD4KICAgICA8cmRmOmxpIHhtbDpsYW5nPSJ4LWRlZmF1bHQiLz4KICAgIDwvcmRmOkFsdD4KICAgPC9kYzp0aXRsZT4KICAgPHhtcFJpZ2h0czpVc2FnZVRlcm1zPgogICAgPHJkZjpBbHQ+CiAgICAgPHJkZjpsaSB4bWw6bGFuZz0ieC1kZWZhdWx0Ii8+CiAgICA8L3JkZjpBbHQ+CiAgIDwveG1wUmlnaHRzOlVzYWdlVGVybXM+CiAgIDxJcHRjNHhtcENvcmU6Q3JlYXRvckNvbnRhY3RJbmZvCiAgICBJcHRjNHhtcENvcmU6Q2lBZHJFeHRhZHI9IiIKICAgIElwdGM0eG1wQ29yZTpDaUFkckNpdHk9IiIKICAgIElwdGM0eG1wQ29yZTpDaUFkclJlZ2lvbj0iIgogICAgSXB0YzR4bXBDb3JlOkNpQWRyUGNvZGU9IiIKICAgIElwdGM0eG1wQ29yZTpDaUFkckN0cnk9IiIKICAgIElwdGM0eG1wQ29yZTpDaVRlbFdvcms9IiIKICAgIElwdGM0eG1wQ29yZTpDaUVtYWlsV29yaz0iIgogICAgSXB0YzR4bXBDb3JlOkNpVXJsV29yaz0iIi8+CiAgPC9yZGY6RGVzY3JpcHRpb24+CiA8L3JkZjpSREY+CjwveDp4bXBtZXRhPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgCjw/eHBhY2tldCBlbmQ9InciPz6wCsCdAAACDElEQVR42qRTS2hTQRQ975e0edW0Rvw0Vau2ImIFUz8I/haCa0UioXQhLkoXQQQtrtwr1IVgXYnVCoJSrVTc6UaKQQ1IshI/kPBim8bmpU378v7jmzF9QXATeuHMHWbmnDncucMRQrCW4DwEPLTVczNhelgWvSHc/2x4/qRiIvlFQ9BtOOqceMryr8GEv6aLHG73y/i8WUI6fn8TFZAjSwZWYn0Y77UxkFlER8355yqpeyfLpTYBTw60g4QEbP2aoUsy7w3CnWkFUaWMGbeIkT0a0sYcrHzOF6DzlF3E9d4aPlpz6M4vYHS6QLcE6gChqoHL73IIdjl4vn89bh5qQSKr42pd4NEuDpP7guBtDQPpCuKzEnTNYHs8q4ZpgKgqEjMKrr35iZq2hHt7Rd/Bgx4ebnURN17/wLlUAU5FZZzV2K2qKmk2KIdymQNd15t+/1UO8/nt9HHUAhIMU8etExGMH2yH5BCYybd/m2XsDMvJVBlXPpTBt7ZCMa1GDXTLwm9ZxKULXZiIbcAWjeDxVNG/7eGrIiIGwdixjRg6H8VyUGAcXyAb5nExHkV2RxiHF1xMvZjH0ZLrC5yadTD5soS+CpDq6WBnv8uNVt4mjhzJ297sbM7AUEaDSP7fiQbP4W4shPedAbTYBProp+1UIOwh6mFdk3WseihQAaH+kYQmBWi/m9xav/MfAQYAyCX8EJn5nxAAAAAASUVORK5CYII=) no-repeat left center; padding-left: 20px;'),
		'fo.utf8' => array('value' => get_string('language.fo_FO', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAFUSURBVHjapJM/T8JQFMV/r5SqAStgiIMxmmAcHBhMDPoRWBwccHd3cSfGz8Lu4sQ3kAEGVjRVExOMMdIHJFDaPgf+mCIVCWe5yb05Jzf3nCuUUiwDARhAfFQXgQN0ANJqCvliWb2dX6h8saz+ApDWgdi0dHYvhSEOye6m5m0R04HIdPf5o4378oq11p4nENEAPM8LdO3OAF9KZHcQyhxzdIDjqzu2Nk3s7gDF0BW/ZWN3HU6v73+REzGD9085cSFTP8o9mp6PL+W/zq+ZJjKika1V9nWAs4NLWEnQ6f2sXHm4JXdyM1MgvhqFfgtqFQAylmUF7EkWSuppJ6OShVKohZZlKSCjAfR6val0CRBiWEMw5mgA/X4/MDR0DRGNYuhaqMCYowOO67pUq9XJUDYb1FtfyGYj0J8VZwFsANvA+oK/0AbexCiJxqxEzoEHOGLZd/4eALo5ytUa0CWHAAAAAElFTkSuQmCC) no-repeat left center; padding-left: 20px;'),
		'fr.utf8' => array('value' => get_string('language.fr_FR', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAFESURBVHjapJM7TsNAEIa/9RqjKECEkGgoKOgoaHKCdByBc3APDsIRaHID01ASRTxEgxI5zsvetb1DkcSJCUaKMtJqZzSrb2f/nVEiwj6mgAA4Wu67mAWmPtDqdB6+f2e73ftK3L+92yJcPT2e+0CzKBzt9uW/1zVurpc1KxBh/vwC0PQBbW3BeJwyHE5rAebto/T9s1MkywC0D2CMJYpmRNG8FuBG49IvPE1u0wUMIEkMg8GMOJ5T9yn5KC59AWxiNgEJvV5GHCf1kvffS1+3Tkh9bw1I0wSRBs65ehU3cpJlpJnbBBi0PsS5+qaSFUBAihyTWwC8hYgG50ApVa6tjlvlPAVOSIxdV2CtJQiEIPDrnxAcVKoxdi2iFSmYTD4r58MwrMSvcfSntgpoARfA8Y6zMAG+FKCXg6R3BBSAVfuO888AocKXohfLXWQAAAAASUVORK5CYII=) no-repeat left center; padding-left: 20px;'),
		'ie.utf8' => array('value' => get_string('language.ie_GA', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAE2SURBVHjapJPNSsNAEIC/TdKgttJDwYv07FXwCfRtvHjxPQQvPo4+Qd5AEZVebGqJabs/ye56aEkT2xRKB4bdYZhvhvkR3nsOEQHEQG/17iMGmEVA//rp7vu/9/n2sWH/PNxsEAb3L2cR0LXOcjW82JmuM7xc1SzAe4qPBKAbAaGxJb9qwWSetQJs+l79g94ArAEIIwBtDNNFzlTOWgFuMa11LqAwGoAIQGpJOs/I1Iy2qTQAeAotawCleE1HZKq9Ajt+W8OO+6iyswYoJfEnHZx37V10NV9ZoqStAzThUbAbUPe5Eq2XgABAK43zHiFEpRvxQqwVh9S1JprCEHtHvBzK9pWN4kY12pgKYLx15J/jRkCSJA178pVvXWcB9IFz4HTPW8iBkQDC1SGFewIsYMSh5/w3AFIBl8AWLNaEAAAAAElFTkSuQmCC) no-repeat left center; padding-left: 20px;'),
		'gd.utf8' => array('value' => get_string('language.gb_GD', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAJySURBVHjapJNNSFQBFEbPm3nqOE5Jg39omimEaVNG46bSrMyFJGErq03QJheVQYuKSlGEwNqEy0BCbFEQVhgZlpmLUkRMS8GsxNG00Bwd5+/Ne++2yKyW4re5m++ezb1HERHWEwWIBhwrcy3RgGUViBeRH4GwTlP7GM/7plCjFCwWy39t0xR0zaC0II1zR3OIs6koipKkAJldQzNfi10pAHR/mKW9bxpdBHUFopsmVgXK3Js5uPN3r2fkO0V5KVstgPXKvQHa3nkQEQ7sSOFk8VZ8gQhjHi9jHi9Lfo3KoqzV5Se9U1xuHgCwqgCmYdLSOU7f2ByXjueRn+Wk/lQ+DQ+GMUzhWqWLVKcdr1/j9qMRRiYWiBgGACpAWNPwabE86/Uw6vFSc2IX+VlObp3ZgwC2KCvDE15q7w8yPrVIcoIDLaz9BQTDIT7PKCwFI4xPLjL4ZYHum6VkJDoA8Mz5qWh4zezsErHxNpYjJqqEALAABAMhIrrg92lkpsfTXL2PjEQHEcNE003SE+JovriXrIxN+H0aEV0IBv4FhEL4fWEO7U6lo76EYlcy3+YDlNd1UVb7kqn5AIW5SXTUHeaIOw3/cphgKLR64mxbSaNcbxkUTTdEROTNx1nJrWoTW3mL2MpbJOdsm7wamhEREd0wpab1vdhKGgXItgLOuqsXzt84XYjVonD3xSeqmnr56QsTZ48mJkZlYVmj7e0kG+1RFGxLoNiVTLSxSOfj1jsKkN7f3z8ZCBs0PR3lYc8ElijL6hP9iWGaGJpJxf4tVB/bjj1Gxe12ZyhAPJAGbFijCz5gWgGsKyJZ1wgwAE1Zr86/BgBdMB7GtRftcAAAAABJRU5ErkJggg==) no-repeat left center; padding-left: 20px;'),
		'am.utf8' => array('value' => get_string('language.am_HY', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAEkSURBVHjapJOxTgJBEIa/yS6cBAlgQYOvYIy1T2BhY+MTWthY6xPYamxtSKShAOFM7naP3bG4Uw5DceDfbDPzzzczO6Kq/EcCtIHj6t1HHviyQH96czs7pPr44X5kga6GQHJ+1hBZUBT3+gbQtYDRokDTlDBfbEdrrdGazMkQLQoAYwGCc8TlirhaNaMwhuByACzAVXLNKAxYSk59KSKwa0mDcMQs+QSeSoMsy3mfzFmmrhFBv5fQEr8hyPIcNZYYdWfPW/MAihBZr2stPF7ecTpsQ5EhVazU8v76aavDx8Jz8fxD4ByiFhX5LaS7i5eAGsmc2xB474EOUi6lgWKVUxr4dYy8TBaHfEYvQB8YA709k1NgKoCpDsnsaRAAL/895+8BAJDZeW1oOvlvAAAAAElFTkSuQmCC) no-repeat left center; padding-left: 20px;'),
		'hr.utf8' => array('value' => get_string('language.hr_HR', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAGvSURBVHjapJO/bhNBEMZ/u3s+fLZDhEEBERBFoAiKKGgoSBcpEkjQ5g0iISHxCslDpIGCmoqHoADpCoSQIoVQWPyRosgccWyfb9feGwonl7t0Jl/zrXa++XZmNaNEhItAASHQOuFZ4IBBAMz/fPL8UNejUizHiKBE0CLkSjFRGpSuOCy+f7cQAE2ZeMKV+9NbEcbNiK6eY9SqAxANMtp5n9pwhNIaEcF++QrQDADDeIzvdpn8SQDYXX1MrK/TODqgdzuiHlzmoYUH8Wd8YDBX2/jjYwATAEysxRz1yHs9AJqp8IwfNMihMyANQ/qEuOFgWqUxeGuLVpbSNJUykjiWzqNVEZGCkziuaNI0FWBJA9iSG4DPMu58+sD3hVsF+yyraE5zNEB2LogI+9ducvfwV8Gcm5fTnABg+ekO5lKb4WhcCDbXNnl55Qb3/h7wamOL13sN4OP0j6Ia3iZnBjbLqNcEVXrhzV4Dtf4CNran5/KU5IItV+CcI8yFsGYqbbz91gIFoRHQqmLgnCsMnEhOP+n8zyo4BcwDi8DcjMl94LcCzMkimRkNPODURdf53wBKN975O564GAAAAABJRU5ErkJggg==) no-repeat left center; padding-left: 20px;'),
		'is.utf8' => array('value' => get_string('language.is_IS', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAF0SURBVHjapJNNLwNRFIaf6Z1WmlJCgrCQEPG5ILG0KYmNpY2w4j/4FfaWrIiNn8AvMLGyI76NSMpomfbeztxr0ZpWW5Kmb3Jzcz5yznvve45ljKEdWEAC6KzcrUABnzbQvbR1+FobOd3f4HltnaGTY5a3j/6scHaw2W8DqSAwLMwM/gomZqcBmJsYaORs4PzyBSBlA6IUhHx8SrJeIcoL7u4BuH70sOo69/UkKQUhgLABpCzh5SVeXkZJOpcD4KPG9wMhYkhZiuwx3/dNPR4yK+Y/+L5vgDEb4GoxQzrUUdcf3IxONv28WDpNTsSqDFzXbehwPTL+LwPXdasMplb3EB29fBXK7ypc7IDWACTndxsYpJJxQvlWZgMgi0W0NlgVlcpyWZFq9UdrgywWAbABlFIktCERF1W54/HyPNT4IoW0QSkVFVBGa/LZ2yjBcRwevXfeHOeXv9k4W0A3MAx0tbgLeeDJAkRlkUSLBUJAWe2u8/cA98bqGC97rHcAAAAASUVORK5CYII=) no-repeat left center; padding-left: 20px;'),
		'it.utf8' => array('value' => get_string('language.it_IT', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAE+SURBVHjapJM9TsNAEIW/jW1ACiSCIk1KOoTEVRA03AauQwGcJAWiRhDEnyhCYhNnf+wdilhxDHakKCOtdkajfftm5o0SETYxBWwBu8W9jlngJwS6Z3dXX3+zN6eXlfj9/OIfQv/2uhcC7dznnPQOV363dXxUUFYIgrl/AGiHQOB8RmJTRjppBMiGLws/ONhHnAMIQgDjHBM9JdbTRgAfx2XjgoDcaABCAG00I5MQ2xShfip+PCkDAatNCTDThif5JLZpIwP3PFz4rU4HHbSWAVJa0TZefCOA+DLnM8fM+qUStCZqR/hVoloCUC7DODtnA/N6RDyqUJaqlZxaHBFBW1syMNaygxDNh1Iv2SiqsDGmbKKV3DN+/Kg8GAwGlfh1/F0rZwV0gT6wt+YuJMCbAoJikYI1AXLAqk3X+XcArNyT5mhM57UAAAAASUVORK5CYII=) no-repeat left center; padding-left: 20px;'),
		'ge.utf8' => array('value' => get_string('language.ge_KA', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAGDSURBVHjapJO9LgRRGIafM2d2Q3btbuJnN0QlUQkahVKJAi0FhTvQuQCde9HouYBJ5gLsKoQs8b+W2THnfIoZZiZLsfEmJ6d4z3ny/SoR4T9SQBEoJ/cgCoE3gHH5RTboyfXKqtxsbctfAsZdoASACG8npwCUN9dBOxQX53HKZfr8jTVwHICSC2gAMRbz8BC/tRZE+Gxd4lRqPzFnfRUDtAtgjEG7msreDoigtAatsc+voHRSLUV1fzeFGQOAC9CcW6I0Uce+vEC2K0phn565WljOVc+p1eje3aaA949HnIvbGJCRm5QnopsHVKu8FwopoH52TqNe7+tTa3QagNlup89rt9swORkDeghqeIj7g0MAxo6P4lSsyaWT9YNeL40gCAKIIkQpQCAyoB1QKgVYm/ggYRj/STTj+35+QowRsVaalYY0Kw0Ra+OTke/7Asy4QBhFEZ7n5ZMU4arzCsCT56XRSLIAyTgroApMASMD7kIHuFbJJBa/J3IAGSBU/13nrwEA0CDu7ciww4cAAAAASUVORK5CYII=) no-repeat left center; padding-left: 20px;'),
		'la.utf8' => array('value' => get_string('language.la_LA', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAFpSURBVHjapJPNahsxEMd/WmllOx9NICFQegh2esqxUPoCgTxmXiMvEXJIcsih9FBsSmHdfHYl7UqTg2XHcZ0Uk4FBSDP8Zhj9R4kI7zEFWGAjn6tYAB4MsOVHx7//CUsEpbEfT1+vrtSeAdahpeh8WQr4j60bQIs0SLpHYrUAKF4ypUUpM/+kDUBsA4X+A+lmLruFnJxSILZ3NP4K0zlE6x1gMnwDEJoaU46RdDsLTAAlAOPqJ7QjyuqasFthbInuHT0DfF3TK79nwLSDZgbo9PZJjaKuL9nsbmNt4tF7AAoA52pEGiAtcegWZ5i/J8Rh5O5mQJBvOOeeO3DeTyYuaVr+BUCZHaL/SldbiviA1tsLAOdA1rKuJDemsoOxn/nQPyDID+ygD0rh3K+5GfgmV7cLIi3nVYM9GMyuPs/AACHGxPnF7YKQJt9oq7M35ayALeATsLniLtwDQwXo3LteERCBoN67zk8DAFgLnuHCeQF3AAAAAElFTkSuQmCC) no-repeat left center; padding-left: 20px;'),
		'lv.utf8' => array('value' => get_string('language.lv_LV', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAD1SURBVHjapJNBbsIwEEWfYytFSiukIrFhyYY9J6A37gXKCThAD9ANEo1EipWZEE8XEFoqVUrgbyxb/m/G4xlnZtwjB+TA43kdIgW+AjB+W622t0R/Wa+nASisbXleLnvm7MCMz80GoAiAT6o0+z2y211f7urj3NXxw2RCUgXwAaARQcoSLct+WWQZjchlO48x2lDFGA2YZwDyi9ZXnScDqOt6MKDzBIDXxYKp9xwPh17mUBRs2/YHICIwGuH+VPtfpXR5wgmgiuU5Wd6vGS0l5PSNBECPZrxX1S3NqA4YAzPgaaC5Aj4c4M+D5AcCWkDdveP8PQACvZWvMV3xjgAAAABJRU5ErkJggg==) no-repeat left center; padding-left: 20px;'),
		'lt.utf8' => array('value' => get_string('language.lt_LT', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAERSURBVHjapJMxTsMwFIY/x05LCJCqEggJcQY4AQM7l2PjHHADRhbEBRgQEgIJQoua2HH8GJqqgclp/8Wy5Pf5e7KfEhG2iQJGwF63DokDfgxQtOXV+ya368ntkQFy8GDOB0gL+EeA3ABapEHJHMLnv8PSK+olmSLSAGgD0HqLSUqQMk4iJLTeAmAALu/OOCymzOwxgvRk1Z/9KsU45+N7AtwvAVVteZY3Zm4RJXAw2kVbWRtU9YIkHRMkRAF88Li618LN9QOn6YhQVVGAJMt4aRwXK0DtHGJSUCoKICLUzq0NrLWwk6HSNPIVwrKmA7hWhKfya5PP6BRQACfA/sDiOfCqAN0Nkh4IaAGnth3n3wEAueFrXoFF3HkAAAAASUVORK5CYII=) no-repeat left center; padding-left: 20px;'),
		'hu.utf8' => array('value' => get_string('language.hu_HU', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAD3SURBVHjapJMxTgMxEEWf12ZRtKAIkGhSpqFFnICajmtxHmpOgLgDUhoQrJKAdz2766HJJqSzk+/C8sj/+2nkMarKMTJACZxt9hwJ8OOA6eL+4eOQ12cvz9cOqHToOb27TUM2BlUlvL4BVA6wKh1xtWL4+t6/PfbHmL2yvbpEpQOwDqAPgquXxHqZRlFY+hC257n3XnPlvVdgXgCEf2mpGj0FQNu22QGjxwHcPD1iLyb8SpNkrsoJQ93sCEITiDFiEleMkdCEHYF0QqlKaU+SCKIq0sk2QDRG1u+fh3xGMcAUmAHnmeY1sDCA3QySzQwYADHHjvPfAKSbmsRgfC7bAAAAAElFTkSuQmCC) no-repeat left center; padding-left: 20px;'),
		'mk.utf8' => array('value' => get_string('language.mk_MK', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAI4SURBVHjapJM7axRRAIW/e+fO3Vn3MUmKxUSTGBARBAtTiTbiPwiJgjaKlSBG/A1CEETsNLGwUIgpTKPYBGxSpIqVD9DKwoTNss/Z2ezcuTPXwmDq4GlO98GB8wnnHP8TAWigfNBHiQH6CghbT+f2qndS4veK6I1GFB0uEYw+SCCAzuMCaIcbCMrXDJUFS++Vz+jiek0BpfijRAQXKM+lyNBjuKkAcNIicOhzPgDBxYzgkiV669P/8BWgpAAPbUi+xJhvLSo3U/xpSbyhUKdS0A7zo8CxKxY1YWk90rh0DHwD4CmAtG3wRntkjYj2EpTmDGrGI9t1gEBNDZAjltaSJo8TvJoibScAKIBgoYeOG6S6T9aA3rKgcsNQuW7JrSN6remu+MiRAWrC4Z+2MBvB1gGgeLtDGDbJowi7IxhueaiTOTLMkBLUhE/1FgSXM9R4jqxW6XY9eHgAqL/IsF1F+ktj65JsV1CeT9FnLS6B4bZgsKHwVhWqluNPK5qhPZxQf+bwlSCLPKSG8G5C8tmjcb8IAvI4pzKf0HmuGe57eFVB3f49oARIVIJzDn/SUVveJ+tJBp98vMkMdcayv6mxe5La8hA943DOkajkEDDsGArnU8bXUsz3gP5qCVnx8acVekohq4r+uxLJdsDxtZRg1jBsm38TTOGqYefeLj9XfDovNbIYkw8FY3GCzB3NfoAoOPIngrBpKC92CHwN6xgBhMAJoHJEFyLgtwC8A5G8IwIywIj/1fnPAPjK8ViCBCZyAAAAAElFTkSuQmCC) no-repeat left center; padding-left: 20px;'),
		'mt.utf8' => array('value' => get_string('language.mt_MT', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAEgSURBVHjapJPRSgMxEEVPmrRQWrcqKrj1C8S/8cVf9MU/6YP4A1VbwSq4WsEkm2R8sFrX3aql8xIY7hzuXDJKRNikFNAB+ot3nfLAqwEGIjIrJlMAto+GFdXd6dlKwvDi/MAAveJ2QuEdCjAPj/T3975EnZPjH5YVguAurwB6BtCCkGIkhkAsfWUgjG8+Fv1WencHKUsAbQCyPEffz0gxMsjzijjNX+rBaU10FgAD4L0nyw8b90zFc70p4K1bApxzdLvdRkA5vq71WlmG1a0lwFq7MmlJqe4qlLz59D8ADQBVBtwi7L8BStVdiWC9r2awcr7dbnT1OWMAH0JgNBo1AibF06/fWQEDYAhsrXkLc2CqAL04JL0mIAJebXrO7wMAkcmAHXrf52IAAAAASUVORK5CYII=) no-repeat left center; padding-left: 20px;'),
		'nl.utf8' => array('value' => get_string('language.nl_NL', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAD1SURBVHjapJMxTgMxEEWfY2ejaEERKWhSpkqBOAgdDTfjDjTcBXEBmjRIBLELxvbKMzSbsFSsky9ZliX/P88jj1FVTpEBKuCs30uUgE8HLLa3d6/HVF89Plw6oNacmV1fjUQ2KEp8egaoHWC169C2Je/e/97WwUMHsssLtOsArAPIMSIfDdI04yisJcdwOK+991oq770C6wlAjLG4gXvPBCCEUByw9ziAzc09drbk67sbZa7nU3Lc/RLEEBBRTN/w/5aIEocEKSUqUaqpHUUgoqSUDgFJRWjfXo75jMkAC2AFnBeaW2BrANsPki0MyEAyp47zzwB3zZnF+zoIqAAAAABJRU5ErkJggg==) no-repeat left center; padding-left: 20px;'),
		'no.utf8' => array('value' => get_string('language.no_NO', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAFrSURBVHjapJNBLwNRFIW/MTOt0qZhQbAgsSFCCDs7SwmRsLPyb8TaysbKjg17/2ASYS0q6IREdToy+t505lq0pkZL0ribd9+7J+eenHevISL8JwwgA+RbZy+hgXcLKJY3tl++V8bOT1nbO+HyeBd3c+dXhvGLsxELGCSKyC4upIqLMyMAZOfnuogW1NU1wKAFmBKGxL5PVHlLYHePVQDC0j0YRorCHB5CwhDAtAAaSmFWPeKql4DeanUAYq/Wqb2vj4ZSyXU6CAL5GQtbR/JXBEEgwLQFMLt+SCgZPF+lGuWXD7qaVyxksQ3dVuC6bkeH3NL+nwpc120ruF1ZRds2cfABwNTzPXHcHLDS6GSnBQM5HpomYgHUtUJMK+V2kv34AQCJhbpWbQKlNdKfg4ydgDK2+ZV0EkiM0joh0JEIN141Abw6Dn6lhOM4lL+9dxtnAygCE0Chx13wgScDMFuLZPZIEAHa+O86fw4ApIbl9EMu4AwAAAAASUVORK5CYII=) no-repeat left center; padding-left: 20px;'),
		'pl.utf8' => array('value' => get_string('language.pl_PL', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAADCSURBVHjapJM7DsIwEAVnnY9AQOhoOAgV9z8ANSUNFQSkxOvYSwMipZNs42rGz6tnMTOWjAA1sP2eU0aBdwnszew+63aRgwM2C16wcUCxQFA4gBjjZPLHOABVnSz4MSXA9XSmGSKpbbNg1zS0ZfEX9F3HDgcxZQksDPRhlMB3PVJVWMoTyBDwIYwSqMeKEkTyEiSjVz9KoIqt1lBXeQJL+NESNZpxeT7m9EAF2ANHYDcRfgE3+TaxntHICKgs/c6fAQCPgVSYGmG1OQAAAABJRU5ErkJggg==) no-repeat left center; padding-left: 20px;'),
		'pt.utf8' => array('value' => get_string('language.pt_PT', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAHVSURBVHjapJNNa1NBFIafuXOTa3KbRkmbKkFERQSxILh0I4huxIV/yJ9hcaGgbly6ciGudCO4iCAqiB9Vq1FTb5MmzZ07ny4aTQpmEXpgGBg4D++Z874ihMB+SgBlYGF8z1MaGMZA/drDG7/+vjohkCFw6/brmZ3BeeLGIVbu32nGQOq859zyCQC8EEQhkKzOFh28w37+CpDGgDTOMtA5XTXEy0Ar2uH3wQ0WX3qEgxCLPYioXgdrAGQMUFhNVuygbZ+LbpNad4Tv9shOJSy+9cgMQrRnCGyhAYgB8kLxYzTi8vI7rr/ZJh9cxR+V6M0nDI91qT1NCCVgvLDgHdr5CUCpgp7/Tlb6QHs9QV66gq1VaK3dJT/yhWQj3W0eTyLSKurwypQCNULLKh1TIV/oc/rFI2w5JT6/RbIOjAIkE78EY1F5PgXIC6JqmedbTU6ubpO173Gh3cOdzUkel0A48FN7MIZCFbsfCqALhcCRmQo3f57hwfEmRTmisZYQdUpQEiAmJ/iAUmqiQGvDAQLVOMKImI+6ydKzT7iy/K8/g3UUxvwD6GAd/fcdAGwkiH3gVb8324nWIZcaAFoAdaAF1ObMwgD4JgA5DpKcE+AALfYb5z8DAGOr1n0Zxwo7AAAAAElFTkSuQmCC) no-repeat left center; padding-left: 20px;'),
		'ro.utf8' => array('value' => get_string('language.ro_RO', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAFTSURBVHjapJM9TgMxEEafs5sgSEgEBUJwBAQNJ+AaVNyJI0BLRc8NEIgaQVIAEgKym0Ra/+x6KIhiO9BEWBpZn2w/fx7PKBHhP0MBHaA3n1cZFpjlwODk7PJ9efXm/CLRb6e9X4S966udHOg2jef4YDddzY4SuXa4HpkWzP0DQDcHMusaJjPDZ1GFE34IyOKQG3UDe3sLcQ4gywGMdYwnmvFEB4AUiQNf1EG0WtTGAJADVJXho6gop4bFr0ixsAvgyyYYAlylY0DF46ihnJroyqfEgXseBAP9PlWeBYDWGslzfFITPk1qE7S4Gu1sBDCGjA28jwASAwR80Kp2mJ8k0gIwusJ7Qc1frRbfFYUKIV7QNkqitZaOFzrtLKrRpcLstIMf8RgbnmBFPNOvYbL/9q5M9Gvp/yxnBQyAfWBzxV6YAi8KyOaNlK0IaACr/tvO3wMAJOGiBWzsFlIAAAAASUVORK5CYII=) no-repeat left center; padding-left: 20px;'),
		'ru.utf8' => array('value' => get_string('language.ru_RU', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAADkSURBVHjapJM5TgNBEEVfzeZlQIOE5ARxClLOxC04CzFX4ArERAhZsoTEyDDurqa7SGxsIYJZKqnov9p+iZkxJQSogLN9HhIKfBZAY2abUdVFVhlQT5igzoB8AiDPAGKMg5UHTQFwc/fI6rKh/VLsz4b/u9FFXbF5b4+AnXe8rIW2C72qN8uSwtwJoHMwm5NSP0+EbyP4E8DD0z3XZUnqdr0A2XLBawjcHgBOPZYXINILYMlw6o8deFVsvoCq7AewhFf9BWg047n9GOMDFaABroDzgeIt8CZ7J1YjHBkBlanv/DMAwHdYum9dlZQAAAAASUVORK5CYII=) no-repeat left center; padding-left: 20px;'),
		'al.utf8' => array('value' => get_string('language.al_SQ', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAGdSURBVHjapJOxbhNBEIa/2d07+zg7ESkosOhpIIgKnoEqDQ1vSMMz8AiJREEVCYiEIhQrZ8e+3dudofARTEeSvxmtVv+/s6NvxMx4iASogdlY76IErANweHHy/vI+ry8+fXwSgNZKYXL88vbC7/UnwJ9fFkBkd46nZwBtALwNA7paoVdLEsb3kvlRCtkMAZwIz7xn4QNTBHf0GBsGAB8AcoyE6w7tOtSMz9sbTofIzDkE6FR5UdV8aGaoCHhPjhGAAJD6nupqCd01RY2hX3OTIl+1YGYsfGCoJpRYUBHMdp7bgH67pT4/x7qOrRk/Nx3H4vmyXZMx3jVzLrWwaedU4uDggN67fwPmzmGqTICFCL9KpjVQgXXJPHWOxkBNkTzQJ/0bEPsIVYWpMhXhuQTWXvnmHBl47QOPRGjMSGbIkIm7IY4dpIiFACJE4M1kSkA48oFixtt6QgZ6M0QEM6NPe0OMKULTIHWFjIgl4FXVArDBwMDJyIfpzjMGpKzG2XJ5HxiTAIfAApjf0bwCLmQkt94n+D9VgCQPXeffAwBmvdNGVik6WQAAAABJRU5ErkJggg==) no-repeat left center; padding-left: 20px;'),
		'sk.utf8' => array('value' => get_string('language.sk_SK', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAF1SURBVHjapJM/TxtBEEff3N6dY4EBCXEFiAa7iDBFPgAVfZpU6ZMqXyhSCrrQIDoaeigipfApBc3ZUmRHQlYiEWN0f3y7k8KWr7BTYE+zze7b/c28FVVlnRIgBDZn60uqAMY+sK2qw5VuF4k8YGONBBseYNYAGB/AWosxU87vjx/wj9tgDDaO2T0/X3rSWguAD3By9oUo2mE/qvG5zKm12yCCjb/z/tMV98kITDWtna1XDAd/KkCa5iQPY8pJRjjqot/uwPMIf3bpN0Z0h8/UjJsDntIJTsoKkKUZGmzyN1Mu7Gvuvg4oxXC218Q5cOqRatUq3xkkK+ceNG+PjpLDIMClKcPWG97qOwo13HiXRElnIb9Xr9OfTDjt9VrTF+Q56vsgQtSNuW5NN+4lMYgsAFSVLM+rCHlRoPU6BAEo7Pc6iCqFHy4HOEc2Hs8BhVXlx+PjkmGl/xVAnQMoBNgGDoDGCyV6An7JzMRwBSMtUMi63/nfAFaqlZR0varHAAAAAElFTkSuQmCC) no-repeat left center; padding-left: 20px;'),
		'sl.utf8' => array('value' => get_string('language.sl_SI', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAFaSURBVHjapJO7SgNBFIa/SXajMcaNJAZBhBRCxNJWaxvxAWwEO99BiJ0PYGEh2IuFnY1YWFnZiSKChWggJF5WY7LZ2ewem9wgGnL5m2EG/m/mnPOPEhFGkQIiwGRjHUQa+DEAS0SKQ92uVNoAYp2H+YJNueJgxWPMpuKokOrFiBlAuLmrvtns7V/ghSuYfoyD3BoTqUQvQDgE4Ps+ABIEZDNJdrbXyWaSSBD862x6DIDljWPSMxal109yW4ssLUzzcA0rm6dIpLu3ialxiiW7Dag5NZ6ehY9vze7hDeeXj1zdf/HOGGF0F6DseJiq3gZUaw4YJgqh4Jmc3TlABIXwVxGeH+DVnTbgpHjEvGkSOE5f4wtVo7x4HqutElwXMQxQqi+AiFBz3fYLXK2RaBRMsz9AEOBq3QJoX4Rb2x4mjFoBFjAHxAc0l4G8aiQx0pnIPuUDWo36nX8HAEkLg3WpiPuDAAAAAElFTkSuQmCC) no-repeat left center; padding-left: 20px;'),
		'fi.utf8' => array('value' => get_string('language.fi_FI', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAEbSURBVHjapJNBSsNAFIa/IWMk1BJdWBdddlvQteDGC3gTvYMb996kF/AGuYF0KOKmRWpaYZJ0puPCNolJS1L6YJjH+/n/ebz3j3DOcUwIwAfONvchkQE/Egidc9Mycv/wmudvo8f9rwvRk0CnCtwM+2276EjAq1bV5AuAFuPxJIC1Fs8rdOaxbmRaawGQAMPbZ3qXF8SLhPJWhBBc373UyOdhwHQ2LwS0Tnkfz4gXur4jV5scYTfgRK4KgURrnAhYr9t4wmGMwa6SvDJQSrlynF495WdfKKUcMPjrIEmq+23sY8uRAGma/gN932sU2HIkkBljiKIoB5ffH3leru+yswBCoA90D/wLS+BTbJzo73Jkk5eATBz7nX8HAMMWjMlbPf5vAAAAAElFTkSuQmCC) no-repeat left center; padding-left: 20px;'),
		'rs.utf8' => array('value' => get_string('language.rs_SR', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAGkSURBVHjapJPNaxNBGMZ/szPtFttkFdMgtEKCoRQsInjwIFVUvBR6Kj158tK/qpde0j+gePMDTeNFc/NmMRHrJaLpJu1+THd2PGTbDdocYh8YBmZ4Xn68z/sKay2XkQCmgbnsnkQaOFaA9+3xRjd/t9kBcMa6LZrK272yAmatMczcu5ObvAzG10D6F7MAawlbHwFmFSCtPiXtDzC/erBUwt6WYAziE/Dl59A0Inn9GmgHQCqAJI5RPR/T8zHfNeqmQViBPlTIo5N/+R2HJI4BUADrNzYpF+eR+GzfPcYZaIRS2PtXeP5eMRDuSF/gatGlW+oBr4YFwjDi4GsXVwfIZ2vIRgMEOKsP6ey+5OhUDvPK5BVcppIgJ4iiEBuDjAKC/QYz/m9smhI0myT9kDSdBpETJFhM0D+fg1vtdvugUqkA8PnpI7wnDwCL/+YDK6/fXRhjp9OhWq3WnCFBdP6xvFPHLdVw55dY3qmPnYMzjwKIs44CqMUFylsv8szHKB5JQSdJQqvV+p9V0ALwgAWgMKF5APwQgMwWSU5YwABaXHad/wwAxXWgtmMJh4EAAAAASUVORK5CYII=) no-repeat left center; padding-left: 20px;'),
		'sv.utf8' => array('value' => get_string('language.sv_SE', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAFeSURBVHjapJPLSsNQEIa/kxwaeiNKoRs3oq5b8BkEfRMF14WuXLjtC/RRFHwFFbfibSNioNjSS5KTNMdFDklaK7R0NnOYy39m/pkRWmu2EQGUgJrRm4gCJhJwT6/uvKLnptPL3me9zr8It9cnTQlU4yTh+KCRe+xW9mzt7y6WLEBreHgbAFQlYEexZuxHDMZhGpV8mHDN+/cEIRZ/btQdolgD2BJAKcXPVDGcKpM3zIJHM/WndMsSKKUyEg9nXvulXG6CHgF6ieMVUxI7+L5Hpfl0JAHCwKfsvBqAdWbnEgYSAAkQBD64AMmaE4wIgigHaHe72LUm0zBGAMP+uSkf3Is+SxxScSTziQdcYqUthOgELIRJtIwWmU0UtE7SnKwCFYU4WlOSlukxX8rMVpBEa1SUA6hkPmf09ZwF3D/mZBbtq9ZZkNK3B9Q3vIUx8CkA2xySvSHAHFBi23P+HQBzz4TV9Rc47AAAAABJRU5ErkJggg==) no-repeat left center; padding-left: 20px;'),
		'tr.utf8' => array('value' => get_string('language.tr_TR', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAHFSURBVHjapJOxbhNREEXP2/d2bWKvo0UKBS5SQpMPyAekSpnUQcoHICjgHyJTAAV0blNE6VJEgvyEI6jSYMlO5Ci2Y+zs7tvnHYpdsBAgYeU2U82de2fuKBHhPlBAANTLugwsMDXAan9nZyDWooLlOB4fHz8yQI35nMrGBsoYxDkkz1HGoDzvL5oViJB2OgA1A2jJMvLJhPlwiL++jt9s4vp9sm63aFDqNw4dRUiWAWgD4NIUPRiwsr1NfXeX+PSUfDZDXV/jrq6Kwb6/YPA8XJoCYACyJOHB2hqNvT1GBwdMDw9R9RDlG1a2trg7OwNjiqmlmixJFgRxHNMIAhBhenSEu7lB3d4SvXhJuL+PV6sz/vgBSRLQGq/RINZ6QZDEMdnFBShFdXOTSbuNF4YM37RAhNG7twsLeY44R2JtsQ/g4bMwfF7rfkNQRK9eo6MI3QghTZmdnKC0BucKAhE8z2NoLe3R6H2hIE1BG8atFnefPhM8fULW6+EuL1GVyh+XEJGi56eF1FqkWkWFIfbrF+x5B3z/n8GSPCctLRjAzkU4H4+Lab+Cav8rzgpYBZpAuOQvfAd6qlxkUNZlMAesuu87/xgAWtDAdYqEIUUAAAAASUVORK5CYII=) no-repeat left center; padding-left: 20px;'),
		'ua.utf8' => array('value' => get_string('language.ua_UK', 'artefact.survey'), 'style' => 'background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAEVSURBVHjapJNBTsQwDEVf2ggWBRU0EhsQGxAH4AxInIUTcRYkrgDsESsEG6RhphqYJk6asEiHKRWLdsaSlSxs/+9vW8UY2cYUsAPste8YE+BLA+X17f3HJuh3N1dHGih8CFyeTIZRVhAjPL5NAQoN5K6JLIxj+m3/BMdOn12bFLu4JgLkGkBEmC2FeS2DWGRKIZJiNUBthc+lUBlHdygruv9pX1v5ZXc2e714OSg9xGrg7Ermlebw9PlcAxhTQwkQBurvMMatWzDGQsg7BWJPul4fscEYn/RIBQyoblLW/vueJVchga4YWOsSuhq6jAFr11MQ7wMPT9UmyyiKJN8xsD8yeQG8KyBvDykfWaABRG17zj8DAM90dGbtg3QaAAAAAElFTkSuQmCC) no-repeat left center; padding-left: 20px;'),
	);
}


function return_bool($input) {
	$value = false;
	if (is_string($input) and (strtolower($input) == 'true'  or $input == '1')) {
		$value = true;
	}
	return $value;
}


function get_surveyhelpfile($plugintype, $pluginname, $surveyname, $question, $language='en.utf8') {
    $helpfile = get_config('docroot') . $plugintype . '/' . $pluginname;
	$helpfile .= '/surveys/' . $surveyname . '/' . $language . '/' . $question . '.html';
    if ($helpfile) {
        return file_get_contents(realpath($helpfile));
    }
    return false;
}


//---------------------------
// Helper function to recursively delete all files and sub-folders in selected folder (with the folder itself)
//---------------------------

function recursive_folder_delete($path) {
	$files = scandir($path);
	foreach ($files as $file) {
		if ($file != '.' && $file != '..') {
			if (is_dir($path . $file)) {
				recursive_folder_delete($path . $file . '/');
			} else {
				unlink($path . $file);
			}
		}
	}
	rmdir($path);
}


//---------------------------
// Helper functions for convertng MyLearning artefacts, blocktypes, etc. into Survey  artefacts, blocktypes, etc.
//---------------------------

function convert_learningstyles_to_survey($data) {
	foreach ($data as $key => $value) {
		$data[$key] = $value - 1;
	}
	return array(
		'V_q01' => $data['V01'], 'V_q02' => $data['V02'], 'V_q03' => $data['V03'], 'V_q04' => $data['V04'],
		'V_q05' => $data['V05'], 'V_q06' => $data['V06'], 'V_q07' => $data['V07'], 'V_q08' => $data['V08'],
		'V_q09' => $data['V09'], 'V_q10' => $data['V10'], 'V_q11' => $data['V11'],
		'A_q12' => $data['A01'], 'A_q13' => $data['A02'], 'A_q14' => $data['A03'], 'A_q15' => $data['A04'],
		'A_q16' => $data['A05'], 'A_q17' => $data['A06'], 'A_q18' => $data['A07'], 'A_q19' => $data['A08'],
		'A_q20' => $data['A09'], 'A_q21' => $data['A10'], 'A_q22' => $data['A11'],
		'K_q23' => $data['K01'], 'K_q24' => $data['K02'], 'K_q25' => $data['K03'], 'K_q26' => $data['K04'],
		'K_q27' => $data['K05'], 'K_q28' => $data['K06'], 'K_q29' => $data['K07'], 'K_q30' => $data['K08'],
		'K_q31' => $data['K09'], 'K_q32' => $data['K10'], 'K_q33' => $data['K11'],
	);
}

function convert_multipleintelligences_to_survey($data) {
	return array(
		'A_q01' => $data['A1'], 'A_q02' => $data['A2'], 'A_q03' => $data['A2'], 'A_q04' => $data['A4'],
		'B_q05' => $data['B1'], 'B_q06' => $data['B2'], 'B_q07' => $data['B3'], 'B_q08' => $data['B4'],
		'C_q09' => $data['C1'], 'C_q10' => $data['C2'], 'C_q11' => $data['C3'], 'C_q12' => $data['C4'],
		'D_q13' => $data['D1'], 'D_q14' => $data['D2'], 'D_q15' => $data['D3'], 'D_q16' => $data['D4'],
		'E_q17' => $data['E1'], 'E_q18' => $data['E2'], 'E_q19' => $data['E3'], 'E_q20' => $data['E4'],
		'F_q21' => $data['F1'], 'F_q22' => $data['F2'], 'F_q23' => $data['F3'], 'F_q24' => $data['F4'],
		'G_q25' => $data['G1'], 'G_q26' => $data['G2'], 'G_q27' => $data['G3'], 'G_q28' => $data['G4'],
		'H_q29' => $data['H1'], 'H_q30' => $data['H2'], 'H_q31' => $data['H3'], 'H_q32' => $data['H4'],
	);
}

function convert_artefacts_to_survey($type) {
	$artefacts = get_column('artefact', 'id', 'artefacttype', $type);
	foreach ($artefacts as $artefact) {
		$desc = unserialize(get_field('artefact', 'description', 'id', $artefact));
		if ($type == 'multipleintelligences') {
			$title = 'survey.multipleintelligences.xml';
			$description = convert_multipleintelligences_to_survey($desc);
		}
		if ($type == 'learningstyles') {
			$title = 'survey.learningstyles.xml';
			$description = convert_learningstyles_to_survey($desc);
		}
		$data = (object)array(
			'id'           => $artefact,
			'artefacttype' => 'survey',
			'title'        => $title,
			'description'  => serialize($description),
			'mtime'        => db_format_timestamp(time()),
		);
		update_record('artefact', $data, 'id');
	}
}
			
function convert_blocks_used_in_views($type) {
	$instances = get_column('block_instance', 'id', 'blocktype', $type);
	foreach ($instances as $instance) {
		$oldconfig = unserialize(get_field('block_instance', 'configdata', 'id', $instance));
		$configdata = array(
			'includetitle'  => 1,
			'artefactid'    => $oldconfig['artefactids'],
			'showresponses' => null,
			'showresults'   => 1,
			'showchart'     => 1,
			'palette'       => 'default',
			'legend'        => 'key',
			'fonttype'      => 'sans',
			'fontsize'      => 10,
			'height'        => 250,
			'width'         => 400,
		);
		if ($type == 'multipleintelligences') {	$filename = 'survey.multipleintelligences.xml'; }
		if ($type == 'learningstyles') { $filename = 'survey.learningstyles.xml'; }
		$title = get_field('artefact', 'title', 'id', $oldconfig['artefactids']);
		$data = (object)array(
			'id'         => $instance,
			'blocktype'  => 'survey',
			'title'      => get_string('Survey', 'artefact.survey') . ': ' . ArtefactTypeSurvey::get_survey_title_from_xml($filename),
			'configdata' => serialize($configdata),
		);
		update_record('block_instance', $data, 'id');
	}
}

function install_survey_blocktype() {
	require_once('upgrade.php');
	require_once(get_config('docroot') . 'artefact/survey/blocktype/survey/version.php');

	$data = (object)array(
		'name'           => 'survey',
		'version'        => $config->version,
		'release'        => $config->release,
		'active'         => 1,
		'artefactplugin' => 'survey',
	);
	insert_record('blocktype_installed', $data);

	install_blocktype_categories_for_plugin('survey');
	install_blocktype_viewtypes_for_plugin('survey');
}

?>
