<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
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
 * @author Simon Coggins <simon.coggins@totaralearning.com>
 * @package totara_reportbuilder
 */

/**
 * Page containing new report form
 */

use core\record\tenant;
use totara_core\advanced_feature;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/totara/reportbuilder/lib.php');
require_once($CFG->dirroot . '/totara/reportbuilder/report_forms.php');

$tenantid = optional_param('tenantid', 0, PARAM_INT);

// This is not a real admin page, set actual URL here to avoid admin menu issues.
$params = [];
$options = [
    'pagelayout' => 'noblocks',
];

if ($tenantid) {
    $context = tenant::fetch($tenantid)->category_context;
    $params['tenantid'] = $tenantid;
    $options['context'] = $context;
} else {
    $context = context_system::instance();
}

$PAGE->set_context($context);
$baseurl = new moodle_url('/totara/reportbuilder/create.php', $params);

$title = get_string('createreport', 'totara_reportbuilder');
$PAGE->set_title($title);

if ($context->contextlevel == CONTEXT_SYSTEM) {
    admin_externalpage_setup('rbmanagereports', '', null, $baseurl, array('pagelayout' => 'noblocks'));
} else {
    require_capability('totara/reportbuilder:managereports', $context);
    $PAGE->set_url($baseurl);
    $PAGE->set_pagelayout('noblocks');
}

$params = [];
if ($context->contextlevel == CONTEXT_COURSECAT) {
    $params['contextid'] = $context->id;
}
navigation_node::override_active_url(new moodle_url('/totara/reportbuilder/index.php', $params));

$output = $PAGE->get_renderer('totara_reportbuilder');

/** @var totara_reportbuilder_renderer $output */
echo $output->header();

// User generated reports.
echo $output->page_main_heading($title);

advanced_feature::require('user_reports');

echo $output->render(\totara_reportbuilder\output\create_report::create($tenantid));
echo $output->footer();
