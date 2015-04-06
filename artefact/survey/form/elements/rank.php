<?php
/**
 * Pieforms: Advanced web forms made easy
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @package    pieform
 * @subpackage element
 * @author     Gregor Anzelj <gregor.anzelj@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2012 Gregor Anzelj
 *
 */

/**
 * Renders a drag & drop sortable list using Javascript and CSS.
 * Useful for ranking answers to a question.
 *
 * Based on ToolMan DHTML Library. See: http://tool-man.org/examples/sorting.html
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_rank(Pieform $form, $element) {/*{{{*/
    $optionsavailable = true;
    if (!isset($element['options']) || !is_array($element['options']) || count($element['options']) < 1) {
        $optionsavailable = false;
        Pieform::info('Rank elements should have at least one option');
    }

    if (!empty($element['collapseifoneoption']) && isset($element['options']) && is_array($element['options']) && count($element['options']) == 1) {
        foreach ($element['options'] as $key => $value) {
            if (is_array($value)) {
                $value = $value['value'];
            }
            $result = Pieform::hsc($value) . '<input type="hidden" name="' . Pieform::hsc($element['name']) . '" value="' . Pieform::hsc($key) . '">';
        }
        return $result;
    }

    $result = '<ul'
        . $form->element_attributes($element)
        . ">\n";
    if (!$optionsavailable) {
        $result .= "\t<li>&nbsp;</li>\n</ul>";
        return $result;
    }

	// Values are serialized like: a|b|c|d
	// so we need to unserialze them...
    $values = explode('|', $form->get_value($element)); 
    $optionselected = false;
    foreach ($element['options'] as $key => $value) {
        // Get the value to display/put in the value attribute
        if (is_array($value)) {
            if (!isset($value['value'])) {
                Pieform::info('No value set for option "' . $key . '" of rank element "' . $element['name'] . '"');
                $value = '';
            }
            else {
                $value = $value['value'];
            }
        }

        $result .= "\t<li itemID=\"" . Pieform::hsc($key) . "\">" . Pieform::hsc($value) . "</li>\n";
    }

    if (!$optionselected && !is_array($values) && $values !== null) {
        Pieform::info('Invalid value for rank "' . $element['name'] .'"');
    }

    $result .= '</ul>';
    return $result;
}/*}}}*/

function pieform_element_rank_set_attributes($element) {/*{{{*/
    if (!isset($element['collapseifoneoption'])) {
        $element['collapseifoneoption'] = true;
    }
    return $element;
}/*}}}*/

function pieform_element_rank_get_value(Pieform $form, $element) {/*{{{*/
	// Ckeck if cookie exists and return it's value...
	if (array_key_exists('list-questionnaire_' . $element['name'], $_COOKIE)) {
		return $_COOKIE['list-questionnaire_' . $element['name']];
	} else {
		return null;
	}
}/*}}}*/

/**
 * Returns code to go in <head> for the given color selector instance
 *
 * @param array $element The element to get <head> code for
 * @return array         An array of HTML elements to go in the <head>
 */
//function pieform_element_rank_get_headdata($element) {/*{{{*/
	//log_debug($element['defaultvalue']);
	//log_debug($element['name']);
/*
	$id = $element['id'];
	$jsinit = <<<EOF
<script type="text/javascript"><!--
	var dragsort = ToolMan.dragsort()
	var junkdrawer = ToolMan.junkdrawer()

	window.onload = function() {
		junkdrawer.restoreListOrder("questionnaire_$id")
		dragsort.makeListSortable(document.getElementById("questionnaire_$id"), verticalOnly, saveOrder)
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
	// Set cookie (in case it doesn't exist already)
	if (isset($element['defaultvalue']) && !empty($element['defaultvalue'])) {
		$expires = 365 * 24 * 60 * 60 * 1000; // Turn days into miliseconds
		$jsinit .= 'ToolMan.cookies().set("list-questionnaire_' . $element['name'] . '", "' . $element['defaultvalue'] . '", ' . $expires . ')</script>';
	} else {
		$jsinit .= '</script>';
	}

	// Maybe later (if this Pieform element is merged with master code) the path should change to:
	// $libpath = get_config('wwwroot')  . 'js/rank/';
    $libpath = get_config('wwwroot')  . 'artefact/survey/js/rank/';
    $result = array(
		'<link rel="stylesheet" type="text/css" href="' . $libpath . 'rank.css">',
        '<script type="text/javascript" src="' . $libpath . 'core.js"></script>',
        '<script type="text/javascript" src="' . $libpath . 'events.js"></script>',
        '<script type="text/javascript" src="' . $libpath . 'css.js"></script>',
        '<script type="text/javascript" src="' . $libpath . 'coordinates.js"></script>',
        '<script type="text/javascript" src="' . $libpath . 'drag.js"></script>',
        '<script type="text/javascript" src="' . $libpath . 'dragsort.js"></script>',
        '<script type="text/javascript" src="' . $libpath . 'cookies.js"></script>',
		//$jsinit
    );
	// Please note that additional code needs to be inserted into <head>, to link javascript
	// with HTML lists, so that they become drag & drop sortable lists...
	// This code needs to be set up and inserted in the page, that will render such lists.
	// See example at: htdocs/artefact/survey/edit.php
    return $result;
*/
//}/*}}}*/
