<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Command line utility functions and classes
 *
 * @package    core
 * @subpackage cli
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, sometimes we use this before requiring Moodle libs!

/**
 * Write a text to the given stream
 *
 * @param string $text text to be written
 * @param resource $stream output stream to be written to, defaults to STDOUT
 */
function cli_write($text, $stream=STDOUT) {
    fwrite($stream, $text);
}

/**
 * Write a text followed by an end of line symbol to the given stream
 *
 * @param string $text text to be written
 * @param resource $stream output stream to be written to, defaults to STDOUT
 */
function cli_writeln($text, $stream=STDOUT) {
    cli_write($text.PHP_EOL, $stream);
}

/**
 * Get input from user
 * @param string $prompt text prompt, should include possible options
 * @param string $default default value when enter pressed
 * @param array $options list of allowed options, empty means any text
 * @param bool $casesensitive true if options are case sensitive
 * @return string entered text
 */
function cli_input($prompt, $default='', array $options=null, $casesensitiveoptions=false) {
    cli_writeln($prompt);
    cli_write(': ');
    $input = fread(STDIN, 2048);
    $input = trim($input);
    if ($input === '') {
        $input = $default;
    }
    if ($options) {
        if (!$casesensitiveoptions) {
            $input = strtolower($input);
        }
        if (!in_array($input, $options)) {
            cli_writeln(get_string('cliincorrectvalueretry', 'admin'));
            return cli_input($prompt, $default, $options, $casesensitiveoptions);
        }
    }
    return $input;
}

/**
 * Returns cli script parameters.
 * @param array $longoptions array of --style options ex:('verbose'=>false)
 * @param array $shortmapping array describing mapping of short to long style options ex:('h'=>'help', 'v'=>'verbose')
 * @return array array of arrays, options, unrecognised as optionlongname=>value
 */
function cli_get_params(array $longoptions, array $shortmapping=null) {
    $shortmapping = (array)$shortmapping;
    $options      = array();
    $unrecognized = array();

    if (empty($_SERVER['argv'])) {
        // bad luck, we can continue in interactive mode ;-)
        return array($options, $unrecognized);
    }
    $rawoptions = $_SERVER['argv'];

    //remove anything after '--', options can not be there
    if (($key = array_search('--', $rawoptions)) !== false) {
        $rawoptions = array_slice($rawoptions, 0, $key);
    }

    //remove script
    unset($rawoptions[0]);
    foreach ($rawoptions as $raw) {
        if (substr($raw, 0, 2) === '--') {
            $value = substr($raw, 2);
            $parts = explode('=', $value);
            if (count($parts) == 1) {
                $key   = reset($parts);
                $value = true;
            } else {
                $key = array_shift($parts);
                $value = implode('=', $parts);
            }
            if (array_key_exists($key, $longoptions)) {
                $options[$key] = $value;
            } else {
                $unrecognized[] = $raw;
            }

        } else if (substr($raw, 0, 1) === '-') {
            $value = substr($raw, 1);
            $parts = explode('=', $value);
            if (count($parts) == 1) {
                $key   = reset($parts);
                $value = true;
            } else {
                $key = array_shift($parts);
                $value = implode('=', $parts);
            }
            if (array_key_exists($key, $shortmapping)) {
                $options[$shortmapping[$key]] = $value;
            } else {
                $unrecognized[] = $raw;
            }
        } else {
            $unrecognized[] = $raw;
            continue;
        }
    }
    //apply defaults
    foreach ($longoptions as $key=>$default) {
        if (!array_key_exists($key, $options)) {
            $options[$key] = $default;
        }
    }
    // finished
    return array($options, $unrecognized);
}

/**
 * Print or return section separator string
 * @param bool $return false means print, true return as string
 * @return mixed void or string
 */
function cli_separator($return=false) {
    $separator = str_repeat('-', 79).PHP_EOL;
    if ($return) {
        return $separator;
    } else {
        cli_write($separator);
    }
}

/**
 * Print or return section heading string
 * @param string $string text
 * @param bool $return false means print, true return as string
 * @return mixed void or string
 */
function cli_heading($string, $return=false) {
    $string = "== $string ==".PHP_EOL;
    if ($return) {
        return $string;
    } else {
        cli_write($string);
    }
}

/**
 * Write error notification
 * @param $text
 * @return void
 */
function cli_problem($text) {
    cli_writeln($text, STDERR);
}

/**
 * Write to standard error output and exit with the given code
 *
 * @param string $text
 * @param int $errorcode
 * @return void (does not return)
 */
function cli_error($text, $errorcode=1) {
    cli_writeln($text.PHP_EOL, STDERR);
    die($errorcode);
}

/**
 * Print an ASCII version of the Moodle logo.
 *
 * @param int $padding left padding of the logo
 * @param bool $return should we print directly (false) or return the string (true)
 * @return mixed void or string
 */
