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

use totara_core\progressinfo\progressinfo;
use totara_program\content\course_set;
use totara_program\program;
use totara_program\content\program_content;
use totara_program\utils;
use totara_tui\output\component;

class recurring_course_set extends course_set  {

    public $recurrencetime, $recurrencetimenum, $recurrencetimeperiod;
    public $recurcreatetime, $recurcreatetimenum, $recurcreatetimeperiod;
    public $course;

    /**
     * @inheritdoc
     */
    public function __construct(int $programid, $setob = null, string $uniqueid = null) {
        global $DB;
        parent::__construct($programid, $setob, $uniqueid);

        $this->contenttype = program_content::CONTENTTYPE_RECURRING;

        if (is_object($setob)) {
            if ($courseset_course = $DB->get_record('prog_courseset_course', array('coursesetid' => $this->id))) {
                $course = $DB->get_record('course', array('id' => $courseset_course->courseid));
                if (!$course) {
                    $DB->delete_records('prog_courseset_course', array('id' => $courseset_course->id));
                    $this->course = array();
                } else {
                    $this->course = $course;
                }
            }
            $recurrencetime = utils::duration_explode($this->recurrencetime);
            $this->recurrencetimenum = $recurrencetime->num;
            $this->recurrencetimeperiod = $recurrencetime->period;
            $recurcreatetime = utils::duration_explode($this->recurcreatetime);
            $this->recurcreatetimenum = $recurcreatetime->num;
            $this->recurcreatetimeperiod = $recurcreatetime->period;
        } else {
            $this->recurrencetimenum = 0;
            $this->recurrencetimeperiod = 0;
            $this->recurcreatetimenum = 0;
            $this->recurcreatetimeperiod = 0;
        }

    }

    /**
     * @inheritdoc
     */
    public function init_form_data(string $formnameprefix, $formdata): void {
        global $DB;
        parent::init_form_data($formnameprefix, $formdata);

        $this->recurrencetimenum = $formdata->{$formnameprefix.'recurrencetimenum'};
        $this->recurrencetimeperiod = $formdata->{$formnameprefix.'recurrencetimeperiod'};
        $this->recurrencetime = utils::duration_implode($this->recurrencetimenum, $this->recurrencetimeperiod);

        $this->recurcreatetimenum = $formdata->{$formnameprefix.'recurcreatetimenum'};
        $this->recurcreatetimeperiod = $formdata->{$formnameprefix.'recurcreatetimeperiod'};
        $this->recurcreatetime = utils::duration_implode($this->recurcreatetimenum, $this->recurcreatetimeperiod);

        $courseid = $formdata->{$formnameprefix.'courseid'};
        if ($course = $DB->get_record('course', array('id' => $courseid))) {
            $this->course = $course;
        }
    }

