<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Cody Finegan <cody.finegan@totaralearning.com>
 * @package totara_engage
 */

use totara_core\advanced_feature;
use core\notification;
use core_user\profile\user_field_resolver as user_resolver;
use totara_tui\output\component as tui_component;

require_once(__DIR__ . '/../../config.php');
global $USER, $OUTPUT, $PAGE;

require_login();
advanced_feature::require('engage_resources');

$user_id = required_param('user_id', PARAM_INT);

// Bounce the active user to their own page
if ($user_id == $USER->id) {
    $your_resource_url = new moodle_url("/totara/engage/your_resources.php");
    redirect($your_resource_url);
    exit;
}

$target_context = context_user::instance($user_id);
$user_resolver = user_resolver::from_id($user_id);
$full_name = $user_resolver->get_field_value('fullname');
$tui = null;

// Set page properties.
$PAGE->set_context($target_context);
$PAGE->set_url(new moodle_url('/totara/engage/user_resources.php'));
$PAGE->set_pagelayout('noblocks');

if ($full_name) {
    $PAGE->set_title(get_string('usersresources', 'totara_engage', $full_name));
    $PAGE->set_privacy_aware_title(get_string('usersresources', 'totara_engage', get_string('userx', 'moodle', $user_id)));

    $tui = new tui_component(
        'totara_engage/pages/OtherUserLibrary',
        [
            'id' => 'userslibrary',
            'name' => $full_name,
            'userId' => $user_id,
            'pageId' => 'userslibrary',
            'profileUrl' => $user_resolver->get_field_value('profileurl'),
        ]
    );
    $tui->register($PAGE);
}

echo $OUTPUT->header();

if (null !== $tui) {
    echo $OUTPUT->render($tui);
} else {
    notification::error(get_string('error:view_user_resources', 'totara_engage'));
}

echo $OUTPUT->footer();
