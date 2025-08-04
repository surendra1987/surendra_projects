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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon Player <simon.player@totara.com>
 * @package totara_program
 */

namespace totara_program\content\course_sets;

use core\format;
use core\webapi\formatter\field\string_field_formatter;
use totara_core\progressinfo\progressinfo;
use totara_program\content\course_set;
use totara_program\content\program_content;
use totara_program\program;
use totara_program\utils;
use totara_tui\output\component;

class multi_course_set extends course_set  {

    public $courses, $courses_deleted_ids;
    public $mincourses, $coursesumfield, $coursesumfieldtotal;

    /**
     * @inheritdoc
     */
    public function __construct(int $programid, $setob = null, string $uniqueid = null) {
        global $DB;
        parent::__construct($programid, $setob, $uniqueid);

        $this->contenttype = program_content::CONTENTTYPE_MULTICOURSE;
        $this->courses = array();
        $this->courses_deleted_ids = array();

        $this->mincourses = 0;
        $this->coursesumfield = 0;
        $this->coursesumfieldtotal = 0;

        if (is_object($setob)) {
            $courseset_courses = $DB->get_records('prog_courseset_course', array('coursesetid' => $this->id), 'sortorder ASC');
            foreach ($courseset_courses as $courseset_course) {
                $course = $DB->get_record('course', array('id' => $courseset_course->courseid));
                if (!$course) {
                    // if the course has been deleted before being removed from the program we remove it from the course set
                    $DB->delete_records('prog_courseset_course', array('id' => $courseset_course->id));
                } else {
                    $this->courses[] = $course;
                }
            }

            $this->mincourses = $setob->mincourses;
            $this->coursesumfield = $setob->coursesumfield;
            $this->coursesumfieldtotal = $setob->coursesumfieldtotal;
        }
    }

    /**
     * @inheritdoc
     */
    public function init_form_data(string $formnameprefix, $formdata): void {
        global $DB;
        parent::init_form_data($formnameprefix, $formdata);

        $this->completiontype = $formdata->{$formnameprefix.'completiontype'};

        $this->mincourses = isset($formdata->{$formnameprefix.'mincourses'})
        && ! empty($formdata->{$formnameprefix.'mincourses'}) ? $formdata->{$formnameprefix.'mincourses'} : '';

        $this->coursesumfield = isset($formdata->{$formnameprefix.'coursesumfield'})
        && ! empty($formdata->{$formnameprefix.'coursesumfield'}) ? $formdata->{$formnameprefix.'coursesumfield'} : '';

        $this->coursesumfieldtotal = isset($formdata->{$formnameprefix.'coursesumfieldtotal'})
        && ! empty($formdata->{$formnameprefix.'coursesumfieldtotal'}) ? $formdata->{$formnameprefix.'coursesumfieldtotal'} : '';

        if (isset($formdata->{$formnameprefix.'courses'})) {
            $courseids = explode(',', $formdata->{$formnameprefix.'courses'});
            foreach ($courseids as $courseid) {
                if ($courseid && $course = $DB->get_record('course', array('id' => $courseid))) {
                    $this->courses[] = $course;
                }
            }
        }

        $this->courses_deleted_ids = $this->get_deleted_courses($formdata);

    }

    /**
     * Retrieves the ids of any deleted courses for this course set from the
     * submitted data and returns an array containing the course id numbers
     * or an empty array
     *
     * @param \stdClass $formdata
     * @return array of course ids
     */
    public function get_deleted_courses($formdata): array {

        $prefix = $this->get_set_prefix();

        if (!isset($formdata->{$prefix.'deleted_courses'}) || empty($formdata->{$prefix.'deleted_courses'})) {
            return array();
        }
        return explode(',', $formdata->{$prefix.'deleted_courses'});
    }

