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
use totara_program\content\program_content;
use totara_program\program;
use totara_program\utils;
use totara_tui\output\component;

/**
 * Class implementing a comptency course set.
 */
class competency_course_set extends course_set  {

    /**
     * @inheritdoc
     */
    public function __construct(int $programid, $setob = null, string $uniqueid = null) {
        parent::__construct($programid, $setob, $uniqueid);
        $this->contenttype = program_content::CONTENTTYPE_COMPETENCY;

        if (is_object($setob)) {
            // completiontype can change if the competency changes so we have to check it every time
            if (!$this->completiontype = $this->get_completion_type()) {
                $this->completiontype = self::COMPLETIONTYPE_ALL;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function save_set(): bool {
        $courses = $this->get_competency_courses();
        $program_plugin = enrol_get_plugin('totara_program');
        foreach ($courses as $course) {
            //check if program enrolment plugin is already enabled on this course
            $instance = $program_plugin->get_instance_for_course($course->id);
            if (!$instance) {
                //add it
                $program_plugin->add_instance($course);
            }
        }
        return parent::save_set();
    }

    /**
     * @inheritdoc
     */
    public function init_form_data(string $formnameprefix, $formdata): void {
        parent::init_form_data($formnameprefix, $formdata);

        $this->competencyid = $formdata->{$formnameprefix.'competencyid'};

        if (!$this->completiontype = $this->get_completion_type()) {
            $this->completiontype = self::COMPLETIONTYPE_ALL;
        }
    }

    /**
     * Get completion type enum
     *
     * @return int|bool Returns false if no completion type
     */
    public function get_completion_type() {
        global $DB;
        if (!$competency = $DB->get_record('comp', array('id' => $this->competencyid))) {
            return false;
        } else {
            return ($competency->aggregationmethod == course_set::COMPLETIONTYPE_ALL ? course_set::COMPLETIONTYPE_ALL : course_set::COMPLETIONTYPE_ANY);
        }
    }

    /**
     * Add competency
     *
     * @param \stdClass $formdata
     * @return bool
     */
    public function add_competency($formdata): bool {
        global $DB;
        $competencyid_elementname = $this->get_set_prefix().'competencyid';

        if (isset($formdata->$competencyid_elementname)) {
            $competencyid = $formdata->$competencyid_elementname;
            if ($competency = $DB->get_record('comp', array('id' => $competencyid))) {
                $this->competencyid = $competency->id;
                $this->completiontype = $this->get_completion_type();
                // completiontype can change if the competency changes so we have to check it every time
                return true;
            }
        }
        return false;
    }

    /**
     * Get competency courses
     *
     * @return array
     */
    public function get_competency_courses(): array {
        global $DB;

        $sql = "SELECT c.*
            FROM {course} AS c
            JOIN {comp_criteria} AS cc ON c.id = cc.iteminstance
           WHERE cc.competencyid = ?
             AND cc.itemtype = ?";

        return $DB->get_records_sql($sql, array($this->competencyid, 'coursecompletion'));
    }

    /**
     * @inheritdoc
     */
    public function contains_course(int $courseid): bool {

        $courses = $this->get_competency_courses();

        if ($courses) {
            foreach ($courses as $course) {
                if ($course->id == $courseid) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function check_courseset_complete(int $userid) {

        $courses = $this->get_competency_courses();
        $completiontype = $this->get_completion_type();

        // check that the course set contains at least one course
        if (!$courses || !count($courses)) {
            return false;
        }

        foreach ($courses as $course) {

            $set_completed = false;

            // create a new completion object for this course
            $completion_info = new \completion_info($course);

            // check if the course is complete
            if ($completion_info->is_course_complete($userid)) {
                if ($completiontype == self::COMPLETIONTYPE_ANY) {
                    $completionsettings = array(
                        'status'        => program::STATUS_COURSESET_COMPLETE,
                        'timecompleted' => time()
                    );
                    return $this->update_courseset_complete($userid, $completionsettings);
                }
            } else {
                // if all courses must be completed for this ourse set to be complete
                if ($completiontype == self::COMPLETIONTYPE_ALL) {
                    return false;
                }
            }
        }

        // if processing reaches here and all courses in this set must be comleted then the course set is complete
        if ($completiontype == self::COMPLETIONTYPE_ALL) {
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
        global $OUTPUT, $DB, $CFG, $USER;

        //----------------
        $componentdata = [];
        $componentdata['courses'] = [];

        //----------------

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
        }

        $numperiod = utils::get_duration_num_and_period($this->timeallowed);

        if ($this->timeallowed > 0) {
            $out .= \html_writer::tag('p', get_string('allowtimeforset' . $numperiod->periodkey, 'totara_program', $numperiod->num));
        }

        $courses = $this->get_competency_courses();

        if ($courses && count($courses) > 0) {
            foreach ($courses as $course) {
                $vue_course = [];
                $vue_course['fullname'] = format_string($course->fullname);
                $vue_course['id'] = $course->id;
                $vue_course['image'] = course_get_image($course->id)->out();

                if (is_siteadmin($USER->id)) {
                    $showcourseset = true;
                } else if ($userid && $accessible) {
                    $showcourseset = totara_course_is_viewable($course->id, $userid);
                } else {
                    $coursecontext = \context_course::instance($course->id);
                    $showcourseset = is_viewing($coursecontext, $userid ? $userid : $USER->id) ? true :
                        totara_course_is_viewable($course->id, $userid) && is_enrolled($coursecontext, $userid ? $userid : $USER->id, '', true);
                }

                if ($showcourseset) {
                    $vue_course['launchURL'] = (new \moodle_url('/course/view.php', array('id' => $course->id)))->out(false);
                } else if ($userid && $accessible && !empty($CFG->audiencevisibility) && $course->audiencevisible != COHORT_VISIBLE_NOUSERS) {
                    // If the program has been assigned but the user is not yet enrolled in the course,
                    // a course with audience visibility set to "Enrolled users" would not allow the user to become enrolled.
                    // Instead, when "Launch" is clicked, we redirect to the program requirements page, which will then directly enrol them into the course.
                    // This isn't needed for normal visibility because if the course is hidden then it will be inaccessible anyway.
                    $params = array('id' => $this->programid, 'cid' => $course->id, 'userid' => $userid, 'sesskey' => $USER->sesskey);
                    $requrl = new \moodle_url('/totara/program/required.php', $params);
                    $vue_course['launchURL'] = $requrl->out(false);
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
        $courses = $this->get_competency_courses();

        $out = '';
        $out .= \html_writer::start_tag('div', array('class' => 'courseset'));
        $out .= \html_writer::start_tag('div', array('class' => 'courses'));

        if ($courses && count($courses) > 0) {
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

        $out .= \html_writer::end_tag('div');

        return $out;
    }

    /**
     * @inheritdoc
     */
    public function print_set_minimal(): string {
        $prefix = $this->get_set_prefix();

        $out = '';
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."id", 'value' => $this->id));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."label", 'value' => ''));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."sortorder", 'value' => $this->sortorder));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."contenttype", 'value' => $this->contenttype));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."nextsetoperator", 'value' => ''));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."timeallowedperiod", 'value' => utils::TIME_SELECTOR_DAYS));
        $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."timeallowednum", 'value' => '1'));

        if ($this->competencyid > 0) {
            $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."competencyid", 'value' => $this->competencyid));
        } else {
            $out .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $prefix."competencyid", 'value' => '0'));
        }

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

        $helpbutton = $OUTPUT->help_icon('competencycourseset', 'totara_program');
        $legend = ((isset($this->label) && ! empty($this->label)) ? $this->label : get_string('legend:courseset', 'totara_program',
                $this->sortorder)) . ' ' . $helpbutton;
        $templatehtml .= \html_writer::tag('legend', $legend);
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
        }
        $templatehtml .= '%'.$prefix.'moveup%'."\n";

        // Add the move down button for this set
        if ($updateform) {
            $attributes = array();
            $attributes['class'] = 'btn-cancel movedown fieldsetbutton';
            if (isset($this->islastset)) {
                $attributes['disabled'] = 'disabled';
                $attributes['class'] .= ' disabled';
            }
            $mform->addElement('submit', $prefix.'movedown', get_string('movedown', 'totara_program'), $attributes);
            $template_values['%'.$prefix.'movedown%'] = array('name' => $prefix.'movedown', 'value' => null);
        }
        $templatehtml .= '%'.$prefix.'movedown%'."\n";

        // Add the delete button for this set
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

        if ($this->competencyid > 0) {
            if ($competency = $DB->get_record('comp', array('id' => $this->competencyid))) {
                $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitem'));
                $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitemtitle'));
                $templatehtml .= \html_writer::tag('label', get_string('label:competencyname', 'totara_program'));
                $templatehtml .= \html_writer::end_tag('div');
                $templatehtml .= \html_writer::tag('div', format_string($competency->fullname), array('class' => 'felement'));
                $templatehtml .= \html_writer::end_tag('div');
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

        $templatehtml .= \html_writer::start_tag('div', array('id' => $prefix.'courselist', 'class' => 'courselist'));
        $templatehtml .= \html_writer::start_tag('div', array('class' => 'fitem'));
        $templatehtml .= \html_writer::tag('div', get_string('courses', 'totara_program'). ':', array('class' => 'fitemtitle'));

        if ($this->competencyid > 0) {

            if (!$completiontypestr = $this->get_completion_type_string()) {
                print_error('unknowncompletiontype', 'totara_program', '', $this->sortorder);
            }

            if ($courses = $this->get_competency_courses()) {
                $firstcourse = true;
                $list = '';
                foreach ($courses as $course) {
                    if ($firstcourse) {
                        $content = \html_writer::tag('span', '&nbsp;', array('class' => 'operator'));
                        $firstcourse = false;
                    } else {
                        $content = \html_writer::tag('span', $completiontypestr, array('class' => 'operator'));
                    }
                    $content .= \html_writer::start_tag('div', array('class' => 'totara-item-group delete_item'));
                    $content .= \html_writer::start_tag('a',
                        array('class' => 'totara-item-group-icon coursedeletelink', 'href' => 'javascript:;',
                            'data-coursesetid' => $this->id, 'data-coursesetprefix' => $prefix,
                            'data-coursetodelete_id' => $course->id)
                    );
                    $content .= $OUTPUT->pix_icon('t/delete', get_string('delete'));
                    $content .= \html_writer::end_tag('a');
                    $content .= format_string($course->fullname);
                    $content .= \html_writer::end_tag('div');
                    $list .= \html_writer::tag('li', $content);
                }
                $ulattrs = array('id' => $prefix.'courselist', 'class' => 'course_list');
                $templatehtml .= \html_writer::tag('div', \html_writer::tag('ul', $list, $ulattrs), array('class' => 'felement'));
            } else {
                $templatehtml .= \html_writer::tag('div', get_string('nocourses', 'totara_program'), array('class' => 'felement'));
            }

            // Add the competency id element to the form
            if ($updateform) {
                $mform->addElement('hidden', $prefix.'competencyid', $this->competencyid);
                $mform->setType($prefix.'competencyid', PARAM_INT);
                $template_values['%'.$prefix.'competencyid%'] = array('name'=>$prefix.'competencyid', 'value'=>null);
            }
            $templatehtml .= '%'.$prefix.'competencyid%'."\n";
            $formdataobject->{$prefix.'competencyid'} = $this->competencyid;

        } else { // if no competency has been added to this set yet

            $course_competencies = $DB->get_records_menu('comp', null, 'fullname ASC', 'id,fullname');
            if (count($course_competencies) > 0) {
                if ($updateform) {
                    $mform->addElement('select',  $prefix.'competencyid', '', $course_competencies);
                    $mform->addElement('submit', $prefix.'addcompetency', get_string('addcompetency', 'totara_program'));
                    $template_values['%'.$prefix.'competencyid%'] = array('name'=>$prefix.'competencyid', 'value'=>null);
                    $template_values['%'.$prefix.'addcompetency%'] = array('name'=>$prefix.'addcompetency', 'value'=>null);
                }
                $templatehtml .= '%'.$prefix.'competencyid%'."\n";
                $templatehtml .= '%'.$prefix.'addcompetency%'."\n";
            } else {
                // Add the competency id element to the form
                if ($updateform) {
                    $mform->addElement('hidden', $prefix.'competencyid', 0);
                    $mform->setType($prefix.'competencyid', PARAM_INT);
                    $template_values['%'.$prefix.'competencyid%'] = array('name'=>$prefix.'competencyid', 'value'=>null);
                }
                $templatehtml .= '%'.$prefix.'competencyid%'."\n";
                $templatehtml .= \html_writer::tag('p', get_string('nocompetenciestoadd', 'totara_program'));
                $formdataobject->{$prefix.'competencyid'} = 0;
            }
        }

        $templatehtml .= \html_writer::end_tag('div'); // End fitem.
        $templatehtml .= \html_writer::end_tag('div');
        $templatehtml .= \html_writer::end_tag('fieldset');
        $templatehtml .= $this->get_nextsetoperator_select_form_template($mform, $template_values, $formdataobject, $prefix, $updateform);

        return $templatehtml;
    }

    /**
     * @inheritdoc
     */
    public function get_course_text(course_set $courseset): string {
        if ($courseset->completiontype == self::COMPLETIONTYPE_ALL) {
            return get_string('allcoursesfrom', 'totara_program') . ' "' . format_string($courseset->label) . '"';
        } else {
            return get_string('onecoursesfrom', 'totara_program') . ' "' . format_string($courseset->label) . '"';
        }
    }

    /**
     * @inheritdoc
     */
    public function get_courses(): array {
        return $this->get_competency_courses();
    }

    /**
     * @inheritdoc
     */
    public function delete_course(int $courseid): bool {
        // This is dictated by what courses are connected to competencies so deleting is not possible via
        // the course set.
        // This might have been called without knowing what type of course set this is. So let the script
        //  carry on, but in case anything needs to know whether it was deleted, return false.
        return false;
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

            default:
                $agg_method = progressinfo::AGGREGATE_ALL;
        }

        $progressinfo = progressinfo::from_data($agg_method, 0, 0, $customdata, $agg_class);

        // Add courses in the course set
        $courses = $this->get_competency_courses();
        if ($courses) {
            foreach ($courses as $course) {
                $progressinfo->add_criteria(parent::get_progressinfo_course_key($course));
            }
        }

        return $progressinfo;
    }
}