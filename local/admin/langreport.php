<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
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
 * @package    mahara
 * @subpackage admin/lang
 * @author     David Mudrak <david.mudrak@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'adminlang/reports');

require(dirname(dirname(__FILE__)).'/init.php');
require(get_config('libroot') . 'lang.php');

$report = param_variable('report', 'missing');
$selected_lang =  param_variable('lang', '');

$lang_options = get_languages();
if (!empty($selected_lang) && array_key_exists($selected_lang, $lang_options)) {
    $USER->set_account_preference('lang', $selected_lang);
}
$smarty = smarty();

$available_reports = array(
    'missing' => get_string('langmissingstrings', 'admin'),
);
$smarty->assign('available_reports', $available_reports);

$js = <<<EOJS
    /**
     * Add class "hidden" to the given element
     * 
     * @param string id Element ID
     */
    function hide_element(id) {
        var e = $(id);  // element

        e.className = e.className + ' hidden';
    }

EOJS;
$smarty->assign('INLINEJAVASCRIPT', $js);

switch ($report) {
case 'missing':
    $smarty->assign('reportid', 'missing');

    $DOCROOT = get_config('docroot');
    while (substr($DOCROOT, -1) == '/') {
        $DOCROOT = substr_replace($DOCROOT, '', -1);
    }

    define('TITLE', get_string('langreports', 'admin'));

    // Get list of lang packs - try cached first
    $lang_dirs = $SESSION->get('admin_lang_directories');
    if (empty($lang_dirs)) {
        // no cache found - rebuild the list
        try {
            $lang_locations = array('lang');                                 // core language files
            $lang_locations = array_merge($lang_locations, plugin_types());  // plugin language files
            $lang_dirs = get_lang_directories($lang_locations);
            $SESSION->set('admin_lang_directories', $lang_dirs);
        }
        catch (Exception $e) {
            log_info($e->getMessage());
            log_info($e->getTrace());
            $SESSION->add_error_msg("An error occurred while getting locations of lang packs: " . $e->getMessage());
        }
    }
    // print_object($lang_dirs); die(); // XXX debug

    $lang_files = array();
    foreach ($lang_dirs as $lang_dir) {
        $files = get_directory_list(implode('/', array($DOCROOT, $lang_dir, 'lang', 'en.utf8')));
        foreach($files as $file) {
            if ((substr($file, -4) == ".php") && $file !== 'langconfig.php') {
                if (empty($lang_dir)) {
                    $lang_files[implode('/', array('lang', 'en.utf8', $file))] = $file;
                } else {
                    $lang_files[implode('/', array($lang_dir, 'lang', 'en.utf8', $file))] = $file;
                }
            }
        }
    }
    // print_object($lang_files); die(); // XXX debug

    $smarty->assign_by_ref('lang_files', $lang_files);
    //$smarty->assign('current_language', current_language());

    // create 2-D array of missing strings indexed by file path and stringid
    $lang_strings = array();
    $counter_files = 0;
    $counter_strings = 0;
    $counter_all_strings = 0;
    foreach ($lang_files as $current_file => $current_filename) {
        unset($string);
        $enstrings = language_load_strings('en.utf8', $current_file);
        $trstrings = language_load_strings(current_language(), $current_file);
        $lang_strings[$current_file] = array();
        foreach ($enstrings as $stringid => $original) {
            $counter_all_strings++;
            if (empty($trstrings[$stringid])) {
                $lang_strings[$current_file][$stringid] = language_fix_value_before_save($original);
                $counter_strings++;
            }
        }
        if (empty($lang_strings[$current_file])) {
            // no missing string in this file
            unset ($lang_strings[$current_file]);
        } else {
            $counter_files++;
        }
    }
    // print_object($lang_strings); die(); // XXX debug
    $smarty->assign_by_ref('lang_strings', $lang_strings);
    $smarty->assign('lang_options', $lang_options);
    $smarty->assign('current_language', current_language());
    $smarty->assign('count_files', $counter_files); // not used yet
    $smarty->assign('count_strings', $counter_strings); // not used yet
    $untranslated_ratio = sprintf("%3.1f", $counter_strings/$counter_all_strings*100);
    $SESSION->add_info_msg("Found $counter_strings of $counter_all_strings ($untranslated_ratio%) missing strings in $counter_files files"); // TODO translate
    break; // end of "missing" report

}
$smarty->display('admin/lang/langreport.tpl');

?>
