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
define('MENUITEM', 'adminlang/translate');

require(dirname(dirname(__FILE__)).'/init.php');
require(get_config('libroot') . 'lang.php');

$current_file = param_variable('file', '');
$strings = param_variable('strings', null); // strings to save at once
$selected_lang =  param_variable('lang', '');

$lang_options = get_languages();
if (!empty($selected_lang) && array_key_exists($selected_lang, $lang_options)) {
    $USER->set_account_preference('lang', $selected_lang);
}

if ($strings) {
    // save all strings at once
    try {
        language_save_string(current_language(), $current_file, '', '', $strings);
        $SESSION->add_info_msg('Strings saved');    // todo translate
    }
    catch (LanguageException $e) {
        $SESSION->add_error_msg('Error saving file'); // todo translate
    }
    redirect(get_config('wwwroot') . 'admin/lang.php?file='.urlencode($current_file));
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

$lang_files = array(''=> get_string('strchooselangfile', 'admin'));
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
$smarty->assign('lang_files', $lang_files);
$smarty->assign('lang_options', $lang_options);
$smarty->assign('current_language', current_language());
$smarty->assign('current_file', $current_file);
$smarty->assign('save_all_handler', get_config('wwwroot') . 'admin/lang.php?file='.urlencode($current_file));

$lang_strings = array();
$onload_focus = '';
if (!empty($current_file)) {
    unset($string);
    include(implode('/', array($DOCROOT, $current_file)));
    $enstrings = language_load_strings('en.utf8', $current_file);
    if (is_array($enstrings)) {
        $trstrings = language_load_strings(current_language(), $current_file);
        $SESSION->add_info_msg('Loaded '.$current_file . ' ('.count($enstrings).' strings)'); // todo translate
        foreach ($enstrings as $stringid => $original) {
            $lang_strings[$stringid] = new stdClass();
            $lang_strings[$stringid]->id = $stringid;
            $lang_strings[$stringid]->elementid = str_replace('/', '::', $stringid);
            $lang_strings[$stringid]->original = $original;
            if (! empty($trstrings[$stringid])) {
                $lang_strings[$stringid]->translated = $trstrings[$stringid];
            } else {
                $lang_strings[$stringid]->translated = '';
                if (empty($onload_focus)) {
                    $onload_focus = "$('text_$stringid').focus()";
                }
            }
            $lang_strings[$stringid]->status = '';
        }
    }
} else {
    $smarty->assign('current_file', '');
}
$smarty->assign_by_ref('lang_strings', $lang_strings);
$smarty->assign('BODYONLOAD', $onload_focus);

$smarty->display('admin/lang/lang.tpl');

?>
