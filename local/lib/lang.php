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
 * @subpackage core
 * @author     David Mudrak <david.mudrak@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 */

defined('INTERNAL') || die();

/**
 * Language error codes
 */
define('LANG_EMISSINGACTION', 1);
define('LANG_ESAVESTRING', 2);

/**
 * Class to use for language related exceptions
 */
class LanguageException extends SystemException {}


/**
 * Return an array of all directories containing "lang/en.utf8" withing Mahara installation
 *
 * Directories are relative to Mahara docroot. The returned array looks like:
 *   Array
 *      (
 *          [0] => 
 *          [1] => artefact/blog/blocktype/blog
 *          [2] => artefact/blog/blocktype/blogpost
 *          [3] => artefact/blog/blocktype/recentposts
 *          [4] => artefact/blog
 *          ...
 *      )
 * The first empty record represents 'lang/en.utf8' in the Mahara root installation.
 *
 * @param string $basedir Starting point relative to the Mahara docroot
 * @param array $exclude Directory names to exclude from searching (CVS and hidden are excluded automatically)
 * @throws LanguageException
 * @return array Array of strings
 * @todo Move this function into more general library
 */
function get_lang_directories($basedirs='', $exclude='') {
    
    static $dirs;

    if (empty($dirs)) {
        $dirs = array();
    }

    if (!is_array($basedirs)) {
        $basedirs = array('');
    }

    foreach ($basedirs as $basedir) {
        while (substr($basedir, 0, 1) == '/') {
            $basedir = substr_replace($basedir, '', 0, 1);
        }
        while (substr($basedir, strlen($basedir) - 1) == '/') {
            $basedir = substr_replace($basedir, '', -1);
        }
        $rootdir = get_config('docroot');
        while (substr($rootdir, strlen($rootdir) - 1) == '/') {
            $rootdir = substr_replace($rootdir, '', -1);
        }
        $rootdir = $rootdir .'/'. $basedir;

        if (!is_dir($rootdir)) {
            throw new LanguageException("Not a directory: $rootdir");
        }

        if (!$dir = opendir($rootdir)) {
            throw new LanguageException("Can't open $rootdir"); 
        }

        if (!is_array($exclude)) {
            $exclude = array($exclude);
        }

        while (false !== ($file = readdir($dir))) {
            $firstchar = substr($file, 0, 1);
            if ($firstchar == '.' or $file == 'CVS' or in_array($file, $exclude)) {
                continue;
            }
            $fullfile = $rootdir .'/'. $file;
            if (filetype($fullfile) == 'dir') {
                if ($file == 'en.utf8') {
                    $bits = $basedir .'/'. $file;
                    $bits = explode('/', $bits);
                    if ('en.utf8' == array_pop($bits)) {
                        if ('lang' == array_pop($bits)) {
                            $bits = implode('/', $bits);
                            $dirs[] = $bits;
                        }
                    }
                }
                // descend into subdirectories
                get_lang_directories(array($basedir .'/'. $file), $exclude);
            }
        }
        closedir($dir);
    }
    return $dirs;
}


/**
 * Returns an array with all the filenames in all subdirectories, relative to the given rootdir
 *
 * If excludefile is defined, then that file/directory is ignored
 * If getdirs is true, then (sub)directories are included in the output
 * If getfiles is true, then files are included in the output
 * (at least one of these must be true!)
 *
 * @param string $rootdir Starting point
 * @param string $excludefile  If defined then the specified file/directory is ignored
 * @param bool $descend Shall I dive into subdirectories recursively?
 * @param bool $getdirs If true then (sub)directories are included in the output
 * @param bool $getfiles If true then files are included in the output
 * @return array An array with all the filenames in all subdirectories, relative to the given rootdir
 * @todo Move this function into more general library
 */
