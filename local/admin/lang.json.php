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
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require(get_config('libroot') . 'lang.php');

$action = param_variable('action', '');

switch ($action) {
case 'save':
    $current_language = current_language();
    $current_file = param_variable('file');
    $stringid = param_variable('stringid');
    $text = param_variable('text');

    try {
        $savedtext = language_save_string($current_language, $current_file, $stringid, $text);
        $returndata = array(
            'id' => $stringid,
            'savedtext' => $savedtext,
            'error' => false,
            'message' => get_string('stringsaved','admin'),
        );
        json_reply(false, $returndata);
    }
    catch (LanguageException $e) {
        $returndata = array(
            'error' =>true,
            'errormessage' =>  get_string($e->getMessage(), 'admin'),
        );
        json_reply('local', $returndata, LANG_ESAVESTRING);
    }
    break;
case 'savehelp':
    $current_language = current_language();
    $current_help_file = param_variable('helpfile');
    $stringid = param_variable('stringid');
    $text = param_variable('text');

    try {
        $savedtext = language_save_help_string($current_language, $current_help_file, $stringid, $text);
        $returndata = array(
            'id' => $stringid,
            'savedtext' => $savedtext,
            'error' => false,
            'message' => get_string('stringsaved', 'admin'),
        );
        json_reply(false, $returndata);
    }
    catch (LanguageException $e) {
        $returndata = array(
            'error' =>true,
            'errormessage' =>  get_string($e->getMessage(), 'admin'),
        );
        json_reply('local', $returndata, LANG_ESAVESTRING);
    }
    break;
case '':
default:
    $returndata = array(
        'error' => true,
        'errormessage' => get_string('langerrormissingaction', 'admin'),
    );
    json_reply('local', $returndata, LANG_EMISSINGACTION);
    break;
}

?>
