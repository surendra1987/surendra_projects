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
 * @author Kian Nguyen <kian.nguyen@totaralearning.com>
 * @package container_workspace
 */

use container_workspace\interactor\workspace\category_interactor;
use container_workspace\workspace;
use container_workspace\totara\menu\your_spaces;
use core\notification;
use core\output\notification as output_notification;
use totara_core\advanced_feature;
use totara_tui\output\component;
use container_workspace\query\workspace\sort;

require_once(__DIR__ . "/../../../config.php");
require_login();
advanced_feature::require('container_workspace');

global $PAGE, $OUTPUT, $USER;

$action = optional_param('action', '', PARAM_TEXT);

// Get the flag of notification to check the notification is fired or not.
$hold_notification = optional_param('hold_notification', false, PARAM_BOOL);

// Righty, no workspace. time to render an empty page. We use category context here.
$category_id = workspace::get_default_category_id();
$context = \context_coursecat::instance($category_id);

$PAGE->set_context($context);
$PAGE->set_title(get_string('spaces', 'container_workspace'));

$PAGE->set_pagelayout('noblocks');
$PAGE->set_url(new \moodle_url("/container/type/workspace/index.php"));
$PAGE->set_totara_menu_selected(your_spaces::class);

$notifications = [];
if ($hold_notification) {
    $notifications = notification::fetch();
}

$context = context_user::instance($USER->id);
if (!has_capability('container/workspace:workspacesview', $context, $USER->id)) {
    $PAGE->set_title(get_string('error:view_workspace', 'container_workspace'));
    $component = new component('container_workspace/pages/WorkspaceEmptyPage');
} else {

    $category_id = workspace::get_default_category_id();
    $props = [
        'show-recommended' => advanced_feature::is_enabled('ml_recommender'),
        'selected-sort' => sort::get_code(sort::RECENT),
        'search-term' => '',
        'can-create-workspace' => (category_interactor::from_category_id($category_id, $USER->id))->can_create_workspace()
    ];

    if ($action && $action === 'cw') {
        $props['creation'] = $action;
    }

    $component = new component(
        'container_workspace/pages/YourWorkspace',
        $props
    );
}

$component->register($PAGE);

echo $OUTPUT->header();
echo $OUTPUT->render($component);
echo $OUTPUT->footer();

if ($hold_notification) {
    // Only add the notification at the very end of the process, as we can skip it from being
    // printed to the head of the page.

    /** @var output_notification $notification */
    foreach ($notifications as $notification) {
        notification::add($notification->get_message(), $notification->get_message_type());
    }
}