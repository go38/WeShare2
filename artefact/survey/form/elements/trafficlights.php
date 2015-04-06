<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Pieforms: Advanced web forms made easy
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
 * @package    pieform
 * @subpackage element
 * @author     Gregor Anzelj <gregor.anzelj@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2011 Gregor Anzelj
 *
 */

/**
 * Renders a set (red, yellow, green) of radio buttons with corresponding background colors as in traffic lights
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */

 function pieform_element_trafficlights(Pieform $form, $element) {/*{{{*/
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    $submitted = $form->is_submitted();
    if ($submitted && isset($global[$element['name']])) {
        $value = $global[$element['name']];
    }

    $result = '';

    if (!isset($element['options'])) {
        $element['options'] = array(
			array('value' => '0', 'title' => 'r'),
			array('value' => '1', 'title' => 'y'),
			array('value' => '2', 'title' => 'g'),
		);
    }

    $result .= "<span style='display:block;border:0px;padding-bottom:4px;width:70px !important;'>";
    $colors = array(
		'r' => '#FF8080',
		'y' => '#FFEB80',
		'g' => '#80B280',
    );
	$borders = array(
		'r' => 'border-top:1px solid #000000;border-left:1px solid #000000;border-bottom:1px solid #000000;',
		'y' => 'border-top:1px solid #000000;border-bottom:1px solid #000000',
		'g' => 'border-top:1px solid #000000;border-right:1px solid #000000;border-bottom:1px solid #000000;',
	);

    foreach ($element['options'] as $e) {
        $checked = ($form->get_value($element) === $e['value'] && !is_null($form->get_value($element)));
		if ($e['value'] == null) $checked = false;
        $result .= '<span style="' . $borders[$e['title']] . ';padding-bottom:4px;background-color:' . $colors[$e['title']] . '"><input type="radio" value="' . $e['value'] . '" '
        . $form->element_attributes($element)
        . ($checked ? ' checked="checked"' : '')
        . ' style="vertical-align:top"></span>';
    }
    $result .= "</span>";

    $result .= '<div class="cl"></div>';

    return $result;
}/*}}}*/

function pieform_element_trafficlights_get_value(Pieform $form, $element) {/*{{{*/
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    if (isset($element['value'])) {
        $value = $element['value'];
    }
    else if ($form->is_submitted() && isset($global[$element['name']])) {
        $value = $global[$element['name']];
    }
    else if (!$form->is_submitted() && isset($element['defaultvalue'])) {
        $value = $element['defaultvalue'];
    }
    else {
        $value = null;
    }

    return $value;
}/*}}}*/
