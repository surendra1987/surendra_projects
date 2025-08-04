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
 * Extra classes needed for HTMLPurifier customisation for Moodle.
 *
 * @package    core
 * @copyright  2012 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL 3 or later
 */

defined('MOODLE_INTERNAL') || die();

if (during_initial_install() && !class_exists('HTMLPurifier_URIScheme')) {
    // Class autoloading is not working here. We can however manually get it going.
    require_once(__DIR__ . '/../../../libraries/required/autoload.php');
}

/**
 * Validates RTSP defined by RFC 2326
 */
class HTMLPurifier_URIScheme_rtsp extends HTMLPurifier_URIScheme {

    public $browsable = true;
    public $hierarchical = true;

    public function doValidate(&$uri, $config, $context) {
        $uri->userinfo = null;
        return true;
    }

}


/**
 * Validates RTMP defined by Adobe
 */
class HTMLPurifier_URIScheme_rtmp extends HTMLPurifier_URIScheme {

    public $browsable = false;
    public $hierarchical = true;

    public function doValidate(&$uri, $config, $context) {
        $uri->userinfo = null;
        return true;
    }

}


/**
 * Validates IRC defined by IETF Draft
 */
class HTMLPurifier_URIScheme_irc extends HTMLPurifier_URIScheme {

    public $browsable = true;
    public $hierarchical = true;

    public function doValidate(&$uri, $config, $context) {
        $uri->userinfo = null;
        return true;
    }

}


/**
 * Validates MMS defined by Microsoft
 */
class HTMLPurifier_URIScheme_mms extends HTMLPurifier_URIScheme {

    public $browsable = true;
    public $hierarchical = true;

    public function doValidate(&$uri, $config, $context) {
        $uri->userinfo = null;
        return true;
    }

}

/**
 * Validates TeamSpeak defined by TeamSpeak
 */
class HTMLPurifier_URIScheme_teamspeak extends HTMLPurifier_URIScheme {

    public $browsable = true;
    public $hierarchical = true;

    public function doValidate(&$uri, $config, $context) {
        $uri->userinfo = null;
        return true;
    }

}

/**
 *  Additional protocol validation classes added by Totara
 */
class HTMLPurifier_URIScheme_skype extends HTMLPurifier_URIScheme {
    public $browsable = true;
    public $hierarchical = true;
    public function doValidate(&$uri, $config, $context) {
        $uri->userinfo = null;
        return true;
    }
}

class HTMLPurifier_URIScheme_meet extends HTMLPurifier_URIScheme {
    public $browsable = true;
    public $hierarchical = true;
    public function doValidate(&$uri, $config, $context) {
        $uri->userinfo = null;
        return true;
    }
}

class HTMLPurifier_URIScheme_sip extends HTMLPurifier_URIScheme {
    public $browsable = true;
    public $hierarchical = true;
    public function doValidate(&$uri, $config, $context) {
        $uri->userinfo = null;
        return true;
    }
}

class HTMLPurifier_URIScheme_xmpp extends HTMLPurifier_URIScheme {
    public $browsable = true;
    public $hierarchical = true;
    public function doValidate(&$uri, $config, $context) {
        $uri->userinfo = null;
        return true;
    }
}

/**
 * This class overrides HTMLPurifier_DefinitionCache_Serializer and replaces
 * the trigger_error methods instead with debugging calls.
 */
class HTMLPurifier_LocalSerializer extends HTMLPurifier_DefinitionCache_Serializer {
    /**
     * @param HTMLPurifier_Definition $def
     * @param HTMLPurifier_Config $config
     * @return int|bool
     */
    public function add($def, $config) {
        if (!$this->checkDefType($def)) {
            return false;
        }
        $file = $this->generateFilePath($config);
        if (file_exists($file)) {
            return false;
        }
        if (!$this->_prepareDir($config)) {
            return false;
        }
        return $this->_write($file, serialize($def), $config);
    }

