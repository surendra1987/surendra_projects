<?php
/*
 * This file is part of Totara Core
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package format_pathway
 */

defined('MOODLE_INTERNAL') || die();

use container_course\hook\remove_module_hook;
use core\hook\before_output_start;
use core\hook\modify_footer;
use core\hook\modify_header;
use core\hook\module_available_display_options;
use core\hook\module_get_final_display_type;
use core\hook\navigation_load_course_settings;
use core\hook\phpunit_reset;
use core\hook\reset_theme_and_output;
use core\hook\set_page_layout;
use core\hook\theme_initialised_for_page_layout;
use core_completion\hook\override_activity_self_completion_form;
use core_course\hook\course_view;
use format_pathway\watcher\pathway_watcher;
use format_pathway\watcher\phpunit_reset_watcher;
use format_pathway\watcher\remove_module_watcher;
use core\hook\course_module_available_info;

$watchers = [
    [
        'hookname' => course_view::class,
        'callback' => [pathway_watcher::class, 'redirect_to_first_activity'],
    ],
    [
        'hookname' => theme_initialised_for_page_layout::class,
        'callback' => [pathway_watcher::class, 'intialise_instance'],
    ],
    [
        'hookname' => modify_header::class,
        'callback' => [pathway_watcher::class, 'modify_header'],
    ],
    [
        'hookname' => modify_footer::class,
        'callback' => [pathway_watcher::class, 'modify_footer'],
    ],
    [
        'hookname' => before_output_start::class,
        'callback' => [pathway_watcher::class, 'before_output_start'],
    ],
    [
        'hookname' => navigation_load_course_settings::class,
        'callback' => [pathway_watcher::class, 'navigation_load_course_settings'],
    ],
    [
        'hookname' => reset_theme_and_output::class,
        'callback' => [pathway_watcher::class, 'reset_theme_and_output'],
    ],
    [
        'hookname' => remove_module_hook::class,
        'callback' => [remove_module_watcher::class, 'watch'],
    ],
    [
        /** @see phpunit_reset_watcher::watch_phpunit_reset() */
        'hookname' => phpunit_reset::class,
        'callback' => [phpunit_reset_watcher::class, 'watch_phpunit_reset']
    ],
    [
        'hookname' => override_activity_self_completion_form::class,
        'callback' => [pathway_watcher::class, 'override_activity_self_completion_form']
    ],
    [
        'hookname' => module_get_final_display_type::class,
        'callback' => [pathway_watcher::class, 'module_get_final_display_type'],
    ],
    [
        'hookname' => module_available_display_options::class,
        'callback' => [pathway_watcher::class, 'module_available_display_options'],
    ],
    [
        'hookname' => course_module_available_info::class,
        'callback' => [pathway_watcher::class, 'course_module_available_info'],
    ],
    [
        'hookname' => set_page_layout::class,
        'callback' => [pathway_watcher::class, 'watch_set_page_layout'],
    ],
];