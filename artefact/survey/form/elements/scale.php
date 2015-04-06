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
 * Renders a set of radio buttons for scale type questions in surveys.
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */

 function pieform_element_scale(Pieform $form, $element) {/*{{{*/
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    $submitted = $form->is_submitted();
    if ($submitted && isset($global[$element['name']])) {
        $value = $global[$element['name']];
    }

    $result = '';

    $separator = null;
    //$separator = '';
    if (isset($element['separator'])) {
        $separator = $element['separator'];
    }
	
	$length = 17;
    if (isset($element['length'])) {
        $length = $element['length'];
    }

    if (!isset($element['options'])) {
        $steps = 5;
        if (isset($element['steps'])) {
            if ($element['steps'] < 2) {
                $steps = 2;
            } elseif ($element['steps'] > 10) {
                $steps = 10;
            } else {
                $steps = $element['steps'];
            }
        }
        $element['options'] = array();
        for ($i=0; $i<$steps; $i++) {
            $element['options'] = array_merge($element['options'], array(array('value' => strval($i), 'title' => '')));
        }
    }

    // If needed, set answers in reverse order
    if (isset($element['reverse']) && $element['reverse'] == 'true') {
        $element['options'] = array_reverse($element['options']);
    }

    //$result .= "<span style='display:block-inline'>";
    $result .= "<table width='100%' border='0' cellpadding='0' cellspacing='0'><tr>";
	$result .= "<td width='45%' valign='top' align='right'>";
    if (isset($element['labelleft'])) {
        $result .= "<strong>" . $element['labelleft'] . "</strong>";
    }
    if (isset($element['titleleft'])) {
        $result .= $element['titleleft'];
    }
	$result .= "</td>";

	$result .= "<td width='10%' valign='top' align='center'>";
	$result .= "<div style='white-space:nowrap;'>";
    foreach ($element['options'] as $e) {
        $checked = ($form->get_value($element) === $e['value'] && !is_null($form->get_value($element)));
		if ($e['value'] == null) $checked = false;
        $result .= '<input type="radio" value="' . $e['value'] . '" '
        . $form->element_attributes($element)
        . ($checked ? ' checked="checked"' : '')
        . ' style="vertical-align:top">' . Pieform::hsc(str_shorten_text($e['title'], $length, true));
		$result .= $separator;
    }
	// Strip the separator after the last option...
    //$result = substr($result, 0, -strlen($separator));
	$result .= "</div>";
	$result .= "</td>";

	$result .= "<td width='45%' valign='top' align='left'>";
    if (isset($element['titleright'])) {
        $result .= $element['titleright'];
    }
    if (isset($element['labelright'])) {
        $result .= "<strong>" . $element['labelright'] . "</strong>";
    }
	$result .= "</td>";
	$result .= "</tr></table>";
    //$result .= "</span>";

    $result .= '<div class="cl"></div>';

    return $result;
}/*}}}*/

function pieform_element_scale_get_value(Pieform $form, $element) {/*{{{*/
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
