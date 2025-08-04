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
 * Allows the admin to enable, disable and uninstall course formats
 *
 * @package    core_admin
 * @copyright  2012 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_course\hook\format\format_enable;

require_once('../config.php');
require_once($CFG->libdir.'/adminlib.php');

$action  = required_param('action', PARAM_ALPHANUMEXT);
$formatname   = required_param('format', PARAM_PLUGIN);

$syscontext = context_system::instance();
$PAGE->set_url('/admin/courseformats.php');
$PAGE->set_context($syscontext);

require_login();
require_capability('moodle/site:config', $syscontext);
require_sesskey();

$return = new moodle_url('/admin/settings.php', array('section' => 'manageformats'));

$formatplugins = core_plugin_manager::instance()->get_plugins_of_type('format');
$sortorder = array_flip(array_keys($formatplugins));

if (!isset($formatplugins[$formatname])) {
    print_error('courseformatnotfound', 'error', $return, $formatname);
}

switch ($action) {
    case 'disable':
        if ($formatplugins[$formatname]->is_enabled()) {
            if (get_config('moodlecourse', 'format') === $formatname) {
                print_error('cannotdisableformat', 'error', $return);
            }
            set_config('disabled', 1, 'format_'. $formatname);
            core_plugin_manager::reset_caches();
        }
        break;
    case 'enable':
        $format = $formatplugins[$formatname]->name;
        // Trigger a hook to check if any other components want to prevent enabling of this course format plugin.
        $hook = new format_enable($format);
        $hook->execute();

        if ($hook->prevent_enable === true) {
            $a = ['format' => $hook->format, 'reason' => $hook->prevent_enable_reason];
            print_error('error:cannot_enable_prohibited_course_format_plugin', 'admin', '', $a);
        }

        if (!$formatplugins[$formatname]->is_enabled()) {
            unset_config('disabled', 'format_'. $formatname);
            core_plugin_manager::reset_caches();
        }
        break;
    case 'up':
        if ($sortorder[$formatname]) {
            $currentindex = $sortorder[$formatname];
            $seq = array_keys($formatplugins);
            $seq[$currentindex] = $seq[$currentindex-1];
            $seq[$currentindex-1] = $formatname;
            set_config('format_plugins_sortorder', implode(',', $seq));
        }
        break;
    case 'down':
        if ($sortorder[$formatname] < count($sortorder)-1) {
            $currentindex = $sortorder[$formatname];
            $seq = array_keys($formatplugins);
            $seq[$currentindex] = $seq[$currentindex+1];
            $seq[$currentindex+1] = $formatname;
            set_config('format_plugins_sortorder', implode(',', $seq));
        }
        break;
}
redirect($return);
