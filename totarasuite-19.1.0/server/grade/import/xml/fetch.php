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

define('NO_MOODLE_COOKIES', true); // session not used here
global $CFG, $PAGE, $DB, $OUTPUT;

require_once '../../../config.php';
require_once 'lib.php';
require_once $CFG->libdir.'/filelib.php';

if (empty($CFG->gradepublishing)) {
    print_error('gradepubdisable');
}

$id = required_param('id', PARAM_INT); // course id
$gradesurl = required_param('url', PARAM_URL); // only real urls here
$feedback = optional_param('feedback', 0, PARAM_BOOL);

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('invalidcourseid');
}

require_user_key_login('grade/import', $id); // we want different keys for each course
require_login($course);

$context = context_course::instance($id);
require_capability('gradeimport/xml:publish', $context);
require_capability('moodle/grade:import', $context);
require_capability('gradeimport/xml:view', $context);

$page_url = new moodle_url('/grade/import/xml/fetch.php', array('id' => $id, 'url' => $gradesurl));
if ($feedback !== 0) {
    $page_url->param('feedback', $feedback);
}
$PAGE->set_url($page_url);

// Large files are likely to take their time and memory. Let PHP know
// that we'll take longer, and that the process should be recycled soon
// to free up memory.
core_php_time_limit::raise();
raise_memory_limit(MEMORY_EXTRA);

$data = (object)array('id' => $id, 'url' => $gradesurl, 'feedback' => $feedback);
import_xml_grades_from_url($data);