function get_directory_list($rootdir, $excludefiles='', $descend=true, $getdirs=false, $getfiles=true) {

    $dirs = array();

    if (!$getdirs and !$getfiles) {   // Nothing to show
        return $dirs;
    }

    if (!is_dir($rootdir)) {          // Must be a directory
        return $dirs;
    }

    if (!$dir = opendir($rootdir)) {  // Can't open it for some reason
        return $dirs;
    }

    if (!is_array($excludefiles)) {
        $excludefiles = array($excludefiles);
    }

    while (false !== ($file = readdir($dir))) {
        $firstchar = substr($file, 0, 1);
        if ($firstchar == '.' or $file == 'CVS' or in_array($file, $excludefiles)) {
            continue;
        }
        $fullfile = $rootdir .'/'. $file;
        if (filetype($fullfile) == 'dir') {
            if ($getdirs) {
                $dirs[] = $file;
            }
            if ($descend) {
                $subdirs = get_directory_list($fullfile, $excludefiles, $descend, $getdirs, $getfiles);
                foreach ($subdirs as $subdir) {
                    $dirs[] = $file .'/'. $subdir;
                }
            }
        } else if ($getfiles) {
            $dirs[] = $file;
        }
    }
    closedir($dir);

    asort($dirs);

    return $dirs;
}


/**
 * Creates directory structure
 * 
 * @param mixed $fullpath Unix-style full path
 * @return bool True if success, false otherwise
 * @todo Move to the appropriate library, make it throws an exception
 */
function create_dir_structure($fullpath) {

    if (!file_exists($fullpath)) {
        $_open_basedir_ini = ini_get('open_basedir');
        $_dir = $fullpath;
        $_dir_parts = preg_split('!/+!', $_dir, -1, PREG_SPLIT_NO_EMPTY);
        $_new_dir = (substr($_dir, 0, 1)=='/') ? '/' : getcwd().'/';
        if($_use_open_basedir = !empty($_open_basedir_ini)) {
            $_open_basedirs = explode(':', $_open_basedir_ini);
        }

        foreach ($_dir_parts as $_dir_part) {
            $_new_dir .= $_dir_part;

            if ($_use_open_basedir) {
                // do not attempt to test or make directories outside of open_basedir
                $_make_new_dir = false;
                foreach ($_open_basedirs as $_open_basedir) {
                    if (substr($_new_dir, 0, strlen($_open_basedir)) == $_open_basedir) {
                        $_make_new_dir = true;
                        break;
                    }
                }
            } else {
                $_make_new_dir = true;
            }

            if ($_make_new_dir && !file_exists($_new_dir) && !@mkdir($_new_dir, get_config('directorypermissions')) && !is_dir($_new_dir)) {
                return false;
            }
            $_new_dir .= '/';
        }
    }
    return true;
}

/**
 * Return array of strings defined in the given file
 *
 * Given the language code and the file path to the English language file,
 * the function returns the content of the copy of the $file in the $lang package
 * 
 * @param mixed $file Path to the English file relative to the language root
 * @return array $string array defined in the $file or empty array
 */
function language_load_strings($lang, $file) {

    $return = array();
    $fullpath = get_language_root($lang) . str_replace('en.utf8', $lang, $file);
    if (file_exists($fullpath)) {
        include($fullpath);
        if (!empty($string)) {
            $return = $string;
        }
    }
    ksort($return);
    return $return;
}


/**
 * Return string of help file html text
 *
 * Given the language code and the file path to the English language help file,
 * the function returns the content of the copy of the $file in the $lang package
 * 
 * @param mixed $file Path to the English file relative to the language root
 * @return $string help file html text
 */
function language_load_help_string($lang, $file) {

    $return = '';
    $fullpath = get_language_root($lang) . str_replace('en.utf8', $lang, $file);
    if (file_exists($fullpath)) {
        $string = file_get_contents($fullpath);
        if (!empty($string)) {
            $return = $string;
        }
    }
    return $return;
}


/**
 * Fix value of the translated string before it is saved into the file
 *
 * @param string $value Raw string to be saved into the lang pack
 * @return string Fixed value
 */
function language_fix_value_before_save($value, $lang='') {

    if ($lang != "zh_hk" && $lang != "zh_tw") {         // Some MB languages include backslash bytes
        $value = str_replace("\\","",$value);           // Delete all slashes
    }
    if (ini_get_bool('magic_quotes_sybase')) {          // Unescape escaped sybase quotes
        $value = str_replace("''", "'", $value);
    }
    $value = str_replace("'", "\\'", $value);           // Add slashes for '
    //$value = str_replace('"', "\\\"", $value);          // Add slashes for "
    //$value = str_replace("%","%%",$value);              // Escape % characters
    $value = str_replace("\r", "",$value);              // Remove linefeed characters
    $value = trim($value);                              // Delete leading/trailing white space
    return $value;
}


