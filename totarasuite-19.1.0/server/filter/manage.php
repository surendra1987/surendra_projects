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
 * Lets users configure which filters are active in a sub-context.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package    core
 * @subpackage filter
 */

require_once(__DIR__ . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('global_settings_form.php');

$contextid = required_param('contextid',PARAM_INT);
$forfilter = optional_param('filter', '', PARAM_SAFEDIR);
$returnto  = optional_param('return', null, PARAM_ALPHANUMEXT);

list($context, $course, $cm) = get_context_info_array($contextid);

/// Check login and permissions.
require_login($course, false, $cm);
require_capability('moodle/filter:manage', $context);
$PAGE->set_context($context);

$args = array('contextid'=>$contextid);
$baseurl = new moodle_url('/filter/manage.php', $args);
if (!empty($forfilter)) {
    $args['filter'] = $forfilter;
}
$PAGE->set_url($baseurl, $args);
if ($returnto !== null) {
    $baseurl->param('return', $returnto);
}

// This is a policy decision, rather than something that would be impossible to implement.
if (!in_array($context->contextlevel, array(CONTEXT_COURSECAT, CONTEXT_COURSE, CONTEXT_MODULE))) {
    print_error('cannotcustomisefiltersblockuser', 'error');
}

$isfrontpage = ($context->contextlevel == CONTEXT_COURSE && $context->instanceid == SITEID);

$contextname = $context->get_context_name();

if ($context->contextlevel == CONTEXT_COURSECAT) {
    $heading = $SITE->fullname;
} else if ($context->contextlevel == CONTEXT_COURSE) {
    $heading = $course->fullname;
} else if ($context->contextlevel == CONTEXT_MODULE) {
    // Must be module context.
    $heading = $PAGE->activityrecord->name;
} else {
    $heading = '';
}

/// Check login and permissions.
require_login($course, false, $cm);
require_capability('moodle/filter:manage', $context);

/** @var filtersettings_form */
$filtersettingsform = new filter_global_settings_form(null, ['contextid' => $contextid]);

$PAGE->set_context($context);
$PAGE->set_heading($heading);

/// Get the list of available filters.
$availablefilters = filter_get_available_in_context($context);
if (!$isfrontpage && empty($availablefilters)) {
    print_error('nofiltersenabled', 'error');
}

// If we are handling local settings for a particular filter, start processing.
if ($forfilter) {
    if (!filter_has_local_settings($forfilter)) {
        print_error('filterdoesnothavelocalconfig', 'error', $forfilter);
    }
    require_once($CFG->dirroot . '/filter/local_settings_form.php');
    require_once($CFG->dirroot . '/filter/' . $forfilter . '/filterlocalsettings.php');
    $formname = $forfilter . '_filter_local_settings_form';
    $settingsform = new $formname($CFG->wwwroot . '/filter/manage.php', $forfilter, $context);
    if ($settingsform->is_cancelled()) {
        redirect($baseurl);
    } else if ($data = $settingsform->get_data()) {
        $settingsform->save_changes($data);
        redirect($baseurl);
    }
}

if ($data = $filtersettingsform->get_data()) {
    $contextid = $data->contextid;
    foreach ($availablefilters as $filter => $value) {
        filter_set_local_state($filter, $context->id, $data->$filter);
    }
} else {
    $form_data = [];
    foreach ($availablefilters as $filter => $value) {
        $form_data[$filter] = $value->localstate;
    }
    $filtersettingsform->set_data($form_data);
}

/// Process any form submission.
if ($forfilter == '' && optional_param('savechanges', false, PARAM_BOOL) && confirm_sesskey()) {
    foreach ($availablefilters as $filter => $filterinfo) {
        $newstate = optional_param($filter, false, PARAM_INT);
        if ($newstate !== false && $newstate != $filterinfo->localstate) {
            filter_set_local_state($filter, $context->id, $newstate);
        }
    }
    redirect($baseurl, get_string('changessaved'), 1);
}

/// Work out an appropriate page title.
if ($forfilter) {
    $a = new stdClass;
    $a->filter = filter_get_name($forfilter);
    $a->context = $contextname;
    $title = get_string('filtersettingsforin', 'filters', $a);
} else {
    $title = get_string('filtersettingsin', 'filters', $contextname);
}
$straction = get_string('filters', 'admin'); // Used by tabs.php

// Print the header and tabs.
$PAGE->set_cacheable(false);
$PAGE->set_title($title);
$PAGE->set_pagelayout('admin');
echo $OUTPUT->header();

/// Print heading.
echo $OUTPUT->heading_with_help($title, 'filtersettings', 'filters', '', '', 1);

if (empty($availablefilters)) {
    echo '<p class="centerpara">' . get_string('nofiltersenabled', 'filters') . "</p>\n";
} else if ($forfilter) {
    $current = filter_get_local_config($forfilter, $contextid);
    $settingsform->set_data((object) $current);
    $settingsform->display();
} else {
    $filtersettingsform->display();
}

/// Appropriate back link.
if (!$isfrontpage) {

    if ($context->contextlevel === CONTEXT_COURSECAT && $returnto === 'management') {
        $url = new moodle_url('/course/management.php', array('categoryid' => $context->instanceid));
    } else {
        $url = $context->get_url();
    }

    echo html_writer::start_tag('div', array('class'=>'backlink'));
    echo html_writer::tag('a', get_string('backto', '', $contextname), array('href' => $url));
    echo html_writer::end_tag('div');
}

echo $OUTPUT->footer();