    /**
     * @inheritdoc
     */
    public function is_recurring(): bool {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function save_set(): bool {
        if (parent::save_set()) {
            return $this->save_course();
        } else {
            return false;
        }
    }

    /**
     * Save course
     *
     * @return bool
     */
    public function save_course(): bool {
        global $DB;
        if (!$this->id) {
            return false;
        }

        if (!is_object($this->course)) {
            // Check for an old record that might need to be removed.
            $oldrecord = $DB->get_record('prog_courseset_course', array('coursesetid' => $this->id));
            if ($oldrecord) {
                $removed_id = $oldrecord->courseid;
                $DB->delete_records('prog_courseset_course', array('coursesetid' => $this->id));
            } else {
                // There was no record and nothing to save.
                return false;
            }
        } else if ($ob = $DB->get_record('prog_courseset_course', array('coursesetid' => $this->id))) {
            if ($this->course->id == $ob->courseid) {
                // nothing to do
                return true;
            } else {
                $removed_id = $ob->courseid;
                $added_id = $this->course->id;
                $ob->courseid = $this->course->id;
                $DB->update_record('prog_courseset_course', $ob);
            }
        } else {
            $removed_id = false;
            $added_id = $this->course->id;
            $ob = new \stdClass();
            $ob->coursesetid = $this->id;
            $ob->courseid = $this->course->id;
            $DB->insert_record('prog_courseset_course', $ob);
        }

        $program_plugin = enrol_get_plugin('totara_program');

        // if the course no longer exists in any programs, remove the program enrolment plugin
        if ($removed_id) {
            $courses_still_associated = prog_get_courses_associated_with_programs(array($removed_id));
            // don't consider the one we've just added
            if (isset($added_id)) {
                unset($courses_still_associated[$added_id]);
            }
            if (empty($courses_still_associated)) {
                $instance = $program_plugin->get_instance_for_course($removed_id);
                if ($instance) {
                    $program_plugin->delete_instance($instance);
                }
            }
        }

        if (isset($added_id)) {
            // if the new course doesn't yet have the enrollment plugin, add it
            $instance = $program_plugin->get_instance_for_course($added_id);
            if (!$instance) {
                $course = $DB->get_record('course', array('id' => $added_id));
                $program_plugin->add_instance($course);
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function contains_course(int $courseid): bool {

        if ($this->course->id == $courseid) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function check_courseset_complete(int $userid) {

        $course = $this->course;

        // create a new completion object for this course
        $completion_info = new \completion_info($course);

        // check if the course is complete
        if ($completion_info->is_course_complete($userid)) {
            $completionsettings = array(
                'status'        => program::STATUS_COURSESET_COMPLETE,
                'timecompleted' => time()
            );
            return $this->update_courseset_complete($userid, $completionsettings);
        }

        return false;
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
        global $CFG, $OUTPUT, $DB, $USER;

        //----------------
        $componentdata = [];
        $componentdata['courses'] = [];

        //----------------

        $out = '';
        $out .= \html_writer::start_tag('div', array('class' => 'surround display-program'));
        $out .= $OUTPUT->heading(format_string($this->label), 3);

        $numperiod = utils::get_duration_num_and_period($this->timeallowed);

        if ($this->timeallowed > 0) {
            $out .= \html_writer::tag('p', get_string('allowtimeforset' . $numperiod->periodkey, 'totara_program', $numperiod->num));
        }

        if (is_object($this->course)) {
            $vue_course = [];
            $vue_course['fullname'] = format_string($this->course->fullname);
            $vue_course['id'] = $this->course->id;
            $vue_course['image'] = course_get_image($this->course->id)->out();

            $course = $this->course;

            if (is_siteadmin($USER->id)) {
                $showcourse = true;
            } else if ($userid && $accessible) {
                $showcourse = totara_course_is_viewable($course->id, $userid);
            } else {
                $coursecontext = \context_course::instance($course->id);
                $showcourse = is_viewing($coursecontext, $userid ? $userid : $USER->id) ? true :
                    totara_course_is_viewable($course->id, $userid) && is_enrolled($coursecontext, $userid ? $userid : $USER->id, '', true);
            }

            // User must be enrolled or course can be viewed (checks audience visibility),
            // And course must be accessible.
            if ($showcourse) {
                $vue_course['launchURL'] = (new \moodle_url('/course/view.php', array('id' => $course->id)))->out(false);
            } else if ($userid && $accessible && !empty($CFG->audiencevisibility) && $course->audiencevisible != COHORT_VISIBLE_NOUSERS) {
                // If the program has been assigned but the user is not yet enrolled in the course,
                // a course with audience visibility set to "Enrolled users" would not allow the user to become enrolled.
                // Instead, when "Launch" is clicked, we redirect to the program requirements page, which will then directly enrol them into the course.
                // This isn't needed for normal visibility because if the course is hidden then it will be inaccessible anyway.
                $params = array('id' => $this->programid, 'cid' => $course->id, 'userid' => $userid, 'sesskey' => $USER->sesskey);
                $vue_course['launchURL'] = (new \moodle_url('/totara/program/required.php', $params))->out(false);
            }

            if ($userid) {
                if (!$hide_progress) {
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
                }
            }

            $componentdata['courses'][] = $vue_course;
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
    public function display_nextsetoperator(): string {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function display_form_element(): string {
        return format_string($this->course->fullname);
    }

    /**
     * @inheritdoc
     */
    public function print_set_minimal(): string {
        $prefix = $this->get_set_prefix();

        $out = '';
        $courseid = (is_object($this->course) ? $this->course->id : 0);
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."id", 'value' => $this->id));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."label", 'value' => $this->label));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."courseid", 'value' => $courseid));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."sortorder", 'value' => $this->sortorder));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."contenttype", 'value' => $this->contenttype));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."recurrencetime", 'value' => $this->recurrencetime));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."nextsetoperator", 'value' => $this->nextsetoperator));

        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."timeallowedperiod", 'value' => utils::TIME_SELECTOR_DAYS));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."timeallowednum", 'value' => '30'));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."recurrencetimeperiod", 'value' => utils::TIME_SELECTOR_DAYS));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."recurrencetimenum", 'value' => '365'));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."recurcreatetimeperiod", 'value' => utils::TIME_SELECTOR_DAYS));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."recurcreatetimenum", 'value' => '1'));

        return $out;
    }

    /**
     * @inheritdoc
     */
    public function get_courseset_form_template(&$mform, array &$template_values, &$formdataobject, bool $updateform = true): string {
        global $OUTPUT, $DB;
        $prefix = $this->get_set_prefix();

        $templatehtml = '';
        $templatehtml .= \html_writer::start_tag('fieldset', array('id' => $prefix, 'class' => 'course_set surround edit-program'));

        $helpbutton = $OUTPUT->help_icon('recurringcourseset', 'totara_program');
        $legend = ((isset($this->label) && ! empty($this->label)) ? $this->label : get_string('legend:recurringcourseset', 'totara_program',
                $this->sortorder)) . ' ' . $helpbutton;
        $templatehtml .= \html_writer::tag('legend', $legend);

        // Recurring programs don't need a nextsetoperator property but we must
        // include it in the form to avoid any problems when the data is submitted
        $templatehtml .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix.'nextsetoperator', 'value' => '0'));

        // Add the delete button for this set
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'setbuttons'));

        if ($updateform) {
            $mform->addElement('submit', $prefix.'delete', get_string('delete', 'totara_program'),
                array('class' => "btn-cancel delete fieldsetbutton setdeletebutton"));
            $template_values['%'.$prefix.'delete%'] = array('name' => $prefix.'delete', 'value' => null);
        }
        $templatehtml .= '%'.$prefix.'delete%'."\n";
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

        // Add the course set label
        if ($updateform) {
            $mform->addElement('text', $prefix.'label', $this->label, array('size' => '40', 'maxlength' => '255'));
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

        // Display the course name
        if (is_object($this->course)) {
            if (isset($this->course->fullname)) {
                $hascourseoptions = $DB->record_exists_select('course', 'id <> ?', array(SITEID));

                $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitem'));
                $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitemtitle'));
                if ($hascourseoptions) {
                    $labelcontent = get_string('coursename', 'totara_program');
                    $labelcontent .= $OUTPUT->help_icon('recurringcourse', 'totara_program');
                    $templatehtml .= \html_writer::tag('label', $labelcontent);
                } else {
                    $templatehtml .= \html_writer::tag('label', get_string('coursename', 'totara_program'));
                }
                $templatehtml .= \html_writer::end_tag('div');

                // Display the recurring course with dialog link to change.
                $templatehtml .= \html_writer::start_tag('div', array('class' => 'courseselector felement'));
                if ($hascourseoptions) {
                    if ($updateform) {
                        $mform->addElement('hidden',  $prefix.'courseid');
                        $mform->setType($prefix.'courseid', PARAM_INT);

                        $coursename = \html_writer::span(format_string($this->course->fullname), '', array('id' => 'recurringcoursename'));

                        $iconlink = $OUTPUT->action_icon('#', new \pix_icon('i/edit', get_string('changecourse', 'totara_program')),
                            null, array('id' => 'amendrecurringcourselink', 'data-program-courseset-prefix' => $prefix));

                        $templatehtml .= \html_writer::div($coursename . $iconlink);

                        $template_values['%'.$prefix.'courseid%'] = array('name' => $prefix.'courseid', 'value' => null);
                    }
                    $templatehtml .= '%'.$prefix.'courseid%'."\n";
                    $formdataobject->{$prefix.'courseid'} = $this->course->id;
                } else {
                    $templatehtml .= \html_writer::tag('p', get_string('nocoursestoselect', 'totara_program'));
                }
                $templatehtml .= \html_writer::end_tag('div');

                $templatehtml .= \html_writer::end_tag('div'); // End fitem.
            }
        }

        // Add the time allowance selection group
        if ($updateform) {
            $mform->addElement('text', $prefix.'timeallowednum', $this->timeallowednum, array('size' => 4, 'maxlength' => 3));
            $mform->setType($prefix.'timeallowednum', PARAM_INT);
            $mform->addRule($prefix.'timeallowednum', get_string('required'), 'required', null, 'server');

            $timeallowanceoptions = utils::get_standard_time_allowance_options(true);
            $mform->addElement('select', $prefix.'timeallowedperiod', '', $timeallowanceoptions);
            $mform->setType($prefix.'timeallowedperiod', PARAM_INT);

            $template_values['%'.$prefix.'timeallowednum%'] = array('name' => $prefix.'timeallowednum', 'value' => null);
            $template_values['%'.$prefix.'timeallowedperiod%'] = array('name' => $prefix.'timeallowedperiod', 'value' => null);
        }
        $helpbutton = $OUTPUT->help_icon('minimumtimerequired', 'totara_program');
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitem'));
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitemtitle'));
        $templatehtml .= \html_writer::tag('label', get_string('label:minimumtimerequired', 'totara_program') . ' ' . $helpbutton,
            array('for' => $prefix.'timeallowance'));
        $templatehtml .= \html_writer::end_tag('div');
        $templatehtml .= \html_writer::tag('div', '%' . $prefix . 'timeallowednum% %' . $prefix . 'timeallowedperiod%',
            array('class' => 'felement'));
        $templatehtml .= \html_writer::end_tag('div');
        $formdataobject->{$prefix.'timeallowednum'} = $this->timeallowednum;
        $formdataobject->{$prefix.'timeallowedperiod'} = $this->timeallowedperiod;

        // Add the recurrence period selection group
        if ($updateform) {
            $mform->addElement('text', $prefix.'recurrencetimenum', $this->recurrencetimenum, array('size' => 4, 'maxlength' => 3));
            $mform->setType($prefix.'recurrencetimenum', PARAM_INT);
            $mform->addRule($prefix.'recurrencetimenum', get_string('required'), 'required', null, 'server');

            $timeallowanceoptions = utils::get_standard_time_allowance_options();
            $mform->addElement('select', $prefix.'recurrencetimeperiod', '', $timeallowanceoptions);
            $mform->setType($prefix.'recurrencetimeperiod', PARAM_INT);

            $template_values['%'.$prefix.'recurrencetimenum%'] = array('name' => $prefix.'recurrencetimenum', 'value' => null);
            $template_values['%'.$prefix.'recurrencetimeperiod%'] = array('name' => $prefix.'recurrencetimeperiod', 'value' => null);
        }
        $helpbutton = $OUTPUT->help_icon('recurrence', 'totara_program');
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitem'));
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitemtitle'));
        $templatehtml .= \html_writer::tag('label', get_string('label:recurrence', 'totara_program') . ' ' . $helpbutton,
            array('for' => $prefix.'recurrencetimenum'));
        $templatehtml .= \html_writer::end_tag('div');
        $templatehtml .= \html_writer::tag('div', get_string('repeatevery', 'totara_program') . ' %' . $prefix .
            'recurrencetimenum% %' . $prefix . 'recurrencetimeperiod%', array('class' => 'felement'));
        $templatehtml .= \html_writer::end_tag('div');
        $formdataobject->{$prefix.'recurrencetimenum'} = $this->recurrencetimenum;
        $formdataobject->{$prefix.'recurrencetimeperiod'} = $this->recurrencetimeperiod;

        // Add the recur create period selection group
        if ($updateform) {
            $mform->addElement('text', $prefix.'recurcreatetimenum', $this->recurcreatetimenum, array('size' => 4, 'maxlength' => 3));
            $mform->setType($prefix.'recurcreatetimenum', PARAM_INT);

            $timeallowanceoptions = utils::get_standard_time_allowance_options();
            $mform->addElement('select', $prefix.'recurcreatetimeperiod', '', $timeallowanceoptions);
            $mform->setType($prefix.'recurcreatetimeperiod', PARAM_INT);
            $mform->addRule($prefix.'recurcreatetimeperiod', get_string('required'), 'required', null, 'server');

            $template_values['%'.$prefix.'recurcreatetimenum%'] = array('name' => $prefix.'recurcreatetimenum', 'value' => null);
            $template_values['%'.$prefix.'recurcreatetimeperiod%'] = array('name' => $prefix.'recurcreatetimeperiod', 'value' => null);
        }
        $helpbutton = $OUTPUT->help_icon('coursecreation', 'totara_program');
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitem'));
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitemtitle'));
        $templatehtml .= \html_writer::tag('label', get_string('label:recurcreation', 'totara_program') . ' ' . $helpbutton,
            array('for' => $prefix.'recurcreatetimenum'));
        $templatehtml .= \html_writer::end_tag('div');
        $templatehtml .= \html_writer::tag('div', get_string('createcourse', 'totara_program') . ' %' .
            $prefix . 'recurcreatetimenum% %' . $prefix . 'recurcreatetimeperiod% '.
            get_string('beforecourserepeats', 'totara_program'), array('class' => 'felement'));
        $templatehtml .= \html_writer::end_tag('div');
        $formdataobject->{$prefix.'recurcreatetimenum'} = $this->recurcreatetimenum;
        $formdataobject->{$prefix.'recurcreatetimeperiod'} = $this->recurcreatetimeperiod;

        $templatehtml .= \html_writer::start_tag('div', array('class' => 'setbuttons'));
        // Add the update button for this set
        if ($updateform) {
            $mform->addElement('submit', $prefix.'update', get_string('update', 'totara_program'),
                array('class' => "fieldsetbutton updatebutton"));
            $template_values['%'.$prefix.'update%'] = array('name' => $prefix.'update', 'value' => null);
        }
        $templatehtml .= '%'.$prefix.'update%'."\n";
        $templatehtml .= \html_writer::end_tag('div');
        $templatehtml .= \html_writer::end_tag('fieldset');

        return $templatehtml;
    }

    /**
     * @inheritdoc
     */
    public function get_course_text(course_set $courseset): string {
        if ($courseset->completiontype == course_set::COMPLETIONTYPE_ALL) {
            return get_string('allcoursesfrom', 'totara_program') . ' "' . format_string($courseset->label) . '"';
        } else {
            return get_string('onecoursesfrom', 'totara_program') . ' "' . format_string($courseset->label) . '"';
        }
    }

    /**
     * @inheritdoc
     */
    public function get_courses(): array {
        if (empty($this->course)) {
            return array();
        } else {
            return array($this->course);
        }
    }

    /**
     * @inheritdoc
     */
    public function delete_course(int $courseid): bool {
        if (!empty($this->course) && $this->course->id == $courseid) {
            $this->course = false;

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function build_progressinfo(): progressinfo {
        $agg_class = '';
        $customdata = null;
        $agg_method = progressinfo::AGGREGATE_ALL;

        $progressinfo = progressinfo::from_data($agg_method, 0, 0, $customdata, $agg_class);

        // Add courses in the course set
        if (is_object($this->course)) {
            $progressinfo->add_criteria(parent::get_progressinfo_course_key($this->course));
        }

        return $progressinfo;
    }
}