    /**
     * @param HTMLPurifier_Definition $def
     * @param HTMLPurifier_Config $config
     * @return int|bool
     */
    public function set($def, $config) {
        if (!$this->checkDefType($def)) {
            return false;
        }
        $file = $this->generateFilePath($config);
        if (!$this->_prepareDir($config)) {
            return false;
        }
        return $this->_write($file, serialize($def), $config);
    }

    /**
     * @param HTMLPurifier_Definition $def
     * @param HTMLPurifier_Config $config
     * @return int|bool
     */
    public function replace($def, $config) {
        if (!$this->checkDefType($def)) {
            return false;
        }
        $file = $this->generateFilePath($config);
        if (!file_exists($file)) {
            return false;
        }
        if (!$this->_prepareDir($config)) {
            return false;
        }
        return $this->_write($file, serialize($def), $config);
    }

    /**
     * Convenience wrapper function for file_put_contents
     * @param string $file File name to write to
     * @param string $data Data to write into file
     * @param HTMLPurifier_Config $config
     * @return int|bool Number of bytes written if success, or false if failure.
     */
    private function _write($file, $data, $config) {
        $result = file_put_contents($file, $data);
        if ($result !== false) {
            // set permissions of the new file (no execute)
            $chmod = $config->get('Cache.SerializerPermissions');
            if ($chmod !== null) {
                chmod($file, $chmod & 0666);
            }
        }
        return $result;
    }

    /**
     * Prepares the directory that this type stores the serials in
     * @param HTMLPurifier_Config $config
     * @return bool True if successful
     */
    private function _prepareDir($config) {
        $directory = $this->generateDirectoryPath($config);
        $chmod = $config->get('Cache.SerializerPermissions');
        if ($chmod === null) {
            if (!@mkdir($directory) && !is_dir($directory)) {
                debugging('HTMLPurifier: Could not create directory ' . $directory, DEBUG_DEVELOPER);
                return false;
            }
            return true;
        }
        if (!is_dir($directory)) {
            $base = $this->generateBaseDirectoryPath($config);
            if (!is_dir($base)) {
                debugging('HTMLPurifier: Base directory ' . $base . ' does not exist', DEBUG_DEVELOPER);
                return false;
            } elseif (!$this->_testPermissions($base, $chmod)) {
                return false;
            }
            if (!@mkdir($directory, $chmod) && !is_dir($directory)) {
                debugging('HTMLPurifier: Could not create directory ' . $directory, DEBUG_DEVELOPER);
                return false;
            }
            if (!$this->_testPermissions($directory, $chmod)) {
                return false;
            }
        } elseif (!$this->_testPermissions($directory, $chmod)) {
            return false;
        }
        return true;
    }

    /**
     * Tests permissions on a directory and throws out friendly
     * error messages and attempts to chmod it itself if possible
     * @param string $dir Directory path
     * @param int $chmod Permissions
     * @return bool True if directory is writable
     */
    private function _testPermissions($dir, $chmod) {
        // early abort, if it is writable, everything is hunky-dory
        if (is_writable($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            // generally, you'll want to handle this beforehand
            // so a more specific error message can be given
            debugging('HTMLPurifier: Directory ' . $dir . ' does not exist', DEBUG_DEVELOPER);
            return false;
        }
        if (function_exists('posix_getuid') && $chmod !== null) {
            // POSIX system, we can give more specific advice
            if (fileowner($dir) === posix_getuid()) {
                // we can chmod it ourselves
                $chmod = $chmod | 0700;
                if (chmod($dir, $chmod)) {
                    return true;
                }
            } elseif (filegroup($dir) === posix_getgid()) {
                $chmod = $chmod | 0070;
            } else {
                // PHP's probably running as nobody, so we'll
                // need to give global permissions
                $chmod = $chmod | 0777;
            }
            debugging('HTMLPurifier: Directory ' . $dir . ' not writable, please chmod to ' . decoct($chmod), DEBUG_DEVELOPER);
        } else {
            // generic error message
            debugging('HTMLPurifier: Directory ' . $dir . ' not writable, please alter file permissions', DEBUG_DEVELOPER);
        }
        return false;
    }

}