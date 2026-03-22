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
 * This page shows all course enrolment options for current user.
 *
 * @package    core_enrol
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\format;
use core\webapi\formatter\field\string_field_formatter;
use core_enrol\model\user_enrolment_application;
use mod_approval\controllers\application\edit as application_edit_controller;
use totara_tui\output\component;

require('../config.php');
require_once("$CFG->libdir/formslib.php");
require_once($CFG->libdir. '/coursecatlib.php');

$id = required_param('id', PARAM_INT);
$returnurl = optional_param('returnurl', 0, PARAM_LOCALURL);

// Totara: Add the ability to redirect user out of here if this $id is a non-course.
$hook = new \totara_core\hook\enrol_index_page($id);
$hook->execute();

if (!isloggedin()) {
    $referer = get_local_referer();
    if (empty($referer)) {
        // A user that is not logged in has arrived directly on this page,
        // they should be redirected to the course page they are trying to enrol on after logging in.
        $SESSION->wantsurl = "$CFG->wwwroot/course/view.php?id=$id";
    }
    // do not use require_login here because we are usually coming from it,
    // it would also mess up the SESSION->wantsurl
    redirect(get_login_url());
}

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

// Everybody is enrolled on the frontpage
if ($course->id == SITEID) {
    redirect("$CFG->wwwroot/");
}

if (!totara_course_is_viewable($course->id)) {
    print_error('coursehidden');
}

$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/enrol/index.php', array('id'=>$course->id));

// do not allow enrols when in login-as session
if (\core\session\manager::is_loggedinas() and $USER->loginascontext->contextlevel == CONTEXT_COURSE) {
    print_error('loginasnoenrol', '', $CFG->wwwroot.'/course/view.php?id='.$USER->loginascontext->instanceid);
}

// get all enrol forms available in this course
$enrols = enrol_get_plugins(true);
$enrolinstances = enrol_get_instances($course->id, true);
$forms = array();
foreach($enrolinstances as $instance) {
    if (!isset($enrols[$instance->enrol])) {
        continue;
    }

    /** @var enrol_plugin $enrol_plugin */
    $enrol_plugin = $enrols[$instance->enrol];

    $user_enrolment = $DB->get_record('user_enrolments', array('userid' => $USER->id, 'enrolid' => $instance->id));

    if (!$user_enrolment) {
        // The first thing we need to do is load the form, which will process form submission (if it happened).
        $form = $enrol_plugin->enrol_page_hook($instance);
        // Then we need to re-check enrolment, which might have occurred if the form was submitted by the hook.
        $user_enrolment = $DB->get_record('user_enrolments', array('userid' => $USER->id, 'enrolid' => $instance->id));
        if ($user_enrolment && $instance->workflow_id && $user_enrolment->status == ENROL_USER_PENDING_APPLICATION) {
            // If the user just became enrolled, and the enrolment instance requires approval, and approval
            // is pending (should be if it was just created) then redirect to the application.
            $user_enrolment_application = user_enrolment_application::find_with_user_enrolment_id($user_enrolment->id);
            $course = $user_enrolment_application->get_course();
            $return_address = '/enrol/index.php?id=' . $course->id;
            $return_address_label = get_string('back_to_course', 'core_enrol');

            redirect(application_edit_controller::get_url_for($user_enrolment_application->approval_application_id, $return_address, $return_address_label));
        }
        if (!empty($form)) {
            // Show the enrolment plugin's form.
            $forms[$instance->id] = $form;
        }
        continue;
    }

    // User is enrolled. We won't show the enrolment form (if there is one) because enrolment is already complete.

    // Is approval required for this enrolment instance? If not, we can continue to the next instance.
    if (empty($instance->workflow_id)) {
        continue;
    }

    $user_enrolment_application = user_enrolment_application::find_with_user_enrolment_id($user_enrolment->id);

    if (!$user_enrolment_application) {
        $user_has_approval = false;
    } else {
        // Determine the status of their application.
        $user_has_approval = $user_enrolment->status != ENROL_USER_PENDING_APPLICATION;
    }

    // If the user has approval, we can continue to the next instance.
    if ($user_has_approval) {
        continue;
    }

    if (isset($user_enrolment_application->approval_application)) {
        $forms[$instance->id] = $enrol_plugin->get_request_approval_form($instance, $user_enrolment_application);
    } else {
        // If there is no application, show create new application form.
        $forms[$instance->id] = $enrol_plugin->get_create_new_application_form($instance, $user_enrolment);
    }
}

