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
define('MENUITEM', 'adminlang/translatehelp');

require(dirname(dirname(__FILE__)).'/init.php');
require(get_config('libroot') . 'lang.php');

$current_help_file = param_variable('helpfile', '');
$help_string = param_variable('helpstring', null); // help string to save
$selected_lang =  param_variable('lang', '');

$lang_options = get_languages();
if (!empty($selected_lang) && array_key_exists($selected_lang, $lang_options)) {
    $USER->set_account_preference('lang', $selected_lang);
}

if ($help_string) {
    // save help string
    try {
        language_save_help_string(current_language(), $current_help_file, '', '', $help_string);
        $SESSION->add_info_msg('Help string saved');    // todo translate
    }
    catch (LanguageException $e) {
        $SESSION->add_error_msg('Error saving help file'); // todo translate
    }
    redirect(get_config('wwwroot') . 'admin/langhelp.php?helpfile='.urlencode($current_help_file));
}

$DOCROOT = get_config('docroot');
while (substr($DOCROOT, -1) == '/') {
    $DOCROOT = substr_replace($DOCROOT, '', -1);
}

define('TITLE', get_string('administration', 'admin'));

$jsstrings = array(
    'admin' => array ('stringsaving', 'stringunsaved'),
    );

$smarty = smarty(array('js/lang/lang.js'), array(), $jsstrings);

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

$help_files = array(''=> get_string('strchoosehelpfile', 'admin'));
foreach ($lang_dirs as $lang_dir) {
    $files = get_directory_list(implode('/', array($DOCROOT, $lang_dir, 'lang', 'en.utf8')));
    foreach($files as $file) {
        if ((substr($file, -5) == ".html")) {
            if (empty($lang_dir)) {
                $help_files[implode('/', array('lang', 'en.utf8', $file))] = $file;
            } else {
                $help_files[implode('/', array($lang_dir, 'lang', 'en.utf8', $file))] = implode('/', array($lang_dir, 'lang', 'en.utf8', $file));
            }
        }
    }
}

$smarty->assign('help_files', $help_files);
$smarty->assign('lang_options', $lang_options);
$smarty->assign('current_language', current_language());
$smarty->assign('current_help_file', $current_help_file);
$smarty->assign('save_all_help_handler', get_config('wwwroot') . 'admin/langhelp.php?helpfile='.urlencode($current_help_file));

$lang_help_strings = array();
$onload_focus = '';
if (!empty($current_help_file)) {
    unset($string);
    $en_help_html_string = language_load_help_string('en.utf8', $current_help_file);
    if($en_help_html_string) {
        $tr_help_html_string = language_load_help_string(current_language(), $current_help_file);
        $stringid = $current_help_file;
        $lang_help_strings[$stringid] = new stdClass();
        $lang_help_strings[$stringid]->id = $stringid;
        $lang_help_strings[$stringid]->elementid = str_replace('/', '::', $stringid);
        $lang_help_strings[$stringid]->original = $en_help_html_string;
        if (!empty($tr_help_html_string)) {
            $lang_help_strings[$stringid]->translated = $tr_help_html_string;
        } else {
            $lang_help_strings[$stringid]->translated = '';
            if (empty($onload_focus)) {
                $onload_focus = "$('text_$stringid').focus()";
            }
        }
        $lang_help_strings[$stringid]->status = '';
    }
} else {
    $smarty->assign('current_help_file', '');
}

//log_debug($lang_help_strings);
$smarty->assign_by_ref('lang_help_strings', $lang_help_strings);
$smarty->assign('BODYONLOAD', $onload_focus);

$smarty->display('admin/lang/langhelp.tpl');

?>
