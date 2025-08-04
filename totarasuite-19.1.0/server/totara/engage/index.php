<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Qingyang Liu <gary.liu@totara.com>
 * @package totara_engage
 */

use totara_core\advanced_feature;
use totara_engage\access\access_manager;
use totara_engage\query\query;
use totara_engage\sidepanel\engage_provider;
use totara_tui\output\component;
use totara_playlist\totara_engage\sidepanel\playlist_provider;

require_once(__DIR__ . '/../../config.php');
global $USER, $OUTPUT, $PAGE;

require_login();
advanced_feature::require('engage_resources');
access_manager::require_library_capability();

$title = get_string('yourlibrary', 'totara_engage');
$action = optional_param('action', '', PARAM_TEXT);


// Set page properties.
$PAGE->set_context(\context_user::instance($USER->id));
$PAGE->set_title($title);
$PAGE->set_pagelayout('noblocks');
$PAGE->set_url(new moodle_url('/totara/engage/index.php'));
$PAGE->set_totara_menu_selected('\totara_engage\totara\menu\library');

$props = [
    'id' => 'yourlibrary',
    'page-props' => [
        // Always show notification banner after resources creation.
        'showNotification' => true,
        'options' => (new query())->get_section_filter_options(),
        'showCreateResource' => (new engage_provider())->show_create_button(),
        'showCreatePlaylist' => (new playlist_provider())->show_create_button(),
    ],
];
if ($action && in_array($action, ['cr', 'cp'])) {
    $props['creation'] = $action;
}

$tui = new component('totara_engage/pages/LibraryView', $props);
$tui->register($PAGE);

echo $OUTPUT->header();
echo $OUTPUT->render($tui);
echo $OUTPUT->footer();