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
 * Web service mod_brprojects external functions and service definitions.
 *
 * @package    bi_intellidata
 * @copyright  2020 IntelliBoard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.

defined('MOODLE_INTERNAL') || die;

$functions = [
    'bi_intelldata_save_tracking' => [
        'classname'     => 'bi_intellidata_trackinglib',
        'methodname'    => 'save_tracking',
        'classpath'     => 'integrations/bi/intellidata/classes/api/trackinglib.php',
        'description'   => 'Save Tracking',
        'type'          => 'write',
        'ajax'          => true
    ],
];
