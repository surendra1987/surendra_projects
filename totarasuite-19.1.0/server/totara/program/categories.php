<?php
/*
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
 * @author David Curry <david.curry@totara.com>
 * @package totara_program
 */

/**
 * The Purpose of this page is to give highly performant access to a list of all
 * top level categories, users must have site level access to hidden categories
 * to keep permissions checks to a minimum.
 *
 * It is not recommended to use this page unless the regular management page is
 * having performance issues.
 *
 * Dev Note: Mimic changes to this file in the course/categories.php file
 */

require_once("../../config.php");
require_once($CFG->libdir.'/adminlib.php');

$viewtype = optional_param('viewtype', 'program', PARAM_TEXT);
$search = optional_param('search', '', PARAM_RAW);
$page_num = optional_param('page', 0, PARAM_INT);
$per_page = 20; // Static for consistency.

$sysontext = context_system::instance();

$PAGE->set_context($sysontext);
$PAGE->set_url('/totara/program/categories.php');
$PAGE->set_pagelayout('coursecategory');
$PAGE->set_pagetype('program-index-category');

// Make the viewer is logged in.
require_login(null, false);

// To use this page you must be logged in and have the view hidden categories permission.
$root = 0;
if (!empty($USER->tenantid)) {
    $tenant = core\record\tenant::fetch($USER->tenantid);
    $root = $tenant->categoryid;
}
$context = empty($root) ? context_system::instance() : context_coursecat::instance($root);

if (!coursecat::has_capability_on_any('moodle/category:viewhiddencategories')) {
    redirect(new moodle_url('/'));
}

// Set up the page based on viewtype.
$pagetype = ($viewtype == 'program') ? 'programmgmt' : 'managecertifications';
admin_externalpage_setup($pagetype);

$PAGE->navbar->ignore_active(true);
$PAGE->navbar->add(get_string('coursemgmt', 'admin'), null);

/** @var core_renderer|core_course_renderer $courserenderer */
$courserenderer = $PAGE->get_renderer('core', 'course');

echo $courserenderer->header();
echo $courserenderer->heading(get_string('categorymanagement'));

if (has_capability('moodle/category:manage', $context)) {
    $add_url = new \moodle_url('/course/editcategory.php', ['parent' => $root, 'type' => $viewtype]);
    echo $OUTPUT->single_button($add_url, get_string('addnewcategory'), 'get', ['class' => 'new_category_button']);
}

// Output a quick basic search box.
$search = trim(strip_tags($search)); // trim & clean raw searched string
echo $courserenderer->coursecat_search_form('/totara/program/categories.php?viewtype=' . $viewtype, $search);

// Lets get all the (non-system) top level categories.
$limit = $page_num * $per_page;
$sql_where = 'issystem = 0 AND parent = :root';
$params = ['root' => $root];

// Include search terms.
if (!empty($search)) {
    $search_sql = $DB->sql_like('name', ':search', false, false);
    $sql_where .= ' AND ' . $search_sql;
    $params['search'] = "%$search%";
}

$cat_page = $DB->get_records_select(
    'course_categories',
    $sql_where,
    $params,
    'sortorder',
    '*',
    $limit,
    $per_page
);

// List out the current page of category links.
$content = html_writer::start_tag('ul', ['class' => 'category_list']); // wrapper.
foreach ($cat_page as $category) {
    $context = context_coursecat::instance($category->id);
    $categoryname = format_string($category->name, true, ['context' => $context]);
    $categorylink = html_writer::link(
        new moodle_url(
            '/totara/program/index.php',
            [
                'categoryid' => $category->id,
                'viewtype' => $viewtype,
            ]
        ),
        $categoryname
    );

    $content .= html_writer::start_tag('div', ['class' => 'category']); // category.
    $content .= html_writer::tag('li', $categorylink, ['class' => 'category_link']);
    $content .= html_writer::end_tag('div'); // category.
}
$content .= html_writer::end_tag('ul'); // wrapper.
echo $content;

// And add pagination controlls at the bottom.
$page_url = new \moodle_url('/totara/program/categories.php', ['viewtype' => $viewtype, 'search' => $search]);
$max_cat = $DB->count_records_select(
    'course_categories',
    $sql_where,
    $params,
);

echo $OUTPUT->paging_bar($max_cat, $page_num, $per_page, $page_url, 'page');

echo $courserenderer->footer();
