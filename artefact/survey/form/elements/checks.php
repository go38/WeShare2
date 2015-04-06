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
 * Based on Nigel McNie's select Pieform element.
 *
 * Renders a group of checkboxes for multiple answer type questions in surveys.
 *
 * @todo Currently, putting a junk defaultvalue/value for a multiple select
 * does not trigger any kind of error, it should perhaps trigger a
 * Pieform::info
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_checks(Pieform $form, $element) {/*{{{*/
    $element['name'] .= '[]';

	$result = '';

    $separator = "\n";
    if (isset($element['separator'])) {
        $separator = $element['separator'] . $separator;
    }

    $optionsavailable = true;
    if (!isset($element['options']) || !is_array($element['options']) || count($element['options']) < 1) {
        $optionsavailable = false;
        Pieform::info('Group of checkboxes should have at least one checkbox');
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

    $values = $form->get_value($element); 
    $optionselected = false;
    foreach ($element['options'] as $key => $value) {
        // Select the element if it's in the values or if there are no values
        // and this is the first option
        if (
            (!is_array($values) && $key == $values)
            ||
            (is_array($values) && 
                (array_key_exists($key, $values)
                || (isset($values[0]) && $values[0] === null && !$optionselected)))) {
            $selected = ' checked="checked"';
            $optionselected = true;
        }
        else {
            $selected = '';
        }

        // Disable the option if necessary
        if (is_array($value) && !empty($value['disabled'])) {
            $disabled = ' disabled="disabled"';
        }
        else {
            $disabled = '';
        }

        // Add a label if necessary. None of the common browsers actually render
        // this properly at the moment, but that may change in future.
        if (is_array($value) && isset($value['label'])) {
            $label = ' label="' . Pieform::hsc($value['label']) . '"';
        }
        else {
            $label = '';
        }

        // Get the value to display/put in the value attribute
        if (is_array($value)) {
            if (!isset($value['value'])) {
                Pieform::info('No value set for option "' . $key . '" of checkboxes element "' . $element['name'] . '"');
                $value = '';
            }
            else {
                $value = $value['value'];
            }
        }

        $result .= "<input type=\"checkbox\" name=\"" . $element['id'] . "[" . Pieform::hsc($key) . "]\"{$selected}{$label}{$disabled}>&nbsp;" . Pieform::hsc($value) . "\n";
		$result .= $separator;
    }

    if (!$optionselected && !is_array($values) && $values !== null) {
        Pieform::info('Invalid value for checkboxes "' . $element['name'] .'"');
    }

    $result = substr($result, 0, -strlen($separator));
	
    return $result;
}/*}}}*/

function pieform_element_checks_set_attributes($element) {/*{{{*/
    if (!isset($element['collapseifoneoption'])) {
        $element['collapseifoneoption'] = true;
    }
    //$element['rules']['validateoptions'] = true;
    return $element;
}/*}}}*/

function pieform_element_checks_get_value(Pieform $form, $element) {/*{{{*/
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    if (isset($element['value'])) {
        $values = (array) $element['value'];
    }
    else if ($form->is_submitted() && isset($global[$element['name']])) {
        $values = (array) $global[$element['name']];
    }
    else if (!$form->is_submitted() && isset($element['defaultvalue'])) {
        $values = (array) $element['defaultvalue'];
    }
    else {
        $values = array();
    }

    return $values;
}/*}}}*/

?>
