<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Chris Snyder <chris.snyder@totaralearning.com>
 * @package mod_approval
 */

use core\entity\user;
use mod_approval\interactor\application_interactor;
use mod_approval\model\form\form_data;

/**
 * This plugin is a placeholder; workflow assignments are implemented in container_approval.
 */

/**
 * Required in order to prevent failures in tests.
 */
function approval_add_instance($data) {
    return null;
}

function approval_update_instance($data) {
    return true;
}

function approval_delete_instance($id) {
    return true;
}

/**
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function approval_supports($feature) {
    switch ($feature) {
        case FEATURE_NO_VIEW_LINK:
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_COMMENT:
        case FEATURE_IDNUMBER:
            return true;

        case FEATURE_GRADE_HAS_GRADE:
        case FEATURE_USES_QUESTIONS:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_ARCHIVE_COMPLETION:
        case FEATURE_COMPLETION_HAS_RULES:
        case FEATURE_COMPLETION_TIME_IN_TIMECOMPLETED:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_MODEDIT_DEFAULT_COMPLETION:
        case FEATURE_MOD_INTRO:
        case FEATURE_GROUPINGS:
        case FEATURE_GROUPS:
        case FEATURE_GRADE_OUTCOMES:
        case FEATURE_PLAGIARISM:
            return false;

        default:
            return null;
    }
}

/**
 * Serve the files for the mod_approval file component.
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anything
 */
function mod_approval_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    if ($filearea === form_data::FILE_AREA) {
        $application_id = (int)array_shift($args);
        $user = user::logged_in();
        if (!$user) {
            return false;
        }
        $application_interactor = application_interactor::from_application_id($application_id, $user->id);
        if (!$application_interactor->can_view()) {
            return false;
        }

        $file_name = array_pop($args);
        $file_path = DIRECTORY_SEPARATOR;

        if ($args) {
            $file_path .= implode(DIRECTORY_SEPARATOR, $args). DIRECTORY_SEPARATOR;
        }

        $file = get_file_storage()->get_file(
            $context->id,
            form_data::FILE_COMPONENT,
            form_data::FILE_AREA,
            $application_interactor->get_application()->id,
            $file_path,
            $file_name
        );

        if (!$file) {
            return false;
        }

        send_stored_file($file, DAYSECS, 0, $forcedownload, $options);
    }

    return false;
}

/**
 * Return a placeholder name to prevent get_array_of_activities() from executing an extra database query.
 *
 * @param stdClass $mod {course_modules} record
 * @return cached_cm_info|stdClass
 */
function approval_get_coursemodule_info($mod) {
    $info = new cached_cm_info();
    $info->name = 'approval workflow';
    return $info;
}

/**
 * Adds approval workflow links to category nav when required.
 *
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param context $context The context of the course
 * @return void|null return null if we don't want to display the node.
 */
function mod_approval_extend_navigation_category_settings($navigation, $context) {
    global $PAGE, $DB;

    if (totara_core\advanced_feature::is_disabled('approval_workflows')) {
        return null;
    }

    if (!$context->tenantid) {
        return null;
    }

    if (!($context instanceof context_coursecat)) {
        return;
    }

    $tenant = $DB->get_record('tenant', ['categoryid' => $context->instanceid]);
    if (!$tenant) {
        return null;
    }

    $categorycontext = context_coursecat::instance($tenant->categoryid);
    $nodes = [];

    // Check capabilities per node.
    if (has_capability('mod/approval:manage_workflows', $categorycontext)) {
        $workflow_url = new moodle_url('/mod/approval/workflow/index.php', ['tenant_id' => $tenant->id]);
        $nodes[] = navigation_node::create(
            get_string('approval:manage_workflows', 'mod_approval'),
            $workflow_url,
            navigation_node::NODETYPE_LEAF,
            null,
            'manageapprovalworkflows',
            new pix_icon('i/settings', '')
        );
    }

    $category_approval = $navigation->find('category_approval', navigation_node::TYPE_CONTAINER);
    if (!$category_approval && !empty($nodes)) {
        $category_approval = $navigation->add(
            get_string('pluginname', 'mod_approval'),
            null,
            navigation_node::TYPE_CONTAINER,
            null,
            'category_approval'
        );
    }

    foreach ($nodes as $node) {
        $category_approval->add_node($node);
        if ($PAGE->url->compare($node->action, URL_MATCH_EXACT)) {
            $node->make_active();
        }
    }
}
