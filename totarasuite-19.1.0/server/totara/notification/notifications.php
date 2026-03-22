<?php
/**
 * This file is part of Totara Learn
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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package totara_notification
 */

use mod_perform\views\override_nav_breadcrumbs;
use totara_core\extended_context;
use totara_notification\exception\notification_exception;
use totara_notification\factory\notifiable_event_resolver_factory;
use totara_notification\local\helper as notification_helper;
use totara_tui\output\component;

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Get URL parameters.
$context_id = optional_param('context_id', context_system::instance()->id, PARAM_INT);
$component = optional_param('component', extended_context::NATURAL_CONTEXT_COMPONENT, PARAM_TEXT);
$area = optional_param('area', extended_context::NATURAL_CONTEXT_AREA, PARAM_TEXT);
$item_id = optional_param('item_id', extended_context::NATURAL_CONTEXT_ITEM_ID, PARAM_INT);

// Calculate what context we're in.
$extended_context = extended_context::make_with_id($context_id, $component, $area, $item_id);
$context = $extended_context->get_context();

// Require manage or auditing capability to access this page
if (!notifiable_event_resolver_factory::context_has_resolvers_with_capabilities($extended_context)) {
    throw notification_exception::on_manage();
}

// Set up the page.
$PAGE->set_context($context);

$url = new moodle_url("/totara/notification/notifications.php", [
    'context_id' => $context_id,
]);
if (!$extended_context->is_natural_context()) {
    $url->params([
        'component' => $extended_context->get_component(),
        'area' => $extended_context->get_area(),
        'item_id' => $extended_context->get_item_id(),
    ]);
}
$PAGE->set_url($url);

$page_title = get_string('notifications', 'totara_notification');
if ($context->contextlevel == CONTEXT_SYSTEM && $extended_context->is_natural_context()) {
    admin_externalpage_setup('notifications_setup', '', null, $url, ['pagelayout' => 'noblocks']);
    $PAGE->set_button('');
} else if ($context->contextlevel === CONTEXT_SYSTEM) {
    require_login();
    $PAGE->set_pagelayout('admin');
} else if ($context->contextlevel === CONTEXT_TENANT) {
    require_login();
    $PAGE->set_pagelayout('admin');
} else if ($context->contextlevel === CONTEXT_COURSECAT) {
    require_login();
    $PAGE->set_pagelayout('admin');
} else if ($context->contextlevel === CONTEXT_COURSE) {
    $course = get_course($context->instanceid);
    require_login($course);
    $PAGE->set_pagelayout('admin');
} else if ($context->contextlevel === CONTEXT_MODULE) {
    [$course, $cm] = get_course_and_cm_from_cmid($context->instanceid);
    require_login($course, true, $cm);

    if ($course->containertype === 'container_perform') {
        $PAGE->set_pagelayout('noblocks');
        override_nav_breadcrumbs::remove_nav_breadcrumbs($PAGE);
    } else {
        $PAGE->set_pagelayout('admin');
    }
} else if ($context->contextlevel === CONTEXT_PROGRAM) {
    require_login();
    $PAGE->set_url(
        new moodle_url('/totara/program/edit_notifications.php'),
        ['context_id' => $context_id, 'id' => $context->instanceid]
    );
    $program = $DB->get_record('prog', array('id' => $context->instanceid), '*', MUST_EXIST);
    $PAGE->set_pagelayout('admin');
} else {
    require_login();
    $PAGE->set_pagelayout('noblocks');
}
$PAGE->set_title($page_title);

// Load the context.
$tui = new component(
    'totara_notification/pages/NotificationPage',
    [
        'title' => $page_title,
        'context-id' => $context_id,
        'extended-context' => [
            'component' => $extended_context->get_component(),
            'area' => $extended_context->get_area(),
            'itemId' => $extended_context->get_item_id(),
        ],
        'preferred-editor-format' => notification_helper::get_preferred_editor_format(FORMAT_JSON_EDITOR),
        'can-change-delivery-channel-defaults' => $context->contextlevel == CONTEXT_SYSTEM && $extended_context->is_natural_context(),
    ]
);

$tui->register($PAGE);

echo $OUTPUT->header();
echo $OUTPUT->render($tui);
echo $OUTPUT->footer();