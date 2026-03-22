<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package totara_mobile
 */

use totara_mobile\local\util;

global $CFG, $PAGE, $SESSION;

require(__DIR__ . '/../../config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/totara/mobile/browser_login.php');

// Just send people who direct access away
$return = optional_param('return', null, PARAM_INT);
if (empty($return)) {
    $home_page = new \moodle_url($CFG->wwwroot . '/');
    redirect($home_page);
}

// This page requires a login & the capability for mobile.
require_login();
require_capability('totara/mobile:use', \context_system::instance());

if (isguestuser()) {
    throw new \coding_exception('Guest is not valid for mobile login');
}

if (!get_config('totara_mobile', 'enable')) {
    throw new \coding_exception('Mobile is not enabled');
}
$authtype = get_config('totara_mobile', 'authtype');
if ($authtype !== 'browser') {
    throw new \coding_exception('Mobile authtype is not browser');
}

$url = util::get_app_universal_registration_link();
redirect($url);