// Check if user already enrolled (and approved, if approval is required)
if (is_enrolled($context, $USER, '', true)) {
    $default_destination = "$CFG->wwwroot/course/view.php?id=$course->id";
    if (!empty($SESSION->wantsurl)) {
        // Check there hasn't been a user navigation earlier that inadvertently changed the SESSION->wantsurl value to
        // contain a different course id.
        $check_url = new moodle_url($SESSION->wantsurl);
        $session_course_id = $check_url->get_param('id');
        if ($session_course_id != $course->id && $check_url->get_path() === '/course/view.php') {
            $SESSION->wantsurl = $default_destination;
        }
        $destination = $SESSION->wantsurl;
        unset($SESSION->wantsurl);
    } else {
        $destination = $default_destination;
    }
    redirect($destination);   // Bye!
}
$string_field_formatter = new string_field_formatter(format::FORMAT_PLAIN, $context);
$PAGE->set_title(get_string('coursetitle', 'moodle', array('course' => $course->fullname)));
$PAGE->set_heading($string_field_formatter->format($course->fullname));

echo $OUTPUT->header();
echo $OUTPUT->page_main_heading($string_field_formatter->format($course->fullname));

// This function call has a number of side effects (including loading the classes below)
$courserenderer = $PAGE->get_renderer('core', 'course');

$chelper = new coursecat_helper();
$chelper->set_show_courses(core_course_renderer::COURSECAT_SHOW_COURSES_EXPANDED);
$chelper->set_show_course_links(false);
$list_course = new course_in_list($course);
$summary = $chelper->get_course_formatted_summary(
    $list_course,
    ['overflowdiv' => true, 'noclean' => true, 'para' => false]
);

$course_images = [];
$course_files = [];

foreach ($list_course->get_course_overviewfiles() as $file) {
    $is_image = $file->is_valid_image();
    $url = moodle_url::make_pluginfile_url(
        $file->get_contextid(),
        $file->get_component(),
        $file->get_filearea(),
        null,
        $file->get_filepath(),
        $file->get_filename(),
        !$is_image
    );

    if ($is_image) {
        $course_images[] = [
            'url' => $url->out(),
            'alt' => ''
        ];
    } else {
        $course_files[] = [
            'url' => $url->out(),
            'icon' => $OUTPUT->pix_icon(file_file_icon($file, 24), $file->get_filename(), 'moodle'),
            'name' => $file->get_filename()
        ];
    }
}

$contacts = [];
foreach ($list_course->get_course_contacts() as $contact) {
    $contacts[] = [
        'id' => $contact['user']->id,
        'name' => $contact['username'],
        'role' => $contact['rolename'],
    ];
}

$componentdata = [
    'image' => course_get_image($course)->out(),
    'summary' => $summary,
    'summary-images' => $course_images,
    'summary-files' => $course_files,
    'contacts' => $contacts,
];

$vue_component = new component('core_course/components/course_info/CourseInfo', $componentdata);
echo '<div class="core_enrol--tuiContainer">';
echo $vue_component->out_html();
echo '</div>';

echo $OUTPUT->heading(get_string('enrolmentoptions', 'enrol'), 2);

//TODO: find if future enrolments present and display some info

foreach ($forms as $form) {
    echo $form;
}

if (!$forms) {
    // Totara: ignore the wanted URL, most likely we cannot go there without enrolment.
    unset($SESSION->wantsurl);
    if (isguestuser()) {
        echo get_string('noguestaccess', 'enrol') . ' ' . html_writer::link(get_login_url(), get_string('login', 'core'), array('class' => 'btn btn-default'));
    } else if ($returnurl) {
        notice(get_string('notenrollable', 'enrol'), $returnurl);
    } else {
        $url = get_local_referer(false);
        if (empty($url)) {
            $url = new moodle_url('/index.php');
        }
        notice(get_string('notenrollable', 'enrol'), $url);
    }
}

echo $OUTPUT->footer();