/**
 * Fix value of the translated string after it is loaded from the file.
 *
 * These modifications are typically necessary to work with the same string coming from two sources.
 * We need to compare the content of these sources and we want to have e.g. "This string\r\n"
 * to be the same as " This string\n".
 *
 * @param string $value Original string from the file
 * @return string Fixed value
 */
function language_fix_value_from_file($value='') {

    $value = str_replace("\r","",$value);              // Bad character caused by Windows
    $value = preg_replace("/\n{3,}/", "\n\n", $value); // Collapse runs of blank lines
    $value = trim($value);                             // Delete leading/trailing white space
    $value = str_replace("\\","",$value);              // Delete all slashes
    //$value = str_replace("%%","%",$value);
    $value = str_replace("&","&amp;",$value);
    $value = str_replace("<","&lt;",$value);
    $value = str_replace(">","&gt;",$value);
    $value = str_replace('"',"&quot;",$value);
    return $value;
}


/**
 * Saves translated help string into a language help file
 *
 * @param string $lang Language code of destination
 * @param string $file The source English pack file of the help string
 * @param string $stringid The ID of translated string (unsed right now)
 * @param string $text The value of help string translated into $lang
 * @access public
 * $throws LanguageException
 * @return string Saved string
 */
function language_save_help_string($lang, $file, $stringid, $text) {

    $save_to = get_language_root($lang) . str_replace('en.utf8', $lang, $file);

    if (!create_dir_structure(dirname($save_to))) {
        throw new LanguageException('unabletocreatedirectory');
    }

    $save_to_tmp = $save_to.'~';
    if (! $f = fopen($save_to_tmp, 'w')) {
        throw new LanguageException('unabletowritetofile');
        return false;
    }
    fwrite($f, $text);
    fclose($f);
    // file was successfully written - let us replace the original
    if (file_exists($save_to)) {
        unlink($save_to);
    }
    rename($save_to_tmp, $save_to);
    chmod($save_to, get_config('directorypermissions') & 0666);
    return language_fix_value_from_file($text);
}


/**
 * Saves translated string or strings into a language file
 *
 * Keep this function at the bottom of the library file. It contains PHP end mark
 * which may confuse editor syntax highlighting.
 *
 * @param string $lang Language code of destination
 * @param string $file The source English pack file of the string
 * @param string $stringid The ID of translated string
 * @param string $text The value of $stringid translated into $lang
 * @param array $bulk Optional array of $stringid => $text to translate.
 * @access public
 * $throws LanguageException
 * @return string Saved string
 */
function language_save_string($lang, $file, $stringid, $text, $bulk=null) {

    $save_to = get_language_root($lang) . str_replace('en.utf8', $lang, $file);

    if (!create_dir_structure(dirname($save_to))) {
        throw new LanguageException('unabletocreatedirectory');
    }

    if (file_exists($save_to)) {
        include($save_to);
    } else {
        $string = array();
    }
    // optional $bulk may contain the array of translated strings
    if (is_array($bulk)) {
        foreach ($bulk as $strid => $strtxt) {
            $string[$strid] = $strtxt;
        }
    }
    if (!empty($stringid)) {
        $string[$stringid] = $text;
    }
    ksort($string);
    $lang_author = get_config('lang_author');
    $lang_copyright = get_config('lang_copyright');
    $fileheader = <<< EOF
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
 * @subpackage lang/{$lang}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @author     {$lang_author}
 * @copyright  {$lang_copyright}
 *
 */

defined('INTERNAL') || die();


EOF;

    $save_to_tmp = $save_to.'~';
    if (! $f = fopen($save_to_tmp, 'w')) {
        throw new LanguageException('unabletowritetofile');
        return false;
    }
    fwrite($f, $fileheader);
    foreach ($string as $strid => $strval) {
        $string[$strid] = language_fix_value_before_save($strval, $lang);
        if (! empty($string[$strid])) {
            fwrite($f, "\$string['$strid'] = '" . $string[$strid] . "';\n" );
        }
    }
    fclose($f);
    // file was successfully written - let us replace the original
    if (file_exists($save_to)) {
        unlink($save_to);
    }
    rename($save_to_tmp, $save_to);
    chmod($save_to, get_config('directorypermissions') & 0666);
    return language_fix_value_from_file($string[$stringid]);
}


?>