function cli_logo($padding=2, $return=false) {

    $lines = array(
        '                    /\              ',
        '                   |  |             ',
        '               ,_   \/   _,         ',
        "               \ '-.||.-' /       ",
        "     _____     _'.__\/__.'        ",
        '    |_   _|__ | |_ __ _ _ __ __ _  ',
        "      | |/ _ \| __/ _` | '__/ _` | ",
        '      | | (_) | || (_| | | | (_| | ',
        '      |_|\___/ \__\__,_|_|  \__,_| '
    );
    $logo = '';

    foreach ($lines as $line) {
        $logo .= str_repeat(' ', $padding);
        $logo .= $line;
        $logo .= PHP_EOL;
    }

    if ($return) {
        return $logo;
    } else {
        cli_write($logo);
    }
}

/**
 * Fake up a configuration and library bootstrapping in the absence of a config.php file,
 * to be used for build scripts where no database connection is necessary.
 *
 * @param string $dirroot
 */
function cli_configless_setup(string $dirroot) {
    // This is only ever meant to be used for CLI scripts.
    if (!defined('CLI_SCRIPT') || !CLI_SCRIPT) {
        die();
    }

    // Add an extra global constant to declare that we are executing without a full configuration.
    if (!defined('CLI_CONFIGLESS')) {
        define('CLI_CONFIGLESS', true);
    }

    /**
     * Most of the following was taken from install.php
     */

    // Servers should define a default timezone in php.ini, but if they don't then make sure something is defined.
    if (!function_exists('date_default_timezone_set') or !function_exists('date_default_timezone_get')) {
        fwrite(STDERR, "Timezone functions are not available.\n");
        exit(1);
    }
    date_default_timezone_set(@date_default_timezone_get());

    // make sure PHP errors are displayed - helps with diagnosing of problems
    @error_reporting(E_ALL);
    @ini_set('display_errors', '1');
    // we need a lot of memory
    @ini_set('memory_limit', '128M');

    /** Used by library scripts to check they are being called by Moodle */
    define('MOODLE_INTERNAL', true);

    // Disables all caching.
    define('CACHE_DISABLE_ALL', true);

    define('PHPUNIT_TEST', false);

    define('IGNORE_COMPONENT_CACHE', true);

    // set up configuration
    global $CFG;
    $CFG = new stdClass();
    $CFG->lang                 = 'en';
    $CFG->dirroot              = $dirroot;
    $CFG->srcroot              = realpath("$CFG->dirroot/..");
    $CFG->libdir               = "$CFG->dirroot/lib";
    $CFG->libraries            = "$CFG->dirroot/../libraries";
    $CFG->wwwroot              = "http://localhost";
    $CFG->httpswwwroot         = $CFG->wwwroot;
    $CFG->docroot              = '';
    $CFG->running_installer    = true;
    $CFG->early_install_lang   = true;
    $CFG->ostype               = (stristr(PHP_OS, 'win') && !stristr(PHP_OS, 'darwin')) ? 'WINDOWS' : 'UNIX';
    $CFG->dboptions            = array();
    $CFG->debug                = (E_ALL | E_STRICT);
    $CFG->debugdisplay         = true;
    $CFG->debugdeveloper       = true;
    $CFG->admin                = 'admin';

    //point pear include path to moodles lib/pear so that includes and requires will search there for files before anywhere else
    //the problem is that we need specific version of quickforms and hacked excel files :-(
    ini_set('include_path', $CFG->libdir . '/pear' . PATH_SEPARATOR . ini_get('include_path'));

    require_once($CFG->libdir . '/classes/component.php');
    require_once($CFG->libdir . '/classes/text.php');
    require_once($CFG->libdir . '/classes/string_manager.php');
    require_once($CFG->libdir . '/classes/string_manager_install.php');
    require_once($CFG->libdir . '/classes/string_manager_standard.php');
    require_once($CFG->libdir . '/installlib.php');
    require_once($CFG->libdir . '/clilib.php');
    require_once($CFG->libdir . '/setuplib.php');
    require_once($CFG->libdir . '/weblib.php');
    require_once($CFG->libdir . '/outputlib.php');
    require_once($CFG->libdir . '/dmllib.php');
    require_once($CFG->libdir . '/moodlelib.php');
    require_once($CFG->libdir . '/deprecatedlib.php');
    require_once($CFG->libdir . '/adminlib.php');
    require_once($CFG->libdir . '/componentlib.class.php');
    require_once($CFG->dirroot . '/cache/lib.php');

    // Register our classloader, in theory somebody might want to replace it to load other hacked core classes.
    // Required because the database checks below lead to session interaction which is going to lead us to requiring autoloaded classes.
    require_once($CFG->dirroot . '/../libraries/required/autoload.php');
    if (defined('COMPONENT_CLASSLOADER')) {
        spl_autoload_register(COMPONENT_CLASSLOADER);
    } else {
        spl_autoload_register('core_component::classloader');
    }
}