    /**
     * @inheritdoc
     */
    public function check_course_action(string $action, $formdata) {

        $prefix = $this->get_set_prefix();

        foreach ($this->courses as $course) {
            if (isset($formdata->{$prefix . $action . '_' . $course->id})) {
                return $course->id;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function save_set(): bool {
        global $DB;
        // Make sure the course set is saved with a sensible label instead of the default
        if ($this->label == $this->get_default_label()) {
            $this->label = get_string('legend:courseset', 'totara_program', $this->sortorder);
        }

        if (empty($this->islastset)) {
            $nextsetoperator = $this->nextsetoperator;
        } else {
            // We should have an empty nextsetoperator if this is the last course set.
            $nextsetoperator = 0;
        }

        $todb = new \stdClass();
        $todb->programid = $this->programid;
        $todb->sortorder = $this->sortorder;
        $todb->competencyid = $this->competencyid;
        $todb->nextsetoperator = $nextsetoperator;
        $todb->completiontype = $this->completiontype;
        $todb->timeallowed = $this->timeallowed;
        $todb->recurrencetime = $this->recurrencetime;
        $todb->recurcreatetime = $this->recurcreatetime;
        $todb->contenttype = $this->contenttype;
        $todb->label = $this->label;
        $todb->certifpath = $this->certifpath;

        $todb->mincourses = $this->mincourses;
        $todb->coursesumfield = $this->coursesumfield;
        $todb->coursesumfieldtotal = $this->coursesumfieldtotal;

        if ($this->id == 0) { // if this set doesn't already exist in the database
            $id = $DB->insert_record('prog_courseset', $todb);

            $this->id = $id;
        } else {
            $todb->id = $this->id;
            $DB->update_record('prog_courseset', $todb);
        }

        return $this->save_courses();
    }

    /**
     * Save course
     *
     * @return bool
     */
    public function save_courses(): bool {
        global $DB;
        if (!$this->id) {
            return false;
        }

        // first get program enrolment plugin class
        $program_plugin = enrol_get_plugin('totara_program');
        // then delete any courses from the database that have been marked for deletion
        foreach ($this->courses_deleted_ids as $courseid) {
            if ($courseset_course = $DB->get_record('prog_courseset_course',
                array('coursesetid' => $this->id, 'courseid' => $courseid))) {
                $DB->delete_records('prog_courseset_course', array('coursesetid' => $this->id, 'courseid' => $courseid));
            }
        }

        //if the course no longer exists in any programs, remove the program enrolment plugin
        $courses_still_associated = prog_get_courses_associated_with_programs($this->courses_deleted_ids);
        $courses_to_remove_plugin_from = array_diff($this->courses_deleted_ids, array_keys($courses_still_associated));
        foreach ($courses_to_remove_plugin_from as $courseid) {
            $instance = $program_plugin->get_instance_for_course($courseid);
            if ($instance) {
                $program_plugin->delete_instance($instance);
            }
        }


        // then add any new courses
        $sortorder = 1;
        foreach ($this->courses as $course) {
            if (!$ob = $DB->get_record('prog_courseset_course', array('coursesetid' => $this->id, 'courseid' => $course->id))) {
                //check if program enrolment plugin is already enabled on this course
                $instance = $program_plugin->get_instance_for_course($course->id);
                if (!$instance) {
                    //add it
                    $program_plugin->add_instance($course);
                }
                $ob = new \stdClass();
                $ob->coursesetid = $this->id;
                $ob->courseid = $course->id;
                $ob->sortorder = $sortorder++;
                $DB->insert_record('prog_courseset_course', $ob);
            } else {
                // Update.
                $ob->sortorder = $sortorder++;
                $DB->update_record('prog_courseset_course', $ob);
            }
        }
        return true;
    }

    /**
     * Add course
     *
     * @param \stdClass $formdata
     * @return bool
     */
    public function add_course($formdata): bool {
        global $DB;
        $courseid_elementname = $this->get_set_prefix().'courseid';

        if (isset($formdata->$courseid_elementname)) {
            $courseid = $formdata->$courseid_elementname;
            foreach ($this->courses as $course) {
                if ($courseid == $course->id) {
                    return true;
                }
            }
            $course = $DB->get_record('course', array('id' => $courseid));
            $this->courses[] = $course;
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function delete_course(int $courseid): bool {
        global $DB;
        $new_courses = array();
        $coursefound = false;

        foreach ($this->courses as $course) {
            if ($course->id != $courseid) {
                $new_courses[] = $course;
            } else {
                if ($courseset_course = $DB->get_record('prog_courseset_course',
                    array('coursesetid' => $this->id, 'courseid' => $course->id))) {
                    $this->courses_deleted_ids[] = $course->id;
                }
                $coursefound = true;
            }
        }

        if ($coursefound) {
            $this->courses = $new_courses;
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function contains_course(int $courseid): bool {

        $courses = $this->courses;

        foreach ($courses as $course) {
            if ($course->id == $courseid) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function check_courseset_complete(int $userid) {
        $courses = $this->courses;
        $completiontype = $this->completiontype;

        // Check if this is an 'optional' or 'some 0' course set where the user doesn't have to complete any courses.
        if ($completiontype == self::COMPLETIONTYPE_OPTIONAL ||
            $completiontype == self::COMPLETIONTYPE_SOME && $this->mincourses == 0 && $this->coursesumfieldtotal == 0) {
            $completionsettings = array(
                'status'        => program::STATUS_COURSESET_COMPLETE,
                'timecompleted' => time()
            );
            return $this->update_courseset_complete($userid, $completionsettings);
        }

        // Check that the course set contains at least one course.
        if (!count($courses)) {
            return false;
        }

        $incomplete = false;
        $timestarted = 0;
        $completedcourses = 0;
        $coursefieldvalsum = 0;
        foreach ($courses as $course) {
            // create a new completion object for this course
            $completion_info = new \completion_info($course);

            $params = array('userid' => $userid, 'course' => $course->id);
            $completion_completion = new \completion_completion($params);

            // check if the course is complete
            if ($completion_completion->is_complete()) {
                if ($completiontype == self::COMPLETIONTYPE_ANY) {
                    $completionsettings = array(
                        'status'        => program::STATUS_COURSESET_COMPLETE,
                        'timestarted'   => $completion_completion->timestarted,
                        'timecompleted' => $completion_completion->timecompleted
                    );
                    return $this->update_courseset_complete($userid, $completionsettings);
                } else if ($completiontype == self::COMPLETIONTYPE_SOME) {
                    $completedcourses++;
                    if ($completedcourses >= $this->mincourses) {
                        $coursessatisfied = true;
                    }

                    if ($sumfield = customfield_get_field_instance($course, $this->coursesumfield, 'course', 'course')) {
                        $sumfieldval = $sumfield->display_data();
                        if ($sumfieldval === (string)(int)$sumfieldval) {
                            $coursefieldvalsum += (int)$sumfieldval;
                        }
                    }
                    if ($coursefieldvalsum >= $this->coursesumfieldtotal) {
                        $fieldsumsatisfied = true;
                    }

                    if (!empty($coursessatisfied) && !empty($fieldsumsatisfied)) {
                        return $this->complete_courseset_latest_completion($userid, $courses);
                    }
                }

                if (!empty($completion_completion->timestarted) && (empty($timestarted) || $completion_completion->timestarted < $timestarted)) {
                    $timestarted = $completion_completion->timestarted;
                }
            } else {
                $incomplete = true;
                if (!empty($completion_completion->timestarted) && (empty($timestarted) || $completion_completion->timestarted < $timestarted)) {
                    $timestarted = $completion_completion->timestarted;
                }
            }
        }

        // Now we have the earliest time started, mark the course set as started.
        if (!empty($timestarted)) {
            $this->mark_started($userid, $timestarted);
        }

        // If there is an incomplete course in a complete all course set return false.
        if ($completiontype == self::COMPLETIONTYPE_ALL && $incomplete) {
            return false;
        }

        // If processing reaches here and all courses in this set must be completed then
        // the course set is complete.
        if ($completiontype == self::COMPLETIONTYPE_ALL) {
            return $this->complete_courseset_latest_completion($userid, $courses);
        }

        return false;
    }

    /**
     * Set the timestarted field in the user's program completion record.
     *
     * If the user doesn't currently have a program completion record then it will be created. Certifications
     * may be restored from history.
     *
     * @param int $userid
     * @param int $timestarted
     * @return void
     */
    private function mark_started(int $userid, int $timestarted): void {
        global $DB;

        // Check what to do with the course set completion record.
        $csetcomp = prog_load_courseset_completion($this->id, $userid, false);

        if (!empty($csetcomp)) {
            // Course set completion record exists, so we might update it.
            if (empty($csetcomp->timestarted)) {
                $csetcomp->timestarted = $timestarted;

                prog_write_courseset_completion($csetcomp, "Courseset completion marked as started");
            }
        } else {
            // Course set completion record doesn't exist, so we create it and set time started.
            $data = array('timestarted' => $timestarted);
            prog_create_courseset_completion($this->id, $userid, $data, "Courseset completion created and marked as started");
        }

        $sql = "SELECT id FROM {prog} WHERE id = :programid AND certifid IS NOT NULL";
        $iscertif = $DB->record_exists_sql($sql, array('programid' => $this->programid));

        // Check what to do with the program completion record.
        if ($iscertif) {
            list($certcomp, $progcomp) = certif_load_completion($this->programid, $userid, false);
        } else {
            $progcomp = prog_load_completion($this->programid, $userid, false);
        }

        if (!empty($progcomp)) {
            // Program completion record exists, so we might update it.
            if (empty($progcomp->timestarted)) {
                $progcomp->timestarted = $timestarted;

                $message = "Program completion marked as started for courseset {$this->id}";
                if (!empty($certcomp)) {
                    certif_write_completion($certcomp, $progcomp, $message);
                } else {
                    prog_write_completion($progcomp, $message);
                }
            }
        } else {
            // Program completion record doesn't exist, so we create it and set time started.
            if ($iscertif) {
                // Certif_create_completion may restore a previous assignment.
                certif_create_completion($this->programid, $userid, "Completion created in mark_started");
                list($certcomp, $progcomp) = certif_load_completion($this->programid, $userid);
                // Double-check in case we restored a previous completion that already had a timestarted.
                if (empty($progcomp->timestarted)) {
                    $progcomp->timestarted = $timestarted;
                    certif_write_completion($certcomp, $progcomp, "Program completion marked as started due to courseset {$this->id}");
                }
            } else {
                $data = array('timestarted' => $timestarted);
                prog_create_completion($this->programid, $userid, $data, "Program completion created and marked as started due to courseset {$this->id}");
            }
        }
    }

    /**
     * Complete the course set, using the latest course completion in the set.
     *
     * @param int $userid
     * @param array $courses to use for deternminig the latest completion
     *
     * @return bool|int
     */
    private function complete_courseset_latest_completion(int $userid, array $courses) {
        global $DB;

        // Get the last course completed so we can use that timestamp for the course set.
        $courseids = array();
        foreach ($courses as $course) {
            $courseids[] = $course->id;
        }

        list($incourse, $params) = $DB->get_in_or_equal($courseids);
        $sql = "SELECT MAX(timecompleted) AS timecompleted
            FROM {course_completions}
            WHERE course $incourse
            AND userid = ?";
        $params[] = $userid;
        $completion = $DB->get_record_sql($sql, $params);

        $completionsettings = array(
            'status'        => program::STATUS_COURSESET_COMPLETE,
            'timecompleted' => $completion->timecompleted
        );

        return $this->update_courseset_complete($userid, $completionsettings);
    }


    /**
     * @inheritdoc
     */
    public function display(
        int $userid=null,
        array $previous_sets = [],
        array $next_sets = [],
        bool $accessible = true,
        bool $viewinganothersprogram = false,
        bool $hide_progress = false
    ): string {
        global $USER, $OUTPUT, $DB, $CFG;

        //----------------
        $componentdata = [];
        $componentdata['courses'] = [];

        //----------------

        if ($userid) {
            $usercontext = \context_user::instance($userid);
        }

        $out = '';
        $out .= \html_writer::start_tag('div', array('class' => 'surround display-program'));
        $out .= $OUTPUT->heading(format_string($this->label), 3);

        switch ($this->completiontype) {
            case self::COMPLETIONTYPE_ALL:
                $out .= \html_writer::tag('p', \html_writer::tag('strong', get_string('completeallcourses', 'totara_program')));
                break;
            case self::COMPLETIONTYPE_ANY:
                $out .= \html_writer::tag('p', \html_writer::tag('strong', get_string('completeanycourse', 'totara_program')));
                break;
            case self::COMPLETIONTYPE_SOME:
                $str = new \stdClass();
                $str->mincourses = $this->mincourses;
                $str->sumfield = '';
                if ($coursecustomfield = $DB->get_record('course_info_field', array('id' => $this->coursesumfield))) {
                    $str->sumfield = format_string($coursecustomfield->fullname);
                }
                $str->sumfieldtotal = $this->coursesumfieldtotal;
                if (!empty($str->mincourses) && !empty($str->sumfield) && !empty($str->sumfieldtotal)) {
                    $completestr = get_string('completemincoursesminsum', 'totara_program', $str);
                } else if (!empty($this->mincourses)) {
                    $completestr = get_string('completemincourses', 'totara_program', $str);
                } else {
                    $completestr = get_string('completeminsumfield', 'totara_program', $str);
                }


                $out .= \html_writer::tag('p', \html_writer::tag('strong', $completestr));
                if (!empty($str->sumfield)) {
                    // Add information about the criteria.
                    $criteriacompletionset = get_string('criteriacompletioncourseset', 'totara_program', $str->sumfield);
                    $out .= \html_writer::tag('p', \html_writer::tag('strong', $criteriacompletionset));
                }

                break;
            case self::COMPLETIONTYPE_OPTIONAL:
                $out .= \html_writer::tag('p', \html_writer::tag('strong', get_string('completeoptionalcourses', 'totara_program')));
                break;
        }

        $numperiod = utils::get_duration_num_and_period($this->timeallowed);

        if ($this->timeallowed > 0) {
            $out .= \html_writer::tag('p', get_string('allowtimeforset' . $numperiod->periodkey, 'totara_program', $numperiod->num));
        }

        $customfieldcriteria = $this->completiontype == self::COMPLETIONTYPE_SOME &&
            $coursecustomfield && $coursecustomfield->hidden != 1;
        if (count($this->courses) > 0) {
            foreach ($this->courses as $course) {
                $vue_course = [];
                $coursecontext = \context_course::instance($course->id);
                $string_field_formatter = new string_field_formatter(format::FORMAT_PLAIN, $coursecontext);
                $vue_course['fullname'] = $string_field_formatter->format($course->fullname);
                $vue_course['id'] = $course->id;
                $vue_course['image'] = course_get_image($course->id)->out();

                $vue_course['no_criteria'] = false;
                if ($userid && $accessible) {
                    $showcourseset = totara_course_is_viewable($course->id, $userid);
                } else {
                    $showcourseset = is_viewing($coursecontext, $userid ? $userid : $USER->id) ? true :
                        totara_course_is_viewable($course->id, $userid) && is_enrolled($coursecontext, $userid ? $userid : $USER->id, '', true);
                }

                // Site admin can access any course.
                if (is_siteadmin($USER->id)) {
                    $vue_course['launchURL'] = (new \moodle_url('/course/view.php', array('id' => $course->id)))->out(false);
                } else {
                    // User must be enrolled or course can be viewed (checks audience visibility),
                    // And course must be accessible.
                    if ($showcourseset) {
                        $vue_course['launchURL'] = (new \moodle_url('/course/view.php', array('id' => $course->id)))->out(false);
                    } else if ($userid && $accessible && !empty($CFG->audiencevisibility) && $course->audiencevisible != COHORT_VISIBLE_NOUSERS) {
                        // If the program has been assigned but the user is not yet enrolled in the course,
                        // a course with audience visibility set to "Enrolled users" would not allow the user to become enrolled.
                        // Instead, when accessing the course, we redirect to the program requirements page, which will then directly enrol them into the course.
                        // This isn't needed for normal visibility because if the course is hidden then it will be inaccessible anyway.
                        $params = array('id' => $this->programid, 'cid' => $course->id, 'userid' => $userid, 'sesskey' => $USER->sesskey);
                        $vue_course['launchURL'] = (new \moodle_url('/totara/program/required.php', $params))->out(false);
                    }
                }
                if ($customfieldcriteria) {
                    $customfieldsdata = customfield_get_data($course, 'course', 'course');
                    $criteria = $coursecustomfield->defaultdata;
                    if (array_key_exists($coursecustomfield->fullname, $customfieldsdata)) {
                        $criteria = $customfieldsdata[$coursecustomfield->fullname];
                    }
                    $vue_course['score'] = $criteria;
                }

                if ($userid && !$hide_progress) {

                    $completion = new \completion_completion(['userid' => $userid, 'course' => $course->id]);
                    $progress_info = $completion->get_progressinfo();
                    $percent = $progress_info->get_percentagecomplete();

                    // For the purposes of displaying the progress bar in the course card, we
                    // do not distinguish between whether the course completion is untracked,
                    // or whether there is no completion criteria.
                    $vue_course['no_criteria'] = $progress_info->count_criteria() == 0 && $percent !== 100;
                    $customdata = $progress_info->get_customdata();
                    if ($percent === false && isset($customdata['enabled']) && $customdata['enabled'] === false) {
                        $vue_course['no_criteria'] = true;
                    }
                    if ($progress_info->get_percentagecomplete() !== false) {
                        $vue_course['progress'] = $progress_info->get_percentagecomplete() ?: 0;
                    }

                    $markstaff = (\totara_job\job_assignment::is_managing($USER->id, $userid) && has_capability('totara/program:markstaffcoursecomplete', $usercontext));
                    $markuser = has_capability('totara/core:markusercoursecomplete', $usercontext);
                    $markcourse = has_capability('totara/program:markcoursecomplete', $coursecontext);
                    if ($accessible && ($markstaff || $markuser || $markcourse)) {
                        $vue_course['can_mark_complete'] = true;
                        $completion_info = new \completion_info($course);

                        if ($completion_info->is_course_complete($userid)) {
                            $vue_course['is_complete'] = true;
                        } else {
                            $vue_course['is_complete'] = false;
                        }

                        $urlparams = array(
                            'userid' => $userid,
                            'courseid' => $course->id,
                            'progid' => $this->programid,
                            'sesskey' => sesskey()
                        );

                        $url = new \moodle_url('/totara/program/content/completecourse.php', $urlparams);
                        $vue_course['completeURL'] = $url->out(false);
                    }
                }
                $componentdata['courses'][] = $vue_course;
            }
            $vue_component = new component('totara_program/components/learner/CourseSet', $componentdata);
            $out .= $vue_component->out_html();
        } else {
            $out .= \html_writer::tag('p', get_string('nocourses', 'totara_program'));
        }

        $out .= \html_writer::end_tag('div');
        
        return $out;
    }

    /**
     * @inheritdoc
     */
    public function display_form_element(): string {

        $completiontypestr = $this->get_completion_type_string();
        $courses = $this->courses;

        $out = '';
        $out .= \html_writer::start_tag('div', array('class' => 'courseset'));
        $out .= \html_writer::start_tag('div', array('class' => 'courses'));

        if (count($courses)) {
            $coursestr = '';
            foreach ($courses as $course) {
                $coursestr .= format_string($course->fullname).' '.$completiontypestr.' ';
            }
            $coursestr = trim($coursestr);
            $coursestr = rtrim($coursestr, $completiontypestr);
            $out .= $coursestr;
        } else {
            $out .= get_string('nocourses', 'totara_program');
        }

        $out .= \html_writer::end_tag('div');
        $out .= $this->get_completion_explanation_html();

        if (!isset($this->islastset) || $this->islastset === false) {
            if ($this->nextsetoperator != 0) {
                $out .= \html_writer::start_tag('div', array('class' => 'nextsetoperator'));
                if ($this->nextsetoperator == self::NEXTSETOPERATOR_THEN) {
                    $out .= get_string('then', 'totara_program');
                } else if ($this->nextsetoperator == self::NEXTSETOPERATOR_OR) {
                    $out .= get_string('or', 'totara_program');
                } else {
                    $out .= get_string('and', 'totara_program');
                }
                $out .= \html_writer::end_tag('div');
            }
        }
        $out .= \html_writer::end_tag('div');

        return $out;
    }

    /**
     * @inheritdoc
     */
    public function print_set_minimal(): string {
        global $CFG;

        require_once($CFG->dirroot . '/totara/certification/lib.php');

        $prefix = $this->get_set_prefix();

        $out = '';
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."id", 'value' => $this->id));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."label", 'value' => ''));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."sortorder", 'value' => $this->sortorder));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."contenttype", 'value' => $this->contenttype));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."nextsetoperator", 'value' => ''));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."completiontype", 'value' => self::COMPLETIONTYPE_ALL));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."mincourses", 'value' => 0));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."coursesumfield", 'value' => 0));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."coursesumfieldtotal", 'value' => 0));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."timeallowedperiod", 'value' => utils::TIME_SELECTOR_DAYS));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."timeallowednum", 'value' => '1'));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."certifpath", 'value' => CERTIFPATH_CERT));

        if (isset($this->courses) && is_array($this->courses) && count($this->courses)>0) {
            $courseidsarray = array();
            foreach ($this->courses as $course) {
                $courseidsarray[] = $course->id;
            }
            $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."courses", 'value' => implode(',', $courseidsarray)));
        } else {
            $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."courses", 'value' => ''));
        }

        return $out;
    }

    /**
     * Print courses
     *
     * @return string
     */
    public function print_courses(): string {
        $prefix = $this->get_set_prefix();

        $out = '';
        $out .= \html_writer::start_tag('div', array('class' => 'fitem'));
        $out .= \html_writer::tag('div', get_string('courses', 'totara_program'). ':', array('class' => 'fitemtitle'));
        if (isset($this->courses) && is_array($this->courses) && count($this->courses)>0) {

            if (!$completiontypestr = $this->get_completion_type_string()) {
                print_error('unknowncompletiontype', 'totara_program', '', $this->sortorder);
            }

            $coursesinsetcount = count($this->courses);
            $firstcourse = true;
            $list = '';

            foreach ($this->courses as $course) {
                $course->coursesetid = $this->id;

                if ($firstcourse) {
                    $content = \html_writer::tag('span', '&nbsp;', array('class' => 'operator'));
                    $firstcourse = false;
                } else {
                    $content = \html_writer::tag('span', $completiontypestr, array('class' => 'operator'));
                }

                $content .= $this->course_content_table_toolbar($course, $prefix);

                $list .= \html_writer::tag('li', $content, array('data-courseid' => $course->id));
            }
            $ulattrs = array('id' => $prefix.'courselist', 'class' => 'course_list');
            $out .= \html_writer::tag('div', \html_writer::tag('ul', $list, $ulattrs), array('class' => 'felement'));

            $courseidsarray = array();
            foreach ($this->courses as $course) {
                $courseidsarray[] = $course->id;
            }

            $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix.'courses', 'value' => implode(',', $courseidsarray)));
        } else {
            $out .= \html_writer::tag('div', get_string('nocourses', 'totara_program'), array('class' => 'felement'));
            $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix.'courses', 'value' => ''));
        }
        $out .= \html_writer::end_tag('div'); // End fitem.

        return $out;
    }

    /**
     * Print deleted courses
     *
     * @return string
     */
    public function print_deleted_courses(): string {
        $prefix = $this->get_set_prefix();

        $out = '';
        $deletedcourseidsarray = array();

        if ($this->courses_deleted_ids) {
            foreach ($this->courses_deleted_ids as $deleted_course_id) {
                $deletedcourseidsarray[] = $deleted_course_id;
            }
        }

        $deletedcoursesstr = implode(',', $deletedcourseidsarray);
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."deleted_courses", 'value' => $deletedcoursesstr));
        return $out;
    }

    /**
     * @inheritdoc
     */
    public function get_courseset_form_template(&$mform, array &$template_values, &$formdataobject, bool $updateform = true): string {
        global $OUTPUT, $DB;
        $prefix = $this->get_set_prefix();

        $templatehtml = '';
        $templatehtml .= \html_writer::start_tag('fieldset', array('id' => $prefix, 'class' => 'surround course_set edit-program'));

        $helpbutton = $OUTPUT->help_icon('multicourseset', 'totara_program');
        $legend = ((isset($this->label) && ! empty($this->label)) ? format_string($this->label) : get_string('untitledset', 'totara_program',
                $this->sortorder)) . ' ' . $helpbutton;
        $templatehtml .= \html_writer::tag('legend', $legend);


        // Add set buttons
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'setbuttons'));

        // Add the move up button for this set
        if ($updateform) {
            $attributes = array();
            $attributes['class'] = 'btn-cancel moveup fieldsetbutton';
            if (isset($this->isfirstset)) {
                $attributes['disabled'] = 'disabled';
                $attributes['class'] .= ' disabled';
            }
            $mform->addElement('submit', $prefix.'moveup', get_string('moveup', 'totara_program'), $attributes);
            $template_values['%'.$prefix.'moveup%'] = array('name' => $prefix.'moveup', 'value' => null);
            $templatehtml .= '%'.$prefix.'moveup%'."\n";

            // Add the move down button for this set
            $attributes = array();
            $attributes['class'] = 'btn-cancel movedown fieldsetbutton';
            if (isset($this->islastset)) {
                $attributes['disabled'] = 'disabled';
                $attributes['class'] .= ' disabled';
            }
            $mform->addElement('submit', $prefix.'movedown', get_string('movedown', 'totara_program'), $attributes);
            $template_values['%'.$prefix.'movedown%'] = array('name' => $prefix.'movedown', 'value' => null);
            $templatehtml .= '%'.$prefix.'movedown%'."\n";

            // Add the delete button for this set
            $mform->addElement('submit', $prefix.'delete', get_string('delete', 'totara_program'),
                array('class' => "btn-cancel delete fieldsetbutton setdeletebutton"));
            $template_values['%'.$prefix.'delete%'] = array('name' => $prefix.'delete', 'value' => null);
            $templatehtml .= '%'.$prefix.'delete%'."\n";
        }

        $templatehtml .= \html_writer::end_tag('div');


        // Add the course set id
        if ($updateform) {
            $mform->addElement('hidden', $prefix.'id', $this->id);
            $mform->setType($prefix.'id', PARAM_INT);
            $mform->setConstant($prefix.'id', $this->id);
            $template_values['%'.$prefix.'id%'] = array('name' => $prefix.'id', 'value' => null);
        }
        $templatehtml .= '%'.$prefix.'id%'."\n";
        $formdataobject->{$prefix.'id'} = $this->id;

        // Add the course set sort order
        if ($updateform) {
            $mform->addElement('hidden', $prefix.'sortorder', $this->sortorder);
            $mform->setType($prefix.'sortorder', PARAM_INT);
            $mform->setConstant($prefix.'sortorder', $this->sortorder);
            $template_values['%'.$prefix.'sortorder%'] = array('name' => $prefix.'sortorder', 'value' => null);
        }
        $templatehtml .= '%'.$prefix.'sortorder%'."\n";
        $formdataobject->{$prefix.'sortorder'} = $this->sortorder;

        // Add the course set content type
        if ($updateform) {
            $mform->addElement('hidden', $prefix.'contenttype', $this->contenttype);
            $mform->setType($prefix.'contenttype', PARAM_INT);
            $mform->setConstant($prefix.'contenttype', $this->contenttype);
            $template_values['%'.$prefix.'contenttype%'] = array('name' => $prefix.'contenttype', 'value' => null);
        }
        $templatehtml .= '%'.$prefix.'contenttype%'."\n";
        $formdataobject->{$prefix.'contenttype'} = $this->contenttype;

        // Add the list of deleted courses
        $templatehtml .= \html_writer::start_tag('div', array('id' => $prefix.'deletedcourseslist'));
        $templatehtml .= $this->get_deleted_courses_form_template($mform, $template_values, $formdataobject, $updateform);
        $templatehtml .= \html_writer::end_tag('div');

        // Add the course set label
        if ($updateform) {
            $mform->addElement('text', $prefix.'label', $this->label, array('size' => '40', 'maxlength' => '255', 'id' => $prefix.'label'));
            $mform->setType($prefix.'label', PARAM_TEXT);
            $template_values['%'.$prefix.'label%'] = array('name' => $prefix.'label', 'value' => null);
        }
        $helpbutton = $OUTPUT->help_icon('setlabel', 'totara_program');
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitem'));
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitemtitle'));
        $templatehtml .= \html_writer::tag('label', get_string('label:setname', 'totara_program') . ' ' . $helpbutton,
            array('for' => $prefix.'label'));
        $templatehtml .= \html_writer::end_tag('div');
        $templatehtml .= \html_writer::tag('div', '%'.$prefix.'label%', array('class' => 'felement'));
        $templatehtml .= \html_writer::end_tag('div');
        $formdataobject->{$prefix.'label'} = $this->label;

        // Add the completion type drop down field
        if ($updateform) {
            $completiontypeoptions = array(
                self::COMPLETIONTYPE_ANY => get_string('onecourse', 'totara_program'),
                self::COMPLETIONTYPE_ALL => get_string('allcourses', 'totara_program'),
                self::COMPLETIONTYPE_SOME => get_string('somecourses', 'totara_program'),
                self::COMPLETIONTYPE_OPTIONAL => get_string('completionoptional', 'totara_program'),
            );
            $onchange = 'return M.totara_programcontent.changeCompletionTypeString('.$prefix.');';
            $mform->addElement('select', $prefix.'completiontype', get_string('label:learnermustcomplete', 'totara_program'),
                $completiontypeoptions, array('class' => 'completiontype', 'onchange' => $onchange, 'id' => $prefix.'completiontype'));
            $mform->setType($prefix.'completiontype', PARAM_INT);
            $mform->setDefault($prefix.'completiontype', self::COMPLETIONTYPE_ALL);
            $template_values['%'.$prefix.'completiontype%'] = array('name' => $prefix.'completiontype', 'value' => null);
        }
        $helpbutton = $OUTPUT->help_icon('completiontype', 'totara_program');
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitem'));
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitemtitle'));
        $templatehtml .= \html_writer::tag('label', get_string('label:learnermustcomplete', 'totara_program') . ' ' . $helpbutton,
            array('for' => $prefix.'completiontype'));
        $templatehtml .= \html_writer::end_tag('div');
        $templatehtml .= \html_writer::tag('div', '%'.$prefix.'completiontype%', array('class' => 'felement'));
        $templatehtml .= \html_writer::end_tag('div');
        $formdataobject->{$prefix.'completiontype'} = $this->completiontype;

        // Min courses.
        if ($updateform) {
            $mform->addElement('text', $prefix.'mincourses', $this->mincourses, array('size' => '10', 'maxlength' => '255', 'id' => $prefix.'mincourses'));
            $mform->setType($prefix.'mincourses', PARAM_INT);
            $template_values['%'.$prefix.'mincourses%'] = array('name' => $prefix.'mincourses', 'value' => null);
        }
        $helpbutton = $OUTPUT->help_icon('mincourses', 'totara_program');
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitem'));
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitemtitle'));
        $templatehtml .= \html_writer::tag('label', get_string('mincourses', 'totara_program') . ' ' . $helpbutton,
            array('for' => $prefix.'mincourses'));
        $templatehtml .= \html_writer::end_tag('div');
        $templatehtml .= \html_writer::tag('div', '%'.$prefix.'mincourses%', array('class' => 'felement'));
        $templatehtml .= \html_writer::end_tag('div');
        $formdataobject->{$prefix.'mincourses'} = $this->mincourses;

        // Course custom field to be used for calculating the minimum sum
        if ($updateform) {
            $customfields  = $DB->get_records('course_info_field', array(), 'fullname');
            $canviewhidden = is_siteadmin();
            foreach ($customfields as $i => $field) {
                if ($field->hidden && !$canviewhidden) {
                    $customfields[$i] = get_string('hiddenfield', 'totara_program');
                } else {
                    $customfields[$i] = format_string($field->fullname);
                }
            }
            $customfields = array(0 => get_string('none')) + $customfields;
            $mform->addElement('select', $prefix.'coursesumfield', get_string('label:coursescorefield', 'totara_program'),
                $customfields, array('id' => $prefix.'coursesumfield'));
            $mform->setType($prefix.'coursesumfield', PARAM_INT);
            $template_values['%'.$prefix.'coursesumfield%'] = array('name' => $prefix.'coursesumfield', 'value' => null);
        }
        $helpbutton = $OUTPUT->help_icon('coursescorefield', 'totara_program');
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitem'));
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitemtitle'));
        $templatehtml .= \html_writer::tag('label', get_string('label:coursescorefield', 'totara_program') . ' ' . $helpbutton,
            array('for' => $prefix.'coursesumfield'));
        $templatehtml .= \html_writer::end_tag('div');
        $templatehtml .= \html_writer::tag('div', '%'.$prefix.'coursesumfield%', array('class' => 'felement'));
        $templatehtml .= \html_writer::end_tag('div');
        $formdataobject->{$prefix.'coursesumfield'} = $this->coursesumfield;

        // Course field sum min val.
        if ($updateform) {
            $mform->addElement('text', $prefix.'coursesumfieldtotal', $this->coursesumfieldtotal, array('size' => '10', 'maxlength' => '255', 'id' => $prefix.'coursesumfieldtotal'));
            $mform->setType($prefix.'coursesumfieldtotal', PARAM_INT);
            $template_values['%'.$prefix.'coursesumfieldtotal%'] = array('name' => $prefix.'coursesumfieldtotal', 'value' => null);
        }
        $helpbutton = $OUTPUT->help_icon('minimumscore', 'totara_program');
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitem'));
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitemtitle'));
        $templatehtml .= \html_writer::tag('label', get_string('label:minimumscore', 'totara_program') . ' ' . $helpbutton,
            array('for' => $prefix.'coursesumfieldtotal'));
        $templatehtml .= \html_writer::end_tag('div');
        $templatehtml .= \html_writer::tag('div', '%'.$prefix.'coursesumfieldtotal%', array('class' => 'felement'));
        $templatehtml .= \html_writer::end_tag('div');
        $formdataobject->{$prefix.'coursesumfieldtotal'} = $this->coursesumfieldtotal;

        // Add the time allowance selection group
        if ($updateform) {
            $element = $mform->addElement('text', $prefix.'timeallowednum', $this->timeallowednum, array('size' => 4, 'maxlength' => 3, 'id' => $prefix.'timeallowance'));
            $mform->setType($prefix.'timeallowednum', PARAM_INT);
            $mform->addRule($prefix.'timeallowednum', get_string('required'), 'required', null, 'server');

            $timeallowanceoptions = utils::get_standard_time_allowance_options(true);
            $select = $mform->addElement('select', $prefix.'timeallowedperiod', '', $timeallowanceoptions, array('id' => $prefix.'timeperiod'));
            $mform->setType($prefix.'timeallowedperiod', PARAM_INT);
            $templatehtml .= \html_writer::tag('label', get_string('timeperiod', 'totara_program'), array('for' => $prefix.'timeperiod', 'class' => 'accesshide'));

            $template_values['%'.$prefix.'timeallowednum%'] = array('name'=>$prefix.'timeallowednum', 'value'=>null);
            $template_values['%'.$prefix.'timeallowedperiod%'] = array('name'=>$prefix.'timeallowedperiod', 'value'=>null);
        }
        $component = utils::is_certif($this->programid) ? 'totara_certification' : 'totara_program';
        $helpbutton = $OUTPUT->help_icon('minimumtimerequired', $component);
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitem'));
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitemtitle'));
        $templatehtml .= \html_writer::tag('label', get_string('label:minimumtimerequired', 'totara_program') . ' ' . $helpbutton,
            array('for' => $prefix.'timeallowance'));
        $templatehtml .= \html_writer::end_tag('div');
        $templatehtml .= \html_writer::tag('div', '%'.$prefix.'timeallowednum% %'.$prefix.'timeallowedperiod%',
            array('class' => 'felement'));
        $templatehtml .= \html_writer::end_tag('div');
        $formdataobject->{$prefix.'timeallowednum'} = $this->timeallowednum;
        $formdataobject->{$prefix.'timeallowedperiod'} = $this->timeallowedperiod;

        // Add the list of courses for this set
        $templatehtml .= \html_writer::start_tag('div', array('id' => $prefix.'courselist', 'class' => 'courselist'));
        $templatehtml .= $this->get_courses_form_template($mform, $template_values, $formdataobject, $updateform);
        $templatehtml .= \html_writer::end_tag('div');

        // Add the 'Add course' drop down list
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitem'));
        $templatehtml .= \html_writer::tag('div', '', array('class' => 'fitemtitle'));
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'courseadder felement'));

        if ($updateform) {
            $mform->addElement('button', $prefix . 'addcourse', get_string('addcourses', 'totara_program'),
                array('data-program-courseset-prefix' => $prefix));
            $template_values['%' . $prefix . 'addcourse%'] = array('name' => $prefix . 'addcourse', 'value' => null);
            $templatehtml .= '%' . $prefix . 'addcourse%' . "\n";
        }

        $templatehtml .= \html_writer::end_tag('div'); // End felement.
        $templatehtml .= \html_writer::end_tag('div'); // End fitem.

        $templatehtml .= \html_writer::end_tag('fieldset');

        $templatehtml .= $this->get_nextsetoperator_select_form_template($mform, $template_values, $formdataobject, $prefix, $updateform);

        return $templatehtml;
    }

    /**
     * Defines the form elemens for the courses in a course set
     *
     * @param $mform A moodleform-like object
     * @param array $template_values
     * @param \stdClass $formdataobject
     * @param bool $updateform
     * @return string the courses html
     */
    public function get_courses_form_template(&$mform, array &$template_values, &$formdataobject, bool $updateform = true): string {
        $prefix = $this->get_set_prefix();

        $templatehtml = '';
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitem'));
        $templatehtml .= \html_writer::tag('div', get_string('courses', 'totara_program'). ':', array('class' => 'fitemtitle'));
        if (isset($this->courses) && is_array($this->courses) && count($this->courses)>0) {

            if (!$completiontypestr = $this->get_completion_type_string()) {
                print_error('unknowncompletiontype', 'totara_program', '', $this->sortorder);
            }

            $coursesinsetcount = count($this->courses);
            $firstcourse = true;
            $list = '';

            foreach ($this->courses as $course) {
                $course->coursesetid = $this->id;
                $classes = 'operator';

                if ($firstcourse) {
                    $content = \html_writer::tag('span', '&nbsp;', array('class' => $classes));
                    $firstcourse = false;
                } else {
                    $content = \html_writer::tag('span', $completiontypestr, array('class' => $classes));
                }

                // Get the action buttons.
                $content .= $this->course_content_table_toolbar($course, $prefix);

                $list .= \html_writer::tag('li', $content, array('data-courseid' => $course->id));
            }

            $ulattrs = array('id' => $prefix.'displaycourselist', 'class' => 'course_list');
            $templatehtml .= \html_writer::tag('div', \html_writer::tag('ul', $list, $ulattrs), array('class' => 'felement'));

            $courseidsarray = array();
            foreach ($this->courses as $course) {
                $courseidsarray[] = $course->id;
            }
            $coursesstr = implode(',', $courseidsarray);
            if ($updateform) {
                $mform->addElement('hidden', $prefix.'courses', $coursesstr);
                $mform->setType($prefix.'courses', PARAM_SEQUENCE);
                $mform->setConstant($prefix.'courses', $coursesstr);
                $template_values['%'.$prefix.'courses%'] = array('name'=>$prefix.'courses', 'value'=>null);
            }
            $templatehtml .= '%'.$prefix.'courses%'."\n";
            $formdataobject->{$prefix.'courses'} = $coursesstr;

        } else {
            if ($updateform) {
                $mform->addElement('hidden', $prefix.'courses');
                $mform->setType($prefix.'courses', PARAM_SEQUENCE);
                $mform->setConstant($prefix.'courses', '');
                $template_values['%'.$prefix.'courses%'] = array('name'=>$prefix.'courses', 'value'=>null);
            }
            $templatehtml .= '%'.$prefix.'courses%'."\n";
            $formdataobject->{$prefix.'courses'} = '';

            $templatehtml .= \html_writer::tag('div', get_string('nocourses', 'totara_program'), array('class' => 'felement'));
        }

        $templatehtml .= \html_writer::end_tag('div'); // End fitem.

        return $templatehtml;
    }

    /**
     * Generate HTML for the toolbar attached to this course in courses_html_table().
     *
     * @param \stdClass $course The object of the course
     * @param string   $prefix
     * @return string html for the toolbar
     */
    public function course_content_table_toolbar(\stdClass $course, string $prefix): string {
        global $OUTPUT;

        $url = new \moodle_url('/totara/program/content/get_html.php', array(
            'id'          => $this->programid,     // Program ID.
            'courseid'    => $course->id,          // Course ID.
            'coursesetid' => $course->coursesetid, // Course set ID.
        ));

        $params = array(
            'removecourse' => 'delete',
            'moveup'       => 'up',
            'movedown'     => 'down'
        );

        $content = \html_writer::start_tag('div', array('class' => 'totara-item-group'));
        foreach ($params as $htmltype => $action) {
            // Don't allow course removal if only one course.
            if ($htmltype === 'removecourse' && count($this->courses) === 1) {
                continue;
            }

            $params = array('class' => "{$action}item");

            $url->param('htmltype', $htmltype);
            $content .= \html_writer::start_tag('div', $params);
            $content .= \html_writer::start_tag('a', array(
                'class'                           => 'totara-item-group-icon course' . $action . 'link',
                'href'                            => 'javascript:;',
                'data-action'                     => $action,
                'data-coursesetid'                => $this->id,
                'data-coursesetprefix'            => $prefix,
                'data-courseto' . $action . '_id' => $course->id,
            ));
            $content .= $OUTPUT->pix_icon('t/' . $action, get_string($action));
            $content .= \html_writer::end_tag('a');
            $content .= \html_writer::end_tag('div');
        }
        $content .= format_string($course->fullname);
        $content .= \html_writer::end_tag('div');

        return $content;
    }

    /**
     * Defines the form elements for the deleted courses
     *
     * @param $mform A moodleform-like object
     * @param array $template_values
     * @param \stdClass $formdataobject
     * @param bool $updateform
     * @return string
     */
    public function get_deleted_courses_form_template(&$mform, array &$template_values, &$formdataobject, bool $updateform = true): string {

        $prefix = $this->get_set_prefix();

        $templatehtml = '';
        $deletedcourseidsarray = array();

        if ($this->courses_deleted_ids) {
            foreach ($this->courses_deleted_ids as $deleted_course_id) {
                $deletedcourseidsarray[] = $deleted_course_id;
            }
        }

        $deletedcoursesstr = implode(',', $deletedcourseidsarray);
        if ($updateform) {
            $mform->addElement('hidden', $prefix.'deleted_courses', $deletedcoursesstr);
            $mform->setType($prefix.'deleted_courses', PARAM_SEQUENCE);
            $mform->setConstant($prefix.'deleted_courses', $deletedcoursesstr);
            $template_values['%'.$prefix.'deleted_courses%'] = array('name'=>$prefix.'deleted_courses', 'value'=>null);
        }
        $templatehtml .= '%'.$prefix.'deleted_courses%'."\n";
        $formdataobject->{$prefix.'deleted_courses'} = $deletedcoursesstr;


        return $templatehtml;
    }

    /**
     * @inheritdoc
     */
    public function get_course_text(course_set $courseset): string {
        if ($courseset->completiontype == self::COMPLETIONTYPE_ALL) {
            return get_string('allcoursesfrom', 'totara_program') . ' "' . format_string($courseset->label) . '"';
        }
        else if ($courseset->completiontype == self::COMPLETIONTYPE_SOME) {
            return get_string('somecoursesfrom', 'totara_program') . ' "' . format_string($courseset->label) . '"';
        }
        else if ($courseset->completiontype == self::COMPLETIONTYPE_OPTIONAL) {
            return get_string('nocoursesfrom', 'totara_program') . ' "' . format_string($courseset->label) . '"';
        }
        else {
            return get_string('onecoursesfrom', 'totara_program') . ' "' . format_string($courseset->label) . '"';
        }
    }

    /**
     * @inheritdoc
     */
    public function get_courses(): array {
        return $this->courses;
    }

    /**
     * @inheritdoc
     */
    public function is_considered_optional(): bool {
        if ($this->completiontype == self::COMPLETIONTYPE_OPTIONAL) {
            // Clearly so.
            return true;
        }
        if ($this->completiontype == self::COMPLETIONTYPE_SOME && $this->mincourses == 0) {
            // Some courses are required, but that is set to 0, so its optional.
            return true;
        }
        return parent::is_considered_optional();
    }

    /**
     * @inheritdoc
     */
    public function build_progressinfo(): progressinfo {
        $agg_class = '';
        $customdata = null;

        switch ($this->completiontype) {
            case self::COMPLETIONTYPE_ANY;
                $agg_method = progressinfo::AGGREGATE_ANY;
                break;

            case self::COMPLETIONTYPE_SOME;
                $agg_method = progressinfo::AGGREGATE_ALL;
                $agg_class = '\totara_program\progress\progressinfo_aggregate_some';
                $customdata = array('requiredcourses' => $this->mincourses,
                    'requiredpoints' => $this->coursesumfieldtotal,
                    'totalcourses' => 0,
                    'totalpoints' => 0);
                break;

            case self::COMPLETIONTYPE_OPTIONAL;
                $agg_method = progressinfo::AGGREGATE_NONE;
                break;

            default:
                $agg_method = progressinfo::AGGREGATE_ALL;
        }

        $progressinfo = progressinfo::from_data($agg_method, 0, 0, $customdata, $agg_class);

        // Add courses in the course set
        foreach ($this->get_courses() as $course) {
            $progressinfo->add_criteria(parent::get_progressinfo_course_key($course));
        }

        return $progressinfo;
    }
}